<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('availabilities', function(Blueprint $t){$t->id();$t->foreignId('provider_id')->constrained()->cascadeOnDelete();$t->unsignedTinyInteger('day_of_week');$t->time('start_time');$t->time('end_time');$t->timestamps();$t->index(['provider_id','day_of_week']);}); } public function down(): void { Schema::dropIfExists('availabilities'); } };
