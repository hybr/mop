<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="V4L - Vocal 4 Local: Empowering Local Voices">
    <title><?php echo $pageTitle ?? 'V4L - Vocal 4 Local'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/toast.js" defer></script>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">V4L</a>

                <nav class="nav">
                    <a href="/organizations-directory.php">Directory</a>
                    <?php if (isset($auth) && $auth->isLoggedIn()): ?>
                        <a href="/dashboard.php">Dashboard</a>
                        <a href="/organizations.php">My Organizations</a>
                        <a href="/organization-departments.php">Departments</a>
                        <a href="/profile.php">Profile</a>
                        <a href="/logout.php" class="btn btn-primary">Logout</a>
                    <?php else: ?>
                        <a href="/login.php">Login</a>
                        <a href="/register.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
