<?php
// admin_login.php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, go straight to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim(strtolower($_POST['username'] ?? $_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Please enter your email/username and password.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role FROM users WHERE LOWER(email) = :l OR LOWER(username) = :l LIMIT 1");
            $stmt->execute([':l' => $login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['role'] === 'admin') {
                    session_regenerate_id(true);
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id']        = $user['id'];
                    $_SESSION['admin_username']  = $user['username'] ?: $user['email'];
                    header('Location: admin.php');
                    exit;
                } else {
                    $error = 'Access denied. This account does not have admin privileges.';
                }
            } else {
                $error = 'Invalid email/username or password.';
            }
        } catch (Exception $e) {
            $error = 'Authentication error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Portal — Accordionella</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --blue: #306fa4;
        --blue-dark: #1e4a70;
        --blue-light: #4f95cf;
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
        color: #fff;
    }
    body::before {
        content: '';
        position: fixed; inset: 0;
        background: radial-gradient(circle at 20% 20%, rgba(48,111,164,0.3), transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(79,149,207,0.2), transparent 50%);
        pointer-events: none;
    }
    .login-card {
        position: relative;
        width: 100%; max-width: 420px;
        background: rgba(255,255,255,0.96);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        box-shadow: 0 30px 100px rgba(0,0,0,0.6);
        padding: 44px 38px;
        border: 1px solid rgba(255,255,255,0.4);
        color: var(--text);
    }
    .toggle-pills {
        display: flex;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 100px;
        margin-bottom: 28px;
    }
    .toggle-pill {
        flex: 1;
        text-align: center;
        padding: 9px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-muted);
        border-radius: 100px;
        text-decoration: none;
        transition: all 0.25s ease;
    }
    .toggle-pill.active {
        background: var(--blue);
        color: #fff;
        box-shadow: 0 4px 12px rgba(48,111,164,0.3);
    }

    .brand-header {
        text-align: center;
        margin-bottom: 28px;
    }
    .brand-logo {
        width: 56px; height: 56px;
        margin: 0 auto 12px;
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 10px 24px rgba(48,111,164,0.3);
    }
    .brand-logo svg { width: 28px; height: 28px; stroke: #fff; fill: none; stroke-width: 2; }
    .brand-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 26px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
    }
    .brand-header .subtitle {
        color: var(--text-muted);
        font-size: 13px;
    }
    .field-group { margin-bottom: 20px; }
    .field-group label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-wrapper svg {
        position: absolute;
        left: 16px;
        width: 18px; height: 18px;
        stroke: var(--text-muted);
        fill: none; stroke-width: 2;
        transition: stroke 0.2s ease;
    }
    input[type="text"], input[type="email"], input[type="password"] {
        width: 100%;
        padding: 14px 16px 14px 46px;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        font-size: 14.5px;
        font-family: inherit;
        outline: none;
        background: #fff;
        color: var(--text);
        transition: all 0.25s ease;
    }
    input:focus {
        border-color: var(--blue);
        box-shadow: 0 8px 24px rgba(48,111,164,0.12);
    }
    input:focus + svg, .input-wrapper:focus-within svg {
        stroke: var(--blue);
    }
    .btn {
        width: 100%;
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: #fff;
        border: none;
        padding: 16px 24px;
        font-size: 15px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        border-radius: 16px;
        margin-top: 8px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 10px 24px rgba(48,111,164,0.3);
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(48,111,164,0.4);
    }
    .btn svg { width: 18px; height: 18px; stroke: #fff; fill: none; stroke-width: 2.2; }
    .error-msg {
        background: rgba(220,38,38,0.08);
        color: #b91c1c;
        border: 1px solid rgba(220,38,38,0.2);
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 13.5px;
        margin-bottom: 22px;
        display: flex; align-items: center; gap: 10px;
    }
    .error-msg svg { width: 18px; height: 18px; stroke: #b91c1c; fill: none; stroke-width: 2; shrink: 0; }
    .back-home {
        text-align: center;
        margin-top: 24px;
    }
    .back-home a {
        color: var(--text-muted);
        font-size: 13px;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }
    .back-home a:hover { color: var(--blue); }
</style>
</head>
<body>
    <div class="login-card">
        
        <!-- Toggle Pill between Client and Admin Portal -->
        <div class="toggle-pills">
            <a href="index.html" class="toggle-pill">Client Portal</a>
            <a href="admin_login.php" class="toggle-pill active">Admin Portal</a>
        </div>

        <div class="brand-header">
            <div class="brand-logo">
                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </div>
            <h1>Admin Access</h1>
            <p class="subtitle">Accordionella Management System</p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="field-group">
                <label>Admin Email or Username</label>
                <div class="input-wrapper">
                    <input type="text" name="username" required autofocus placeholder="admin@accordionella.com">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
            </div>
            <div class="field-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" required placeholder="Enter password">
                    <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
            </div>
            <button type="submit" class="btn">
                <span>Log In to Dashboard</span>
                <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </form>

        <div class="back-home">
            <a href="index.html">&larr; Return to Accordionella Main Website</a>
        </div>
    </div>
</body>
</html>
