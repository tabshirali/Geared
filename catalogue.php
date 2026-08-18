<?php $pageTitle = "Browse | "; include 'header.php'; ?>

<?php
require 'db.php';

$categories = ["Electronics", "Lab tools", "Cameras", "Textbooks"];
$selectedCategory = $_GET['category'] ?? 'All';

$sql = "SELECT 
            i.item_id,
            i.item_title,
            i.description,
            i.price,
            i.availability,
            i.category,
            i.date_created,
            u.user_name,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(DISTINCT r.review_id) AS review_count,
            (SELECT img.image_path FROM image img WHERE img.item_id = i.item_id LIMIT 1) AS image_path
        FROM item i
        JOIN user u ON i.user_id = u.user_id
        LEFT JOIN review r ON r.item_id = i.item_id";

if ($selectedCategory !== 'All') {
    $sql .= " WHERE i.archived != 'yes' AND i.category = :category";
} else {
    $sql .= " WHERE i.archived != 'yes'";
}

$sql .= " GROUP BY i.item_id ORDER BY i.date_created DESC";

$stmt = $pdo->prepare($sql);
if ($selectedCategory !== 'All') {
    $stmt->bindValue(':category', $selectedCategory);
}
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="catalog" id="browse">
  <div class="wrap">
    <div class="catalog-top">
      <div class="section-head" style="margin-bottom:0;">
        <div class="eyebrow">the catalog</div>
        <h2>Available right now</h2>
      </div>
      <div class="filters">
        <a href="catalogue.php" class="chip <?= $selectedCategory === 'All' ? 'active' : '' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
          <a href="catalogue.php?category=<?= urlencode($cat) ?>" class="chip <?= $selectedCategory === $cat ? 'active' : '' ?>"><?= htmlspecialchars($cat) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="grid">
      <?php if (count($items) === 0): ?>
        <p style="color:var(--ink-dim);">No items listed yet.</p>
      <?php endif; ?>

      <?php foreach ($items as $item): ?>
        <?php
          $isAvailable = strtolower($item['availability']) === 'yes';
          $catNo = strtoupper(substr($item['item_id'], 0, 4));
          $rating = round($item['avg_rating'], 1);
        ?>
        <div class="card">
          <div class="card-media">
            <?php if (!empty($item['image_path'])): ?>
              <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <span class="cat-no">CN<?= htmlspecialchars($catNo) ?></span>
            <?php endif; ?>
            <span class="stamp <?= $isAvailable ? '' : 'out' ?>">
              <?= $isAvailable ? 'available' : 'checked out' ?>
            </span>
          </div>
          <div class="card-body">
           <div class="card-id">ITEM #<?= htmlspecialchars($item['item_id']) ?> · listed by <?= htmlspecialchars($item['user_name']) ?></div>
           <div class="card-title"><?= htmlspecialchars($item['item_title']) ?></div>
           <div class="card-desc"><?= htmlspecialchars($item['description']) ?></div>

            <div class="card-foot">
              <div class="price">BDT <?= htmlspecialchars($item['price']) ?><span> / day</span></div>
              <div class="rate mono">
                <?= $item['review_count'] > 0
                      ? "★ $rating ({$item['review_count']})"
                      : "no reviews yet" ?>
              </div>
            </div>
            <a href="request.php?item_id=<?= urlencode($item['item_id']) ?>" class="btn" style="width:100%; text-align:center; margin-top:16px;">View & request</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>