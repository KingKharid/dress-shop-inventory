<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $memoryBefore = memory_get_usage(true);

        $response = $next($request);

        $end = microtime(true);
        $memoryAfter = memory_get_usage(true);
        
        $executionTime = round(($end - $start) * 1000, 2); // Convert to milliseconds
        $memoryUsed = round(($memoryAfter - $memoryBefore) / 1024 / 1024, 2); // Convert to MB
        $peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2); // Convert to MB

        // Add performance headers for debugging
        $response->headers->set('X-Execution-Time', $executionTime . 'ms');
        $response->headers->set('X-Memory-Usage', $memoryUsed . 'MB');
        $response->headers->set('X-Peak-Memory', $peakMemory . 'MB');

        // Log slow requests (> 1 second)
        if ($executionTime > 1000) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime . 'ms',
                'memory_used' => $memoryUsed . 'MB',
                'peak_memory' => $peakMemory . 'MB',
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);
        }

        // Log performance metrics for monitoring
        if (config('app.debug')) {
            Log::info('Performance metrics', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime,
                'memory_used' => $memoryUsed,
                'peak_memory' => $peakMemory,
                'queries' => $this->getQueryCount(),
            ]);
        }

        return $response;
    }

    /**
     * Get the number of database queries executed
     */
    private function getQueryCount(): int
    {
        return count(\DB::getQueryLog());
    }
}