<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Patient Portal' ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
        }
        .portal-header {
            background-color: #007bff;
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .portal-header h1 { margin: 0; font-size: 1.5rem; }
        .portal-header a { color: #fff; text-decoration: none; }
        .portal-container {
            display: flex;
        }
        .portal-sidebar {
            width: 250px;
            background: #343a40;
            color: #fff;
            min-height: calc(100vh - 70px); /* Full height minus header */
        }
        .portal-sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .portal-sidebar nav a {
            display: block;
            padding: 1rem;
            color: #adb5bd;
            text-decoration: none;
            border-bottom: 1px solid #495057;
        }
        .portal-sidebar nav a:hover, .portal-sidebar nav a.active {
            background: #495057;
            color: #fff;
        }
        .portal-content {
            flex: 1;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <?php $session = new \App\Libraries\Core\Session(); ?>
    <header class="portal-header">
        <h1>Patient Portal</h1>
        <nav>
            <?php if ($session->has('user')): ?>
                <span>Welcome, <?= htmlspecialchars($session->get('user')['name']) ?></span> |
                <a href="/logout">Logout</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="portal-container">
        <aside class="portal-sidebar">
            <nav>
                <ul>
                    <li><a href="/portal/dashboard" class="active">Dashboard</a></li>
                    <!-- Future links -->
                    <!-- <li><a href="/portal/appointments">Appointments</a></li> -->
                    <!-- <li><a href="/portal/records">My Records</a></li> -->
                    <!-- <li><a href="/portal/billing">Billing</a></li> -->
                </ul>
            </nav>
        </aside>
        <main class="portal-content">
            <?= $content ?? '' ?>
        </main>
    </div>
</body>
</html>
