<?php
$users = getAllUsers();

// Handle user actions
if ($_POST) {
    if (isset($_POST['toggle_status'])) {
        $user_id = $_POST['user_id'];
        $db = new Database();
        $pdo = $db->getConnection();
        
        $stmt = $pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id = ?");
        $stmt->execute([$user_id]);
        
        header('Location: ?page=manage');
        exit;
    }
    
    if (isset($_POST['regenerate_qr'])) {
        $user_id = $_POST['user_id'];
        $db = new Database();
        $pdo = $db->getConnection();
        
        // Get user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $qr_data = $user['student_id'] . '|' . $user['name'] . '|' . time();
            $stmt = $pdo->prepare("UPDATE users SET qr_code = ? WHERE id = ?");
            $stmt->execute([$qr_data, $user_id]);
            
            $_SESSION['qr_regenerated'] = [
                'user' => $user,
                'qr_code' => generateQRCode($qr_data),
                'qr_data' => $qr_data
            ];
        }
        
        header('Location: ?page=manage');
        exit;
    }
}

if (isset($_SESSION['qr_regenerated'])) {
    $result = $_SESSION['qr_regenerated'];
    echo '<div class="alert alert-success">';
    echo '<h3>QR Code Regenerated!</h3>';
    echo '<p>New QR Code for: ' . htmlspecialchars($result['user']['name']) . '</p>';
    echo '<div class="qr-display">';
    echo '<img src="' . $result['qr_code'] . '" alt="QR Code">';
    echo '<p><strong>New QR Code Data: ' . htmlspecialchars($result['qr_data']) . '</strong></p>';
    echo '</div>';
    echo '</div>';
    unset($_SESSION['qr_regenerated']);
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Manage Trainees</h3>
    </div>
    
    <?php if (empty($users)): ?>
        <p>No trainees registered yet.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Phone</th>
                        <th>School</th>
                        <th>Course</th>
                        <th>Department</th>
                        <th>Agency</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['student_id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['age'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($user['school'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($user['course'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($user['department']) ?></td>
                            <td><?= htmlspecialchars($user['agency']) ?></td>
                            <td>
                                <span style="color: <?= $user['role'] === 'admin' ? '#e74c3c' : '#3498db' ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: <?= $user['is_active'] ? '#27ae60' : '#e74c3c' ?>">
                                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                            <td>
                                <a href="?download_qr=1&user_id=<?= $user['id'] ?>" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.5rem;">
                                    📥 QR
                                </a>
                                <form method="POST" style="display: inline-block; margin-right: 0.5rem;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="toggle_status" class="btn <?= $user['is_active'] ? 'btn-danger' : 'btn-success' ?>" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="regenerate_qr" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        New QR
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
