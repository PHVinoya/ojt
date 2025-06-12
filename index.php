<?php
session_start();
require_once 'includes/functions.php';

// Require login
requireLogin();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle QR download
if (isset($_GET['download_qr']) && isset($_GET['user_id'])) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT qr_code, name FROM users WHERE id = ?");
    $stmt->execute([$_GET['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        downloadQRCode($user['qr_code'], 'qr_code_' . $user['name']);
    }
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['register'])) {
        $student_id = $_POST['student_id'];
        $name = $_POST['name'];
        $department = $_POST['department'];
        $company = $_POST['company'];
        $admin_code = $_POST['admin_code'] ?? '';
        
        $role = ($admin_code === '000-admin-000') ? 'admin' : 'trainee';
        
        $result = registerUser($student_id, $name, $department, $company, $role);
        
        if ($result['success']) {
            $_SESSION['registration_success'] = $result;
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: index.php');
        exit;
    }
    
    if (isset($_POST['scan_qr'])) {
        $qr_data = $_POST['qr_data'];
        $result = loginUser($qr_data);
        $_SESSION['scan_result'] = $result;
        header('Location: index.php');
        exit;
    }
}

// Handle export
if (isset($_GET['export'])) {
    exportToExcel();
}

$current_page = $_GET['page'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OJT Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">☰</button>
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>OJT System</h2>
                <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
                <small><?= ucfirst($_SESSION['user_role']) ?></small>
            </div>
            <ul class="sidebar-nav">
                <li><a href="?page=dashboard" class="<?= $current_page === 'dashboard' ? 'active' : '' ?>">
                    <span class="icon">📊</span> Dashboard
                </a></li>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li><a href="?page=register" class="<?= $current_page === 'register' ? 'active' : '' ?>">
                    <span class="icon">👤</span> Register Trainee
                </a></li>
                <?php endif; ?>
                <li><a href="?page=scanner" class="<?= $current_page === 'scanner' ? 'active' : '' ?>">
                    <span class="icon">📱</span> QR Scanner
                </a></li>
                <li><a href="?page=logs" class="<?= $current_page === 'logs' ? 'active' : '' ?>">
                    <span class="icon">📋</span> Attendance Logs
                </a></li>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li><a href="?page=manage" class="<?= $current_page === 'manage' ? 'active' : '' ?>">
                    <span class="icon">⚙️</span> Manage Trainees
                </a></li>
                <?php endif; ?>
                <li><a href="?logout=1" style="color: #e74c3c;">
                    <span class="icon">🚪</span> Logout
                </a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
                <h1>
                    <?php
                    switch($current_page) {
                        case 'register': echo 'Register New Trainee'; break;
                        case 'scanner': echo 'QR Code Scanner'; break;
                        case 'logs': echo 'Attendance Logs'; break;
                        case 'manage': echo 'Manage Trainees'; break;
                        default: echo 'Dashboard'; break;
                    }
                    ?>
                </h1>
            </div>

            <?php
            // Display alerts
            if (isset($_SESSION['registration_success'])) {
                $result = $_SESSION['registration_success'];
                echo '<div class="alert alert-success">';
                echo '<h3>Registration Successful!</h3>';
                echo '<p>QR Code generated for: ' . htmlspecialchars($result['qr_data']) . '</p>';
                echo '<div class="qr-display">';
                echo '<img src="' . $result['qr_code'] . '" alt="QR Code">';
                echo '<p><strong>Save this QR code for attendance scanning</strong></p>';
                echo '</div>';
                echo '</div>';
                unset($_SESSION['registration_success']);
            }

            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }

            if (isset($_SESSION['scan_result'])) {
                $result = $_SESSION['scan_result'];
                $class = $result['success'] ? 'alert-success' : 'alert-error';
                echo '<div class="alert ' . $class . '">' . htmlspecialchars($result['message']) . '</div>';
                unset($_SESSION['scan_result']);
            }

            // Page content
            switch($current_page) {
                case 'register':
                    include 'pages/register.php';
                    break;
                case 'scanner':
                    include 'pages/scanner.php';
                    break;
                case 'logs':
                    include 'pages/logs.php';
                    break;
                case 'manage':
                    include 'pages/manage.php';
                    break;
                default:
                    include 'pages/dashboard.php';
                    break;
            }
            ?>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
