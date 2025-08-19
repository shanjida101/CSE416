<?php
require 'config.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check email format and domain
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, "@diu.edu.bd")) {
        $errors[] = "Email must be a valid DIU email (example@diu.edu.bd).";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['pass'])) {
            $_SESSION['user'] = [
                'serial' => $user['serial'],
                'name' => $user['name'],
                'email' => $user['email']
            ];
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 420px;
        margin: 60px auto;
        background: #ffffff;
        padding: 25px 30px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        animation: fadeIn 0.4s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #111;
        letter-spacing: 0.5px;
    }

    input {
        width: 100%;
        padding: 12px;
        margin: 8px 0 15px;
        border: 1px solid #bbb;
        border-radius: 8px;
        font-size: 14px;
        background: #fafafa;
        transition: all 0.3s ease;
    }

    input:focus {
        border-color: #000;
        box-shadow: 0 0 5px rgba(0,0,0,0.25);
        background: #fff;
        outline: none;
    }

    button {
        width: 100%;
        padding: 12px;
        background: #000;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.2s ease;
    }

    button:hover {
        background: #333;
        transform: translateY(-2px);
    }

    .error {
        background: #fbeaea;
        color: #b71c1c;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 8px;
        font-size: 14px;
        border: 1px solid #f5c6cb;
    }

    .success {
        background: #e9f7ef;
        color: #1b5e20;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 8px;
        font-size: 14px;
        border: 1px solid #c3e6cb;
    }

    a {
        text-decoration: none;
        display: block;
        text-align: center;
        margin-top: 12px;
        font-size: 14px;
        color: #000;
        transition: color 0.3s ease;
    }

    a:hover {
        color: #555;
    }
</style>
</head>
<body>
<div class="container">
<h2>Login</h2>
<?php if (isset($_GET['registered'])): ?>
<div class="success">Registration successful. Please login.</div>
<?php endif; ?>
<?php if ($errors): ?>
<div class="error"><?= implode("<br>", $errors) ?></div>
<?php endif; ?>
<form method="POST">
    <input type="email" name="email" placeholder="DIU Email (example@diu.edu.bd)" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
</div>
</body>
</html>
