<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('korter_import_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('next_preset')->default(1);
            $table->unsignedInteger('next_page')->default(1);
            $table->boolean('is_idle')->default(false);
            $table->timestamps();
        });

        Schema::create('korter_import_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('preset')->nullable();
            $table->unsignedInteger('page')->nullable();
            $table->unsignedInteger('imported_count')->default(0);
            $table->boolean('had_apartments')->default(false);
            $table->boolean('http_ok')->default(false);
            $table->boolean('parse_ok')->default(false);
            $table->boolean('idle_skip')->default(false);
            $table->string('url', 2048)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('korter_import_runs');
        Schema::dropIfExists('korter_import_states');
    }
};
