<?php
// admin_login.php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, go straight to the dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        // Regenerate session id on login to prevent session fixation
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — Accordionella</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
    :root {
        --blue: #306fa4;
        --blue-dark: #1e4a70;
        --text: #0f172a;
        --text-muted: #64748b;
        --border: #e2e8f0;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #090d16;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    body::before {
        content: '';
        position: fixed; inset: 0;
        background: radial-gradient(circle at 20% 20%, rgba(48,111,164,0.25), transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(79,149,207,0.18), transparent 50%);
        pointer-events: none;
    }
    .login-card {
        position: relative;
        width: 100%; max-width: 380px;
        background: rgba(255,255,255,0.97);
        border-radius: 24px;
        box-shadow: 0 30px 100px rgba(0,0,0,0.5);
        padding: 40px 36px;
        border: 1px solid rgba(255,255,255,0.8);
    }
    .login-card h1 {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        color: var(--text);
        margin-bottom: 6px;
        text-align: center;
    }
    .login-card .subtitle {
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
        margin-bottom: 28px;
    }
    .field-group { margin-bottom: 18px; }
    .field-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 14px 16px;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        font-size: 15px;
        font-family: inherit;
        outline: none;
        transition: all 0.3s ease;
    }
    input:focus { border-color: var(--blue); box-shadow: 0 10px 30px rgba(48,111,164,0.08); }
    .btn {
        width: 100%;
        background: var(--blue);
        color: #fff;
        border: none;
        padding: 15px 24px;
        font-size: 15px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        border-radius: 14px;
        margin-top: 6px;
        transition: all 0.3s ease;
    }
    .btn:hover { background: var(--blue-dark); transform: translateY(-1px); }
    .error-msg {
        background: rgba(220,38,38,0.08);
        color: #b91c1c;
        border: 1px solid rgba(220,38,38,0.2);
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 13px;
        margin-bottom: 18px;
    }
</style>
</head>
<body>
    <div class="login-card">
        <h1>Admin Access</h1>
        <p class="subtitle">Accordionella Bookings Dashboard</p>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="field-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Log In</button>
        </form>
    </div>
</body>
</html>
