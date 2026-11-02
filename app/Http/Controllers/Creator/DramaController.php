<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Drama;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class DramaController extends Controller
{
    /**
     * Show creator studio dashboard with drama listings.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        // If admin, show all dramas. If creator, show their dramas (or all if none created yet)
        $query = Drama::with('creator')->withCount('episodes');
        
        if (!$user->isAdmin()) {
            $userDramas = Drama::where('creator_id', $user->id)->withCount('episodes')->get();
            $dramas = $userDramas->isNotEmpty() ? $userDramas : $query->get();
        } else {
            $dramas = $query->get();
        }

        $totalViews = $dramas->sum('views_count');
        $totalEpisodes = $dramas->sum('episodes_count');

        return view('creator.dashboard', [
            'dramas' => $dramas,
            'totalViews' => $totalViews,
            'totalEpisodes' => $totalEpisodes,
        ]);
    }

    /**
     * Manage a specific drama and all its episodes.
     */
    public function manage(Drama $drama): View
    {
        $episodes = $drama->episodes()->orderBy('episode_number', 'asc')->get();

        return view('creator.dramas.manage', [
            'drama' => $drama,
            'episodes' => $episodes,
        ]);
    }
}
