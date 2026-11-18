<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLenspropertiesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('order_lensproperties', function (Blueprint $table) {
            $table->char('OrdNumb', 20);
            $table->char('OrdNumbS', 20)->nullable()->index();
            $table->char('Side', 1);
            $table->char('Type', 2)->nullable();
            $table->char('StockRX', 1);
            $table->char('Mat_Ext', 1);
            $table->char('Group_Ext', 1);
            $table->char('Material', 2);
            $table->char('LensGroup', 2);
            $table->char('Cribtype', 2);
            $table->char('Diam1', 2);
            $table->char('Diam2', 2);
            $table->char('Diam3', 2);
            $table->char('CTO', 1)->nullable();
            $table->decimal('Sphere', 6, 2)->nullable();
            $table->decimal('Cylinder', 6, 2)->nullable();
            $table->integer('Axis', false, true)->nullable();
            $table->decimal('Addition', 6, 2)->nullable();
            $table->decimal('Prism1', 6, 2)->nullable();
            $table->integer('PrBase1', false, true)->nullable();
            $table->decimal('Prism2', 6, 2)->nullable();
            $table->integer('PrBase2', false, true)->nullable();
            $table->decimal('Prism', 6, 2)->nullable();
            $table->integer('PrBase', false, true)->nullable();
            $table->decimal('RedPrs', 6, 2)->nullable();
            $table->decimal('Edgethick', 4, 1)->nullable();
            $table->decimal('Centthick', 4, 1)->nullable();
            $table->decimal('BaseCurve', 5, 2)->nullable();
            $table->decimal('DecentX', 6, 2)->nullable();
            $table->decimal('DecentY', 6, 2)->nullable();
            $table->integer('Quantity', false, true)->nullable();
            $table->integer('Discount', false, true)->nullable();
            $table->integer('Price', false, true)->nullable();
            $table->char('Centering', 2)->nullable();
            $table->decimal('PD', 5, 1)->nullable();
            $table->decimal('Height', 5, 1)->nullable();
            $table->decimal('FDecX', 5, 1)->nullable();
            $table->decimal('FDecY', 5, 1)->nullable();
            $table->char('Control', 30)->nullable();
            $table->char('IndType', 15)->nullable();
            $table->mediumText('Individual')->nullable();
            $table->mediumText('Remark')->nullable();
            $table->mediumText('Special')->nullable();
            $table->primary(['OrdNumb', 'Side']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('order_lensproperties');
    }
}
