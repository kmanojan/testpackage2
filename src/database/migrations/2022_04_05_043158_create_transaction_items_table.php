<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_transaction_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id')->index('transaction_id')->nullable();
            $table->foreign('transaction_id')->references('id')->on('acc_transactions');
            $table->string('account_type',225);
            $table->uuid('party_id')->index('party_id')->nullable();
            // $table->foreign('party_id')->references('id')->on('core_users');
            $table->uuid('category_id')->index('category_id')->nullable();
            // $table->foreign('category_id')->references('id')->on('acc_categories');
            $table->uuid('bank_account_id')->index('bank_account_id')->nullable();
            // $table->foreign('bank_account_id')->references('id')->on('acc_bank_accounts');
            $table->decimal('debit',20,2)->default(0)->nullable();
            $table->decimal('credit',20,2)->default(0)->nullable();

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
        Schema::dropIfExists('acc_transaction_items');
    }
}
