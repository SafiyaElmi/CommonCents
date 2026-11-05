<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current = basename($_SERVER['PHP_SELF']);
$isAuth = in_array($current, ['login.php', 'register.php']);
$home = isset($_SESSION['userID']) ? 'home.php' : 'index.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title>
            <?= isset($pageTitle) ? htmlspecialchars($pageTitle) . " · CommonCents" : "CommonCents" ?>
        </title>
        <link rel="stylesheet" href="../assets/styles.css">
    </head>
    <body class="page-profile">

        <?php if (!$isAuth): ?>

            <?php
            require __DIR__ . '/marketplace-nav.php';
            ?>

        <?php endif; ?>

        <?php if (!$isAuth): ?>
            <div class="app-shell">
            <?php endif; ?>
