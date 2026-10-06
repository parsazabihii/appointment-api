<?php
namespace App\Services;
use Illuminate\Support\Facades\Cache;
class SlotCacheService { public function version(int $providerId): int { return (int) Cache::get("provider:{$providerId}:slots-version",1); } public function key(int $providerId,int $serviceId,string $date): string { return "slots:provider:{$providerId}:service:{$serviceId}:date:{$date}:v{$this->version($providerId)}"; } public function invalidate(int $providerId): void { $key="provider:{$providerId}:slots-version"; if(!Cache::has($key)){Cache::forever($key,2);return;} Cache::increment($key); } }
