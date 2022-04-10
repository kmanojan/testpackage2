<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_payments', function (Blueprint $table) {
            // $table->engine = "InnoDB";
            $table->uuid('id')->primary();
            $table->string('invoice_number',200)->nullable();
            $table->decimal('payable_amount',20,2)->nullable();
            $table->decimal('serivce_charges',20,2)->nullable();
            $table->decimal('tax',20,2)->nullable();
            $table->decimal('discount_amount',20,2)->nullable();
            $table->decimal('paid_amount',20,2)->nullable();
            $table->string('paid_mode',200)->nullable();
            $table->text('note')->nullable();

            $table->uuid('department_id')->index('department_id')->nullable();
            // $table->foreign('department_id')->references('id')->on('core_departments');
            $table->uuid('added_by_id')->index('added_by_id')->nullable();
            // $table->foreign('added_by_id')->references('id')->on('core_users');
            $table->uuid('updated_by_id')->index('updated_by_id')->nullable();
            // $table->foreign('updated_by_id')->references('id')->on('core_users');
            $table->uuid('deleted_by_id')->index('deleted_by_id')->nullable();
            // $table->foreign('deleted_by_id')->references('id')->on('core_users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payements');
    }
}
