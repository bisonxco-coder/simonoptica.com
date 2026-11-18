<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTracerConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracer_configuration', function (Blueprint $table) {
            $table->integer('id', true);
            $table->char('customerNo', 50)->default('0');
            $table->char('tracer', 100)->default('0');
            $table->char('port', 250)->nullable()->default('0');
            $table->integer('baud')->nullable()->default(0);
            $table->integer('flow')->nullable()->default(0);
            $table->char('parity', 1)->nullable()->default('0');
            $table->integer('databit')->nullable()->default(0);
            $table->integer('stopbit')->nullable()->default(0);
            $table->integer('vca')->nullable()->default(0);
            $table->char('mode', 1)->nullable()->default('0');
            $table->integer('com_soft')->nullable()->default(0);
            $table->integer('com_hard')->nullable()->default(0);
            $table->integer('com_dtr')->nullable()->default(0);
            $table->char('ip', 50)->nullable()->default(null);
            $table->char('tracerip', 250)->nullable()->default(null);
            $table->char('tracerserialno', 250)->nullable()->default(null);
            $table->char('proxyHost', 250)->nullable()->default(null);
            $table->char('proxyPort', 5)->nullable()->default(null);
            $table->char('proxyUser', 250)->nullable()->default(null);
            $table->char('proxyPass', 250)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracer_configuration');
    }
}
