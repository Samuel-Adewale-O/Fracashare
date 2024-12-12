<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, string $name = 'api'): Response
    {
        $key = $this->resolveRequestSignature($request, $name);

        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts($name))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $this->limiter->availableIn($key)
            ], 429);
        }

        $this->limiter->hit($key, $this->decayMinutes($name) * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders(
            $response,
            $this->maxAttempts($name),
            $this->limiter->remaining($key, $this->maxAttempts($name))
        );
    }

    protected function resolveRequestSignature(Request $request, string $name): string
    {
        $signature = $request->user()
            ? $request->user()->id
            : $request->ip();

        return sha1($signature . $name . $request->route()?->getName());
    }

    protected function maxAttempts(string $name): int
    {
        return match($name) {
            'auth' => 5,    // 5 attempts for auth endpoints
            'otp' => 3,     // 3 attempts for OTP verification
            'kyc' => 3,     // 3 attempts for KYC verification
            default => 60   // 60 attempts for general API endpoints
        };
    }

    protected function decayMinutes(string $name): int
    {
        return match($name) {
            'auth' => 1,    // 1 minute for auth endpoints
            'otp' => 10,    // 10 minutes for OTP verification
            'kyc' => 60,    // 1 hour for KYC verification
            default => 1    // 1 minute for general API endpoints
        };
    }

    protected function addRateLimitHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);
    }
}