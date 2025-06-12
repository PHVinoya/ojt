<div class="card">
    <div class="card-header">
        <h3 class="card-title">Register New Trainee</h3>
    </div>
    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label" for="student_id">Student ID</label>
            <input type="text" class="form-control" id="student_id" name="student_id" required>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="name">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="department">Department</label>
            <input type="text" class="form-control" id="department" name="department" required>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="company">Company</label>
            <input type="text" class="form-control" id="company" name="company" required>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="admin_code">Admin Code (Optional - for admin registration)</label>
            <input type="password" class="form-control" id="admin_code" name="admin_code" placeholder="Enter 000-admin-000 for admin access">
            <small style="color: #666; font-size: 0.9rem;">Leave empty for trainee registration</small>
        </div>
        
        <button type="submit" name="register" class="btn btn-primary">Register & Generate QR Code</button>
    </form>
</div>
