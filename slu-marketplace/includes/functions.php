<?php
// Shared helpers: escaping, sessions, CSRF, auth guards, flash messages.

session_name('SLUMARKET');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// Base path relative to the app root ('', '../', '../../', ...).
// Computed from SCRIPT_NAME so links work both at /slu-marketplace/*
// and when the app is served at a domain root (e.g. preview proxy).
function app_base(): string {
    static $base = null;
    if ($base !== null) return $base;
    $sn = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $marker = 'slu-marketplace/';
    $pos = strpos($sn, $marker);
    if ($pos !== false) {
        $rel = substr($sn, $pos + strlen($marker));
        $base = str_repeat('../', substr_count($rel, '/'));
    } else {
        $dir = trim(dirname($sn), '/');
        $base = ($dir === '' || $dir === '.') ? '' : str_repeat('../', substr_count($dir, '/') + 1);
    }
    return $base;
}

function url(string $path = ''): string {
    return app_base() . ltrim($path, '/');
}

// ---------- Flash messages ----------
function flash(string $key, $value = null) {
    if ($value === null) {
        $v = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $v;
    }
    $_SESSION['flash'][$key] = $value;
}

function flash_errors(array $errors): void {
    $_SESSION['flash']['errors'] = $errors;
}

function old(string $key, string $default = ''): string {
    $v = $_SESSION['old'][$key] ?? $default;
    return e($v);
}

function set_old(array $data): void {
    $_SESSION['old'] = $data;
}

function clear_old(): void {
    unset($_SESSION['old']);
}

// ---------- CSRF ----------
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_verify(): void {
    $token = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token. Please go back and try again.');
    }
}

// ---------- Auth ----------
function current_user(): ?array {
    static $user = null;
    static $loaded = false;
    if (!$loaded) {
        $loaded = true;
        if (!empty($_SESSION['user_id'])) {
            require_once __DIR__ . '/../models/User.php';
            $user = User::findById((int)$_SESSION['user_id']);
        }
    }
    return $user;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function is_admin(): bool {
    $u = current_user();
    return $u !== null && $u['role'] === 'admin';
}

function require_login(): void {
    if (!is_logged_in()) {
        flash('errors', ['Please log in to continue.']);
        redirect(url('login.php'));
    }
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Access denied. Administrators only.');
    }
}

// ---------- Misc ----------
function money($n): string {
    return '₱' . number_format((float)$n, 2);
}

function time_ago(string $datetime): string {
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $ts);
}

function star_rating(float $rating): string {
    $full = (int)round($rating);
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $full ? '★' : '☆';
    }
    return $out;
}

function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
