<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderHeaderTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('order_header', function (Blueprint $table) {
            $table->integer('OrdNumb', true);
            $table->char('OrdNumbX', 20)->nullable()->index();
            $table->char('OrdNumbH', 20)->nullable()->index();
            $table->char('CustNumb', 10)->index();
            $table->char('CustNumbX', 20)->nullable()->index();
            $table->char('ProcFlag', 1)->nullable()->index();
            $table->char('StoreNumb', 8)->nullable();
            $table->char('SuppCode', 3)->nullable();
            $table->char('RecStat', 4)->nullable()->index();
            $table->char('Rectype', 2);
            $table->char('Ordtype', 2);
            $table->char('OrdCat', 2);
            $table->char('FrameCat', 2)->nullable();
            $table->char('FrameNo', 20)->nullable();
            $table->char('FSupplier', 3)->nullable();
            $table->char('FReference', 30)->nullable();
            $table->char('Side', 1);
            $table->char('Reference', 30);
            $table->string('Reference1', 50)->default('')->comment('Reference 1');
            $table->string('Reference2', 50)->default('')->comment('Reference 2');
            $table->char('EntryType', 1);
            $table->char('EntryUser', 10);
            $table->date('EntryDate')->nullable()->index();
            $table->time('EntryTime')->nullable();
            $table->string('EntryFile', 50)->nullable();
            $table->char('RefNumb', 20)->nullable();
            $table->date('RefDate')->nullable();
            $table->time('RefTime')->nullable();
            $table->date('ReqDelv')->nullable();
            $table->date('PlanDelv')->nullable();
            $table->string('ProdPlan', 40)->nullable();
            $table->date('Senddate')->nullable();
            $table->time('Sendtime')->nullable();
            $table->char('Status', 4)->nullable();
            $table->date('StatDate')->nullable();
            $table->time('StatTime')->nullable();
            $table->char('StatUser', 10)->nullable();
            $table->char('PrntFlag', 1)->nullable();
            $table->char('PrntLocat', 10)->nullable();
            $table->char('DelvNumb', 20)->nullable();
            $table->char('DelvAdr', 10)->nullable();
            $table->date('DelvDate')->nullable();
            $table->time('DelvTime')->nullable();
            $table->integer('DelvCost', false, true)->nullable();
            $table->char('InvNumb', 20)->nullable();
            $table->char('InvAdr', 20)->nullable();
            $table->date('InvDate')->nullable();
            $table->time('Invtime')->nullable();
            $table->integer('ErrorNumb')->nullable();
            $table->char('Control', 10)->nullable();
            $table->string('ConsRef', 30)->nullable();
            $table->string('ConsName', 30)->nullable();
            $table->string('ConsStreet', 30)->nullable();
            $table->string('ConsZipC', 10)->nullable();
            $table->string('ConsCity', 30)->nullable();
            $table->string('ConsArea', 20)->nullable();
            $table->string('ConsRes', 10)->nullable();
            $table->mediumText('Remark')->nullable();
            $table->mediumText('Special')->nullable();
            $table->mediumText('Adress')->nullable();
            $table->char('Branch', 10)->nullable();
            $table->integer('ErrorNumbS')->nullable();
            $table->index(['OrdNumb', 'ProcFlag']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('order_header');
    }
}
