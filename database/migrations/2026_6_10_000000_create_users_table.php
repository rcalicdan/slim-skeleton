<?php

use Hibla\SchemaManager\Schema\Blueprint;
use Hibla\SchemaManager\Schema\Migration;

use function Hibla\await;

return new class extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        await($this->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        }));
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        await($this->dropIfExists('users'));
    }
};
