<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prompt_history_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_history_id')->constrained()->onDelete('cascade');
            $table->foreignId('prompt_id')->constrained()->onDelete('cascade');
            $table->integer('position')->default(0); // позиция промпта в итоговом тексте
            $table->timestamps();

            $table->unique(['prompt_history_id', 'prompt_id']);
            $table->index(['prompt_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('prompt_history_items');
    }
}; 