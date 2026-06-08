<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('/' . $_SESSION['user_role'] . '/dashboard.php');
}

$pdo = Database::getConnection();
$faculties = $pdo->query("SELECT id, name FROM faculties ORDER BY name")->fetchAll();
$levels    = $pdo->query("SELECT name FROM levels ORDER BY id")->fetchAll();

$pageTitle = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-mark">W</div>
            <h1>Create Account</h1>
            <p>Join the WBDLS learning community</p>
        </div>

        <?php renderFlash(); ?>

        <form method="post" action="<?php echo BASE_URL; ?>/actions/auth/register.php">
            <?php echo csrfField(); ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-input" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-input" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group password-toggle">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required minlength="6">
                <button type="button" class="toggle-btn">Show</button>
            </div>
            <div class="form-group password-toggle">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-input" required minlength="6">
                <button type="button" class="toggle-btn">Show</button>
            </div>
            <div class="form-group">
                <label class="form-label">I am a:</label>
                <div class="role-selector">
                    <label>
                        <input type="radio" name="role" value="student" checked>
                        <span>🎓 Student</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="instructor">
                        <span>👨‍🏫 Instructor</span>
                    </label>
                </div>
            </div>
            <div class="student-fields" id="studentFields" style="display:none;">
                <div class="form-group">
                    <label class="form-label">Faculty</label>
                    <select name="faculty_id" id="facultySelect" class="form-input">
                        <option value="">Select Faculty</option>
                        <?php foreach ($faculties as $f): ?>
                            <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select name="department_id" id="departmentSelect" class="form-input" disabled>
                        <option value="">Select Faculty First</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Level</label>
                    <select name="student_level" class="form-input">
                        <option value="">Select Level</option>
                        <?php foreach ($levels as $l): ?>
                            <option value="<?php echo htmlspecialchars($l['name']); ?>"><?php echo htmlspecialchars($l['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="<?php echo BASE_URL; ?>/login.php">Login</a>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var roleRadios = document.querySelectorAll('input[name="role"]');
    var studentFields = document.getElementById('studentFields');
    var facultySelect = document.getElementById('facultySelect');
    var departmentSelect = document.getElementById('departmentSelect');

    function toggleStudentFields() {
        var isStudent = document.querySelector('input[name="role"]:checked').value === 'student';
        studentFields.style.display = isStudent ? 'block' : 'none';
        var selects = studentFields.querySelectorAll('select');
        for (var i = 0; i < selects.length; i++) {
            selects[i].disabled = !isStudent;
        }
    }

    for (var i = 0; i < roleRadios.length; i++) {
        roleRadios[i].addEventListener('change', toggleStudentFields);
    }
    toggleStudentFields();

    facultySelect.addEventListener('change', function () {
        var fid = this.value;
        if (!fid) {
            departmentSelect.innerHTML = '<option value="">Select Faculty First</option>';
            departmentSelect.disabled = true;
            return;
        }
        departmentSelect.disabled = true;
        departmentSelect.innerHTML = '<option value="">Loading...</option>';

        fetch('<?php echo BASE_URL; ?>/actions/auth/get_departments.php?faculty_id=' + fid)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                departmentSelect.innerHTML = '<option value="">Select Department</option>';
                for (var j = 0; j < data.length; j++) {
                    departmentSelect.innerHTML += '<option value="' + data[j].id + '">' + data[j].name + '</option>';
                }
                departmentSelect.disabled = false;
            })
            .catch(function () {
                departmentSelect.innerHTML = '<option value="">Error loading departments</option>';
            });
    });
});
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
