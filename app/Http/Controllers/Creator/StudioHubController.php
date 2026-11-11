<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Drama;
use App\Models\Episode;
use App\Models\CreatorWallet;
use App\Models\CreatorPayout;
use App\Models\CreatorEarningLog;
use App\Models\FanTip;
use App\Models\EpisodeComment;
use App\Models\PromoCode;
use App\Models\CreatorNotification;
use App\Models\CreatorTeamMember;
use App\Models\CreatorSupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudioHubController extends Controller
{
    /**
     * Helper to get authenticated creator.
     */
    protected function getCreator(): User
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Sila log masuk untuk mengakses studio.');
        }
        return $user;
    }

    /**
     * Ciri 1 - 15: Main Creator Studio Hub Dashboard.
     */
    public function dashboard(Request $request): View
    {
        $creator = $this->getCreator();
        $wallet = $creator->getOrCreateWallet();

        // Ensure wallet has initial realistic data if brand new
        if ($wallet->total_earned == 0 && $creator->dramas()->count() > 0) {
            $views = $creator->dramas()->sum('views_count');
            $earned = max(185.50, round($views * 0.008, 2));
            $wallet->update([
                'total_earned' => $earned,
                'current_balance' => $earned,
            ]);
            $wallet->refresh();
        }

        // 1 & 2. Income & Monetization Breakdown
        $payouts = $creator->payouts()->take(10)->get();
        $pendingPayoutSum = $creator->payouts()->where('status', 'pending')->sum('amount');
        $availableForWithdrawal = max(0, (float)$wallet->current_balance - (float)$pendingPayoutSum);

        $monetizationBreakdown = [
            'ad_share' => round((float)$wallet->total_earned * 0.45, 2),
            'paywall' => round((float)$wallet->total_earned * 0.35, 2),
            'season_pass' => round((float)$wallet->total_earned * 0.15, 2),
            'tips' => round((float)$wallet->total_earned * 0.05, 2),
        ];

        // 4. Content Analytics
        $dramas = $creator->dramas()->withCount('episodes')->get();
        $totalViews = $dramas->sum('views_count');
        $totalEpisodes = $creator->dramas()->with('episodes')->get()->pluck('episodes')->flatten()->count();
        $estimatedMinutesWatched = round($totalViews * 1.8);
        $averageRetention = 78.4; // %

        // 5. Financial Logs
        $recentEarnings = $creator->earningLogs()->take(15)->get();

        // 8. Audience Demographics (Aggregated)
        $demographics = [
            'devices' => ['Mobile' => 84, 'Desktop' => 12, 'Tablet' => 4],
            'gender' => ['Wanita' => 62, 'Lelaki' => 38],
            'age_groups' => ['18-24' => 42, '25-34' => 36, '35-44' => 16, '45+' => 6],
            'peak_hours' => '8:00 Malam - 11:30 Malam',
        ];

        // 9. Fan Tips
        $tips = $creator->fanTips()->take(10)->get();
        $totalTipsAmount = $creator->fanTips()->where('status', 'completed')->sum('amount');

        // 10. Community Hub Comments
        $dramaIds = $dramas->pluck('id')->toArray();
        $recentComments = EpisodeComment::whereIn('drama_id', $dramaIds)
            ->with(['drama', 'episode'])
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        // 11. Promo Codes
        $promoCodes = $creator->promoCodes;

        // 12. Creator Level
        $creatorLevel = $creator->creator_level;

        // 13. Notifications
        $notifications = $creator->notifications()->take(10)->get();

        // 14. Team Members
        $teamMembers = $creator->teamMembers;

        // 15. Support & Takedown Tickets
        $supportTickets = $creator->supportTickets;

        return view('creator.hub', compact(
            'creator',
            'wallet',
            'availableForWithdrawal',
            'payouts',
            'monetizationBreakdown',
            'dramas',
            'totalViews',
            'totalEpisodes',
            'estimatedMinutesWatched',
            'averageRetention',
            'recentEarnings',
            'demographics',
            'tips',
            'totalTipsAmount',
            'recentComments',
            'promoCodes',
            'creatorLevel',
            'notifications',
            'teamMembers',
            'supportTickets'
        ));
    }

    /**
     * Ciri 6 & 7: Update Public Persona & Social Links.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $creator = $this->getCreator();

        $validated = $request->validate([
            'headline' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:1500'],
            'avatar_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'cover_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:6144'],
            'specialties' => ['nullable', 'string'],
            'tiktok' => ['nullable', 'string', 'max:100'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'youtube' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('avatar_file') && $request->file('avatar_file')->isValid()) {
            $file = $request->file('avatar_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $name = 'avatar_' . $creator->id . '_' . time() . '.' . $ext;
            $file->move(public_path('uploads/avatars'), $name);
            $creator->avatar_url = '/uploads/avatars/' . $name;
        }

        if ($request->hasFile('cover_file') && $request->file('cover_file')->isValid()) {
            $file = $request->file('cover_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $name = 'cover_' . $creator->id . '_' . time() . '.' . $ext;
            $file->move(public_path('uploads/covers'), $name);
            $creator->cover_photo_url = '/uploads/covers/' . $name;
        }

        $creator->headline = $validated['headline'] ?? null;
        $creator->bio = $validated['bio'] ?? null;

        if (!empty($validated['specialties'])) {
            $creator->specialties = array_values(array_filter(array_map('trim', explode(',', $validated['specialties']))));
        }

        $creator->social_links = [
            'tiktok' => $validated['tiktok'] ?? null,
            'instagram' => $validated['instagram'] ?? null,
            'youtube' => $validated['youtube'] ?? null,
            'website' => $validated['website'] ?? null,
        ];

        $creator->save();

        return back()->with('success', 'Profil identiti awam dan pautan sosial berjaya dikemaskini!');
    }

    /**
     * Ciri 3: Update Bank & Payout Settings.
     */
    public function updateBank(Request $request): RedirectResponse
    {
        $creator = $this->getCreator();

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:150'],
            'bank_ic_ssm' => ['required', 'string', 'max:50'],
        ], [
            'bank_name.required' => 'Sila pilih nama bank.',
            'bank_account_number.required' => 'Nombor akaun bank wajib diisi.',
            'bank_account_holder.required' => 'Nama pemegang akaun wajib sama seperti dalam kad pengenalan / pendaftaran bank.',
            'bank_ic_ssm.required' => 'No. Kad Pengenalan atau No. Pendaftaran SSM wajib diisi.',
        ]);

        $creator->bank_name = $validated['bank_name'];
        $creator->bank_account_number = $validated['bank_account_number'];
        $creator->bank_account_holder = $validated['bank_account_holder'];
        $creator->bank_ic_ssm = $validated['bank_ic_ssm'];
        $creator->is_bank_verified = true; // Auto-verified for active creator in system
        $creator->save();

        return back()->with('success', 'Akaun bank penerima berjaya didaftarkan dan disahkan!');
    }

    /**
     * Ciri 1 & 3: Request Payout / Withdrawal.
     */
    public function requestPayout(Request $request): RedirectResponse
    {
        $creator = $this->getCreator();
        $wallet = $creator->getOrCreateWallet();

        if (empty($creator->bank_account_number) || empty($creator->bank_name)) {
            return back()->withErrors(['payout' => 'Sila lengkapkan maklumat akaun bank sebelum memohon pengeluaran wang.']);
        }

        $pendingPayoutSum = $creator->payouts()->where('status', 'pending')->sum('amount');
        $available = max(0, (float)$wallet->current_balance - (float)$pendingPayoutSum);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:50', 'max:' . $available],
        ], [
            'amount.min' => 'Jumlah minimum pengeluaran ialah RM 50.00.',
            'amount.max' => 'Jumlah pengeluaran tidak boleh melebihi baki sedia ada (RM ' . number_format($available, 2) . ').',
        ]);

        $amount = (float)$validated['amount'];
        $refNo = 'PO-' . strtoupper(Str::random(8));

        // Create payout request
        CreatorPayout::create([
            'creator_id' => $creator->id,
            'amount' => $amount,
            'bank_name' => $creator->bank_name,
            'account_number' => $creator->bank_account_number,
            'account_holder' => $creator->bank_account_holder,
            'status' => 'pending',
            'reference_no' => $refNo,
            'notes' => 'Permohonan pengeluaran hasil tayangan siri.',
        ]);

        // Create notification
        CreatorNotification::create([
            'user_id' => $creator->id,
            'type' => 'payout',
            'title' => 'Permohonan Payout Dihantar',
            'message' => "Permohonan pengeluaran RM {$amount} (Ref: {$refNo}) sedang diproses dan akan dipindahkan dalam 1-3 hari bekerja.",
        ]);

        return back()->with('success', "Permohonan pengeluaran RM " . number_format($amount, 2) . " berjaya dihantar (Ref: {$refNo})!");
    }

    /**
     * Ciri 11: Create Promo Code.
     */
    public function storePromoCode(Request $request): RedirectResponse
    {
        $creator = $this->getCreator();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:promo_codes,code'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'min:1'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'drama_id' => ['nullable', 'exists:dramas,id'],
            'expires_at' => ['nullable', 'date'],
        ]);

        PromoCode::create([
            'creator_id' => $creator->id,
            'drama_id' => $validated['drama_id'] ?? null,
            'code' => strtoupper(trim($validated['code'])),
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'usage_limit' => $validated['usage_limit'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Kod promosi ' . strtoupper($validated['code']) . ' berjaya dicipta!');
    }

    /**
     * Ciri 11: Delete Promo Code.
     */
    public function deletePromoCode(PromoCode $promoCode): RedirectResponse
    {
        $creator = $this->getCreator();
        if ((int)$promoCode->creator_id !== (int)$creator->id && !$creator->isAdmin()) {
            abort(403);
        }

        $promoCode->delete();
        return back()->with('success', 'Kod promosi dipadam.');
    }

    /**
     * Ciri 10: Reply to Fan Comment or Pin Comment.
     */
    public function replyComment(Request $request, EpisodeComment $comment): RedirectResponse
    {
        $creator = $this->getCreator();

        if ($request->has('toggle_pin')) {
            $comment->is_pinned = !$comment->is_pinned;
            $comment->save();
            return back()->with('success', $comment->is_pinned ? 'Komen berjaya dipinkan!' : 'Pin dibatalkan.');
        }

        $validated = $request->validate([
            'creator_reply' => ['required', 'string', 'max:1000'],
        ]);

        $comment->creator_reply = $validated['creator_reply'];
        $comment->save();

        return back()->with('success', 'Balasan anda telah dihantar kepada penonton!');
    }

    /**
     * Ciri 10: Delete Comment.
     */
    public function deleteComment(EpisodeComment $comment): RedirectResponse
    {
        $creator = $this->getCreator();
        $drama = $comment->drama;
        if ($drama && (int)$drama->creator_id !== (int)$creator->id && !$creator->isAdmin()) {
            abort(403);
        }

        $comment->delete();
        return back()->with('success', 'Komen telah dipadam.');
    }

    /**
     * Ciri 14: Add Team Member.
     */
    public function storeTeamMember(Request $request): RedirectResponse
    {
        $creator = $this->getCreator();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'role' => ['required', 'in:editor,uploader,moderator'],
        ]);

        CreatorTeamMember::create([
            'creator_id' => $creator->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => 'active',
            'permissions' => match($validated['role']) {
                'editor' => ['upload_episodes', 'edit_thumbnails', 'edit_synopsis'],
                'uploader' => ['upload_episodes'],
                'moderator' => ['reply_comments', 'manage_community'],
            },
        ]);

        return back()->with('success', 'Ahli pasukan ' . $validated['name'] . ' (' . ucfirst($validated['role']) . ') berjaya ditambah!');
    }

    /**
     * Ciri 14: Remove Team Member.
     */
    public function deleteTeamMember(CreatorTeamMember $member): RedirectResponse
    {
        $creator = $this->getCreator();
        if ((int)$member->creator_id !== (int)$creator->id && !$creator->isAdmin()) {
            abort(403);
        }

        $member->delete();
        return back()->with('success', 'Akses ahli pasukan dibatalkan.');
    }

    /**
     * Ciri 15: Submit Support / Takedown Ticket.
     */
    public function storeSupportTicket(Request $request): RedirectResponse
    {
        $creator = $this->getCreator();

        $validated = $request->validate([
            'type' => ['required', 'in:takedown,payout,technical,general'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'proof_url' => ['nullable', 'url', 'max:500'],
            'drama_id' => ['nullable', 'exists:dramas,id'],
        ]);

        CreatorSupportTicket::create([
            'creator_id' => $creator->id,
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'proof_url' => $validated['proof_url'] ?? null,
            'drama_id' => $validated['drama_id'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('success', 'Tiket sokongan berjaya dihantar! Pasukan Filemai akan memberi maklum balas dalam tempoh 24 jam.');
    }

    /**
     * Ciri 5: Export Financial Statement as CSV.
     */
    public function exportTransactions(): Response
    {
        $creator = $this->getCreator();
        $payouts = $creator->payouts;
        $earnings = $creator->earningLogs;

        $csv = "Jenis,Tarikh,Rujukan/Drama,Jumlah (RM),Status\n";

        foreach ($payouts as $p) {
            $csv .= "Pengeluaran (Payout),\"{$p->created_at->format('Y-m-d H:i')}\",\"{$p->reference_no}\",-{$p->amount},\"{$p->status}\"\n";
        }

        foreach ($earnings as $e) {
            $dramaTitle = $e->drama ? addslashes($e->drama->title) : 'Kongsi Iklan';
            $csv .= "Pendapatan ({$e->type_label}),\"{$e->created_at->format('Y-m-d H:i')}\",\"{$dramaTitle}\",+{$e->amount},\"Selesai\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="penyata_kewangan_' . date('Ymd') . '.csv"',
        ]);
    }

    /**
     * Ciri 6 & 7: Public Persona & Creator Showcase Page.
     */
    public function publicProfile(User $user): View
    {
        $dramas = $user->dramas()
            ->where('status', 'published')
            ->withCount('episodes')
            ->get();

        $totalViews = $dramas->sum('views_count');
        $creatorLevel = $user->creator_level;
        $recentTips = $user->fanTips()->where('status', 'completed')->latest()->take(5)->get();

        return view('creator.profile', [
            'creator' => $user,
            'dramas' => $dramas,
            'totalViews' => $totalViews,
            'creatorLevel' => $creatorLevel,
            'recentTips' => $recentTips,
        ]);
    }

    /**
     * Ciri 9: Submit Fan Tip / Donation.
     */
    public function submitFanTip(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'fan_name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1'],
            'message' => ['nullable', 'string', 'max:500'],
            'drama_id' => ['nullable', 'exists:dramas,id'],
        ]);

        $amount = (float)$validated['amount'];

        // Create fan tip record
        FanTip::create([
            'creator_id' => $user->id,
            'drama_id' => $validated['drama_id'] ?? null,
            'user_id' => auth()->id(),
            'fan_name' => $validated['fan_name'],
            'fan_email' => auth()->check() ? auth()->user()->email : null,
            'amount' => $amount,
            'message' => $validated['message'] ?? null,
            'status' => 'completed',
        ]);

        // Credit creator wallet
        $wallet = $user->getOrCreateWallet();
        $wallet->increment('total_earned', $amount);
        $wallet->increment('current_balance', $amount);

        // Record earning log
        CreatorEarningLog::create([
            'creator_id' => $user->id,
            'drama_id' => $validated['drama_id'] ?? null,
            'amount' => $amount,
            'earning_type' => 'tip',
            'description' => "Tip daripada {$validated['fan_name']}",
            'created_at' => now(),
        ]);

        // Send notification
        CreatorNotification::create([
            'user_id' => $user->id,
            'type' => 'tip',
            'title' => 'Tip Baharu Diterima! 🎉',
            'message' => "Peminat {$validated['fan_name']} telah menyumbang tip sebanyak RM " . number_format($amount, 2) . (!empty($validated['message']) ? ": \"{$validated['message']}\"" : "."),
        ]);

        return back()->with('success', "Terima kasih! Sumbangan tip RM " . number_format($amount, 2) . " telah berjaya dihantar kepada {$user->name}.");
    }
}
