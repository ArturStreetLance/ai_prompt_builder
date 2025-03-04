<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prompt_templates', function (Blueprint $table) {
            $table->index('rating');
            $table->index('version');
        });

        Schema::table('saved_prompts', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->index(['prompt_template_id', 'score']);
        });
    }

    public function down()
    {
        Schema::table('prompt_templates', function (Blueprint $table) {
            $table->dropIndex(['rating']);
            $table->dropIndex(['version']);
        });

        Schema::table('saved_prompts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex(['prompt_template_id', 'score']);
        });
    }
}; 