<?php
// login.php
session_start();
require_once 'database.php';

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'Administrator':
            header("Location: Admin/dashboard.php");
            break;
        case 'Waiter':
            header("Location: Waiter/dashboard.php");
            break;
        case 'Kitchen Staff':
            header("Location: Kitchen/dashboard.php");
            break;
        default:
            header("Location: login.php");
    }
    exit();
}

$error = '';
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <title>LUXE · Staff Login</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* LOGIN PAGE */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #0a0a0a;
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .logo {
            font-size: 48px;
            font-weight: 300;
            letter-spacing: 8px;
            color: #d4af37;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .logo-subtitle {
            color: #a89436;
            font-size: 12px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 60px;
            font-weight: 300;
        }

        .login-box {
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 80px 70px;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.05) 0%, rgba(0, 0, 0, 0.5) 100%);
            margin-bottom: 40px;
        }

        .form-title {
            color: #a89436;
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 35px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            color: #a89436;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background-color: #1a1a1a;
            border: 1px solid rgba(212, 175, 55, 0.2);
            color: #e0e0e0;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input::placeholder {
            color: #666666;
        }

        .form-group input:focus {
            outline: none;
            border-color: rgba(212, 175, 55, 0.6);
            background-color: #252525;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.1);
        }

        .form-actions {
            margin-top: 30px;
        }

        .btn-signin {
            width: 100%;
            padding: 14px;
            background-color: #d4af37;
            color: #0a0a0a;
            border: none;
            font-size: 13px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .btn-signin:hover {
            background-color: #e5c158;
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
        }

        .forgot-password {
            color: #888888;
            font-size: 12px;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #d4af37;
        }

        .security-notice {
            font-size: 11px;
            color: #666666;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(212, 175, 55, 0.1);
        }

        .back-btn {
            display: block;
            margin-top: 10px;
            text-align: center;
            color: #a89436;
            font-size: 12px;
            text-decoration: none;
            transition: 0.3s;
        }

        .back-btn:hover {
            color: #d4af37;
        }

        /* Error message styling */
        .error-message {
            background-color: rgba(244, 67, 54, 0.15);
            border: 1px solid rgba(244, 67, 54, 0.3);
            color: #ff6b6b;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: left;
        }

        .error-message i {
            margin-right: 8px;
        }

        @media (max-width: 1024px) {
            .login-box {
                padding: 40px 30px;
            }

            .logo {
                font-size: 42px;
            }
        }
    </style>

</head>

<body>

<section class="dashboard">

    <div class="login-container">

        <div class="logo">
            L U X E
        </div>

        <div class="logo-subtitle">
            Staff Management Portal
        </div>

        <div class="login-box">

            <div class="form-title">
                Sign In To Continue
            </div>

            <!-- Display error messages -->
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Updated form - submits to authenticate.php -->
            <form action="authenticate.php" method="POST">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="AD001, WEP001, KD001" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-actions">

                    <button type="submit" class="btn-signin">
                        Sign In
                    </button>

                    <a href="#" class="forgot-password">
                        Forgot Password?
                    </a>

                    <div class="security-notice">
                        Luxe Fine Dining - Authorised Personnel Only
                    </div>

                    <a href="../index.php" class="back-btn">Back</a>

                </div>

            </form>

        </div>

    </div>

</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>