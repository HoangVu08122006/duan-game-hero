// app/Http/Middleware/UpdateUserActivity.php
public function handle($request, Closure $next)
{
    if (auth()->check()) {
        auth()->user()->update([
            'last_login_at' => now(),
            'is_online' => 1
        ]);
    }
    return $next($request);
}