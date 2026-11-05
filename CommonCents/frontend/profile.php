<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Listing.php';
require_once __DIR__ . '/../classes/Order.php';
require_once __DIR__ . '/../classes/Review.php';

if (!isset($_SESSION['userID'])) {
    header("Location: ../authentication/login.php");
    exit();
}

$userID = (int)$_SESSION['userID'];
$userModel = new User();
$accountMessage = '';
$accountError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $accountError = 'Please enter a valid first name, last name and email address.';
    }
    else {
        try {
            if ($userModel->update($userID, $firstName, $lastName, $email)) {
                $accountMessage = 'Your account details have been updated.';
            }
        }
        catch (mysqli_sql_exception $exception) {
            $accountError = 'That email address is already being used by another account.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $conn->begin_transaction();

    try {
        $deleteQueries = [
            'DELETE ci FROM cartitem ci JOIN cart c ON c.cartID = ci.cartID WHERE c.userID = ?',
            'DELETE ci FROM cartitem ci JOIN listing l ON l.listingID = ci.listingID WHERE l.userID = ?',
            'DELETE oi FROM orderitem oi JOIN `order` o ON o.orderID = oi.orderID WHERE o.userID = ?',
            'DELETE oi FROM orderitem oi JOIN listing l ON l.listingID = oi.listingID WHERE l.userID = ?',
            'DELETE p FROM payment p JOIN `order` o ON o.orderID = p.orderID WHERE o.userID = ?',
            'DELETE r FROM review r JOIN listing l ON l.listingID = r.listingID WHERE l.userID = ?',
            'DELETE FROM review WHERE userID = ?',
            'DELETE FROM notification WHERE userID = ?',
            'DELETE FROM `order` WHERE userID = ?',
            'DELETE FROM cart WHERE userID = ?',
            'DELETE FROM listing WHERE userID = ?',
            'DELETE FROM `user` WHERE userID = ?'
        ];

        foreach ($deleteQueries as $query) {
            $deleteStatement = $conn->prepare($query);
            $deleteStatement->bind_param('i', $userID);
            $deleteStatement->execute();
        }

        $conn->commit();
        $_SESSION = [];
        session_destroy();
        header('Location: ../authentication/login.php?account_deleted=1');
        exit;
    }
    catch (Throwable $exception) {
        $conn->rollback();
        $accountError = 'Your account could not be deleted. Please try again.';
    }
}

$user = $userModel->getById($userID);

if (!$user) {
    header("Location: ../authentication/logout.php");
    exit();
}

$listingModel = new Listing();
$myListings   = $listingModel->getByUser($userID);
$listingCount = $myListings->num_rows;

$orderModel = new Order();
$myOrders   = $orderModel->getByUser($userID);
$orderCount = $myOrders->num_rows;
$reviewStatement = $conn->prepare('SELECT COUNT(*) AS total FROM review WHERE userID = ?');
$reviewStatement->bind_param('i', $userID);
$reviewStatement->execute();
$reviewCount = (int) $reviewStatement->get_result()->fetch_assoc()['total'];

$pageTitle = "Profile";
require __DIR__ . '/../partials/header.php';
?>

<div class="profile-hero">
    <div class="d-flex justify-content-between align-items-start">
        <a href="home.php" class="icon-btn profile-header-action">
            <i class="bi bi-arrow-left"></i>
        </a>
        <a href="../authentication/logout.php" class="icon-btn profile-header-action">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>

    <div class="mt-3">
        <div class="profile-avatar"><i class="bi bi-person"></i></div>
        <h2><?= htmlspecialchars($user['firstName'] . " " . $user['lastName']) ?></h2>
        <p><?= htmlspecialchars($user['email']) ?></p>
    </div>
</div>

<div class="stats-row profile-stats-row">
    <div class="stat"><strong><?= (int)$listingCount ?></strong><span>Listings</span></div>
    <div class="stat"><strong><?= (int)$orderCount ?></strong><span>Orders</span></div>
    <div class="stat"><strong><?= (int)$reviewCount ?></strong><span>Reviews</span></div>
</div>

<div class="menu-list">
    <a class="menu-item" href="orders.php">
        <div class="mi-icon"><i class="bi bi-bag"></i></div>
        <div class="mi-text"><strong>My Orders</strong><span>View all your orders (<?= (int)$orderCount ?>)</span></div>
        <i class="bi bi-chevron-right mi-arrow"></i>
    </a>
    <a class="menu-item" href="my-listings.php">
        <div class="mi-icon"><i class="bi bi-box-seam"></i></div>
        <div class="mi-text"><strong>My Listings</strong><span>Manage the items you sell (<?= (int)$listingCount ?>)</span></div>
        <i class="bi bi-chevron-right mi-arrow"></i>
    </a>
    <a class="menu-item" href="reviews.php">
        <div class="mi-icon"><i class="bi bi-star"></i></div>
        <div class="mi-text"><strong>Reviews</strong><span>Your product reviews</span></div>
        <i class="bi bi-chevron-right mi-arrow"></i>
    </a>
    <a class="menu-item" href="notifications.php">
        <div class="mi-icon"><i class="bi bi-bell"></i></div>
        <div class="mi-text"><strong>Notifications</strong><span>View marketplace updates</span></div>
        <i class="bi bi-chevron-right mi-arrow"></i>
    </a>
    <a class="menu-item danger" href="../authentication/logout.php">
        <div class="mi-icon"><i class="bi bi-box-arrow-right"></i></div>
        <div class="mi-text"><strong>Logout</strong><span>Sign out from your account</span></div>
        <i class="bi bi-chevron-right mi-arrow"></i>
    </a>
</div>

<section class="account-settings">
    <h2>Account Details</h2>

    <?php if ($accountMessage): ?>
        <p class="account-message"><?= htmlspecialchars($accountMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($accountError): ?>
        <p class="account-error"><?= htmlspecialchars($accountError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" class="account-form">
        <label for="firstName">First Name</label>
        <input id="firstName" name="firstName" type="text" maxlength="50" required value="<?= htmlspecialchars($user['firstName'], ENT_QUOTES, 'UTF-8') ?>">

        <label for="lastName">Last Name</label>
        <input id="lastName" name="lastName" type="text" maxlength="50" required value="<?= htmlspecialchars($user['lastName'], ENT_QUOTES, 'UTF-8') ?>">

        <label for="email">Email Address</label>
        <input id="email" name="email" type="email" maxlength="100" required value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>">

        <button type="submit" name="update_account" value="1">Save Changes</button>
    </form>

    <div class="delete-account">
        <h3>Delete Account</h3>
        <p>This permanently deletes your account, listings and account-related records.</p>
        <form method="post" onsubmit="return confirm('Are you sure you want to permanently delete your account? This cannot be undone.');">
            <button type="submit" name="delete_account" value="1">Delete My Account</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
