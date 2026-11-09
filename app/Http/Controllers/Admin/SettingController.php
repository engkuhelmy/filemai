<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\CreatorPayout;
use App\Models\CreatorWallet;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the Admin Settings & Monetization Control Center.
     */
    public function index(Request $request): View
    {
        $settings = SystemSetting::getAllAssoc();

        // Fallbacks if not set
        $monthlyPrice = $settings['viewer_vip_monthly_price'] ?? 14.90;
        $yearlyPrice = $settings['viewer_vip_yearly_price'] ?? 129.90;
        $creatorPackage = $settings['creator_package_price'] ?? 50.00;
        $ratePerBlock = $settings['creator_earnings_rate_per_block'] ?? 0.000100;
        $blockSeconds = $settings['block_duration_seconds'] ?? 60;
        $minPayout = $settings['min_payout_amount'] ?? 10.00;

        // Platform Monetization Summary Metrics
        $totalWalletsEarned = CreatorWallet::sum('total_earned');
        $totalWalletsBalance = CreatorWallet::sum('current_balance');
        $totalWalletsWithdrawn = CreatorWallet::sum('total_withdrawn');
        $totalBlocksProcessed = CreatorWallet::sum('total_blocks');

        $pendingPayoutsCount = CreatorPayout::where('status', 'pending')->count();
        $recentPayouts = CreatorPayout::with('creator')->orderBy('created_at', 'desc')->take(10)->get();

        return view('admin.settings', [
            'monthlyPrice' => $monthlyPrice,
            'yearlyPrice' => $yearlyPrice,
            'creatorPackage' => $creatorPackage,
            'ratePerBlock' => $ratePerBlock,
            'blockSeconds' => $blockSeconds,
            'minPayout' => $minPayout,
            'totalWalletsEarned' => $totalWalletsEarned,
            'totalWalletsBalance' => $totalWalletsBalance,
            'totalWalletsWithdrawn' => $totalWalletsWithdrawn,
            'totalBlocksProcessed' => $totalBlocksProcessed,
            'pendingPayoutsCount' => $pendingPayoutsCount,
            'recentPayouts' => $recentPayouts,
        ]);
    }

    /**
     * Save updated system settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'viewer_vip_monthly_price' => 'required|numeric|min:0.50|max:1000',
            'viewer_vip_yearly_price' => 'required|numeric|min:1.00|max:5000',
            'creator_package_price' => 'required|numeric|min:0|max:5000',
            'creator_earnings_rate_per_block' => 'required|numeric|min:0.000001|max:1.000000',
            'block_duration_seconds' => 'required|integer|min:10|max:3600',
            'min_payout_amount' => 'required|numeric|min:1.00|max:1000',
        ], [
            'viewer_vip_monthly_price.required' => 'Sila masukkan harga langganan VIP bulanan.',
            'viewer_vip_yearly_price.required' => 'Sila masukkan harga langganan VIP tahunan.',
            'creator_package_price.required' => 'Sila tetapkan harga pakej video creator.',
            'creator_earnings_rate_per_block.required' => 'Sila masukkan kadar pulangan tontonan setiap blok.',
            'block_duration_seconds.required' => 'Sila masukkan tempoh saat bagi satu blok.',
            'min_payout_amount.required' => 'Sila tentukan had minima pengeluaran royalti.',
        ]);

        SystemSetting::set('viewer_vip_monthly_price', $validated['viewer_vip_monthly_price'], 'decimal', 'Harga langganan bulanan penonton (RM / bulan)');
        SystemSetting::set('viewer_vip_yearly_price', $validated['viewer_vip_yearly_price'], 'decimal', 'Harga langganan tahunan penonton (RM / tahun)');
        SystemSetting::set('creator_package_price', $validated['creator_package_price'], 'decimal', 'Pakej yuran pengesahan & pendaftaran menjadi video creator (RM)');
        SystemSetting::set('creator_earnings_rate_per_block', $validated['creator_earnings_rate_per_block'], 'decimal', 'Pulangan setiap blok tontonan layak yang diterima creator (RM / block)');
        SystemSetting::set('block_duration_seconds', $validated['block_duration_seconds'], 'integer', 'Tempoh saat bagi satu (1) blok tontonan layak (cth: 60 saat = 1 minit)');
        SystemSetting::set('min_payout_amount', $validated['min_payout_amount'], 'decimal', 'Had minima pengeluaran baki royalti tontonan oleh creator (RM)');

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Tetapan harga langganan penonton, pakej video creator, dan kadar pulangan tontonan berjaya dikemaskini!');
    }

    /**
     * Process Creator Payout Status (Approve, Complete, or Reject).
     */
    public function updatePayout(Request $request, CreatorPayout $payout): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,completed,rejected',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $payout->status;

        // If rejecting an existing pending/approved payout, refund to creator wallet
        if ($newStatus === 'rejected' && in_array($oldStatus, ['pending', 'approved'])) {
            $wallet = CreatorWallet::forUser($payout->creator_id);
            $wallet->current_balance = (float)$wallet->current_balance + (float)$payout->amount;
            $wallet->total_withdrawn = max(0, (float)$wallet->total_withdrawn - (float)$payout->amount);
            $wallet->save();
        }

        $payout->status = $newStatus;
        if (!empty($validated['reference_no'])) {
            $payout->reference_no = trim($validated['reference_no']);
        }
        if (!empty($validated['notes'])) {
            $payout->notes = trim($validated['notes']);
        }
        if (in_array($newStatus, ['approved', 'completed'])) {
            $payout->processed_at = now();
        }
        $payout->save();

        return redirect()
            ->route('admin.settings')
            ->with('success', "Permohonan pengeluaran #PAY-{$payout->id} bernilai RM {$payout->amount} telah dikemaskini kepada '{$newStatus}'!");
    }
}

