<?php $pageTitle = "List an item | "; include 'header.php'; ?>

<?php
require 'db.php';

if (!$loggedIn) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";
$categories = ["Electronics", "Lab tools", "Cameras", "Textbooks"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['item_title']);
    $description = trim($_POST['description']);
    $price = (int) $_POST['price'];
    $category = $_POST['category'];

    if (empty($title)) {
        $error = "Title can't be empty.";
    } elseif (empty($description)) {
        $error = "Description can't be empty.";
    } elseif (strlen($description) > 100) {
        $error = "Description has to be under 100 characters.";
    } elseif ($price <= 0) {
        $error = "Price has to be more than 0.";
    } elseif (!in_array($category, $categories)) {
        $error = "Pick a valid category.";
    } else {
        $itemId = "I" . substr(uniqid(), -4);

        $stmt = $pdo->prepare("INSERT INTO item (item_id, user_id, price, availability, item_title, description, category, date_created) VALUES (?, ?, ?, 'yes', ?, ?, ?, CURDATE())");
        $stmt->execute([$itemId, $_SESSION['user_id'], $price, $title, $description, $category]);

        // handle image upload (optional)
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed) && $_FILES['item_image']['size'] < 5 * 1024 * 1024) {
                $fileName = $itemId . "_" . uniqid() . "." . $ext;
                $destination = "uploads/" . $fileName;

                if (move_uploaded_file($_FILES['item_image']['tmp_name'], $destination)) {
                    $imageId = "IM" . substr(uniqid(), -3);
                    $imgStmt = $pdo->prepare("INSERT INTO image (image_id, item_id, image_path) VALUES (?, ?, ?)");
                    $imgStmt->execute([$imageId, $itemId, $destination]);
                }
            }
        }

        $success = "Item listed! Head to the catalog to see it.";
    }
}
?>

<section class="auth-section">
  <div class="wrap-narrow">
    <div class="eyebrow">list something</div>
    <h2>What are you lending?</h2>

    <?php if ($error): ?><div class="form-error"><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="form-success"><?= htmlspecialchars($success) ?> 
      <a href="catalogue.php">view catalog →
        /a>
      </div>
      <?php endif; ?>

    <form method="POST" class="auth-form" enctype="multipart/form-data">
      <label>Title
        <input type="text" name="item_title" maxlength="60" required>
      </label>
      <label>Description (max 100 characters)
        <input type="text" name="description" maxlength="100" required>
      </label>
      <label>Category
        <select name="category" required>
          <option value="" disabled selected>Choose one</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Price per day (BDT)
        <input type="number" name="price" min="1" required>
      </label>
      <label>Photo <span style="opacity:0.6;">(optional, jpg/png/webp, max 5MB)</span>
        <div class="file-upload">
          <input type="file" name="item_image" id="item_image" accept=".jpg,.jpeg,.png,.webp" onchange="document.getElementById('file_label_text').textContent = this.files[0] ? this.files[0].name : 'No file chosen'">
          <label for="item_image" class="file-upload-label">
            <span id="file_label_text">No file chosen</span>
            <span class="file-btn">Browse</span>
          </label>
        </div>
      </label>
      <button type="submit" class="btn" style="width:100%;">List item</button>
    </form>
  </div>
</section>

<?php include 'footer.php'; ?>