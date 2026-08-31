<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 10);
            $table->timestamps();
            $table->unique(['name', 'type']);
        });

        foreach (['incomes', 'expenses'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->foreignId('category_id')->constrained()->restrictOnDelete();
                $table->date('date')->index();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        foreach (['income_details' => 'incomes', 'expense_details' => 'expenses'] as $name => $parent) {
            Schema::create($name, function (Blueprint $table) use ($parent) {
                $table->id();
                $key = $parent === 'incomes' ? 'income_id' : 'expense_id';
                $table->foreignId($key)->constrained($parent)->cascadeOnDelete();
                $table->string('name');
                $table->unsignedBigInteger('amount');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_details');
        Schema::dropIfExists('income_details');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('categories');
    }
};
