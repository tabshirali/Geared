<?php $pageTitle = "Messages | "; include 'header.php'; ?>

<?php
require_once 'db.php';

if (!$loggedIn) {
    header("Location: login.php");
    exit;
}
$myId = $_SESSION['user_id'];
$withUser = $_GET['with'] ?? '';
$itemContext = $_GET['item'] ?? '';

if ($withUser) {
    // mark their messages to me as read
    $pdo->prepare("UPDATE message SET is_read = 'yes' WHERE sender_id = ? AND receiver_id = ?")->execute([$withUser, $myId]);

    $stmt = $pdo->prepare("SELECT * FROM message WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY sent_date ASC");
    $stmt->execute([$myId, $withUser, $withUser, $myId]);
    $thread = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $userStmt = $pdo->prepare("SELECT user_name FROM user WHERE user_id = ?");
    $userStmt->execute([$withUser]);
    $partnerName = $userStmt->fetchColumn();
} else {
    $stmt = $pdo->prepare("SELECT * FROM message WHERE sender_id = ? OR receiver_id = ? ORDER BY sent_date DESC");
    $stmt->execute([$myId, $myId]);
    $allMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $conversations = [];
    foreach ($allMessages as $m) {
        $partnerId = ($m['sender_id'] === $myId) ? $m['receiver_id'] : $m['sender_id'];
        if (!isset($conversations[$partnerId])) {
            $conversations[$partnerId] = $m;
        }
    }
}
?>

<section class="catalog">
  <div class="wrap" style="max-width: 760px;">

    <?php if ($withUser): ?>
      <div class="section-head">
        <a href="messages.php" class="mono" style="font-size:12px; color:var(--beige-dim);">← all messages</a>
        <h2 style="margin-top:14px;"><?= htmlspecialchars($partnerName ?: 'Unknown user') ?></h2>
      </div>

      <div class="thread">
        <?php foreach ($thread as $m): ?>
          <div class="msg-bubble <?= $m['sender_id'] === $myId ? 'mine' : '' ?>">
            <p><?= htmlspecialchars($m['content']) ?></p>
            <span class="mono"><?= date('M j, g:ia', strtotime($m['sent_date'])) ?></span>
          </div>
        <?php endforeach; ?>
        <?php if (count($thread) === 0): ?>
          <p class="empty-note">No messages yet. Say hi.</p>
        <?php endif; ?>
      </div>

      <form method="POST" action="actions.php" class="msg-form">
        <input type="hidden" name="type" value="send_message">
        <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($withUser) ?>">
        <input type="hidden" name="item_id" value="<?= htmlspecialchars($itemContext) ?>">
        <input type="text" name="content" placeholder="Type a message..." required maxlength="500">
        <button type="submit" class="btn">Send</button>
      </form>

    <?php else: ?>
      <div class="section-head">
        <div class="eyebrow">inbox</div>
        <h2>Your messages</h2>
      </div>

      <?php if (count($conversations) === 0): ?>
        <p class="empty-note">No conversations yet. Message someone from an item page.</p>
      <?php else: ?>
        <?php foreach ($conversations as $partnerId => $m): ?>
          <?php
            $nameStmt = $pdo->prepare("SELECT user_name FROM user WHERE user_id = ?");
            $nameStmt->execute([$partnerId]);
            $partnerName = $nameStmt->fetchColumn();
          ?>
          <a href="messages.php?with=<?= urlencode($partnerId) ?>" class="dash-row" style="text-decoration:none;">
            <div>
              <div class="dash-row-title"><?= htmlspecialchars($partnerName ?: 'Unknown user') ?></div>
              <div class="dash-row-sub"><?= htmlspecialchars(substr($m['content'], 0, 60)) ?></div>
            </div>
            <?php if ($m['receiver_id'] === $myId && $m['is_read'] === 'no'): ?>
              <span class="badge-count">new</span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</section>

<?php include 'footer.php'; ?>