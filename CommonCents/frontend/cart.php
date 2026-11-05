<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cart.php';
require_once __DIR__ . '/../classes/CartItem.php';
if (!isset($_SESSION['userID'])) {
    header('Location: ../authentication/login.php');
    exit;
}
$userID=(int)$_SESSION['userID'];
$cartModel=new Cart();
$cart=$cartModel->getByUser($userID);
$cartID=$cart ? (int)$cart['cartID'] : 0;
$itemsModel=new CartItem();
if ($_SERVER['REQUEST_METHOD']==='POST' && $cartID) {
    if (isset($_POST['update_quantity'])) {
        $itemID=(int)($_POST['cartItemID']??0);
        $quantity=max(1,min(99,(int)($_POST['quantity']??1)));
        $check=$conn->prepare('SELECT cartItemID FROM cartitem WHERE cartItemID=? AND cartID=?');
        $check->bind_param('ii',$itemID,$cartID);
        $check->execute();
        if ($check->get_result()->num_rows) {
            $itemsModel->update($itemID,$quantity);
        }
    }
    elseif (isset($_POST['remove_item'])) {
        $itemID=(int)($_POST['cartItemID']??0);
        $check=$conn->prepare('SELECT cartItemID FROM cartitem WHERE cartItemID=? AND cartID=?');
        $check->bind_param('ii',$itemID,$cartID);
        $check->execute();
        if ($check->get_result()->num_rows) {
            $itemsModel->delete($itemID);
        }
    }
    elseif (isset($_POST['clear_cart'])) {
        $stmt=$conn->prepare('DELETE FROM cartitem WHERE cartID=?');
        $stmt->bind_param('i',$cartID);
        $stmt->execute();
    }
    header('Location: cart.php');
    exit;
}
$items=$cartID ? $itemsModel->getByCart($cartID) : false;
$rows=[];
$subtotal=0;
if ($items) while($r=$items->fetch_assoc()) {
    $rows[]=$r;
    $subtotal+=(float)$r['price']*(int)$r['quantity'];
}
$pageTitle='Shopping Cart';
?>

<!doctype html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="../assets/styles.css">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>
        <?= htmlspecialchars($pageTitle) ?> · CommonCents</title>
    </head>
    <body class="page-cart">

        <?php
        require __DIR__ . '/../partials/marketplace-nav.php';
        ?>

        <main class="cart-page">
            <section class="page-heading">
                <h1>Your Shopping Cart</h1>
                <span>
                </span>
                <p>Review your items before checking out.</p>
            </section>

            <?php if (!empty($_SESSION['success'])): ?>
                <p class="cart-notice">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </p>
            <?php endif; ?>

            <section class="cart-layout">
                <div class="cart-items-panel">
                    <div class="section-heading-row">
                        <div>
                            <h2>Cart Items (<?= count($rows) ?>)</h2>
                            <span>
                            </span>
                        </div>
                        <?php if($rows): ?>
                            <form method="post">
                                <button class="text-button" name="clear_cart" value="1" type="submit" onclick="return confirm('Clear all items from your cart?')">Clear cart</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if (!$rows): ?>
                        <div class="empty-cart">
                            <div class="empty-icon">🛒</div>
                            <h3>Your cart is empty</h3>
                            <p>Find something you love and add it to your cart.</p>
                            <a href="home.php" class="primary-button">Continue Shopping</a>
                        </div>
                    <?php else: ?>

                        <?php foreach($rows as $item): ?>
                            <article class="cart-item">
                                <div class="item-image">
                                    <?php if($item['imageURL']): ?>
                                        <img src="<?= htmlspecialchars($item['imageURL']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                    <?php else: ?>
                                        <span>📦</span>
                                    <?php endif; ?>
                                </div>
                                <div class="item-info">
                                    <h3>
                                        <?= htmlspecialchars($item['title']) ?>
                                    </h3>
                                    <p class="item-price">R<?= number_format((float)$item['price'],2) ?>
                                    </p>
                                    <form method="post" class="quantity-form">
                                        <input type="hidden" name="cartItemID" value="<?= (int)$item['cartItemID'] ?>">
                                        <label>Quantity <input type="number" name="quantity" min="1" max="99" value="<?= (int)$item['quantity'] ?>">
                                        </label>
                                        <button class="text-button" name="update_quantity" value="1">Update</button>
                                    </form>
                                </div>
                                <div class="item-actions">
                                    <strong>R<?= number_format((float)$item['price']*(int)$item['quantity'],2) ?>
                                    </strong>
                                    <form method="post">
                                        <input type="hidden" name="cartItemID" value="<?= (int)$item['cartItemID'] ?>">
                                        <button class="text-button remove-button" name="remove_item" value="1" onclick="return confirm('Remove this item?')">Remove</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>
                <aside class="summary-card">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>R<?= number_format($subtotal,2) ?>
                        </strong>
                    </div>
                    <div class="summary-row">
                        <span>Delivery</span>
                        <strong>Calculated at checkout</strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <strong>R<?= number_format($subtotal,2) ?>
                        </strong>
                    </div>
                    <?php if($rows): ?>
                        <a class="checkout-button" href="checkout.php">Proceed to Checkout</a>
                    <?php else: ?>
                        <a class="checkout-button" href="home.php">Browse Products</a>
                    <?php endif; ?>
                    <a class="continue-link" href="home.php">← Continue Shopping</a>
                </aside>
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
