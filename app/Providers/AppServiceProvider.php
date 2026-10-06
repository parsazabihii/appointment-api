<?php
namespace App\Providers;
use App\Models\Appointment; use App\Policies\AppointmentPolicy; use Illuminate\Cache\RateLimiting\Limit; use Illuminate\Http\Request; use Illuminate\Support\Facades\Gate; use Illuminate\Support\Facades\RateLimiter; use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider { public function register(): void {} public function boot(): void { Gate::policy(Appointment::class,AppointmentPolicy::class); RateLimiter::for('auth',fn(Request $r)=>Limit::perMinute(10)->by($r->ip())); RateLimiter::for('booking',fn(Request $r)=>Limit::perMinute(20)->by($r->user()?->id ?: $r->ip())); RateLimiter::for('webhook',fn(Request $r)=>Limit::perMinute(120)->by($r->ip())); } }
