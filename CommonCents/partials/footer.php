<?php
$isAuth = in_array(basename($_SERVER['PHP_SELF']), ['login.php','register.php']);
if (!$isAuth):
$c    = basename($_SERVER['PHP_SELF']);
$home = isset($_SESSION['userID']) ? 'home.php' : 'index.php';
?>
</div><!-- .app-shell -->

<nav class="bottom-nav">
    <a href="<?= $home ?>" class="<?= in_array($c,['index.php','home.php']) ? 'active' : '' ?>">
        <i class="bi bi-house-door"></i>Home
    </a>
    <a href="<?= $home ?>#products" class=""><i class="bi bi-bag"></i>Products</a>
    <a href="cart.php"          class="<?= $c==='cart.php'?'active':'' ?>"><i class="bi bi-cart3"></i>Cart</a>
    <a href="notifications.php" class="<?= $c==='notifications.php'?'active':'' ?>"><i class="bi bi-bell"></i>Notification</a>
    <a href="profile.php"       class="<?= $c==='profile.php'?'active':'' ?>"><i class="bi bi-person"></i>Profile</a>
</nav>
<?php endif; ?>

<script src="../assets/script.js" defer></script>
</body>
</html>
