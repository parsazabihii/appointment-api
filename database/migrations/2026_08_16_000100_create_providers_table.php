<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('providers', function(Blueprint $t){$t->id();$t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();$t->text('bio')->nullable();$t->string('timezone',64)->default('UTC');$t->boolean('is_active')->default(true)->index();$t->timestamps();}); } public function down(): void { Schema::dropIfExists('providers'); } };
