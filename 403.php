<?php http_response_code(403); ?>
<!DOCTYPE html>
<html><head><title>403 Forbidden</title></head>
<body style="font-family:sans-serif; text-align:center; padding:80px 20px;">
    <h1>403 — Forbidden</h1>
    <p>You do not have permission to access this page.</p>
    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>">Go Home</a>
</body>
</html>
