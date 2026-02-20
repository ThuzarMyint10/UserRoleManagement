<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Profile-related fields 
            $table->string('phone')->nullable()->unique(); // Optional phone number for SMS/2FA 
            $table->string('avatar')->nullable(); // Profile picture path 
            $table->text('bio')->nullable(); // Short user description / bio 
            $table->boolean('is_active')->default(true); // Account status flag
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop profile-related fields if rolled back 
            $table->dropColumn(['phone', 'avatar', 'bio', 'is_active']);
        });
    }
};
