<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_metric_inputs', function (Blueprint $table) {
            $table->id();
            $table->date('period_month')->unique();
            $table->unsignedInteger('starting_customers')->default(0);
            $table->unsignedInteger('lost_customers')->default(0);
            $table->unsignedInteger('active_accounts')->default(0);
            $table->unsignedInteger('new_customers')->default(0);
            $table->unsignedBigInteger('monthly_recurring_revenue_cents')->default(0);
            $table->unsignedBigInteger('sales_marketing_cost_cents')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_metric_inputs');
    }
};
