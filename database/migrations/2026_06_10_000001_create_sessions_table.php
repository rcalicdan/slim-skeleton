<?php

declare(strict_types=1);

use Hibla\SchemaManager\Schema\Blueprint;
use Hibla\SchemaManager\Schema\Migration;

use function Hibla\await;

return new class () extends Migration {
    /**
     * Run the migration.
     */
    public function up(): void
    {
        await($this->create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->text('payload');
            $table->integer('last_activity');
        }));
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        await($this->dropIfExists('sessions'));
    }
};
