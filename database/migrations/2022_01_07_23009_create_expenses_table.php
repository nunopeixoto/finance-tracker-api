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
            $table->foreignId('expense_sub_category_id')->constrained()->cascadeOnDelete()->unsigned()->nullable();
            $table->string('note', 200)->comment('Auxiliary note.')->nullable();
            $table->decimal('amount', 15, 2)->nullable(false);
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
