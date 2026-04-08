<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerBadge;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerBadgesController extends Controller
{
    public function show(Request $request, int $id): JsonResponse
    {
        $player = Player::findOrFail($id);
        $season = Season::where('status', 'active')
            ->orWhere('status', 'pending_settlement')
            ->orderByDesc('id')
            ->first();

        $earnedBadges = collect();
        if ($season) {
            $earnedBadges = PlayerBadge::where('player_id', $id)
                ->where('season_id', $season->id)
                ->get()
                ->keyBy(fn ($b) => $b->category . '_' . $b->tier);
        }

        // Build full shelf: 6 categories × 3 tiers
        $tiers = array_keys(PlayerBadge::TIERS);
        $shelf = [];

        foreach (PlayerBadge::CATEGORIES as $category) {
            $categorySlots = [];
            foreach ($tiers as $tier) {
                $key = $category . '_' . $tier;
                /** @var PlayerBadge|null $badge */
                $badge = $earnedBadges[$key] ?? null;
                $categorySlots[] = [
                    'category' => $category,
                    'tier' => $tier,
                    'earned' => $badge !== null,
                    'earnedAt' => $badge?->earned_at?->toISOString(),
                ];
            }
            $shelf[] = [
                'category' => $category,
                'slots' => $categorySlots,
            ];
        }

        return response()->json([
            'player' => $player->toApiArray(),
            'season' => $season?->toApiArray(),
            'shelf' => $shelf,
        ]);
    }
}
