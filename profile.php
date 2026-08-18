<?php $pageTitle = "Profile | "; include 'header.php'; ?>

<?php
require 'db.php';

if (!$loggedIn) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName  = trim($_POST['user_name']);
    $newEmail = trim($_POST['email']);
    $newPhone = trim($_POST['phone_number']);
    $currentPassword = $_POST['current_password'];
    $newPassword = trim($_POST['new_password']);

    $userStmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($currentPassword, $currentUser['password'])) {
        $error = "Current password is wrong.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "That doesn't look like a valid email.";
    } else {
        $check = $pdo->prepare("SELECT user_id FROM user WHERE email = ? AND user_id != ?");
        $check->execute([$newEmail, $_SESSION['user_id']]);

        if ($check->fetch()) {
            $error = "That email's already used by another account.";
        } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
            $error = "New password needs to be at least 6 characters.";
        } else {
            if (!empty($newPassword)) {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE user SET user_name = ?, email = ?, phone_number = ?, password = ? WHERE user_id = ?");
                $stmt->execute([$newName, $newEmail, $newPhone, $hashed, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE user SET user_name = ?, email = ?, phone_number = ? WHERE user_id = ?");
                $stmt->execute([$newName, $newEmail, $newPhone, $_SESSION['user_id']]);
            }
            $_SESSION['user_name'] = $newName;
            $success = "Profile updated.";
        }
    }
}

$userStmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);
?>

<section class="auth-section">
  <div class="wrap-narrow">
    <div class="eyebrow">your account</div>
    <h2>Profile</h2>

    <?php if ($error): ?><div class="form-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="form-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST" class="auth-form">
      <label>Name
        <input type="text" name="user_name" value="<?= htmlspecialchars($currentUser['user_name']) ?>" required>
      </label>
      <label>Email
        <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>" required>
      </label>
      <label>Phone number
        <input type="tel" name="phone_number" value="<?= htmlspecialchars($currentUser['phone_number']) ?>">
      </label>
      <label>New password <span style="opacity:0.6;">(leave blank to keep current)</span>
        <input type="password" name="new_password" minlength="6">
      </label>
      <label>Current password <span style="opacity:0.6;">(required to save any change)</span>
        <input type="password" name="current_password" required>
      </label>
      <button type="submit" class="btn" style="width:100%;">Save changes</button>
    </form>
  </div>
</section>

<?php include 'footer.php'; ?>