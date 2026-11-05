<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['userID']);
$homePage = $isLoggedIn ? 'home.php' : 'index.php';
$loginPage = '../authentication/login.php';
?>

<header class="cc-navbar">
    <div class="cc-navbar-inner">
        <a class="cc-brand" href="<?= $homePage ?>">
            <span class="cc-brand-mark" aria-hidden="true">C</span>
            <span>
                <strong>CommonCents</strong>
                <small>Buy. Sell. Connect.</small>
            </span>
        </a>

        <form class="cc-nav-search" action="<?= $homePage ?>" method="get" role="search">
            <span aria-hidden="true">⌕</span>
            <input
                type="search"
                name="search"
                value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Search products and categories..."
                aria-label="Search products and categories"
            >
        </form>

        <nav class="cc-nav-links" aria-label="Main navigation">
            <a href="<?= $homePage ?>" class="<?= in_array($currentPage, ['home.php', 'index.php'], true) ? 'active' : '' ?>">Home</a>
            <a href="home.php#categories">Categories</a>
            <a href="<?= $isLoggedIn ? 'my-listings.php' : $loginPage ?>" class="<?= $currentPage === 'my-listings.php' ? 'active' : '' ?>">Sell</a>
            <a href="<?= $isLoggedIn ? 'orders.php' : $loginPage ?>" class="<?= in_array($currentPage, ['orders.php', 'order-details.php'], true) ? 'active' : '' ?>">Orders</a>
            <a href="<?= $isLoggedIn ? 'notifications.php' : $loginPage ?>" class="<?= $currentPage === 'notifications.php' ? 'active' : '' ?>">Notifications</a>
        </nav>

        <div class="cc-nav-actions">
            <a class="cc-nav-icon <?= $currentPage === 'cart.php' ? 'active' : '' ?>" href="<?= $isLoggedIn ? 'cart.php' : $loginPage ?>" aria-label="Cart">🛒</a>
            <a class="cc-account-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>" href="<?= $isLoggedIn ? 'profile.php' : $loginPage ?>">
                <?= $isLoggedIn ? 'My Account' : 'Login' ?>
            </a>
        </div>
    </div>
</header>
