<?php
session_start();
include 'config/db.php';

// အကယ်၍ login ဝင်ထားပြီးသားဆိုရင် dashboard ကို တန်းပို့မယ်
if(isset($_SESSION['admin_logged'])) {
    header("Location: admin.php");
    exit;
}

if(isset($_POST['login'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    // အလွယ်တကူ username: admin / password: admin123 လို့ သတ်မှတ်ထားပါတယ်
    if($user == "admin" && $pass == "admin123") {
        $_SESSION['admin_logged'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Username သို့မဟုတ် Password မှားနေပါသည်!";
    }
}
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-primary { background: #6366f1; border: none; padding: 12px; border-radius: 8px; }
        .btn-primary:hover { background: #4f46e5; }
    </style>
</head>
<body>

<div class="card login-card p-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark">Smart Admin</h3>
        <p class="text-muted">စနစ်အတွင်းသို့ ဝင်ရောက်ပါ</p>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger py-2 small text-center"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="user" class="form-control" placeholder="admin" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="pass" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100 fw-bold">Login Now</button>
    </form>
    
    <div class="text-center mt-4">
        <a href="index.php" class="text-decoration-none small text-muted">← Back to Menu</a>
    </div>
</div>

</body>
</html>