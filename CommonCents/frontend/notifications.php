<?php
require_once __DIR__ . '/../config/database.php';
if (!isset($_SESSION['userID'])) {
    header('Location: ../authentication/login.php');
    exit;
}
$userID = (int) $_SESSION['userID'];
function notificationEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    $notificationID = filter_input(INPUT_POST, 'notificationID', FILTER_VALIDATE_INT);
    if ($notificationID) {
        $stmt = $conn->prepare('DELETE FROM notification WHERE notificationID = ? AND userID = ?');
        $stmt->bind_param('ii', $notificationID, $userID);
        $stmt->execute();
    }
    header('Location: notifications.php');
    exit;
}
$stmt = $conn->prepare('SELECT notificationID, message, notificationDate FROM notification WHERE userID = ? ORDER BY notificationDate DESC');
$stmt->bind_param('i', $userID);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Notifications';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="../assets/styles.css">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notifications · CommonCents</title>
    </head>
    <body class="page-notifications">

        <?php
        require __DIR__ . '/../partials/marketplace-nav.php';
        ?>

        <main class="main-container">
            <div class="page-header">
                <div>
                    <h1>Notifications</h1>
                    <p>Stay updated with your marketplace activity.</p>
                </div>
                <button class="secondary-button" type="button" onclick="markAllAsRead()">✓ Mark all as read</button>
            </div>
            <div class="notification-summary">
                <div>
                    <strong id="notificationCount">
                        <?= count($notifications) ?>
                    </strong>
                    <span>notifications</span>
                </div>
            </div>
            <section class="notifications-container" id="notificationsList">

                <?php foreach ($notifications as $n): $date = strtotime($n['notificationDate']); $message = $n['message']; $icon = '🔔'; $title = 'Marketplace Update'; if (stripos($message, 'order') !== false) { $icon = '📦'; $title = 'Order Update'; } elseif (stripos($message, 'review') !== false) { $icon = '⭐'; $title = 'New Review'; } elseif (stripos($message, 'message') !== false) { $icon = '💬'; $title = 'New Message'; } ?>

                    <article class="notification-card unread" id="notification-<?= (int)$n['notificationID'] ?>">
                    <div class="notification-icon">
                        <?= $icon ?>
                    </div>
                    <div class="notification-content">
                        <div class="notification-header">
                            <div>
                                <h2>
                                    <?= notificationEscape($title) ?>
                                </h2>
                                <p class="notification-time">
                                    <?= $date ? notificationEscape(date('d M Y, H:i', $date)) : '' ?>
                                </p>
                            </div>
                            <span class="unread-dot">
                            </span>
                        </div>
                        <p>
                            <?= notificationEscape($message) ?>
                        </p>
                        <div class="notification-actions">
                        <button type="button" onclick="markAsRead(<?= (int)$n['notificationID'] ?>)">Mark as read</button>
                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this notification?')">
                            <input type="hidden" name="notificationID" value="<?= (int)$n['notificationID'] ?>">
                            <button type="submit" name="delete_notification" class="delete-button">Delete</button>
                        </form>
                    </div>
                </div>
            </article>

        <?php endforeach; ?>
    </section>
    <div id="emptyNotifications" class="empty-state <?= $notifications ? 'hidden' : '' ?>">
    <div class="empty-icon">🔔</div>
    <h2>No Notifications</h2>
    <p>You're all caught up! New notifications will appear here.</p>
</div>
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
