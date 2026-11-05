<?php
require_once __DIR__ . '/../config/database.php';
if(!isset($_SESSION['userID'])) {
    header('Location: ../authentication/login.php');
    exit;
}
$userID=(int)$_SESSION['userID'];
$orderID=(int)($_GET['id']??0);
$stmt=$conn->prepare("SELECT * FROM `order` WHERE orderID=? AND userID=?");
$stmt->bind_param('ii',$orderID,$userID);
$stmt->execute();
$order=$stmt->get_result()->fetch_assoc();
if(!$order) {
    http_response_code(404);
    exit('Order not found.');
}
$stmt=$conn->prepare('SELECT oi.quantity,oi.price,l.title,l.imageURL,c.categoryName,u.firstName,u.lastName FROM orderitem oi JOIN listing l ON l.listingID=oi.listingID JOIN category c ON c.categoryID=l.categoryID JOIN `user` u ON u.userID=l.userID WHERE oi.orderID=?');
$stmt->bind_param('i',$orderID);
$stmt->execute();
$items=$stmt->get_result();
$rows=[];
$total=0;
while($r=$items->fetch_assoc()) {
    $rows[]=$r;
    $total+=(float)$r['price']*(int)$r['quantity'];
}
$stmt=$conn->prepare('SELECT * FROM payment WHERE orderID=?');
$stmt->bind_param('i',$orderID);
$stmt->execute();
$payment=$stmt->get_result()->fetch_assoc();
function h($v) {
    return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
}
?>

<!doctype html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="../assets/styles.css">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Order Details · CommonCents</title>
    </head>
    <body class="page-order-details">
        <?php
        require __DIR__ . '/../partials/marketplace-nav.php';
        ?>
        <main class="order-details">
            <a href="orders.php" class="back-link">← Back to Orders</a>
            <section class="order-heading">
                <div>
                    <h1>Order #ORD-<?= (int)$orderID ?>
                    </h1>
                    <span>
                    </span>
                    <p>Placed on <?= h(date('d F Y',strtotime($order['orderDate']))) ?>
                    </p>
                </div>
                <div class="status <?= in_array($order['status'],['Paid','Completed'])?'delivered':($order['status']==='Cancelled'?'cancelled':'processing') ?>">
                <?= h($order['status']) ?>
            </div>
        </section>
        <section class="items-section">
            <div class="section-heading">
                <div>
                    <h2>Order Items</h2>
                    <span>
                    </span>
                </div>
            </div>
            <?php foreach($rows as $r): ?>
                <div class="item-card">
                    <div class="item-image">
                        <?php if($r['imageURL']): ?>
                            <img src="<?= h($r['imageURL']) ?>" alt="<?= h($r['title']) ?>">
                        <?php else: ?>
                            <span>📦</span>
                        <?php endif; ?>
                    </div>
                    <div class="item-info">
                        <h3>
                            <?= h($r['title']) ?>
                        </h3>
                        <p>Category: <?= h($r['categoryName']) ?>
                        </p>
                        <p>Seller: <?= h($r['firstName'].' '.$r['lastName']) ?>
                        </p>
                        <p>Quantity: <strong>
                                <?= (int)$r['quantity'] ?>
                            </strong>
                        </p>
                    </div>
                    <div class="item-price">
                        <p>Unit Price</p>
                        <strong>R<?= number_format((float)$r['price'],2) ?>
                        </strong>
                    </div>
                    <div class="item-total">
                        <p>Total</p>
                        <strong>R<?= number_format((float)$r['price']*(int)$r['quantity'],2) ?>
                        </strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
        <section class="information-grid">
            <div class="info-card">
                <h2>Delivery Information</h2>
                <div class="info-row">
                    <span>Address</span>
                    <strong>
                        <?= h($order['deliveryAddress']) ?>
                    </strong>
                </div>
                <div class="info-row">
                    <span>Delivery status</span>
                    <strong>
                        <?= h($order['status']) ?>
                    </strong>
                </div>
            </div>
            <div class="info-card">
                <h2>Payment Information</h2>
                <div class="info-row">
                    <span>Payment Method</span>
                    <strong>
                        <?= h($payment['paymentMethod']??'Not recorded') ?>
                    </strong>
                </div>
                <div class="info-row">
                    <span>Payment Status</span>
                    <strong>
                        <?= h($payment['paymentStatus']??'Pending') ?>
                    </strong>
                </div>
            </div>
        </section>
        <section class="summary-card">
            <h2>Order Summary</h2>
            <div class="summary-row">
                <span>Subtotal</span>
                <strong>R<?= number_format($total,2) ?>
                </strong>
            </div>
            <div class="summary-row">
                <span>Delivery Fee</span>
                <strong>R0.00</strong>
            </div>
            <div class="summary-row">
                <span>Total</span>
                <strong>R<?= number_format($total,2) ?>
                </strong>
            </div>
        </section>
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
