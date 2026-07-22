<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reported_users', function (Blueprint $table) {
            $table->string('status')->default('open')->after('details');
            $table->text('admin_notes')->nullable()->after('status');
            $table->timestamp('resolved_at')->nullable()->after('admin_notes');
            $table->foreignId('resolved_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('reported_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resolved_by');
            $table->dropIndex(['status', 'created_at']);
            $table->dropColumn(['status', 'admin_notes', 'resolved_at']);
        });
    }
};
