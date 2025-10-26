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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('SYP');
            $table->string('condition')->default('used'); // new, used, excellent, good, fair
            $table->string('location');
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->boolean('is_negotiable')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['active', 'sold', 'expired', 'suspended'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
