<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\CreatorPayout;
use App\Models\CreatorWallet;
use App\Models\CreatorEarningLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Display Creator Wallet & Collection Center.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $wallet = CreatorWallet::forUser($user->id);

        $ratePerBlock = SystemSetting::get('creator_earnings_rate_per_block', 0.000100);
        $blockSeconds = SystemSetting::get('block_duration_seconds', 60);
        $minPayout = SystemSetting::get('min_payout_amount', 10.00);

        // Payout history
        $payouts = CreatorPayout::where('creator_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Recent earning logs
        $recentEarnings = CreatorEarningLog::where('creator_id', $user->id)
            ->with(['drama', 'episode'])
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        // Earnings grouped by drama
        $dramaEarnings = CreatorEarningLog::where('creator_id', $user->id)
            ->select('drama_id', DB::raw('SUM(amount) as total_amount'), DB::raw('SUM(blocks_count) as total_blocks'), DB::raw('COUNT(*) as total_logs'))
            ->groupBy('drama_id')
            ->with('drama')
            ->get();

        return view('creator.wallet', [
            'user' => $user,
            'wallet' => $wallet,
            'ratePerBlock' => $ratePerBlock,
            'blockSeconds' => $blockSeconds,
            'minPayout' => $minPayout,
            'payouts' => $payouts,
            'recentEarnings' => $recentEarnings,
            'dramaEarnings' => $dramaEarnings,
        ]);
    }

    /**
     * Submit a withdrawal request from available balance.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $wallet = CreatorWallet::forUser($user->id);
        $minPayout = (float) SystemSetting::get('min_payout_amount', 10.00);

        $validated = $request->validate([
            'amount' => "required|numeric|min:{$minPayout}|max:{$wallet->current_balance}",
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:150',
        ], [
            'amount.required' => 'Sila masukkan jumlah pengeluaran.',
            'amount.min' => "Had minima pengeluaran royalti ialah RM " . number_format($minPayout, 2) . ".",
            'amount.max' => "Jumlah yang dimohon melebihi baki semasa anda (RM " . number_format($wallet->current_balance, 2) . ").",
            'bank_name.required' => 'Sila pilih atau masukkan nama bank anda.',
            'account_number.required' => 'Sila masukkan nombor akaun bank.',
            'account_holder.required' => 'Sila masukkan nama pemegang akaun seperti di buku bank.',
        ]);

        $amount = (float) $validated['amount'];

        DB::transaction(function () use ($user, $wallet, $validated, $amount) {
            // Deduct balance from wallet
            $wallet->deductPayout($amount);

            // Create payout record
            CreatorPayout::create([
                'creator_id' => $user->id,
                'amount' => $amount,
                'bank_name' => trim($validated['bank_name']),
                'account_number' => trim($validated['account_number']),
                'account_holder' => trim($validated['account_holder']),
                'status' => 'pending',
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('creator.wallet')
            ->with('success', "Permohonan pengeluaran bernilai RM " . number_format($amount, 2) . " telah berjaya dihantar. Pentadbir akan memproses pindahan dana dalam masa 1-3 hari bekerja.");
    }
}
