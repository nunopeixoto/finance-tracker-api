<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable(false)->constrained()->cascadeOnDelete()->unsigned();
            $table->timestamp('date')->nullable(false);
            $table->string('description', 200)->nullable(false);
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete()->unsigned()->nullable(false);
            $table->foreignId('expense_sub_category_id')->nullable()->default(null)->references('id')->on('expense_sub_categories');
            $table->string('note', 200)->comment('Auxiliary note.')->nullable()->default(null);
            $table->decimal('debit', 15, 2)->nullable();
            $table->decimal('credit', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
