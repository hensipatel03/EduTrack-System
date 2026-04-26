<?php
// includes/auth.php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../config.php';

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

function requireLogin(string $requiredRole = null): void
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    if ($requiredRole !== null && $_SESSION['role'] !== $requiredRole) {
        // Unauthorized
        header('HTTP/1.1 403 Forbidden');
        echo 'Access denied.';
        exit;
    }
}

function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    static $user = null;
    if ($user !== null) {
        return $user;
    }

    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name 
                           FROM users u 
                           JOIN roles r ON r.id = u.role_id 
                           WHERE u.id = :id');
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user !== false ? $user : null;
}