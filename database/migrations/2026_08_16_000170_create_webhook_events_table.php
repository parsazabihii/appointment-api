<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('webhook_events', function(Blueprint $t){$t->id();$t->string('event_id')->unique();$t->string('provider',30);$t->string('type',80);$t->json('payload');$t->timestampTz('processed_at')->nullable();$t->timestamps();}); } public function down(): void { Schema::dropIfExists('webhook_events'); } };
