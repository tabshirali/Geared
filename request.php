<?php $pageTitle = "Request item | "; include 'header.php'; ?>

<?php
require 'db.php';

$itemId = $_GET['item_id'] ?? '';

$stmt = $pdo->prepare("
    SELECT i.*, u.user_name
    FROM item i
    JOIN user u ON i.user_id = u.user_id
    WHERE i.item_id = ?
");
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

$error = "";
$success = "";

if (!$item) {
    $error = "Item not found.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {

    if (!$loggedIn) {
        header("Location: login.php");
        exit;
    }

    if ($item['user_id'] === $_SESSION['user_id']) {
        $error = "You can't request your own item.";
    } elseif (strtolower($item['availability']) !== 'yes') {
        $error = "This item isn't available right now.";
    } else {

        /*
         * There is intentionally no check for a previous request here.
         * The same user can request the same item again after a previous
         * order has been completed.
         */
        $requestId = "R" . substr(uniqid(), -4);

        $stmt = $pdo->prepare("
            INSERT INTO borrow_request
            (request_id, item_id, requester_id, request_date, status)
            VALUES (?, ?, ?, CURDATE(), 'pending')
        ");

        $stmt->execute([
            $requestId,
            $itemId,
            $_SESSION['user_id']
        ]);

        $success = "Request sent! Check your dashboard for updates.";
    }
}
?>

<section class="auth-section">
  <div class="wrap-narrow">

    <?php if ($item): ?>

      <div class="eyebrow">
        ITEM #<?= htmlspecialchars($item['item_id']) ?>
      </div>

      <h2><?= htmlspecialchars($item['item_title']) ?></h2>

      <p style="color:var(--ink-dim); margin-bottom:8px;">
        <?= htmlspecialchars($item['description']) ?>
      </p>

      <p style="color:var(--ink-dim); margin-bottom:24px;">
        Listed by <?= htmlspecialchars($item['user_name']) ?>
        · BDT <?= htmlspecialchars($item['price']) ?> / day

        <?php if ($loggedIn && $item['user_id'] !== $_SESSION['user_id']): ?>

          ·
          <a
            href="messages.php?with=<?= urlencode($item['user_id']) ?>&item=<?= urlencode($item['item_id']) ?>"
            style="color:var(--beige);"
          >
            message owner
          </a>

        <?php endif; ?>
      </p>

    <?php endif; ?>

    <?php if ($error): ?>

      <div class="form-error">
        <?= htmlspecialchars($error) ?>
      </div>

    <?php endif; ?>

    <?php if ($success): ?>

      <div class="form-success">
        <?= htmlspecialchars($success) ?>

        <a href="dashboard.php">
          go to dashboard →
        </a>
      </div>

    <?php elseif ($item && strtolower($item['availability']) === 'yes'): ?>

      <?php if (!$loggedIn): ?>

        <p style="margin-bottom:16px;">
          <a href="login.php" style="color:var(--beige);">
            Log in
          </a>
          to request this item.
        </p>

      <?php elseif ($item['user_id'] !== $_SESSION['user_id']): ?>

        <form method="POST" class="auth-form">

          <button
            type="submit"
            name="submit_request"
            class="btn"
            style="width:100%;"
          >
            Request to borrow
          </button>

        </form>

      <?php endif; ?>

    <?php elseif ($item): ?>

      <p style="color:var(--ink-dim);">
        This item is currently checked out.
      </p>

    <?php endif; ?>

  </div>
</section>

<?php include 'footer.php'; ?>