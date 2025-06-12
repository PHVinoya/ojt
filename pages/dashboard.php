<?php
$logs = getAttendanceLogs(10);
$currently_logged_in = getCurrentlyLoggedIn();
$all_users = getAllUsers();

$total_logs = count(getAttendanceLogs());
$total_trainees = count($all_users);
$currently_in = count($currently_logged_in);
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $total_logs ?></div>
        <div class="stat-label">Total Logs</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $currently_in ?></div>
        <div class="stat-label">Currently Logged In</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $total_trainees ?></div>
        <div class="stat-label">Total Trainees</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Currently Logged In</h3>
    </div>
    <?php if (empty($currently_logged_in)): ?>
        <p>No trainees currently logged in.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Department</th>
                        <th>Agency</th>
                        <th>Time In</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currently_logged_in as $trainee): ?>
                        <tr>
                            <td><?= htmlspecialchars($trainee['name']) ?></td>
                            <td><?= htmlspecialchars($trainee['student_id']) ?></td>
                            <td><?= htmlspecialchars($trainee['department']) ?></td>
                            <td><?= htmlspecialchars($trainee['agency']) ?></td>
                            <td><?= date('H:i:s', strtotime($trainee['last_action_time'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Activity</h3>
    </div>
    <?php if (empty($logs)): ?>
        <p>No attendance logs found.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Department</th>
                        <th>Agency</th>
                        <th>Action</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['name']) ?></td>
                            <td><?= htmlspecialchars($log['student_id']) ?></td>
                            <td><?= htmlspecialchars($log['department']) ?></td>
                            <td><?= htmlspecialchars($log['agency']) ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', $log['action'])) ?></td>
                            <td><?= date('Y-m-d', strtotime($log['timestamp'])) ?></td>
                            <td><?= date('H:i:s', strtotime($log['timestamp'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
