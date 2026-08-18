<?php $pageTitle = "Sign up | "; include 'header.php'; ?>

<?php
require 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['user_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "That doesn't look like a valid email.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords don't match.";
    } elseif (strlen($password) < 6) {
        $error = "Password needs to be at least 6 characters.";
    } else {
        $check = $pdo->prepare("SELECT user_id FROM user WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = "That email's already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $userId = "U" . substr(uniqid(), -6); // varchar(15), keep it short and safe

            $stmt = $pdo->prepare("INSERT INTO user (user_id, user_name, password, phone_number, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $name, $hashed, $phone, $email]);

            $_SESSION['user_id']   = $userId;
            $_SESSION['user_name'] = $name;
            header("Location: index.php");
            exit;
        }
    }
}
?>

<section class="auth-section">
  <div class="wrap-narrow">
    <div class="eyebrow">join geared.</div>
    <h2>Create your account</h2>

    <?php if ($error): ?>
      <div class="form-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <label>Name
        <input type="text" name="user_name" required>
      </label>
      <label>Email
        <input type="email" name="email" required>
      </label>
      <label>Phone number
        <input type="tel" name="phone_number">
      </label>
      <label>Password
        <input type="password" name="password" required minlength="6">
      </label>
      <label>Confirm password
        <input type="password" name="confirm_password" required minlength="6">
      </label>
      <button type="submit" class="btn" style="width:100%;">Sign up</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
  </div>
</section>

<?php include 'footer.php'; ?>