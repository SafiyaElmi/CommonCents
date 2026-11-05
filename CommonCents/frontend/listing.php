<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Listing.php';
require_once __DIR__ . '/../classes/Cart.php';
require_once __DIR__ . '/../classes/CartItem.php';
$id=(int)($_GET['id']??0);
$model=new Listing();
$listing=$model->getById($id);
if(!$listing) {
    http_response_code(404);
    exit('Listing not found. <a href="home.php">Return to products</a>');
}
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['addToCart'])) {
    if(!isset($_SESSION['userID'])) {
        header('Location: ../authentication/login.php');
        exit;
    }
    $userID=(int)$_SESSION['userID'];
    if($userID===(int)$listing['userID']) {
        $_SESSION['error']='You cannot add your own listing to your cart.';
    }
    elseif($listing['status']!=='Available') {
        $_SESSION['error']='This listing is no longer available.';
    }
    else {
        $cartModel=new Cart();
        $cart=$cartModel->getByUser($userID);
        if(!$cart) {
            $cartModel->create($userID);
            $cart=$cartModel->getByUser($userID);
        }
        $cartID=(int)$cart['cartID'];
        $listingID=(int)$listing['listingID'];
        $check=$conn->prepare('SELECT cartItemID,quantity FROM cartitem WHERE cartID=? AND listingID=?');
        $check->bind_param('ii',$cartID,$listingID);
        $check->execute();
        $existing=$check->get_result()->fetch_assoc();
        if($existing) {
            $newQty=(int)$existing['quantity']+1;
            $upd=$conn->prepare('UPDATE cartitem SET quantity=? WHERE cartItemID=?');
            $upd->bind_param('ii',$newQty,$existing['cartItemID']);
            $upd->execute();
        }
        else {
            $items=new CartItem();
            $items->create(1,$cartID,$listingID);
        }
        $_SESSION['success']='Item added to your cart.';
        header('Location: cart.php');
        exit;
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
        <title>
        <?= h($listing['title']) ?> · CommonCents</title>
    </head>
    <body class="page-listing">

        <?php
        require __DIR__ . '/../partials/marketplace-nav.php';
        ?>

        <?php if(!empty($_SESSION['error'])): ?>
            <p class="error-note">
                <?= h($_SESSION['error']);unset($_SESSION['error']); ?>
            </p>
        <?php endif; ?>

        <main class="listing-details">
            <a href="home.php" class="back-link">← Back to Listings</a>
            <section class="listing-top">
                <div class="listing-gallery">
                    <div class="main-image">
                        <span class="badge">
                            <?= h($listing['status']) ?>
                        </span>
                        <?php if($listing['imageURL']): ?>
                            <img src="<?= h($listing['imageURL']) ?>" alt="<?= h($listing['title']) ?>">
                        <?php else: ?>
                            <div class="image-placeholder">📦</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="listing-info">
                    <span class="category-tag">
                        <?= h($listing['categoryName']) ?>
                    </span>
                    <h1>
                        <?= h($listing['title']) ?>
                    </h1>
                    <p class="posted-date">Posted <?= h(date('d F Y',strtotime($listing['datePosted']))) ?>
                    </p>
                    <strong class="price">R <?= number_format((float)$listing['price'],2) ?>
                    </strong>
                    <p class="description">
                        <?= nl2br(h($listing['description'])) ?>
                    </p>
                    <div class="action-buttons">
                        <?php if(isset($_SESSION['userID']) && (int)$_SESSION['userID']!==(int)$listing['userID'] && $listing['status']==='Available'): ?>
                            <form method="post">
                                <button class="add-to-cart" name="addToCart" value="1">🛒 Add to Cart</button>
                            </form>
                        <?php elseif(!isset($_SESSION['userID'])): ?>
                            <a class="add-to-cart" href="../authentication/login.php">Login to Buy</a>
                        <?php else: ?>
                            <span class="unavailable">Not available to purchase</span>
                        <?php endif; ?>
                    </div>
                    <div class="meta-row">
                        <span>Condition: <strong>
                                <?= h($listing['type']) ?>
                            </strong>
                        </span>
                        <span>Status: <strong>
                                <?= h($listing['status']) ?>
                            </strong>
                        </span>
                    </div>
                    <div class="seller-info">
                        <p>Seller</p>
                        <strong>
                            <?= h($listing['firstName'].' '.$listing['lastName']) ?>
                        </strong>
                    </div>
                </div>
            </section>
            <section class="information-grid">
                <div class="info-card">
                    <h2>Product Information</h2>
                    <div class="info-row">
                        <span>Category</span>
                        <strong>
                            <?= h($listing['categoryName']) ?>
                        </strong>
                    </div>
                    <div class="info-row">
                        <span>Condition</span>
                        <strong>
                            <?= h($listing['type']) ?>
                        </strong>
                    </div>
                </div>
                <div class="info-card">
                    <h2>Availability</h2>
                    <div class="info-row">
                        <span>Listing Status</span>
                        <strong>
                            <?= h($listing['status']) ?>
                        </strong>
                    </div>
                    <div class="info-row">
                        <span>Listed on</span>
                        <strong>
                            <?= h(date('d M Y',strtotime($listing['datePosted']))) ?>
                        </strong>
                    </div>
                </div>
            </section>
        </main>
        <footer class="site-footer">© <?= date('Y') ?> CommonCents</footer>
        <script src="../assets/script.js" defer>
        </script>
    </body>
</html>
