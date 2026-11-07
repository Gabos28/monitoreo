<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Services\ServerMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ServerController extends Controller
{
    public function index(ServerMetrics $metrics): View
    {
        return view('servidor', [
            'data' => $metrics->snapshot(),
            'loginAttempts' => $this->recentFailedLoginAttempts(),
        ]);
    }

    public function api(ServerMetrics $metrics): JsonResponse
    {
        return response()->json([
            ...$metrics->snapshot(),
            'login_attempts' => $this->recentFailedLoginAttempts(),
        ]);
    }

    public function loginAttempts(): JsonResponse
    {
        return response()->json([
            'login_attempts' => $this->recentFailedLoginAttempts(),
        ]);
    }

    /**
     * @return array<int, array{ip_address: string, status: string, created_at: string}>
     */
    private function recentFailedLoginAttempts(): array
    {
        return LoginAttempt::query()
            ->whereIn('status', ['failed', 'blocked'])
            ->latest('created_at')
            ->limit(10)
            ->get(['ip_address', 'status', 'created_at'])
            ->map(static fn (LoginAttempt $attempt): array => [
                'ip_address' => $attempt->ip_address,
                'status' => $attempt->status,
                'created_at' => (string) $attempt->created_at,
            ])
            ->all();
    }
}