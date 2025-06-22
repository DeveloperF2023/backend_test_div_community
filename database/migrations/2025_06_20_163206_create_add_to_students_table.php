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
        Schema::table('students', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->nullable(); // Gender filter
            $table->date('birth_date')->nullable();                 // For age filter
            $table->string('cne')->unique();                        // National student ID / unique code
            $table->string('level')->nullable();                    // Example: Primary, Middle, High
            $table->string('section')->nullable();                  // Example: Science, Literary
            $table->string('city')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'birth_date',
                'cne',
                'level',
                'section',
                'city',
            ]);
        });
    }
};
