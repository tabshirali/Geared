<?php $pageTitle = "Edit listing | "; include 'header.php'; ?>

<?php
require_once 'db.php';

if (!$loggedIn) {
    header("Location: login.php");
    exit;
}

$itemId = $_GET['item_id'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM item WHERE item_id = ? AND user_id = ?");
$stmt->execute([$itemId, $_SESSION['user_id']]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

$error = "";
$success = "";
$categories = ["Electronics", "Lab tools", "Cameras", "Textbooks"];

if (!$item) {
    $error = "Item not found, or it's not yours.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['item_title']);
    $description = trim($_POST['description']);
    $price = (int) $_POST['price'];
    $category = $_POST['category'];

    if (empty($title)) {
        $error = "Title can't be empty.";
    } elseif (empty($description) || strlen($description) > 100) {
        $error = "Description has to be non-empty and under 100 characters.";
    } elseif ($price <= 0) {
        $error = "Price has to be more than 0.";
    } elseif (!in_array($category, $categories)) {
        $error = "Pick a valid category.";
    } else {
        $stmt = $pdo->prepare("UPDATE item SET item_title = ?, description = ?, price = ?, category = ? WHERE item_id = ?");
        $stmt->execute([$title, $description, $price, $category, $itemId]);

        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed) && $_FILES['item_image']['size'] < 5 * 1024 * 1024) {
                $fileName = $itemId . "_" . uniqid() . "." . $ext;
                $destination = "uploads/" . $fileName;

                if (move_uploaded_file($_FILES['item_image']['tmp_name'], $destination)) {
                    $pdo->prepare("DELETE FROM image WHERE item_id = ?")->execute([$itemId]);
                    $imageId = "IM" . substr(uniqid(), -3);
                    $pdo->prepare("INSERT INTO image (image_id, item_id, image_path) VALUES (?, ?, ?)")->execute([$imageId, $itemId, $destination]);
                }
            }
        }

        $success = "Listing updated.";
        $stmt = $pdo->prepare("SELECT * FROM item WHERE item_id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<section class="auth-section">
  <div class="wrap-narrow">
    <div class="eyebrow">edit listing</div>
    <h2>Update your item</h2>

    <?php if ($error): ?><div class="form-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="form-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <?php if ($item): ?>
      <form method="POST" class="auth-form" enctype="multipart/form-data">
        <label>Title
          <input type="text" name="item_title" maxlength="60" value="<?= htmlspecialchars($item['item_title']) ?>" required>
        </label>
        <label>Description (max 100 characters)
          <input type="text" name="description" maxlength="100" value="<?= htmlspecialchars($item['description']) ?>" required>
        </label>
        <label>Category
          <select name="category" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $item['category'] === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Price per day (BDT)
          <input type="number" name="price" min="1" value="<?= htmlspecialchars($item['price']) ?>" required>
        </label>
        <label>Replace photo <span style="opacity:0.6;">(optional)</span>
          <div class="file-upload">
            <input type="file" name="item_image" id="item_image" accept=".jpg,.jpeg,.png,.webp" onchange="document.getElementById('file_label_text').textContent = this.files[0] ? this.files[0].name : 'No file chosen'">
            <label for="item_image" class="file-upload-label">
              <span id="file_label_text">No file chosen</span>
              <span class="file-btn">Browse</span>
            </label>
          </div>
        </label>
        <button type="submit" class="btn" style="width:100%;">Save changes</button>
      </form>

      <div class="danger-zone">
        <form method="POST" action="actions.php">
          <input type="hidden" name="type" value="archive_item">
          <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['item_id']) ?>">
          <button type="submit" class="btn btn-ghost" style="width:100%;">
            <?= $item['archived'] === 'yes' ? 'Unarchive listing' : 'Archive listing' ?>
          </button>
        </form>
        <form method="POST" action="actions.php" onsubmit="return confirm('Delete this listing permanently? This can\'t be undone.');">
          <input type="hidden" name="type" value="delete_item">
          <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['item_id']) ?>">
          <button type="submit" class="btn-danger" style="width:100%;">Delete listing</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include 'footer.php'; ?>