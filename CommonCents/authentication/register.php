<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="../assets/styles.css">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Create Account · CommonCents</title>
    </head>
    <body class="page-auth">

        <div class="auth-split">

            <div class="auth-form">
                <a href="../frontend/index.php" class="brand">
                    <span class="cc-brand-mark" aria-hidden="true">C</span>
                </a>

                <h2>Create your account</h2>
                <p class="sub">Join students buying &amp; selling on campus.</p>

                <?php if (!empty($_SESSION['error'])): ?>

                    <div class="alert alert-danger auto-dismiss">
                        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>

                <?php endif; ?>

                <form action="processRegister.php" method="POST" novalidate>
                    <div class="mb-3">
                        <label>First Name</label>
                        <div class="input-icon">
                            <i class="bi bi-person">
                            </i>
                            <input type="text" name="first_name" class="form-control" placeholder="John" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Last Name</label>
                        <div class="input-icon">
                            <i class="bi bi-person">
                            </i>
                            <input type="text" name="last_name" class="form-control" placeholder="Doe" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <div class="input-icon">
                            <i class="bi bi-envelope">
                            </i>
                            <input type="email" name="email" class="form-control" placeholder="you@campus.edu" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <div class="input-icon">
                            <i class="bi bi-lock">
                            </i>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="6" required>
                        </div>
                    </div>
                    <button class="btn-purple">Create Account</button>
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
                    Already have an account? <a href="login.php" class="text-purple fw-bold">Login</a>
                </p>
            </div>

            <div class="auth-art">
                <div class="art-copy">
                    <h3>Join the campus<br>marketplace today.</h3>
                    <p>Buy smart. Sell fast. Save money.</p>
                </div>
                <img class="art-img" src="../images/person.avif" alt="">
            </div>

        </div>
        <script src="../assets/script.js" defer>
        </script>
    </body>
</html>
