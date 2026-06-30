<?php
// app/Services/AnalyticsService.php

namespace App\Services;

use App\Models\Link;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get analytics for a link.
     */
    public function getLinkAnalytics(Link $link): array
    {
        return [
            'total_clicks' => $link->clicks,
            'unique_visitors' => Visit::where('link_id', $link->id)
                ->distinct('ip_address')
                ->count(),
            'last_24_hours' => Visit::where('link_id', $link->id)
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'last_7_days' => Visit::where('link_id', $link->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'top_referers' => Visit::where('link_id', $link->id)
                ->select('referer', DB::raw('count(*) as total'))
                ->groupBy('referer')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'referer' => $item->referer ?: 'Direct',
                        'total' => $item->total,
                    ];
                }),
            'daily_clicks' => Visit::where('link_id', $link->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get(),
        ];
    }
}