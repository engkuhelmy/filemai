<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drama;
use App\Models\Episode;
use App\Models\EpisodeView;
use App\Models\User;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the Executive Admin Analytics Dashboard.
     */
    public function index(Request $request): View
    {
        $timeframe = $request->query('range', '30days'); // today, 7days, 30days, all

        $now = Carbon::now();
        $startDate = match ($timeframe) {
            'today' => $now->copy()->startOfDay(),
            '7days' => $now->copy()->subDays(7)->startOfDay(),
            'all' => Carbon::createFromTimestamp(0),
            default => $now->copy()->subDays(30)->startOfDay(),
        };

        // 1. Core KPIs
        $totalRealViews = EpisodeView::count();
        $filteredViewsCount = EpisodeView::where('viewed_at', '>=', $startDate)->count();
        $viewsToday = EpisodeView::whereDate('viewed_at', Carbon::today())->count();
        $viewsYesterday = EpisodeView::whereDate('viewed_at', Carbon::yesterday())->count();
        $viewsGrowth = $viewsYesterday > 0 
            ? round((($viewsToday - $viewsYesterday) / $viewsYesterday) * 100, 1) 
            : ($viewsToday > 0 ? 100 : 0);

        $uniqueViewers = EpisodeView::distinct('ip_address')->count();
        $uniqueViewersToday = EpisodeView::whereDate('viewed_at', Carbon::today())->distinct('ip_address')->count();

        // 2. Revenue & VIP Subscriptions
        $totalRevenue = (float) DB::table('subscriptions')->where('status', 'success')->sum('amount');
        $successfulSubsCount = DB::table('subscriptions')->where('status', 'success')->count();
        
        $totalUsers = User::count();
        $vipUsersCount = User::all()->filter(fn($u) => $u->isPremium())->count();
        $creatorsCount = User::where('role', 'creator')->count();
        $regularViewersCount = User::where('role', 'viewer')->count();
        $vipConversionRate = $totalUsers > 0 ? round(($vipUsersCount / $totalUsers) * 100, 1) : 0;

        // 3. Content Metrics
        $totalDramas = Drama::count();
        $totalEpisodes = Episode::count();
        $comingSoonEpisodes = Episode::where('is_coming_soon', true)->count();
        $activeEpisodes = $totalEpisodes - $comingSoonEpisodes;
        $totalAdImpressions = (int) Ad::sum('views_count');

        // 4. Daily Views Timeline Chart (Past 14 Days)
        $dailyTrend = [];
        $maxDailyViews = 1;
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->toDateString();
            $label = $date->format('d M');
            $dayName = $date->isoFormat('ddd');

            $viewsCount = EpisodeView::whereDate('viewed_at', $dateStr)->count();
            $vipViews = EpisodeView::whereDate('viewed_at', $dateStr)->where('is_vip', true)->count();
            $freeViews = $viewsCount - $vipViews;

            if ($viewsCount > $maxDailyViews) {
                $maxDailyViews = $viewsCount;
            }

            $dailyTrend[] = [
                'date' => $dateStr,
                'label' => $label,
                'day' => $dayName,
                'total' => $viewsCount,
                'vip' => $vipViews,
                'free' => $freeViews,
                'height_percent' => 0, // will compute below
            ];
        }

        foreach ($dailyTrend as &$day) {
            $day['height_percent'] = max(6, round(($day['total'] / $maxDailyViews) * 100));
        }
        unset($day);

        // 5. Views Breakdown by Genre
        $genreViews = EpisodeView::join('dramas', 'episode_views.drama_id', '=', 'dramas.id')
            ->select('dramas.genre', DB::raw('count(*) as count'))
            ->groupBy('dramas.genre')
            ->orderByDesc('count')
            ->get();

        $genreShare = [];
        foreach ($genreViews as $gv) {
            $percentage = $totalRealViews > 0 ? round(($gv->count / $totalRealViews) * 100, 1) : 0;
            $genreShare[] = [
                'genre' => $gv->genre,
                'count' => $gv->count,
                'percent' => $percentage,
            ];
        }

        // 6. Device Distribution (Mobile vs Desktop vs Tablet)
        $deviceCounts = EpisodeView::select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->pluck('count', 'device_type')
            ->toArray();

        $mobileCount = $deviceCounts['mobile'] ?? 0;
        $desktopCount = $deviceCounts['desktop'] ?? 0;
        $tabletCount = $deviceCounts['tablet'] ?? 0;
        $deviceTotal = max(1, $mobileCount + $desktopCount + $tabletCount);

        $deviceStats = [
            'mobile' => [
                'count' => $mobileCount,
                'percent' => round(($mobileCount / $deviceTotal) * 100, 1),
            ],
            'desktop' => [
                'count' => $desktopCount,
                'percent' => round(($desktopCount / $deviceTotal) * 100, 1),
            ],
            'tablet' => [
                'count' => $tabletCount,
                'percent' => round(($tabletCount / $deviceTotal) * 100, 1),
            ],
        ];

        // 7. Platform / OS Breakdown
        $platformCounts = EpisodeView::select('platform', DB::raw('count(*) as count'))
            ->groupBy('platform')
            ->orderByDesc('count')
            ->pluck('count', 'platform')
            ->toArray();

        // 8. Drama Performance Ranking
        $dramasRanking = Drama::with(['creator'])
            ->withCount(['episodes'])
            ->orderByDesc('views_count')
            ->get()
            ->map(function ($drama) use ($totalRealViews) {
                $drama->unique_viewers = EpisodeView::where('drama_id', $drama->id)->distinct('ip_address')->count();
                $drama->views_share = $totalRealViews > 0 ? round(($drama->views_count / $totalRealViews) * 100, 1) : 0;
                $drama->coming_soon_count = $drama->episodes()->where('is_coming_soon', true)->count();
                $drama->active_episodes_count = $drama->episodes_count - $drama->coming_soon_count;
                return $drama;
            });

        // 9. Real-Time View Stream (Latest 15 Events)
        $recentViewLogs = EpisodeView::with(['drama', 'episode', 'user'])
            ->latest('viewed_at')
            ->take(15)
            ->get();

        // 10. Creators Performance Summary
        $creators = User::where('role', 'creator')
            ->withCount('dramas')
            ->get()
            ->map(function ($creator) {
                $creator->total_episodes = Episode::whereIn('drama_id', $creator->dramas()->pluck('id'))->count();
                $creator->total_views = Drama::where('creator_id', $creator->id)->sum('views_count');
                return $creator;
            })
            ->sortByDesc('total_views')
            ->values();

        // 11. Recent VIP Subscriptions Transactions
        $recentTransactions = DB::table('subscriptions')
            ->leftJoin('users', 'subscriptions.user_id', '=', 'users.id')
            ->select('subscriptions.*', 'users.name as user_name', 'users.email as user_email')
            ->orderByDesc('subscriptions.paid_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', [
            'timeframe' => $timeframe,
            'totalRealViews' => $totalRealViews,
            'filteredViewsCount' => $filteredViewsCount,
            'viewsToday' => $viewsToday,
            'viewsGrowth' => $viewsGrowth,
            'uniqueViewers' => $uniqueViewers,
            'uniqueViewersToday' => $uniqueViewersToday,
            'totalRevenue' => $totalRevenue,
            'successfulSubsCount' => $successfulSubsCount,
            'totalUsers' => $totalUsers,
            'vipUsersCount' => $vipUsersCount,
            'creatorsCount' => $creatorsCount,
            'regularViewersCount' => $regularViewersCount,
            'vipConversionRate' => $vipConversionRate,
            'totalDramas' => $totalDramas,
            'totalEpisodes' => $totalEpisodes,
            'comingSoonEpisodes' => $comingSoonEpisodes,
            'activeEpisodes' => $activeEpisodes,
            'totalAdImpressions' => $totalAdImpressions,
            'dailyTrend' => $dailyTrend,
            'maxDailyViews' => $maxDailyViews,
            'genreShare' => $genreShare,
            'deviceStats' => $deviceStats,
            'platformCounts' => $platformCounts,
            'dramasRanking' => $dramasRanking,
            'recentViewLogs' => $recentViewLogs,
            'creators' => $creators,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
