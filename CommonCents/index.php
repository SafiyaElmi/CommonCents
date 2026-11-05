<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Location: frontend/index.php');
exit;
