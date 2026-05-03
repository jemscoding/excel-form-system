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
        Schema::create('excel_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('agent_id')->constrained();
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('depositing_bank_id')->constrained();
            $table->string('container_code');
            $table->date('china_recieved_date');
            $table->string('order_reference');
            $table->integer('pkgs');
            $table->decimal('total_cbm', 8, 2);
            $table->string('soa_number');
            $table->decimal('actual_payment', 8, 2);
            $table->decimal('initial_billing', 8, 2);
            $table->decimal('withholding_tax', 8, 2);
            $table->decimal('inbound_cost', 8, 2);
            $table->decimal('service_fee', 8, 2);
            $table->decimal('overweight', 8, 2);
            $table->decimal('discount', 8, 2);
            $table->decimal('others', 8, 2);
            $table->decimal('amount_to_be_paid', 8, 2);
            $table->decimal('balance', 8, 2);
            $table->string('payment_reference_number');
            $table->enum('status', ['pending', 'paid', 'unpaid'])->default('pending');
            $table->timestamps();
            $table->decimal('total');
            $table->enum('purpose',['freight_payment', 'pthers'])->default('freight_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_forms');
    }
};
