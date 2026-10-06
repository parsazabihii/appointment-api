<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('blocked_times', function(Blueprint $t){$t->id();$t->foreignId('provider_id')->constrained()->cascadeOnDelete();$t->timestampTz('starts_at');$t->timestampTz('ends_at');$t->string('reason')->nullable();$t->timestamps();$t->index(['provider_id','starts_at','ends_at']);}); } public function down(): void { Schema::dropIfExists('blocked_times'); } };
