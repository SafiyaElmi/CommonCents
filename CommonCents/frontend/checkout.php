<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cart.php';
require_once __DIR__ . '/../classes/CartItem.php';
if(!isset($_SESSION['userID'])) {
    header('Location: ../authentication/login.php');
    exit;
}
$userID=(int)$_SESSION['userID'];
$cartModel=new Cart();
$cart=$cartModel->getByUser($userID);
if(!$cart) {
    $_SESSION['error']='Your cart is empty.';
    header('Location: cart.php');
    exit;
}
$cartID=(int)$cart['cartID'];
$itemsModel=new CartItem();
$itemsResult=$itemsModel->getByCart($cartID);
$rows=[];
$total=0;
while($r=$itemsResult->fetch_assoc()) {
    $rows[]=$r;
    $total+=(float)$r['price']*(int)$r['quantity'];
}
if(!$rows) {
    header('Location: cart.php');
    exit;
}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST') {
    $address=trim($_POST['address']??'');
    $method=$_POST['method']??'Card';
    if($address==='') {
        $error='Please enter a delivery or collection address.';
    }
    elseif(!in_array($method,['Card','EFT','PayFast','Cash'],true)) {
        $error='Please choose a valid payment method.';
    }
    else {
        try {
            $conn->begin_transaction();
            $date=date('Y-m-d H:i:s');
            $status='Pending';
            $stmt=$conn->prepare('INSERT INTO `order` (orderDate,status,deliveryAddress,userID) VALUES (?,?,?,?)');
            $stmt->bind_param('sssi',$date,$status,$address,$userID);
            $stmt->execute();
            $orderID=(int)$conn->insert_id;
            $oi=$conn->prepare('INSERT INTO orderitem (quantity,price,orderID,listingID) VALUES (?,?,?,?)');
            $mark=$conn->prepare("UPDATE listing SET status='Sold' WHERE listingID=? AND status='Available' AND userID<>?");
            $sellerNotification=$conn->prepare('INSERT INTO notification (message, notificationDate, userID) VALUES (?, ?, ?)');
            foreach($rows as $r) {
                $qty=(int)$r['quantity'];
                $price=(float)$r['price'];
                $listingID=(int)$r['listingID'];
                $oi->bind_param('idii',$qty,$price,$orderID,$listingID);
                $oi->execute();

                $listingOwnerQuery=$conn->prepare('SELECT userID, title FROM listing WHERE listingID=?');
                $listingOwnerQuery->bind_param('i',$listingID);
                $listingOwnerQuery->execute();
                $listingOwner=$listingOwnerQuery->get_result()->fetch_assoc();

                if ($listingOwner && (int)$listingOwner['userID'] !== $userID) {
                    $mark->bind_param('ii',$listingID,$userID);
                    $mark->execute();

                    $sellerMessage='A new order (#ORD-'.$orderID.') includes your listing: '.$listingOwner['title'].'.';
                    $sellerDate=date('Y-m-d H:i:s');
                    $sellerID=(int)$listingOwner['userID'];
                    $sellerNotification->bind_param('ssi',$sellerMessage,$sellerDate,$sellerID);
                    $sellerNotification->execute();
                }
            }

            $buyerMessage='Your order (#ORD-'.$orderID.') was placed successfully. Payment is pending confirmation.';
            $buyerDate=date('Y-m-d H:i:s');
            $buyerNotification=$conn->prepare('INSERT INTO notification (message, notificationDate, userID) VALUES (?, ?, ?)');
            $buyerNotification->bind_param('ssi',$buyerMessage,$buyerDate,$userID);
            $buyerNotification->execute();
            $paymentStatus='Pending';
            $paymentDate=date('Y-m-d H:i:s');
            $pay=$conn->prepare('INSERT INTO payment (amount,paymentMethod,paymentStatus,paymentDate,orderID) VALUES (?,?,?,?,?)');
            $pay->bind_param('dsssi',$total,$method,$paymentStatus,$paymentDate,$orderID);
            $pay->execute();
            $delete=$conn->prepare('DELETE FROM cartitem WHERE cartID=?');
            $delete->bind_param('i',$cartID);
            $delete->execute();
            $conn->commit();
            $_SESSION['success']='Order placed successfully. Payment is pending confirmation.';
            header('Location: order-details.php?id='.$orderID);
            exit;
        }
        catch(Throwable $e) {
            $conn->rollback();
            $error='We could not place your order. Please check your cart and try again.';
        }
    }
}
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
        <title>Checkout · CommonCents</title>
    </head>
    <body class="page-checkout">
        <div class="mobile-frame">
            <section class="view-screen active">
                <header class="top-bar">
                    <a class="icon-button" href="cart.php" aria-label="Back to cart">←</a>
                    <div class="breadcrumb-trail">
                        <a href="cart.php">Cart</a> &gt; <span>Place Order</span> &gt; <span class="active-crumb">Pay</span>
                    </div>
                </header>
                <div class="scroll-content">
                    <div class="checkout-intro">
                        <h1 class="page-title">Checkout</h1>
                        <p>Review your order and select a payment method.</p>
                    </div>
                    <?php if($error): ?>
                        <p class="error-message">
                            <?= h($error) ?>
                        </p>
                    <?php endif; ?>
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        <?php foreach($rows as $r): ?>
                            <div class="item-line">
                                <span>
                                    <?= h($r['title']) ?> × <?= (int)$r['quantity'] ?>
                                </span>
                                <strong>R<?= number_format((float)$r['price']*(int)$r['quantity'],2) ?>
                                </strong>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="item-line">
                            <strong>Total</strong>
                            <strong>R<?= number_format($total,2) ?>
                            </strong>
                        </div>
                    </div>
                    <div class="card-section">
                        <h3>Delivery / Collection Address</h3>
                        <p>Enter where you would like to receive or collect your order.</p>
                    </div>
                    <form method="post" class="checkout-form">
                        <label class="field-label" for="address">Address</label>
                        <input type="text" id="address" name="address" value="<?= h($_POST['address']??'') ?>" placeholder="Campus or street address" required>
                        <h3 class="payment-method-heading">Select Payment Method</h3>
                        <label class="method-choice">
                            <input type="radio" name="method" value="Card" <?= ($_POST['method']??'Card')==='Card'?'checked':'' ?>>
                            <span>
                                <strong>Credit / Debit Card</strong>
                                <br>
                                <small>Payment status remains pending until confirmed</small>
                            </span>
                        </label>
                        <label class="method-choice">
                            <input type="radio" name="method" value="EFT" <?= ($_POST['method']??'')==='EFT'?'checked':'' ?>>
                            <span>
                                <strong>Instant EFT</strong>
                                <br>
                                <small>Bank transfer</small>
                            </span>
                        </label>
                        <label class="method-choice">
                            <input type="radio" name="method" value="PayFast" <?= ($_POST['method']??'')==='PayFast'?'checked':'' ?>>
                            <span>
                                <strong>PayFast</strong>
                                <br>
                                <small>Gateway integration not configured</small>
                            </span>
                        </label>
                        <label class="method-choice">
                            <input type="radio" name="method" value="Cash" <?= ($_POST['method']??'')==='Cash'?'checked':'' ?>>
                            <span>
                                <strong>Cash on Pickup</strong>
                            </span>
                        </label>
                        <div class="protection-badge">
                            <span>Buyer protection applies to all purchases.</span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Confirm Order</button>
                    </form>
                    <a class="checkout-link" href="home.php">Continue Shopping</a>
                </div>
            </section>
        </div>
        <script src="../assets/script.js" defer>
        </script>
    </body>
</html>
