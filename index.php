<?php
session_start();
if(isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: /RestoPay/$role/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Kasir Restoran</title>
    <!-- Bootstrap 5 CSS -->
    <link href="/RestoPay/assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="/RestoPay/assets/css/fontawesome/css/fontawesome.css" rel="stylesheet" />
    <link href="/RestoPay/assets/css/fontawesome/css/brands.css" rel="stylesheet" />
    <link href="/RestoPay/assets/css/fontawesome/css/solid.css" rel="stylesheet" />
    <link href="/RestoPay/assets/css/fontawesome/css/sharp-thin.css" rel="stylesheet" />
    <link href="/RestoPay/assets/css/fontawesome/css/duotone-thin.css" rel="stylesheet" />
    <link href="/RestoPay/assets/css/fontawesome/css/sharp-duotone-thin.css" rel="stylesheet" />

    <!-- SweetAlert2 -->
    <link href="/RestoPay/assets/css/sweetalert2.min.css" rel="stylesheet">
    <script src="/RestoPay/assets/js/sweetalert2.all.min.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --success-color: #2ec4b6;
            --warning-color: #ff9f1c;
            --info-color: #4cc9f0;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f5f7fb;
            overflow: hidden;
        }

        .login-wrapper {
            display: flex;
            height: 100vh;
        }

        .login-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background: white;
        }

        .image-section {
            flex: 1.2;
            background: linear-gradient(135deg, rgb(14, 51, 99) 0%, rgb(25, 91, 65) 100%);
            position: relative;
            overflow: hidden;
        }

        .image-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
        }
        
        .login-container {
            width: 100%;
            max-width: 380px;
        }

        .login-header {
            margin-bottom: 3.5rem;
        }

        .login-header h4 {
            color: var(--dark-color);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: #6b7280;
            font-size: 1.1rem;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 1rem;
            position: relative;
        }

        .form-label {
            display: block;
            color: var(--dark-color);
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            background: white;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 0.6rem;
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background: #25a093;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 196, 182, 0.2);
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: #fee2e2;
            color: #dc2626;
            border: none;
        }

        .alert i {
            font-size: 1.25rem;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }

        .input-with-icon {
            padding-left: 2.5rem;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            cursor: pointer;
        }

        @media (max-width: 1024px) {
            .image-section {
                display: none;
            }
            
            .login-section {
                padding: 2rem;
            }

            .login-container {
                max-width: 400px;
            }
        }

        .copyright {
            color: #6b7280;
            font-size: 0.8rem;
            text-align: center;
            width: 100%;
            margin-top: 2rem;
        }

        .copyright a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .copyright a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-section">
            <div class="login-container">
                <div class="login-header">
                    <h4>Selamat Datang Kembali</h4>
                    <p>Silakan masuk untuk melanjutkan ke sistem kasir restoran</p>
                </div>
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="/RestoPay/auth/login.php" method="POST">
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <div class="position-relative">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="username" class="form-control input-with-icon" id="username" placeholder="Masukkan username" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="position-relative">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-control input-with-icon" id="password" placeholder="Masukkan password" required>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Sistem
                    </button>
                </form>
                <div class="copyright">
                    &copy; <?php echo date('Y'); ?> Sistem Kasir Restoran. 
                    <br>
                    Dibuat oleh <a href="#">Hafiz Agha Al-Baith</a>
                </div>
            </div>
        </div>
        <div class="image-section">
            <img src="/RestoPay/assets/images/login-page.jpg" alt="Restaurant Image">
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="/RestoPay/assets/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="/RestoPay/assets/js/jquery.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this;
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
