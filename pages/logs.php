<?php
$logs = getAttendanceLogs();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <h3 class="card-title">All Attendance Logs</h3>
            <a href="?export=excel" class="btn btn-success">📊 Export to Excel</a>
        </div>
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
                            <td>
                                <span style="color: <?= $log['action'] === 'time_in' ? '#27ae60' : '#e74c3c' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $log['action'])) ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d', strtotime($log['timestamp'])) ?></td>
                            <td><?= date('H:i:s', strtotime($log['timestamp'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
