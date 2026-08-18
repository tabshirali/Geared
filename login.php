<?php $pageTitle = "Log in | "; include 'header.php'; ?>

<?php
require 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Wrong email or password.";
    }
}
?>

<section class="auth-section">
  <div class="wrap-narrow">
    <div class="eyebrow">welcome back</div>
    <h2>Log in to geared.</h2>

    <?php if ($error): ?>
      <div class="form-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <label>Email
        <input type="email" name="email" required>
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <button type="submit" class="btn" style="width:100%;">Log in</button>
    </form>

    <p class="auth-switch">No account yet? <a href="register.php">Sign up</a></p>
    <p class="auth-switch"><a href="forgot-password.php">Forgot your password?</a></p>
  </div>
</section>

<?php include 'footer.php'; ?>