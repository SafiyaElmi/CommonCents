<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Listing.php';
require_once __DIR__ . '/../classes/Category.php';

if (!isset($_SESSION['userID'])) {
    header('Location: ../authentication/login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];
$listingID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'listingID', FILTER_VALIDATE_INT);
$listingModel = new Listing();
$categoryModel = new Category();
$error = '';
$success = '';

if (!$listingID) {
    http_response_code(404);
    exit('Listing not found.');
}

$stmt = $conn->prepare('SELECT * FROM listing WHERE listingID = ? AND userID = ?');
$stmt->bind_param('ii', $listingID, $userID);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    http_response_code(404);
    exit('You can only edit your own listings.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_listing'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $type = $_POST['type'] ?? '';
    $status = $_POST['status'] ?? '';
    $categoryID = filter_var($_POST['categoryID'] ?? null, FILTER_VALIDATE_INT);
    $imageURL = $listing['imageURL'];

    if ($title === '' || $description === '' || $price === false || $price <= 0 || !in_array($type, ['New', 'Used'], true) || !in_array($status, ['Available', 'Reserved', 'Sold'], true) || !$categoryID) {
        $error = 'Please complete all fields with valid information.';
    }

    if (!$error) {
        $categoryCheck = $conn->prepare('SELECT categoryID FROM category WHERE categoryID = ?');
        $categoryCheck->bind_param('i', $categoryID);
        $categoryCheck->execute();
        if (!$categoryCheck->get_result()->num_rows) {
            $error = 'Please choose a valid category.';
        }
    }

    if (!$error && isset($_FILES['listingImage']) && $_FILES['listingImage']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['listingImage']['error'] !== UPLOAD_ERR_OK || $_FILES['listingImage']['size'] > 5 * 1024 * 1024) {
            $error = 'Please choose an image smaller than 5 MB.';
        }
        else {
            $imageInfo = getimagesize($_FILES['listingImage']['tmp_name']);
            $allowedTypes = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
            if (!$imageInfo || !isset($allowedTypes[$imageInfo[2]])) {
                $error = 'Please upload a JPG, PNG or WEBP image.';
            }
            else {
                $uploadDirectory = __DIR__ . '/../assets/uploads/';
                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0755, true);
                }
                $imageName = bin2hex(random_bytes(12)) . '.' . $allowedTypes[$imageInfo[2]];
                if (move_uploaded_file($_FILES['listingImage']['tmp_name'], $uploadDirectory . $imageName)) {
                    $imageURL = '../assets/uploads/' . $imageName;
                }
                else {
                    $error = 'The image could not be uploaded. Please try again.';
                }
            }
        }
    }

    if (!$error) {
        $update = $conn->prepare('UPDATE listing SET title = ?, description = ?, price = ?, type = ?, imageURL = ?, status = ?, categoryID = ? WHERE listingID = ? AND userID = ?');
        $update->bind_param('ssdsssiii', $title, $description, $price, $type, $imageURL, $status, $categoryID, $listingID, $userID);
        if ($update->execute()) {
            $success = 'Your listing has been updated.';
            $stmt = $conn->prepare('SELECT * FROM listing WHERE listingID = ? AND userID = ?');
            $stmt->bind_param('ii', $listingID, $userID);
            $stmt->execute();
            $listing = $stmt->get_result()->fetch_assoc();
        }
        else {
            $error = 'The listing could not be updated. Please try again.';
        }
    }
}

$categories = $categoryModel->getAll();
function editListingEscape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="../assets/styles.css">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edit Listing · CommonCents</title>
    </head>
    <body class="page-my-listings">
        <?php require __DIR__ . '/../partials/marketplace-nav.php'; ?>
        <main class="sell-page edit-listing-page">
            <div class="page-heading">
                <h1>Edit Listing</h1>
                <p>Update your item details and availability.</p>
            </div>
            <section class="sell-card">
                <?php if ($error): ?>
                    <div class="form-message form-error"><?= editListingEscape($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="form-message form-success"><?= editListingEscape($success) ?></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="listingID" value="<?= (int) $listingID ?>">
                    <label for="title">Item name</label>
                    <input id="title" name="title" maxlength="100" required value="<?= editListingEscape($_POST['title'] ?? $listing['title']) ?>">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required><?= editListingEscape($_POST['description'] ?? $listing['description']) ?></textarea>
                    <label for="price">Price (R)</label>
                    <input id="price" name="price" type="number" min="0.01" step="0.01" required value="<?= editListingEscape($_POST['price'] ?? $listing['price']) ?>">
                    <label for="type">Condition</label>
                    <select id="type" name="type">
                        <option value="Used" <?= (($_POST['type'] ?? $listing['type']) === 'Used') ? 'selected' : '' ?>>Used</option>
                        <option value="New" <?= (($_POST['type'] ?? $listing['type']) === 'New') ? 'selected' : '' ?>>New</option>
                    </select>
                    <label for="categoryID">Category</label>
                    <select id="categoryID" name="categoryID" required>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <option value="<?= (int) $category['categoryID'] ?>" <?= ((int) ($_POST['categoryID'] ?? $listing['categoryID']) === (int) $category['categoryID']) ? 'selected' : '' ?>><?= editListingEscape($category['categoryName']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="status">Availability</label>
                    <select id="status" name="status" required>
                        <?php foreach (['Available', 'Reserved', 'Sold'] as $statusOption): ?>
                            <option value="<?= $statusOption ?>" <?= (($_POST['status'] ?? $listing['status']) === $statusOption) ? 'selected' : '' ?>><?= $statusOption ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($listing['imageURL'])): ?>
                        <p class="current-listing-image">Current image: <img src="<?= editListingEscape($listing['imageURL']) ?>" alt="Current listing image"></p>
                    <?php endif; ?>
                    <label for="listingImage">Choose a new image (optional)</label>
                    <input id="listingImage" name="listingImage" type="file" accept="image/jpeg,image/png,image/webp">
                    <button class="sell-submit" type="submit" name="update_listing" value="1">Save Changes</button>
                </form>
                <p class="listing-view-link"><a href="my-listings.php">Back to My Listings</a></p>
            </section>
        </main>
        <footer class="site-footer">
            <div class="footer-content"><h2>CommonCents</h2><p>Buy • Sell • Save • Connect</p></div>
            <p>© <?= date('Y') ?> CommonCents. All rights reserved.</p>
        </footer>
        <script src="../assets/script.js" defer></script>
    </body>
</html>
