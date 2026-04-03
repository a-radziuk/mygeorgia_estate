<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('total_area_sqm', 12, 2)->nullable()->after('built_year');
            $table->decimal('living_area_sqm', 12, 2)->nullable()->after('total_area_sqm');
            $table->decimal('kitchen_area_sqm', 12, 2)->nullable()->after('living_area_sqm');
            $table->decimal('land_parcel_area_sqm', 12, 2)->nullable()->after('kitchen_area_sqm');
            $table->decimal('terrace_area_sqm', 12, 2)->nullable()->after('land_parcel_area_sqm');
            $table->unsignedSmallInteger('bedroom_count')->nullable()->after('terrace_area_sqm');
            $table->unsignedSmallInteger('bathroom_count')->nullable()->after('bedroom_count');
            $table->unsignedSmallInteger('room_count')->nullable()->after('bathroom_count');
            $table->decimal('ceiling_height_m', 6, 2)->nullable()->after('room_count');
            $table->boolean('has_balcony')->nullable()->after('ceiling_height_m');
            $table->boolean('has_terrace')->nullable()->after('has_balcony');
            $table->string('parking', 255)->nullable()->after('has_terrace');
            $table->string('floors_label', 120)->nullable()->after('parking');
            $table->string('property_subtype', 120)->nullable()->after('floors_label');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'total_area_sqm',
                'living_area_sqm',
                'kitchen_area_sqm',
                'land_parcel_area_sqm',
                'terrace_area_sqm',
                'bedroom_count',
                'bathroom_count',
                'room_count',
                'ceiling_height_m',
                'has_balcony',
                'has_terrace',
                'parking',
                'floors_label',
                'property_subtype',
            ]);
        });
    }
};
