<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('quota')->default(0);
            $table->integer('registered_count')->default(0);
            $table->date('registration_start');
            $table->date('registration_end');
            $table->date('event_start');
            $table->date('event_end');
            $table->enum('status', ['draft', 'active', 'closed', 'completed'])->default('draft');
            $table->string('location')->nullable();
            $table->string('banner')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
