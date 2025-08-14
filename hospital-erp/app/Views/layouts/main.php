<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Hospital ERP' ?></title>
    <!-- In a real application, you would link your CSS files here -->
    <!-- <link rel="stylesheet" href="/assets/css/app.css"> -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            background-color: #343a40;
            color: #fff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .main-content {
            flex: 1;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .footer {
            background-color: #343a40;
            color: #fff;
            text-align: center;
            padding: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="header-container">
            <h1><a href="/" style="color: #fff; text-decoration: none;"><?= $title ?? 'Hospital ERP' ?></a></h1>
            <nav class="auth-nav">
                <?php
                $session = new \App\Libraries\Core\Session();
                if ($session->has('user')):
                    $user = $session->get('user');
                ?>
                    <span class="welcome-user">Welcome, <?= htmlspecialchars($user['name']) ?></span>
                    <a href="/logout" class="auth-link">Logout</a>
                <?php else: ?>
                    <a href="/login" class="auth-link">Login</a>
                    <a href="/register" class="auth-link">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <?php
            // The $content variable is passed from the BaseController's render method
            echo $content ?? '';
        ?>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Hospital ERP. All rights reserved.</p>
    </footer>

    <!-- In a real application, you would link your JS files here -->
    <!-- <script src="/assets/js/app.js"></script> -->
</body>
</html>
