<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiLog;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StatsController extends Controller
{
    /**
     * Return analytics/statistics for the admin dashboard.
     * Aggregates data across all sites owned by the user.
     * Supports period filter: ?period=7d (default), 30d, 90d.
     *
     * GET /api/v1/admin/stats?period=7d
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $siteIds = $user->accessibleSiteIds();

        $period = $request->query('period', '7d');
        $days = match ($period) {
            '30d' => 30,
            '90d' => 90,
            default => 7,
        };

        $since = Carbon::now()->subDays($days);

        $totalConversations = Conversation::whereIn('site_id', $siteIds)
            ->where('created_at', '>=', $since)
            ->count();

        $totalMessages = Message::whereHas('conversation', fn ($q) => $q->whereIn('site_id', $siteIds))
            ->where('created_at', '>=', $since)
            ->count();

        $aiMessagesCount = Message::whereHas('conversation', fn ($q) => $q->whereIn('site_id', $siteIds))
            ->where('sender_type', 'ai')
            ->where('created_at', '>=', $since)
            ->count();

        // Average AI response time in milliseconds
        $avgResponseTime = AiLog::whereIn('site_id', $siteIds)
            ->where('created_at', '>=', $since)
            ->avg('response_time_ms');

        // Conversations grouped by day for chart display
        $conversationsByDay = Conversation::whereIn('site_id', $siteIds)
            ->where('created_at', '>=', $since)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        return response()->json([
            'total_conversations' => $totalConversations,
            'total_messages' => $totalMessages,
            'ai_messages_count' => $aiMessagesCount,
            'avg_response_time' => round($avgResponseTime ?? 0),
            'conversations_by_day' => $conversationsByDay,
        ]);
    }
}
