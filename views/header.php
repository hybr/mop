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
    <?php
    // Subdomain detection logic
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $subdomain = null;
    $currentOrganization = null;

    // Extract subdomain if present (e.g., nbs.v4l.app -> nbs)
    if (preg_match('/^([^.]+)\.v4l\.app$/i', $host, $matches)) {
        $subdomain = $matches[1];

        // Load organization by subdomain
        if (isset($orgRepo)) {
            $currentOrganization = $orgRepo->findBySubdomainPublic($subdomain);
        }
    }
    ?>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <?php if ($currentOrganization && !empty($currentOrganization['logo_url'])): ?>
                    <a href="/" class="logo">
                        <img src="<?php echo htmlspecialchars($currentOrganization['logo_url']); ?>"
                             alt="<?php echo htmlspecialchars($currentOrganization['name'] ?? 'Organization'); ?>"
                             style="height: 40px; max-width: 150px; object-fit: contain;">
                    </a>
                <?php else: ?>
                    <a href="/" class="logo">V4L</a>
                <?php endif; ?>

                <!-- Desktop Navigation -->
                <nav class="nav">
                    <?php if (isset($auth) && $auth->isLoggedIn()): ?>
                        <?php
                        $currentOrg = $auth->getCurrentOrganization();
                        ?>
                        <a href="/dashboard/">My</a>
                        <div class="org-selector">
                            <a href="/organizations/" class="org-link" title="Switch Organization">
                                <?php if ($currentOrg): ?>
                                    <span class="org-name"><?php echo htmlspecialchars($currentOrg->getName()); ?></span>
                                    <?php if ($currentOrg->getSubdomain()): ?>
                                        <span class="org-code">(<?php echo htmlspecialchars($currentOrg->getSubdomain()); ?>)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="org-name">Select Organization</span>
                                <?php endif; ?>
                                <span class="org-arrow">▼</span>
                            </a>
                        </div>
                        <a href="/vacancies.php">Vacancies</a>
                        <a href="/market/">Market</a>
                        <a href="/auth/logout/" class="btn btn-primary">Logout</a>
                    <?php else: ?>
                        <a href="/vacancies.php">Vacancies</a>
                        <a href="/market/">Market</a>
                        <a href="/auth/login/">Login</a>
                        <a href="/auth/register/" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </nav>

                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Toggle menu">
                    <span id="menu-icon">☰</span>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation (outside container for full width) -->
        <nav class="nav-mobile" id="nav-mobile">
            <div class="container">
                <?php if (isset($auth) && $auth->isLoggedIn()): ?>
                    <?php
                    $currentOrg = $auth->getCurrentOrganization();
                    ?>
                    <a href="/dashboard/">My Dashboard</a>
                    <a href="/organizations/" class="org-link" title="Switch Organization">
                        <?php if ($currentOrg): ?>
                            <span class="org-name"><?php echo htmlspecialchars($currentOrg->getName()); ?></span>
                            <?php if ($currentOrg->getSubdomain()): ?>
                                <span class="org-code">(<?php echo htmlspecialchars($currentOrg->getSubdomain()); ?>)</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="org-name">Select Organization</span>
                        <?php endif; ?>
                    </a>
                    <a href="/vacancies.php">Vacancies</a>
                    <a href="/market/">Market</a>
                    <a href="/auth/logout/" class="btn btn-primary">Logout</a>
                <?php else: ?>
                    <a href="/vacancies.php">Vacancies</a>
                    <a href="/market/">Market</a>
                    <a href="/auth/login/">Login</a>
                    <a href="/auth/register/" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>

        <script>
            function toggleMobileMenu() {
                var nav = document.getElementById('nav-mobile');
                var icon = document.getElementById('menu-icon');
                nav.classList.toggle('active');
                icon.textContent = nav.classList.contains('active') ? '✕' : '☰';
            }
        </script>
    </header>

    <main class="container">
