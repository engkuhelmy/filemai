<?php

namespace App\Controllers;

use App\Database;
use App\Auth;
use App\Router;

class SubscriptionController
{
    public function index(): void
    {
        $user = Auth::user();
        $isPremium = Auth::isPremium();

        Router::render('viewer/subscribe', [
            'title' => 'Langgan Pas VIP Filemai - RM5 / Bulan',
            'user' => $user,
            'isPremium' => $isPremium,
        ]);
    }

    public function checkout(): void
    {
        Auth::requireAuth();
        $user = Auth::user();

        Router::render('viewer/checkout', [
            'title' => 'Pengesahan Pembayaran VIP - RM5.00',
            'user' => $user,
            'amount' => 5.00,
        ]);
    }

    public function process(): void
    {
        Auth::requireAuth();
        $user = Auth::user();

        $paymentChannel = trim($_POST['payment_channel'] ?? 'ToyyibPay FPX');
        $transactionRef = 'FPX-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
        $amount = 5.00;

        // Record transaction
        $subId = Database::insertGetId(
            "INSERT INTO subscriptions (user_id, amount, payment_channel, transaction_ref, status, paid_at)
             VALUES (?, ?, ?, ?, 'success', NOW())",
            [$user['id'], $amount, $paymentChannel, $transactionRef]
        );

        // Update user subscription to premium for 30 days
        Database::execute(
            "UPDATE users SET subscription_status = 'premium', subscription_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
             WHERE id = ?",
            [$user['id']]
        );

        Router::redirect('/subscribe/success?ref=' . urlencode($transactionRef));
    }

    public function success(): void
    {
        Auth::requireAuth();
        $ref = $_GET['ref'] ?? 'N/A';
        $user = Auth::user();

        Router::render('viewer/subscribe_success', [
            'title' => 'Tahniah! Langganan VIP Anda Aktif',
            'ref' => $ref,
            'user' => $user,
        ]);
    }

    public function webhook(): void
    {
        // Mock ToyyibPay / Stripe webhook endpoint
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $ref = $input['refno'] ?? $input['transaction_ref'] ?? null;
        $status = ($input['status_id'] ?? 1) == 1 ? 'success' : 'failed';

        if ($ref) {
            $sub = Database::fetchOne("SELECT * FROM subscriptions WHERE transaction_ref = ?", [$ref]);
            if ($sub) {
                Database::execute("UPDATE subscriptions SET status = ?, paid_at = NOW() WHERE id = ?", [$status, $sub['id']]);
                if ($status === 'success') {
                    Database::execute(
                        "UPDATE users SET subscription_status = 'premium', subscription_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?",
                        [$sub['user_id']]
                    );
                }
            }
        }

        Router::json(['status' => 'acknowledged', 'ref' => $ref]);
    }
}
