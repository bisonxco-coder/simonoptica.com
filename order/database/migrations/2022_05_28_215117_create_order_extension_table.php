<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderExtensionTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('order_extension', function (Blueprint $table) {
            $table->char('OrdNumb', 20);
            $table->char('RecType', 10);
            $table->char('Statistic1', 10)->nullable();
            $table->char('Statistic2', 10)->nullable();
            $table->char('Statistic3', 10)->nullable();
            $table->char('Statistic4', 10)->nullable();
            $table->char('Statistic5', 10)->nullable();
            $table->char('Statistic6', 10)->nullable();
            $table->char('Statistic7', 10)->nullable();
            $table->char('Statistic8', 10)->nullable();
            $table->char('Statistic9', 10)->nullable();
            $table->char('Flag1', 1)->nullable();
            $table->char('Flag2', 1)->nullable();
            $table->char('Flag3', 1)->nullable();
            $table->char('Flag4', 1)->nullable();
            $table->char('Flag5', 1)->nullable();
            $table->string('Information1', 100)->nullable();
            $table->string('Information2', 100)->nullable();
            $table->string('Information3', 100)->nullable();
            $table->string('Information4', 100)->nullable();
            $table->string('Information5', 100)->nullable();
            $table->date('InfoDate1')->nullable();
            $table->date('InfoDate2')->nullable();
            $table->date('InfoDate3')->nullable();
            $table->primary(['OrdNumb', 'RecType']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('order_extension');
    }
}
