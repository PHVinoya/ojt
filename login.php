<?php
session_start();
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle login
if ($_POST && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $user = authenticateUser($username, $password);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}

// Handle registration
if ($_POST && isset($_POST['register'])) {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $age = $_POST['age'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $school = $_POST['school'] ?? null;
    $course = $_POST['course'] ?? null;
    $department = $_POST['department'];
    $agency = $_POST['agency'] ?? 'SSS Dagupan';
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $admin_code = $_POST['admin_code'] ?? '';
    
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif ($role === 'admin' && $admin_code !== '000-admin-000') {
        $error = 'Invalid admin code';
    } else {
        $result = registerUser($username, $name, $age, $phone, $school, $course, $department, $agency, $password, $role);
        if ($result['success']) {
            $_SESSION['registration_success'] = $result;
            $success = 'Registration successful! Your ID is: ' . $result['student_id'];
        } else {
            $error = $result['message'];
        }
    }
}

// Handle QR download
if (isset($_GET['download_qr']) && isset($_SESSION['registration_success'])) {
    $result = $_SESSION['registration_success'];
    downloadQRCode($result['qr_data'], 'qr_code_' . $result['student_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OJT Attendance System - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>OJT Attendance System</h1>
                <p>Please login to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['registration_success'])): ?>
                <?php $result = $_SESSION['registration_success']; ?>
                <div class="alert alert-success">
                    <h3>Registration Successful!</h3>
                    <p><strong>Your ID:</strong> <?= $result['student_id'] ?></p>
                    <p>Your account has been created. Here's your QR code:</p>
                    <div class="qr-display">
                        <img src="<?= $result['qr_code'] ?>" alt="QR Code">
                        <div class="qr-actions">
                            <a href="?download_qr=1" class="btn btn-primary">📥 Download QR Code</a>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['registration_success']); ?>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary btn-full">Login</button>
            </form>

            <div class="login-footer">
                <p>Don't have an account?</p>
                <button class="btn btn-outline" onclick="openRegisterModal()">Register Now</button>
            </div>
        </div>
    </div>

    <!-- Registration Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Choose Registration Type</h2>
                <span class="close" onclick="closeRegisterModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="registration-options">
                    <button class="option-btn trainee-btn" onclick="showRegistrationForm('trainee')">
                        <div class="option-icon">👤</div>
                        <h3>Register as Trainee</h3>
                        <p>For OJT students</p>
                    </button>
                    <button class="option-btn admin-btn" onclick="showRegistrationForm('admin')">
                        <div class="option-icon">⚙️</div>
                        <h3>Register as Admin</h3>
                        <p>For supervisors and managers</p>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Trainee Registration Form Modal -->
    <div id="traineeFormModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Register as Trainee</h2>
                <span class="close" onclick="closeRegistrationForm()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" class="registration-form">
                    <input type="hidden" name="role" value="trainee">
                    <input type="hidden" name="agency" value="SSS Dagupan">
                    
                    <div class="form-group">
                        <label for="trainee_name">Full Name</label>
                        <input type="text" id="trainee_name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="trainee_username">Username</label>
                        <input type="text" id="trainee_username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="trainee_age">Age</label>
                        <input type="number" id="trainee_age" name="age" min="16" max="50" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="trainee_phone">Phone Number</label>
                        <input type="tel" id="trainee_phone" name="phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="trainee_school">School</label>
                        <input type="text" id="trainee_school" name="school" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="trainee_course">Course</label>
                        <select id="trainee_course" name="course" required>
                            <option value="">Select Course</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Business Administration">Business Administration</option>
                            <option value="Accounting">Accounting</option>
                            <option value="Human Resource Management">Human Resource Management</option>
                            <option value="Public Administration">Public Administration</option>
                            <option value="Finance">Finance</option>
                            <option value="Economics">Economics</option>
                            <option value="Social Work">Social Work</option>
                            <option value="Management Information Systems">Management Information Systems</option>
                            <option value="Office Administration">Office Administration</option>
                            <option value="Banking and Finance">Banking and Finance</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="trainee_department">Department</label>
                        <select id="trainee_department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="E-CENTER">E-CENTER</option>
                            <option value="AMS">AMS</option>
                            <option value="MSS">MSS</option>
                            <option value="ADMIN">ADMIN</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="trainee_password">Password</label>
                        <input type="password" id="trainee_password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="trainee_confirm_password">Confirm Password</label>
                        <input type="password" id="trainee_confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeRegistrationForm()">Cancel</button>
                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Admin Registration Form Modal -->
    <div id="adminFormModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Register as Admin</h2>
                <span class="close" onclick="closeRegistrationForm()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" class="registration-form">
                    <input type="hidden" name="role" value="admin">
                    <input type="hidden" name="department" value="ADMIN">
                    <input type="hidden" name="agency" value="SSS Dagupan">
                    
                    <div class="form-group">
                        <label for="admin_id_display">Admin ID</label>
                        <input type="text" id="admin_id_display" value="Auto-generated" disabled class="form-control-disabled">
                        <small>Your ID will be automatically assigned</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_name">Full Name</label>
                        <input type="text" id="admin_name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="admin_username">Username</label>
                        <input type="text" id="admin_username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_department_display">Department</label>
                        <input type="text" id="admin_department_display" value="ADMIN" disabled class="form-control-disabled">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_agency_display">Agency</label>
                        <input type="text" id="admin_agency_display" value="SSS Dagupan" disabled class="form-control-disabled">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password">Password</label>
                        <input type="password" id="admin_password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_confirm_password">Confirm Password</label>
                        <input type="password" id="admin_confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_code">Admin Code</label>
                        <input type="password" id="admin_code" name="admin_code" required>
                        <small>Required for admin registration</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeRegistrationForm()">Cancel</button>
                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>
