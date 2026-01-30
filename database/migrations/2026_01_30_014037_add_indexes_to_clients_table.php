<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->index('name');

            $table->index('is_active');

            $table->index('birth_date');

            $table->index('phone');

            $table->index(['deleted_at', 'is_active']);

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['birth_date']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['deleted_at', 'is_active']);
            $table->dropIndex(['created_at']);
        });
    }
};
