<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderShapeExtensionTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('order_shape_extension', function (Blueprint $table) {
            $table->char('OrdNumb', 20);
            $table->char('Side', 1);
            $table->mediumText('FormPreview')->nullable();
            $table->primary(['OrdNumb', 'Side']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('order_shape_extension');
    }
}
