<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemLanguageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_language', function (Blueprint $table) {
            $table->unsignedInteger('LangNumber')->comment('Language number');
            $table->string('LangCode', 2)->comment('Language code ISO (en, de, fr, ...)');
            $table->string('LangCode2', 5)->comment('Extension for diacritical characters\r\nLanguage subdivision, e.g. fr 1,2,3');
            $table->string('LangPartner', 10)->comment('Partner specific textes for the same number');
            $table->string('LangProject', 25)->default('0')->comment('Application / section')->nullable();
            $table->string('LangDialog', 25)->default('0')->comment('Window / subsection')->nullable();
            $table->string('LangText', 500)->default('0')->comment('Text');
            $table->string('ExportFlag')->default('0')->nullable();
            $table->mediumText('Remark')->nullable();
            $table->primary(['LangNumber', 'LangCode', 'LangCode2', 'LangPartner'], 'PRIMARY');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_language');
    }
}
