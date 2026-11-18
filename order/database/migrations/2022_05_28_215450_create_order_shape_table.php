<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderShapeTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('order_shape', function (Blueprint $table) {
            $table->char('OrdNumb', 20);
            $table->char('Side', 1);
            $table->char('Trctype', 10);
            $table->decimal('PD', 5, 1)->nullable();
            $table->decimal('Height', 5, 1)->nullable();
            $table->decimal('FDecX', 5, 1)->nullable();
            $table->decimal('FDecY', 5, 1)->nullable();
            $table->decimal('DBL', 5, 2)->nullable();
            $table->decimal('HBox', 5, 2)->nullable();
            $table->decimal('VBox', 5, 2)->nullable();
            $table->decimal('Circ', 6, 2)->nullable();
            $table->decimal('Corr1', 5, 2)->nullable();
            $table->decimal('Corr2', 5, 2)->nullable();
            $table->char('Etyp', 3)->nullable();
            $table->char('FTyp', 3)->nullable();
            $table->char('BevP', 3)->nullable();
            $table->decimal('BevM', 5, 2)->nullable();
            $table->decimal('GDepth', 5, 2)->nullable();
            $table->decimal('GWidth', 5, 2)->nullable();
            $table->decimal('FPinB', 5, 2)->nullable();
            $table->decimal('PinB', 5, 2)->nullable();
            $table->char('Polish', 1)->nullable();
            $table->decimal('Frcv', 5, 2)->nullable();
            $table->decimal('FaceFormA', 5, 1)->nullable();
            $table->decimal('Panto', 5, 1)->nullable();
            $table->char('FBlockFlag', 1)->nullable();
            $table->mediumText('Drill')->nullable();
            $table->longText('Form')->nullable();
            $table->primary(['OrdNumb', 'Side']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('order_shape');
    }
}
