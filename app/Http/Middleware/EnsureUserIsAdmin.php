<?php
namespace App\Http\Middleware;
use App\Enums\UserRole; use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class EnsureUserIsAdmin { public function handle(Request $request,Closure $next): Response { abort_unless($request->user()?->role===UserRole::Admin,403,'Admin access required.'); return $next($request); } }
