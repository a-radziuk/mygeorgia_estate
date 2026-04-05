<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dateTime('korter_listed_at')->nullable()->after('market_type');
            $table->dateTime('korter_updated_at')->nullable()->after('korter_listed_at');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['korter_listed_at', 'korter_updated_at']);
        });
    }
};
