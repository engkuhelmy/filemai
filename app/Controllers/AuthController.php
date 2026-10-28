<?php

namespace App\Controllers;

use App\Auth;
use App\Config;
use App\Database;
use App\Router;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Router::redirect('/');
            return;
        }

        Router::render('auth/login', [
            'title' => 'Log Masuk - Filemai.my',
            'noLayout' => true,
        ]);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (Auth::login($email, $password)) {
            $redirect = $_SESSION['redirect_after_login'] ?? '/';
            unset($_SESSION['redirect_after_login']);
            Router::redirect($redirect);
            return;
        }

        $_SESSION['error'] = 'Emel atau kata laluan tidak sah.';
        Router::redirect('/login');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            Router::redirect('/');
            return;
        }

        Router::render('auth/register', [
            'title' => 'Daftar Akaun Baharu - Filemai.my',
            'noLayout' => true,
        ]);
    }

    public function register(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['creator', 'viewer']) ? $_POST['role'] : 'viewer';

        if (strlen($name) < 2 || strlen($password) < 6 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Sila lengkapkan maklumat pendaftaran dengan betul (kata laluan sekurang-kurangnya 6 aksara).';
            Router::redirect('/register');
            return;
        }

        $userId = Auth::register($name, $email, $password, $role);
        if ($userId) {
            $_SESSION['success'] = "Selamat datang, $name! Akaun anda telah berjaya dicipta.";
            Router::redirect($role === 'creator' ? '/creator' : '/');
            return;
        }

        $_SESSION['error'] = 'Emel ini telah pun didaftarkan.';
        Router::redirect('/register');
    }

    public function logout(): void
    {
        Auth::logout();
        Router::redirect('/login');
    }

    public function demoSwitch(string $userId): void
    {
        if (!Config::isDemoMode()) {
            $_SESSION['error'] = 'Fungsi akaun demo hanya aktif di bawah persekitaran staging/demo.';
            Router::redirect('/login');
            return;
        }

        $id = (int)$userId;
        if (Auth::loginById($id)) {
            $user = Auth::user();
            $_SESSION['success'] = "Beralih ke akaun demo: <strong>{$user['name']}</strong> ({$user['role']}" . ($user['subscription_status'] === 'premium' ? ' - VIP' : '') . ")";
            
            if ($user['role'] === 'admin') {
                Router::redirect('/admin');
            } elseif ($user['role'] === 'creator') {
                Router::redirect('/creator');
            } else {
                Router::redirect('/');
            }
            return;
        }

        Router::redirect('/');
    }
}
