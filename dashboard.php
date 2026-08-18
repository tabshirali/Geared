<?php $pageTitle = "Dashboard | "; include 'header.php'; ?>

<?php
require 'db.php';

if (!$loggedIn) {
    header("Location: login.php");
    exit;
}

$myId = $_SESSION['user_id'];

$reviewError = "";
$reviewSuccess = "";

/* handle review submission from order history */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $borrowId = $_POST['borrow_id'] ?? '';
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    $stmt = $pdo->prepare("
        SELECT b.*, i.item_title
        FROM borrowing b
        JOIN item i ON b.item_id = i.item_id
        WHERE b.borrow_id = ?
          AND b.borrower_id = ?
          AND b.status = 'returned'
    ");
    $stmt->execute([$borrowId, $myId]);
    $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$borrow) {
        $reviewError = "You can only review items from your own returned orders.";
    } elseif ($rating < 1 || $rating > 5) {
        $reviewError = "Pick a rating between 1 and 5.";
    } else {
        $alreadyReviewed = $pdo->prepare("
            SELECT COUNT(*)
            FROM review
            WHERE borrow_id = ?
              AND reviewer_id = ?
        ");
        $alreadyReviewed->execute([$borrowId, $myId]);

        if ($alreadyReviewed->fetchColumn() > 0) {
            $reviewError = "You've already reviewed this order.";
        } else {
            $reviewId = "RV" . substr(uniqid(), -3);

            $stmt = $pdo->prepare("
                INSERT INTO review
                (review_id, borrow_id, item_id, reviewer_id, rating, comment, review_date)
                VALUES (?, ?, ?, ?, ?, ?, CURDATE())
            ");

            $stmt->execute([
                $reviewId,
                $borrowId,
                $borrow['item_id'],
                $myId,
                $rating,
                $comment
            ]);

            $reviewSuccess = "Review posted.";
        }
    }
}

$incoming = $pdo->prepare("
    SELECT br.*, i.item_title
    FROM borrow_request br
    JOIN item i ON br.item_id = i.item_id
    WHERE i.user_id = ?
      AND br.status = 'pending'
    ORDER BY br.request_date DESC
");
$incoming->execute([$myId]);
$incomingRequests = $incoming->fetchAll(PDO::FETCH_ASSOC);

$awaitingPickup = $pdo->prepare("
    SELECT br.*, i.item_title
    FROM borrow_request br
    JOIN item i ON br.item_id = i.item_id
    WHERE i.user_id = ?
      AND br.status = 'confirmed'
    ORDER BY br.request_date DESC
");
$awaitingPickup->execute([$myId]);
$awaitingPickupRows = $awaitingPickup->fetchAll(PDO::FETCH_ASSOC);

$active = $pdo->prepare("
    SELECT b.*, i.item_title, i.price
    FROM borrowing b
    JOIN item i ON b.item_id = i.item_id
    WHERE b.borrower_id = ?
      AND b.status = 'borrowed'
    ORDER BY b.borrow_date DESC, b.borrow_id DESC
");
$active->execute([$myId]);
$activeBorrows = $active->fetchAll(PDO::FETCH_ASSOC);

$outgoing = $pdo->prepare("
    SELECT br.*, i.item_title
    FROM borrow_request br
    JOIN item i ON br.item_id = i.item_id
    WHERE br.requester_id = ?
    ORDER BY br.request_date DESC, br.request_id DESC
");
$outgoing->execute([$myId]);
$outgoingRequests = $outgoing->fetchAll(PDO::FETCH_ASSOC);

/*
 * Each borrowing record is a separate order.
 * This means requesting the same item multiple times creates
 * multiple rows here, and each returned order gets its own review option.
 */
$history = $pdo->prepare("
    SELECT
        b.*,
        i.item_title,
        r.review_id
    FROM borrowing b
    JOIN item i ON b.item_id = i.item_id
    LEFT JOIN review r
        ON r.borrow_id = b.borrow_id
       AND r.reviewer_id = b.borrower_id
    WHERE b.borrower_id = ?
      AND b.status = 'returned'
    ORDER BY b.return_date DESC, b.borrow_id DESC
");
$history->execute([$myId]);
$historyRows = $history->fetchAll(PDO::FETCH_ASSOC);

$returnedToYou = $pdo->prepare("
    SELECT
        b.*,
        i.item_title,
        u.user_name AS borrower_name
    FROM borrowing b
    JOIN item i ON b.item_id = i.item_id
    JOIN user u ON b.borrower_id = u.user_id
    WHERE i.user_id = ?
      AND b.status = 'returned'
    ORDER BY b.return_date DESC, b.borrow_id DESC
");
$returnedToYou->execute([$myId]);
$returnedToYouRows = $returnedToYou->fetchAll(PDO::FETCH_ASSOC);

$myItems = $pdo->prepare("
    SELECT
        i.*,
        (
            SELECT img.image_path
            FROM image img
            WHERE img.item_id = i.item_id
            LIMIT 1
        ) AS image_path
    FROM item i
    WHERE i.user_id = ?
    ORDER BY i.date_created DESC
");
$myItems->execute([$myId]);
$myItemRows = $myItems->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="catalog">
  <div class="wrap">

    <div class="section-head">
      <div class="eyebrow">overview</div>
      <h2>Your dashboard</h2>
    </div>

    <div class="stat-cards">
      <div class="stat-card">
        <span class="mono">ITEMS LISTED</span>
        <b><?= count($myItemRows) ?></b>
      </div>

      <div class="stat-card">
        <span class="mono">CURRENTLY BORROWING</span>
        <b><?= count($activeBorrows) ?></b>
      </div>

      <div class="stat-card">
        <span class="mono">PENDING REQUESTS</span>
        <b><?= count($incomingRequests) ?></b>
      </div>
    </div>

    <div class="dash-section">
      <h3>Requests on your items</h3>

      <?php if (count($incomingRequests) === 0): ?>
        <p class="empty-note">Nothing pending right now.</p>
      <?php else: ?>

        <?php foreach ($incomingRequests as $r): ?>
          <div class="dash-row">
            <div>
              <div class="dash-row-title">
                <?= htmlspecialchars($r['item_title']) ?>
              </div>
            </div>

            <form method="POST" action="actions.php" style="display:flex; gap:8px;">
              <input type="hidden" name="type" value="request_action">
              <input type="hidden" name="request_id" value="<?= htmlspecialchars($r['request_id']) ?>">

              <button type="submit" name="action" value="approve" class="btn" style="padding:8px 16px; font-size:13px;">
                Approve
              </button>

              <button type="submit" name="action" value="deny" class="btn btn-ghost" style="padding:8px 16px; font-size:13px;">
                Deny
              </button>
            </form>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>

    <div class="dash-section">
      <h3>Confirmed, waiting for pickup</h3>

      <?php if (count($awaitingPickupRows) === 0): ?>
        <p class="empty-note">Nothing waiting to be handed over.</p>
      <?php else: ?>

        <?php foreach ($awaitingPickupRows as $r): ?>
          <div class="dash-row">
            <div>
              <div class="dash-row-title">
                <?= htmlspecialchars($r['item_title']) ?>
              </div>

              <div class="dash-row-sub">
                Confirmed. Hand off the item, then mark it below.
              </div>
            </div>

            <form method="POST" action="actions.php">
              <input type="hidden" name="type" value="confirm_pickup">
              <input type="hidden" name="request_id" value="<?= htmlspecialchars($r['request_id']) ?>">

              <button type="submit" class="btn" style="padding:8px 16px; font-size:13px;">
                Mark picked up
              </button>
            </form>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>

    <div class="dash-section">
      <h3>You're currently borrowing</h3>

      <?php if (count($activeBorrows) === 0): ?>
        <p class="empty-note">Not borrowing anything right now.</p>
      <?php else: ?>

        <?php foreach ($activeBorrows as $b): ?>
          <div class="dash-row">
            <div>
              <div class="dash-row-title">
                <?= htmlspecialchars($b['item_title']) ?>
              </div>

              <div class="dash-row-sub mono">
                BDT <?= htmlspecialchars($b['price']) ?> / day.
                Sort return timing with the owner directly.
              </div>
            </div>

            <form method="POST" action="actions.php">
              <input type="hidden" name="type" value="mark_returned">
              <input type="hidden" name="borrow_id" value="<?= htmlspecialchars($b['borrow_id']) ?>">

              <button type="submit" class="btn btn-ghost" style="padding:8px 16px; font-size:13px;">
                Mark returned
              </button>
            </form>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>

    <div class="dash-section">
      <h3>Your requests</h3>

      <?php if (count($outgoingRequests) === 0): ?>
        <p class="empty-note">You haven't requested anything yet.</p>
      <?php else: ?>

        <?php foreach ($outgoingRequests as $r): ?>
          <div class="dash-row">
            <div>
              <div class="dash-row-title">
                <?= htmlspecialchars($r['item_title']) ?>
              </div>
            </div>

            <span class="status-badge status-<?= htmlspecialchars($r['status']) ?>">
              <?= htmlspecialchars($r['status']) ?>
            </span>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>

    <div class="dash-section">
      <h3>Order history</h3>

      <?php if ($reviewError): ?>
        <div class="form-error" style="margin-bottom:16px;">
          <?= htmlspecialchars($reviewError) ?>
        </div>
      <?php endif; ?>

      <?php if ($reviewSuccess): ?>
        <div class="form-success" style="margin-bottom:16px;">
          <?= htmlspecialchars($reviewSuccess) ?>
        </div>
      <?php endif; ?>

      <?php if (count($historyRows) === 0): ?>
        <p class="empty-note">Nothing returned yet.</p>
      <?php else: ?>

        <?php foreach ($historyRows as $h): ?>
          <div class="dash-row" style="align-items:flex-start;">

            <div style="flex:1;">
              <div class="dash-row-title">
                <?= htmlspecialchars($h['item_title']) ?>
              </div>

              <div class="dash-row-sub mono">
                Order #<?= htmlspecialchars($h['borrow_id']) ?>
                · Returned <?= htmlspecialchars($h['return_date']) ?>

                <?php if ($h['fine_amount'] > 0): ?>
                  · Fine: BDT <?= htmlspecialchars($h['fine_amount']) ?>

                  <?php if (!empty($h['fine_note'])): ?>
                    (<?= htmlspecialchars($h['fine_note']) ?>)
                  <?php endif; ?>

                <?php endif; ?>
              </div>
            </div>

            <?php if (empty($h['review_id'])): ?>

              <details>
                <summary class="btn" style="padding:8px 16px; font-size:13px; cursor:pointer;">
                  Review
                </summary>

                <form method="POST" class="auth-form" style="margin-top:16px; min-width:260px;">

                  <input
                    type="hidden"
                    name="borrow_id"
                    value="<?= htmlspecialchars($h['borrow_id']) ?>"
                  >

                  <label>
                    Your rating
                    <select name="rating" required>
                      <option value="" disabled selected>Choose a rating</option>
                      <option value="5">★★★★★ (5)</option>
                      <option value="4">★★★★ (4)</option>
                      <option value="3">★★★ (3)</option>
                      <option value="2">★★ (2)</option>
                      <option value="1">★ (1)</option>
                    </select>
                  </label>

                  <label>
                    Comment <span style="opacity:0.6;">(optional)</span>
                    <input type="text" name="comment" maxlength="500">
                  </label>

                  <button type="submit" name="submit_review" class="btn" style="width:100%;">
                    Post review
                  </button>

                </form>
              </details>

            <?php else: ?>

              <span class="status-badge">
                reviewed
              </span>

            <?php endif; ?>

          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>

    <div class="dash-section">
      <h3>Returned to you</h3>

      <?php if (count($returnedToYouRows) === 0): ?>
        <p class="empty-note">Nothing returned to you yet.</p>
      <?php else: ?>

        <?php foreach ($returnedToYouRows as $b): ?>
          <div class="dash-row" style="flex-direction:column; align-items:stretch; gap:10px;">

            <div>
              <div class="dash-row-title">
                <?= htmlspecialchars($b['item_title']) ?>
              </div>

              <div class="dash-row-sub">
                From <?= htmlspecialchars($b['borrower_name']) ?>,
                returned <?= htmlspecialchars($b['return_date']) ?>

                <?php if ($b['fine_amount'] > 0): ?>
                  · Fine logged: BDT <?= htmlspecialchars($b['fine_amount']) ?>
                <?php endif; ?>
              </div>
            </div>

            <form method="POST" action="actions.php" class="fine-form">
              <input type="hidden" name="type" value="set_fine">
              <input type="hidden" name="borrow_id" value="<?= htmlspecialchars($b['borrow_id']) ?>">

              <input
                type="number"
                name="fine_amount"
                min="0"
                placeholder="BDT"
                value="<?= $b['fine_amount'] > 0 ? htmlspecialchars($b['fine_amount']) : '' ?>"
              >

              <input
                type="text"
                name="fine_note"
                maxlength="200"
                placeholder="reason (optional)"
                value="<?= htmlspecialchars($b['fine_note'] ?? '') ?>"
              >

              <button type="submit" class="btn btn-ghost" style="padding:8px 16px; font-size:13px;">
                <?= $b['fine_amount'] > 0 ? 'Update fine' : 'Log fine' ?>
              </button>
            </form>

          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>

    <div class="dash-section">
      <h3>Items you've listed</h3>

      <?php if (count($myItemRows) === 0): ?>

        <p class="empty-note">
          You haven't listed anything yet.
          <a href="add-item.php" style="color:var(--beige);">
            List your first item
          </a>
        </p>

      <?php else: ?>

        <div class="grid">

          <?php foreach ($myItemRows as $item): ?>

            <?php
              $isAvailable = strtolower($item['availability']) === 'yes';
              $isArchived = $item['archived'] === 'yes';
            ?>

            <div class="card">

              <div class="card-media">

                <?php if (!empty($item['image_path'])): ?>

                  <img
                    src="<?= htmlspecialchars($item['image_path']) ?>"
                    alt=""
                    style="width:100%;height:100%;object-fit:cover;"
                  >

                <?php else: ?>

                  <span class="cat-no">
                    CN<?= htmlspecialchars(strtoupper(substr($item['item_id'], 0, 4))) ?>
                  </span>

                <?php endif; ?>

                <span class="stamp <?= $isArchived ? 'out' : ($isAvailable ? '' : 'out') ?>">
                  <?= $isArchived ? 'archived' : ($isAvailable ? 'available' : 'checked out') ?>
                </span>

              </div>

              <div class="card-body">

                <div class="card-id">
                  ITEM #<?= htmlspecialchars($item['item_id']) ?>
                </div>
                <div class="card-title">
                  <?= htmlspecialchars($item['item_title']) ?>
                </div>
                <div class="card-foot">
                  <div class="price">
                    BDT <?= htmlspecialchars($item['price']) ?>
                    <span> / day</span>
                  </div>
                  <a
                    href="edit-item.php?item_id=<?= urlencode($item['item_id']) ?>"
                    class="mono"
                    style="font-size:12px; color:var(--beige);"
                  >
                    edit
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include 'footer.php'; ?>