<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . "geared." : "geared. | borrowed with care" ?></title>
<link rel="stylesheet" href="styles.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>

<nav>
  <div class="wrap">
    <a href="index.php" class="logo">gear<span>ed</span></a>
    <div class="nav-links">
      <a href="catalogue.php">Browse</a>
      <?php if ($loggedIn): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="messages.php">Messages<?php
          require_once 'db.php';
          $unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM message WHERE receiver_id = ? AND is_read = 'no'");
          $unreadStmt->execute([$_SESSION['user_id']]);
          $unread = $unreadStmt->fetchColumn();
          if ($unread > 0) echo " <span class=\"badge-count\">$unread</span>";
        ?></a>
        <a href="add-item.php" class="btn">List an item</a>
        <a href="profile.php" class="nav-user mono">hi, <?= htmlspecialchars($_SESSION['user_name']) ?></a>
        <a href="logout.php" class="btn btn-ghost">Log out</a>
            <?php else: ?>
        <a href="login.php" class="btn btn-ghost">Log in</a>
        <a href="register.php" class="btn">Sign up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>