<?php
require_once 'config/database.php';

function generateQRCode($data) {
    // Simple QR code generation using Google Charts API
    $size = '200x200';
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data=" . urlencode($data);
    return $qr_url;
}

function generateNextStudentId() {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get the highest student_id number
    $stmt = $pdo->query("SELECT student_id FROM users ORDER BY CAST(student_id AS INTEGER) DESC LIMIT 1");
    $lastId = $stmt->fetchColumn();
    
    if ($lastId) {
        $nextNumber = intval($lastId) + 1;
    } else {
        $nextNumber = 1;
    }
    
    return str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
}

function registerUser($username, $name, $age, $phone, $school, $course, $department, $agency, $password, $role = 'trainee') {
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        // Generate auto-increment student ID
        $student_id = generateNextStudentId();
        
        // Generate unique QR code data
        $qr_data = generateUniqueQRData($student_id, $name);
        $qr_code = generateQRCode($qr_data);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (student_id, username, name, age, phone, school, course, department, agency, password, role, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$student_id, $username, $name, $age, $phone, $school, $course, $department, $agency, $hashed_password, $role, $qr_data]);
        
        if ($result) {
            return ['success' => true, 'qr_code' => $qr_code, 'qr_data' => $qr_data, 'user_id' => $pdo->lastInsertId(), 'student_id' => $student_id];
        }
        return ['success' => false, 'message' => 'Registration failed'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

function loginUser($qr_data) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE qr_code = ? AND is_active = 1");
    $stmt->execute([$qr_data]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Check last action to determine if this is time in or time out
        $stmt = $pdo->prepare("SELECT action FROM attendance_logs WHERE user_id = ? ORDER BY timestamp DESC LIMIT 1");
        $stmt->execute([$user['id']]);
        $last_action = $stmt->fetchColumn();
        
        $action = ($last_action === 'time_in') ? 'time_out' : 'time_in';
        
        // Log the attendance
        $stmt = $pdo->prepare("INSERT INTO attendance_logs (user_id, action) VALUES (?, ?)");
        $stmt->execute([$user['id'], $action]);
        
        return [
            'success' => true,
            'user' => $user,
            'action' => $action,
            'message' => ucfirst(str_replace('_', ' ', $action)) . ' successful!'
        ];
    }
    
    return ['success' => false, 'message' => 'Invalid QR code'];
}

function getAttendanceLogs($limit = null) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $sql = "SELECT u.name, u.student_id, u.department, u.agency, a.action, a.timestamp 
            FROM attendance_logs a 
            JOIN users u ON a.user_id = u.id 
            ORDER BY a.timestamp DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCurrentlyLoggedIn() {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $sql = "SELECT u.name, u.student_id, u.department, u.agency, 
                   MAX(a.timestamp) as last_action_time,
                   (SELECT action FROM attendance_logs WHERE user_id = u.id ORDER BY timestamp DESC LIMIT 1) as last_action
            FROM users u 
            JOIN attendance_logs a ON u.id = a.user_id 
            WHERE u.is_active = 1
            GROUP BY u.id
            HAVING last_action = 'time_in'";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllUsers() {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function exportToExcel() {
    $logs = getAttendanceLogs();
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="attendance_logs.xls"');
    header('Cache-Control: max-age=0');
    
    echo "<table border='1'>";
    echo "<tr><th>Name</th><th>Student ID</th><th>Department</th><th>Company</th><th>Action</th><th>Date</th><th>Time</th></tr>";
    
    foreach ($logs as $log) {
        $datetime = new DateTime($log['timestamp']);
        echo "<tr>";
        echo "<td>" . htmlspecialchars($log['name']) . "</td>";
        echo "<td>" . htmlspecialchars($log['student_id']) . "</td>";
        echo "<td>" . htmlspecialchars($log['department']) . "</td>";
        echo "<td>" . htmlspecialchars($log['company']) . "</td>";
        echo "<td>" . ucfirst(str_replace('_', ' ', $log['action'])) . "</td>";
        echo "<td>" . $datetime->format('Y-m-d') . "</td>";
        echo "<td>" . $datetime->format('H:i:s') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

function authenticateUser($username, $password) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function generateUniqueQRData($student_id, $name) {
    return hash('sha256', $student_id . $name . time() . rand(1000, 9999));
}

function downloadQRCode($qr_data, $filename) {
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=png&data=" . urlencode($qr_data);
    
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="' . $filename . '.png"');
    header('Cache-Control: max-age=0');
    
    $image = file_get_contents($qr_url);
    echo $image;
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>
