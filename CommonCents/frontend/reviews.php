<?php
require_once __DIR__ . '/../config/database.php';
if (!isset($_SESSION['userID'])) {
    header('Location: ../authentication/login.php');
    exit;
}
$userID=(int)$_SESSION['userID'];
function reviewEscape($v) {
    return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
}
$listingID=filter_input(INPUT_GET,'listingID',FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST,'listingID',FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action'] ?? '';
    if ($action==='save' && $listingID) {
        $rating=filter_input(INPUT_POST,'rating',FILTER_VALIDATE_INT);
        $comment=trim($_POST['reviewComment'] ?? '');
        if ($rating>=1 && $rating<=5 && $comment!=='') {
            $check=$conn->prepare('SELECT reviewID FROM review WHERE userID=? AND listingID=? LIMIT 1');
            $check->bind_param('ii',$userID,$listingID);
            $check->execute();
            $existing=$check->get_result()->fetch_assoc();
            if ($existing) {
                $stmt=$conn->prepare('UPDATE review SET rating=?, comment=?, reviewDate=NOW() WHERE reviewID=? AND userID=?');
                $stmt->bind_param('isii',$rating,$comment,$existing['reviewID'],$userID);
            }
            else {
                $stmt=$conn->prepare('INSERT INTO review (rating,comment,reviewDate,userID,listingID) VALUES (?,?,NOW(),?,?)');
                $stmt->bind_param('isii',$rating,$comment,$userID,$listingID);
            }
            $stmt->execute();

            if (!$existing) {
                $ownerQuery=$conn->prepare('SELECT userID, title FROM listing WHERE listingID=?');
                $ownerQuery->bind_param('i',$listingID);
                $ownerQuery->execute();
                $listingOwner=$ownerQuery->get_result()->fetch_assoc();
                if ($listingOwner && (int)$listingOwner['userID'] !== $userID) {
                    $reviewMessage='A new review was posted for your listing: '.$listingOwner['title'].'.';
                    $notificationDate=date('Y-m-d H:i:s');
                    $ownerID=(int)$listingOwner['userID'];
                    $notificationStmt=$conn->prepare('INSERT INTO notification (message, notificationDate, userID) VALUES (?, ?, ?)');
                    $notificationStmt->bind_param('ssi',$reviewMessage,$notificationDate,$ownerID);
                    $notificationStmt->execute();
                }
            }
        }
        header('Location: reviews.php?listingID='.$listingID);
        exit;
    }
    if (in_array($action,['delete'],true) && isset($_POST['reviewID'])) {
        $rid=(int)$_POST['reviewID'];
        $stmt=$conn->prepare('DELETE FROM review WHERE reviewID=? AND userID=?');
        $stmt->bind_param('ii',$rid,$userID);
        $stmt->execute();
        header('Location: reviews.php'.($listingID?'?listingID='.$listingID:''));
        exit;
    }
}
$listing=null;
$reviews=null;
if ($listingID) {
    $stmt=$conn->prepare('SELECT listingID,title,description,imageURL FROM listing WHERE listingID=?');
    $stmt->bind_param('i',$listingID);
    $stmt->execute();
    $listing=$stmt->get_result()->fetch_assoc();
}
if (!$listing) {
    $stmt=$conn->prepare('SELECT listingID,title,description,imageURL FROM listing ORDER BY datePosted DESC LIMIT 1');
    $stmt->execute();
    $listing=$stmt->get_result()->fetch_assoc();
    $listingID=$listing ? (int)$listing['listingID'] : 0;
}
if ($listingID) {
    $stmt=$conn->prepare('SELECT r.reviewID,r.rating,r.comment,r.reviewDate,r.userID,u.firstName,u.lastName FROM review r JOIN `user` u ON u.userID=r.userID WHERE r.listingID=? ORDER BY r.reviewDate DESC');
    $stmt->bind_param('i',$listingID);
    $stmt->execute();
    $reviews=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
$avg=0;
if ($reviews) {
    $avg=array_sum(array_column($reviews,'rating'))/count($reviews);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="../assets/styles.css">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reviews · CommonCents</title>
    </head>
    <body class="page-reviews">
        <?php
        require __DIR__ . '/../partials/marketplace-nav.php';
        ?>

        <main class="main-container">
            <div class="page-header">
                <div>
                    <h1>Reviews</h1>
                    <p>View and manage reviews for this listing.</p>
                </div>
                <button class="back-button" onclick="goBack()">← Back</button>
            </div>

            <?php if(!$listing): ?>
                <div class="empty-state">
                    <h2>No listings available</h2>
                    <p>Create or browse a listing before writing a review.</p>
                    <a href="home.php">Browse products</a>
                </div>
            <?php else: ?>

                <section class="listing-card">
                    <div class="listing-image">
                        <?php if($listing['imageURL']): ?>
                            <img src="<?=reviewEscape($listing['imageURL'])?>" alt="<?=reviewEscape($listing['title'])?>">
                        <?php else: ?>
                            Product Image
                        <?php endif; ?>
                    </div>
                    <div class="listing-info">
                        <h2>
                            <?=reviewEscape($listing['title'])?>
                        </h2>
                        <div class="rating-summary">
                            <span class="big-rating">
                                <?=number_format((float)$avg,1)?>
                            </span>
                            <div>
                                <div class="stars">
                                    <?=str_repeat('★',(int)round($avg)).str_repeat('☆',5-(int)round($avg))?>
                                </div>
                                <p>
                                <?=count($reviews ?: [])?> reviews</p>
                            </div>
                        </div>
                        <p class="listing-description">
                            <?=reviewEscape($listing['description'] ?? '')?>
                        </p>
                    </div>
                </section>
                <div class="content-grid">
                    <section class="review-form-card">
                        <h2>Write a Review</h2>
                        <p class="section-description">Share your experience with this listing.</p>
                        <form method="post" id="reviewForm">
                            <input type="hidden" name="listingID" value="<?=$listingID?>">
                            <input type="hidden" name="action" value="save">
                            <label>Your Rating</label>
                            <div class="star-selector">
                                <button type="button" onclick="selectRating(1)">★</button>
                                <button type="button" onclick="selectRating(2)">★</button>
                                <button type="button" onclick="selectRating(3)">★</button>
                                <button type="button" onclick="selectRating(4)">★</button>
                                <button type="button" onclick="selectRating(5)">★</button>
                            </div>
                            <input type="hidden" id="rating" name="rating" value="">
                            <label for="reviewComment">Your Review</label>
                            <textarea id="reviewComment" name="reviewComment" placeholder="Write your review here..." rows="6" required>
                            </textarea>
                            <button type="submit" class="primary-button">Submit Review</button>
                        </form>
                    </section>
                    <section class="reviews-section">
                        <div class="section-title">
                            <h2>Customer Reviews</h2>
                            <span>
                            <?=count($reviews ?: [])?> reviews</span>
                        </div>
                        <?php if (!$reviews): ?>
                            <div class="empty-state">
                                <h2>No reviews yet</h2>
                                <p>Be the first to review this listing.</p>
                            </div>
                        <?php else: ?>

                            <?php foreach($reviews as $r): $initials=strtoupper(substr($r['firstName'],0,1).substr($r['lastName'],0,1)); ?>
                                <article class="review-card">
                                    <div class="review-top">
                                        <div class="review-user">
                                            <div class="avatar">
                                                <?=reviewEscape($initials)?>
                                            </div>
                                            <div>
                                                <h3>
                                                    <?=reviewEscape($r['firstName'].' '.$r['lastName'])?>
                                                </h3>
                                                <p>
                                                    <?=reviewEscape(date('d M Y',strtotime($r['reviewDate'])))?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="review-rating">
                                            <?=str_repeat('★',(int)$r['rating']).str_repeat('☆',5-(int)$r['rating'])?>
                                        </div>
                                    </div>
                                    <p class="review-text">
                                        <?=nl2br(reviewEscape($r['comment']))?>
                                    </p>
                                    <?php if((int)$r['userID']===$userID): ?>
                                        <div class="review-actions">
                                        <button type="button" onclick="loadReview(<?= (int)$r['rating'] ?>,<?=json_encode($r['comment'],JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)?>)">Edit in form</button>
                                        <form method="post" class="inline-form" onsubmit="return confirm('Delete your review?')">
                                            <input type="hidden" name="listingID" value="<?=$listingID?>">
                                            <input type="hidden" name="reviewID" value="<?=(int)$r['reviewID']?>">
                                            <button type="submit" name="action" value="delete" class="delete-button">Delete</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </section>
            </div>
        <?php endif; ?>
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
