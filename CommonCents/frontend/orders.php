<?php
require_once __DIR__ . '/../config/database.php';
if (!isset($_SESSION['userID'])) {
    header('Location: ../authentication/login.php');
    exit;
}
$userID=(int)$_SESSION['userID'];
$sql="SELECT o.orderID,o.orderDate,o.status,o.deliveryAddress,
             COALESCE(SUM(oi.quantity*oi.price),0) AS total,
             COUNT(oi.orderItemID) AS itemCount,
             MIN(l.title) AS firstTitle, MIN(l.imageURL) AS firstImage,
             MIN(c.categoryName) AS categoryName,
             p.paymentMethod,p.paymentStatus
      FROM `order` o
      LEFT JOIN orderitem oi ON oi.orderID=o.orderID
      LEFT JOIN listing l ON l.listingID=oi.listingID
      LEFT JOIN category c ON c.categoryID=l.categoryID
      LEFT JOIN payment p ON p.orderID=o.orderID
      WHERE o.userID=? GROUP BY o.orderID ORDER BY o.orderDate DESC";
$stmt=$conn->prepare($sql);
$stmt->bind_param('i',$userID);
$stmt->execute();
$orders=$stmt->get_result();
function h($v) {
    return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
}
function orderStatusClass($status) {
    return match($status) {
        'Paid','Completed'=>'delivered','Cancelled'=>'cancelled',default=>'processing'
    }
    ;
}
?>

<!doctype html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="../assets/styles.css">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>My Orders · CommonCents</title>
    </head>
    <body class="page-orders">

        <?php
        require __DIR__ . '/../partials/marketplace-nav.php';
        ?>

        <main class="orders-page">
            <section class="page-heading">
                <h1>My Orders</h1>
                <span>
                </span>
                <p>Keep track of your purchases and order status.</p>
            </section>

            <?php if($orders->num_rows===0): ?>
                <div class="empty-orders">
                    <h2>No orders yet</h2>
                    <p>Your purchases will appear here after checkout.</p>
                    <a href="home.php">Browse products</a>
                </div>
            <?php else: while($o=$orders->fetch_assoc()): ?>
                <article class="order-card search-item">
                    <div class="order-top">
                        <div>
                            <p class="label">Order ID</p>
                            <h3>#ORD-<?= (int)$o['orderID'] ?>
                            </h3>
                        </div>
                        <div>
                            <p class="label">Order Date</p>
                            <p>
                                <?= h(date('d F Y',strtotime($o['orderDate']))) ?>
                            </p>
                        </div>
                        <span class="status <?= orderStatusClass($o['status']) ?>">
                        <?= h($o['status']) ?>
                    </span>
                </div>
                <div class="order-content">
                    <div class="product-image">
                        <?php if($o['firstImage']): ?>
                            <img src="<?= h($o['firstImage']) ?>" alt="<?= h($o['firstTitle']) ?>">
                        <?php else: ?>
                            <span>📦</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-details">
                        <h3>
                            <?= h($o['firstTitle'] ?: 'Order items') ?>
                        </h3>
                        <p>Category: <?= h($o['categoryName'] ?: 'Various') ?>
                        </p>
                        <p>Items: <?= (int)$o['itemCount'] ?>
                        </p>
                        <strong>R<?= number_format((float)$o['total'],2) ?>
                        </strong>
                        <p>Payment: <?= h($o['paymentStatus'] ?: 'Pending') ?>
                            <?= $o['paymentMethod'] ? ' · '.h($o['paymentMethod']) : '' ?>
                        </p>
                    </div>
                    <div class="order-total">
                        <p>Total</p>
                        <strong>R<?= number_format((float)$o['total'],2) ?>
                        </strong>
                    <a href="order-details.php?id=<?= (int)$o['orderID'] ?>">View Order</a>
                </div>
            </div>
        </article>
    <?php endwhile; endif; ?>
</main>
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
