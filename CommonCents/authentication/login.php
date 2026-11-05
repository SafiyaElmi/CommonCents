<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="../assets/styles.css">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login · CommonCents</title>
    </head>
    <body class="page-auth">

        <div class="auth-split">

            <div class="auth-form">
                <a href="../frontend/index.php" class="brand">
                    <span class="cc-brand-mark" aria-hidden="true">C</span>
                </a>

                <h2>Welcome back</h2>
                <p class="sub">Sign in to continue buying &amp; selling.</p>

                <?php if (!empty($_SESSION['error'])): ?>

                    <div class="alert alert-danger auto-dismiss">
                        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>

                <?php endif; ?>

                <?php if (!empty($_SESSION['success'])): ?>

                    <div class="alert alert-success auto-dismiss">
                        <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>

                <?php endif; ?>

                <form action="processLogin.php" method="POST" novalidate>
                    <div class="mb-3">
                        <label>Email</label>
                        <div class="input-icon">
                            <i class="bi bi-envelope">
                            </i>
                            <input type="email" name="email" class="form-control" placeholder="you@campus.edu" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label>Password</label>
                        <div class="input-icon">
                            <i class="bi bi-lock">
                            </i>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="text-end mb-3">
                        <a href="#" class="small muted">Forgot password?</a>
                    </div>
                    <button class="btn-purple">Login</button>
                </form>

                <div class="divider-or">OR</div>

                <div class="social-row">
                    <a href="#" class="btn-ghost">
                        <i class="bi bi-google">
                        </i>
                    </a>
                    <a href="#" class="btn-ghost">
                        <i class="bi bi-apple">
                        </i>
                    </a>
                    <a href="#" class="btn-ghost">
                        <i class="bi bi-facebook">
                        </i>
                    </a>
                </div>

                <p class="text-center mt-4 mb-0 small muted">
                    New here? <a href="register.php" class="text-purple fw-bold">Create an account</a>
                </p>
            </div>

            <div class="auth-art">
                <div class="art-copy">
                    <h3>Buy &amp; sell on campus,<br>the easy way.</h3>
                    <p>Find great deals from students just like you.</p>
                </div>
                <img class="art-img" src="../images/laptop.avif" alt="">
            </div>

        </div>
        <script src="../assets/script.js" defer>
        </script>
    </body>
</html>
