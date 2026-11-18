<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderConsumerTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('order_consumer', function (Blueprint $table) {
            $table->char('OrdNumb', 20)->default('')->primary();
            $table->string('Title', 10)->default('');
            $table->string('Name', 30)->default('');
            $table->string('FirstName', 20)->default('');
            $table->string('Street', 30)->default('');
            $table->string('ZipCode', 10)->default('');
            $table->string('City', 30)->default('');
            $table->string('Area', 20)->default('');
            $table->string('Country', 30)->default('');
            $table->string('Language', 10)->default('');
            $table->string('Phone', 20)->default('');
            $table->string('Mobile', 20)->default('');
            $table->string('EMail', 80)->default('');
            $table->string('Fax', 20)->default('');
            $table->string('DateOfBirth', 20)->default('');
            $table->string('Res1', 30)->default('');
            $table->string('Res2', 30)->default('');
            $table->string('Res3', 30)->default('');
            $table->string('Res4', 30)->default('');
            $table->string('Res5', 30)->default('');
            $table->string('Res6', 30)->default('');
            $table->string('Res7', 30)->default('');
            $table->string('Res8', 30)->default('');
            $table->string('Res9', 30)->default('');
            $table->string('Res10', 30)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('order_consumer');
    }
}
