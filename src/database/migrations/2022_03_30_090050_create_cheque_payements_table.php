<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChequePayementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_cheque_payments', function (Blueprint $table) {
            // $table->engine = "InnoDB";
            $table->uuid('id')->primary();
            $table->uuid('payments_id')->nullable();
            $table->uuid('bank_accounts_id')->nullable();
            $table->decimal('amount',20,2)->nullable();

            $table->string('cheque_number',100)->nullable();
            $table->string('status',45)->nullable();
            $table->datetime('due_date')->nullable();
            $table->uuid('banks_id')->nullable();
            $table->string('cheque_type',200)->nullable();


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
        Schema::dropIfExists('cheque_payements');
    }
}
