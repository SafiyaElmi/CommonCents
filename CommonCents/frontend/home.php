<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Category.php';
require_once __DIR__ . '/../classes/Listing.php';
require_once __DIR__ . '/../classes/Notification.php';
if (!isset($_SESSION['userID'])) {
    header('Location: index.php');
    exit;
}
$categoryModel=new Category();
$listingModel=new Listing();
$notificationModel=new Notification();
$notificationResult=$notificationModel->getByUser((int)$_SESSION['userID']);
$recentNotifications=[];
if ($notificationResult) {
    while ($notificationRow=$notificationResult->fetch_assoc()) {
        $recentNotifications[]=$notificationRow;
        if (count($recentNotifications)===3) break;
    }
}
$categories=$categoryModel->getAll();
$listingResult=$listingModel->getAll();
$search=strtolower(trim($_GET['search']??''));
$categoryID=(int)($_GET['category']??0);
$products=[];
while($row=$listingResult->fetch_assoc()) {
    if($row['status']!=='Available') continue;
    if($search && strpos(strtolower($row['title'].' '.($row['description']??'')),$search)===false) continue;
    if($categoryID && (int)$row['categoryID']!==$categoryID) continue;
    $products[]=$row;
}
function esc($v) {
    return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
}
?>

<!doctype html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="../assets/styles.css">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CommonCents · Student Marketplace</title>
    </head>
    <body class="page-home">

        <?php
        require __DIR__ . '/../partials/marketplace-nav.php';
        ?>

        <section class="hero">
            <div class="hero-content">
                <h1>Everything you need,<br>
                    <span>right on campus.</span>
                </h1>
                <p>Shop, sell and connect with fellow students. Find great deals on clothes, tech, books and more.</p>
                <a href="../authentication/login.php">
                    <button type="button">Browse Listings →</button>
                </a>
            </div>
            <div class="hero-image">
                <img src="../images/student market place.jpg" alt="Student marketplace">
            </div>
        </section>
        <section class="home-notifications" aria-labelledby="recent-notifications-heading">
            <h2 id="recent-notifications-heading">Recent Notifications</h2>
            <div class="notification-list">
                <?php if (!$recentNotifications): ?>
                    <div class="no-notifications">You have no notifications yet.</div>
                <?php else: ?>

                    <?php foreach ($recentNotifications as $notification): $notificationDate=strtotime($notification['notificationDate']); $notificationIcon='🔔'; if (stripos($notification['message'],'order')!==false) $notificationIcon='📦'; elseif (stripos($notification['message'],'review')!==false) $notificationIcon='⭐'; elseif (stripos($notification['message'],'listing')!==false) $notificationIcon='🛍️'; ?>

                        <a class="home-notification" href="notifications.php">
                            <span class="n-icon">
                                <?= $notificationIcon ?>
                            </span>
                            <span class="n-message">
                                <strong>
                                    <?= esc(stripos($notification['message'],'order')!==false?'Order Update':(stripos($notification['message'],'review')!==false?'New Review':'Marketplace Update')) ?>
                                </strong>
                                <p>
                                    <?= esc($notification['message']) ?>
                                </p>
                            </span>
                            <time>
                                <?= $notificationDate ? esc(date('d M, H:i',$notificationDate)) : '' ?>
                            </time>
                        </a>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>
            <a class="view-all" href="notifications.php">View all notifications →</a>
        </section>
        <section class="categories" id="categories">
            <h2>Shop by Category</h2>
            <div class="category-list">
                <?php
                $categoryIcons = [
                    'Electronics' => '💻',
                    'Textbooks & Study Materials' => '📚',
                    'Clothing' => '👕',
                    'Living Essentials' => '🏠',
                    'Sports & Fitness' => '⚽'
                ];
                while($cat=$categories->fetch_assoc()):
                ?>
                <a class="category <?= $categoryID===(int)$cat['categoryID']?'active':'' ?>" href="home.php?category=<?= (int)$cat['categoryID'] ?>#featured">
                <div class="category-icon">
                    <?= $categoryIcons[$cat['categoryName']] ?? '🛍️' ?>
                </div>
                <h3>
                    <?= esc($cat['categoryName']) ?>
                </h3>
                <p>Explore listings</p>
            </a>
        <?php endwhile; ?>
        <a class="category" href="home.php#featured">
            <div class="category-icon">→</div>
            <h3>View All</h3>
            <p>Explore more</p>
        </a>
    </div>
</section>
<section class="featured" id="featured">
    <div class="section-heading">
        <div>
            <h2>Featured Listings</h2>
            <span>
            </span>
        </div>
        <a href="home.php#featured">View All →</a>
    </div>
    <div class="product-grid">
        <?php if(!$products): ?>
            <div class="empty-products">No available listings match your search.</div>
        <?php else: ?>

            <?php foreach($products as $product): ?>
                <a class="product-card" href="listing.php?id=<?= (int)$product['listingID'] ?>">
                <div class="product-image">
                    <span class="badge">
                        <?= esc($product['type']) ?>
                    </span>
                    <?php if($product['imageURL']): ?>
                        <img src="<?= esc($product['imageURL']) ?>" alt="<?= esc($product['title']) ?>">
                    <?php else: ?>
                        <div class="no-image">📦</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h3>
                        <?= esc($product['title']) ?>
                    </h3>
                    <strong>R <?= number_format((float)$product['price'],2) ?>
                    </strong>
                    <p>
                        <?= esc($product['categoryName']) ?> · <?= esc($product['firstName']) ?>
                    </p>
                </div>
            </a>
        <?php endforeach; ?>

    <?php endif; ?>
</div>
</section>
<footer class="site-footer">
<div class="footer-content">
<h2>CommonCents</h2>
<p>Buy • Sell • Save • Connect</p>
</div>
<p>© <?= date('Y') ?> CommonCents. All rights reserved.</p>
</footer>
<script src="../assets/script.js" defer>
</script>
</body>
</html>
