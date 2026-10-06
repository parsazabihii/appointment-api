<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('provider_service', function(Blueprint $t){$t->id();$t->foreignId('provider_id')->constrained()->cascadeOnDelete();$t->foreignId('service_id')->constrained()->cascadeOnDelete();$t->decimal('price',12,2);$t->boolean('is_active')->default(true);$t->timestamps();$t->unique(['provider_id','service_id']);}); } public function down(): void { Schema::dropIfExists('provider_service'); } };
