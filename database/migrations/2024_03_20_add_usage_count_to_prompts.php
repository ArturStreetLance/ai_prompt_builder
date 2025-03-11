<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prompts', function (Blueprint $table) {
            $table->unsignedInteger('usage_count')->default(0);
            $table->index('usage_count');
        });
    }

    public function down()
    {
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropColumn('usage_count');
        });
    }
}; 