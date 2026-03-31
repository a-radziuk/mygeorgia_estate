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
            $table->unsignedBigInteger('korter_object_id')->nullable()->unique()->after('id');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedInteger('listing_index')->change();
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('korter_object_id');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedTinyInteger('listing_index')->change();
        });
    }
};
