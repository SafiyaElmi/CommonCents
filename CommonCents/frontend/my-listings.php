<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Listing.php';
require_once __DIR__ . '/../classes/Category.php';
if (!isset($_SESSION['userID'])) {
    header('Location: ../authentication/login.php');
    exit;
}
$userID = (int) $_SESSION['userID'];
$listingModel = new Listing();
$categoryModel = new Category();
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_listing'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $type = $_POST['type'] ?? 'Used';
    $categoryID = filter_var($_POST['categoryID'] ?? null, FILTER_VALIDATE_INT);
    $isOtherCategory = !empty($_POST['otherCategory']);
    $newCategory = $isOtherCategory ? trim($_POST['newCategory'] ?? '') : '';
    $imageURL = null;

    if ($title === '' || $description === '' || $price === false || $price <= 0 || !in_array($type, ['New', 'Used'], true)) {
        $error = 'Please complete all required fields with valid information.';
    }
    elseif (($isOtherCategory && $newCategory === '') || (!$isOtherCategory && !$categoryID)) {
        $error = $isOtherCategory ? 'Please enter the new category name.' : 'Please choose a category or select Other.';
    }
    else {
        if ($newCategory !== '') {
            $categoryCheck = $conn->prepare('SELECT categoryID FROM category WHERE categoryName = ?');
            $categoryCheck->bind_param('s', $newCategory);
            $categoryCheck->execute();
            $existingCategory = $categoryCheck->get_result()->fetch_assoc();

            if ($existingCategory) {
                $categoryID = (int) $existingCategory['categoryID'];
            }
            else {
                $categoryInsert = $conn->prepare('INSERT INTO category (categoryName) VALUES (?)');
                $categoryInsert->bind_param('s', $newCategory);
                if ($categoryInsert->execute()) {
                    $categoryID = (int) $conn->insert_id;
                }
                else {
                    $error = 'The new category could not be created.';
                }
            }
        }

        if (!$error && $categoryID) {
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
                $allowedTypes = [
                    IMAGETYPE_JPEG => 'jpg',
                    IMAGETYPE_PNG => 'png',
                    IMAGETYPE_WEBP => 'webp'
                ];

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

        if (!$error && $listingModel->create($title, $description, $price, $type, $imageURL, 'Available', $userID, $categoryID)) {
            $success = 'Your listing has been created.';
        }
        elseif (!$error) {
            $error = 'The listing could not be created. Please try again.';
        }
    }
}
$categories = $categoryModel->getAll();
$myListings = $listingModel->getByUser($userID);
function listingEscape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!doctype html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="../assets/styles.css">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sell · CommonCents</title>

    </head>
    <body class="page-my-listings">

        <?php
        require __DIR__ . '/../partials/marketplace-nav.php';
        ?>

        <main class="sell-page">
            <div class="page-heading">
                <h1>Sell an Item</h1>
                <span>
                </span>
                <p>Create a listing for other students to discover.</p>
            </div>
            <div class="sell-grid">
                <section class="sell-card">
                    <h2>Create a Listing</h2>

                    <?php if ($error): ?>
                        <div class="form-message form-error">
                            <?= listingEscape($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="form-message form-success">
                            <?= listingEscape($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="my-listings.php" enctype="multipart/form-data">
                        <label for="title">Item name</label>
                        <input id="title" name="title" maxlength="100" required value="<?= listingEscape($_POST['title'] ?? '') ?>">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required>
                            <?= listingEscape($_POST['description'] ?? '') ?>
                        </textarea>
                        <label for="price">Price (R)</label>
                        <input id="price" name="price" type="number" min="0.01" step="0.01" required value="<?= listingEscape($_POST['price'] ?? '') ?>">
                        <label for="type">Condition</label>
                        <select id="type" name="type">
                            <option value="Used">Used</option>
                        <option value="New" <?= (($_POST['type'] ?? '') === 'New') ? 'selected' : '' ?>>New</option>
                    </select>
                    <label for="categoryID">Choose an Existing Category</label>
                <select id="categoryID" name="categoryID">
                    <option value="">Choose a category</option>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <option value="<?= (int)$category['categoryID'] ?>" <?= ((string)($_POST['categoryID'] ?? '') === (string)$category['categoryID']) ? 'selected' : '' ?>>
                            <?= listingEscape($category['categoryName']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <div class="other-category-option">
                    <label for="otherCategory" class="other-category-label">
                        <input id="otherCategory" name="otherCategory" type="checkbox" value="1" <?= !empty($_POST['otherCategory']) ? 'checked' : '' ?>>
                        Other
                    </label>
                    <div id="newCategoryField" class="new-category-field" <?= empty($_POST['otherCategory']) ? 'hidden' : '' ?>>
                        <label for="newCategory">Enter the new category</label>
                        <input id="newCategory" name="newCategory" type="text" maxlength="50" placeholder="Enter a category name" value="<?= listingEscape($_POST['newCategory'] ?? '') ?>" <?= !empty($_POST['otherCategory']) ? 'required' : 'disabled' ?>>
                    </div>
                </div>
                <label for="listingImage">Choose an Image (optional)</label>
                <input id="listingImage" name="listingImage" type="file" accept="image/jpeg,image/png,image/webp">
                <button class="sell-submit" type="submit" name="create_listing" value="1">Publish Listing</button>
            </form>
        </section>
        <section>
            <h2 class="my-listings-heading">My Listings</h2>
            <div class="my-listings-grid">

                <?php if (!$myListings || !$myListings->num_rows): ?>
                    <div class="sell-empty">You haven't created any listings yet.</div>
                <?php else: while ($listing = $myListings->fetch_assoc()): ?>

                    <article class="my-listing">

                        <?php if (!empty($listing['imageURL'])): ?>
                            <img src="<?= listingEscape($listing['imageURL']) ?>" alt="<?= listingEscape($listing['title']) ?>">
                        <?php else: ?>
                            <img src="../assets/frontend/Project3_Order/student market place.jpg" alt="No listing image provided">
                        <?php endif; ?>

                        <h3>
                            <?= listingEscape($listing['title']) ?>
                        </h3>
                        <p>
                            <?= listingEscape($listing['description']) ?>
                        </p>
                        <p class="price">R <?= number_format((float)$listing['price'], 2) ?>
                        </p>
                        <span class="listing-status">
                            <?= listingEscape($listing['status']) ?>
                        </span>
                        <p class="listing-view-link">
                            <a href="listing.php?id=<?= (int)$listing['listingID'] ?>">View listing →</a>
                        </p>
                        <p class="listing-edit-link">
                            <a href="edit-listing.php?id=<?= (int)$listing['listingID'] ?>">Edit listing</a>
                        </p>
                </article>

            <?php endwhile; endif; ?>

        </div>
    </section>
</div>
</main>
<footer>
    <div class="footer-content">
        <h2>CommonCents</h2>
        <p>Buy • Sell • Save • Connect</p>
    </div>
    <p>© <?= date('Y') ?> CommonCents. All rights reserved.</p>
</footer>
<script src="../assets/script.js" defer>
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const otherCategory = document.getElementById("otherCategory");
        const newCategoryField = document.getElementById("newCategoryField");
        const newCategoryInput = document.getElementById("newCategory");
        const categorySelect = document.getElementById("categoryID");

        if (otherCategory && newCategoryField && newCategoryInput && categorySelect) {
            const updateCategoryChoice = function () {
                newCategoryField.hidden = !otherCategory.checked;
                newCategoryInput.disabled = !otherCategory.checked;
                newCategoryInput.required = otherCategory.checked;
                categorySelect.disabled = otherCategory.checked;
            };

            otherCategory.addEventListener("change", updateCategoryChoice);
            updateCategoryChoice();
        }
    });
</script>
</body>
</html>
