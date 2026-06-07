<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        $redirect = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($redirect));
        exit;
    }
}

function requireRole(string ...$roles): void
{
    requireLogin();
    if (!in_array($_SESSION['user_role'], $roles, true)) {
        http_response_code(403);
        echo '<h1>403 — Forbidden</h1><p>You do not have permission to access this page.</p>';
        exit;
    }
}

function getCurrentUser(): array
{
    return [
        'id'           => $_SESSION['user_id'] ?? null,
        'first_name'   => $_SESSION['user_first_name'] ?? '',
        'last_name'    => $_SESSION['user_last_name'] ?? '',
        'email'        => $_SESSION['user_email'] ?? '',
        'role'         => $_SESSION['user_role'] ?? '',
        'profile_photo'=> $_SESSION['user_photo'] ?? null,
    ];
}

function setUserSession(array $user): void
{
    $_SESSION['user_id']         = $user['id'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name']  = $user['last_name'];
    $_SESSION['user_email']      = $user['email'];
    $_SESSION['user_role']       = $user['role'];
    $_SESSION['user_photo']      = $user['profile_photo'] ?? null;
    session_regenerate_id(true);
}

function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

function currentUserRole(): ?string
{
    return $_SESSION['user_role'] ?? null;
}
