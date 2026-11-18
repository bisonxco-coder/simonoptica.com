<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderComponentTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('order_component', function (Blueprint $table) {
            $table->char('OrdNumb', 20)->comment('LO job number / LW job number');
            $table->char('Side', 1)->comment('"R"/"L"');
            $table->char('Position', 3)->comment('001 to nnn');
            $table->char('Comptype', 2)->comment('01 - Lens\\\\r\\\\n02 - Tinting\\\\r\\\\n03 - Coating/Clean\\\\r\\\\n04 - Hard/Clean\\\\r\\\\n05 - UV/Clean\\\\r\\\\n20 - CTO\\\\r\\\\n21 - Knife edge\\\\r\\\\n22 - Oval\\\\r\\\\n24 - Mattieren\\\\r\\\\n25 - Optilenti\\\\r\\\\n26 - SlabOff\\\\r\\\\n40 - Montage');
            $table->char('Compcode', 6)->comment('SF6: Code1 + Code2 (C*3 + C*3)');
            $table->char('CompCodeEx', 10)->nullable()->comment('Alias component code (HOST Import)');
            $table->char('Cribtype', 2)->default('R')->comment('R - Round\\\\r\\\\nO - Oval\\\\r\\\\nF - Flat\\\\r\\\\nS - Shape');
            $table->char('Diam1', 2)->comment('Physical diameter');
            $table->char('Diam2', 2)->comment('Decentration diameter');
            $table->char('Diam3', 2)->nullable()->comment('Height for "O/F"');
            $table->char('PGSph', 2)->nullable()->comment('Pricegroup Spheric');
            $table->char('PGTor', 2)->nullable()->comment('Pricegroup Toric');
            $table->char('StockRX', 1)->nullable()->comment('S - Stock\\\\r\\\\nR - RX');
            $table->char('SpecPrice', 2)->nullable()->comment('Special price');
            $table->integer('Price1', false, true)->nullable()->default(0)->comment('Price 1');
            $table->integer('Price2', false, true)->nullable()->default(0)->comment('Price 2');
            $table->integer('Price3', false, true)->nullable()->default(0)->comment('Price 3');
            $table->decimal('Discount', 6, 2, true)->nullable();
            $table->integer('Quantity', false, true)->nullable();
            $table->char('ItemNumb', 20)->nullable();
            $table->char('ItemType', 4)->nullable();
            $table->char('ItemPartOf', 20)->nullable();
            $table->char('AbsoTop', 3)->nullable();
            $table->char('AbsoBot', 3)->nullable();
            $table->decimal('Sphere', 6, 2)->nullable();
            $table->decimal('Cylinder', 6, 2)->nullable();
            $table->decimal('Addition', 6, 2)->nullable();
            $table->text('Special')->nullable();
            $table->string('CompDescr', 50)->nullable();
            $table->char('GivenLens', 1)->nullable();
            $table->primary(['OrdNumb', 'Side', 'Position']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('order_component');
    }
}
