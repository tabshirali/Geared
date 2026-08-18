<?php $pageTitle = "Reset password | "; include 'header.php'; ?>

<?php
require 'db.php';

$error = "";
$success = "";
$emailFound = false;
$submittedEmail = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedEmail = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT user_id FROM user WHERE email = ?");
    $stmt->execute([$submittedEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = "No account found with that email.";
    } elseif (isset($_POST['new_password'])) {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if (strlen($newPassword) < 6) {
            $error = "Password needs to be at least 6 characters.";
            $emailFound = true;
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Passwords don't match.";
            $emailFound = true;
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            $update->execute([$hashed, $user['user_id']]);
            $success = "Password updated. You can log in now.";
        }
    } else {
        $emailFound = true;
    }
}
?>

<section class="auth-section">
  <div class="wrap-narrow">
    <div class="eyebrow">reset password</div>
    <h2>Forgot your password?</h2>

    <?php if ($error): ?><div class="form-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?>
      <div class="form-success"><?= htmlspecialchars($success) ?> <a href="login.php">log in →</a></div>
    <?php elseif ($emailFound): ?>
      <form method="POST" class="auth-form">
        <input type="hidden" name="email" value="<?= htmlspecialchars($submittedEmail) ?>">
        <label>New password
          <input type="password" name="new_password" required minlength="6">
        </label>
        <label>Confirm new password
          <input type="password" name="confirm_password" required minlength="6">
        </label>
        <button type="submit" class="btn" style="width:100%;">Set new password</button>
      </form>
    <?php else: ?>
      <form method="POST" class="auth-form">
        <label>Email
          <input type="email" name="email" required>
        </label>
        <button type="submit" class="btn" style="width:100%;">Find my account</button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php include 'footer.php'; ?>