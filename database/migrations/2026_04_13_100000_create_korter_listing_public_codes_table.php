<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('korter_listing_public_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('korter_object_id')->nullable()->unique();
            $table->unsignedBigInteger('korter_layout_id')->nullable()->unique();
            $table->char('public_code', 9)->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('korter_listing_public_codes');
    }
};
