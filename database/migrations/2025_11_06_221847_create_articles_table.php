<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('source', 64);
            $table->string('external_id')->nullable();
            $table->string('url')->unique();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->json('authors')->nullable();
            $table->string('category')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();

            $table->index(['source', 'published_at']);

            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'summary']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
