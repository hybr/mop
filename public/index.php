<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;

$auth = new Auth();

// Redirect to dashboard if logged in
if ($auth->isLoggedIn()) {
    header('Location: /market/');
    exit;
}

$pageTitle = 'Home';
include __DIR__ . '/../views/header.php';
?>

<div class="py-4">
    <div class="text-center" style="max-width: 600px; margin: 4rem auto;">
        <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">
            Welcome to MyApp
        </h1>
        <p style="font-size: 1.25rem; color: var(--text-light); margin-bottom: 2rem;">
            A modern, mobile-first user management application built with Core PHP and Supabase.
        </p>

        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="/auth/register/" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.125rem;">
                Get Started
            </a>
            <a href="/auth/login/" class="btn btn-secondary" style="padding: 1rem 2rem; font-size: 1.125rem;">
                Login
            </a>
        </div>
    </div>

    <div style="margin-top: 4rem;">
        <h2 class="text-center mb-4">Features</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div class="card">
                <h3 class="card-title">User Management</h3>
                <p class="text-muted">Complete user registration, login, and profile management system.</p>
            </div>

            <div class="card">
                <h3 class="card-title">Mobile First</h3>
                <p class="text-muted">Fully responsive design that works perfectly on all devices.</p>
            </div>

            <div class="card">
                <h3 class="card-title">Secure</h3>
                <p class="text-muted">Built with Supabase authentication for enterprise-grade security.</p>
            </div>

            <div class="card">
                <h3 class="card-title">Modern Stack</h3>
                <p class="text-muted">Core PHP with object-oriented architecture and clean code.</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
