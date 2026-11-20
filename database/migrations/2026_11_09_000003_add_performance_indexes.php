<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $this->addIndexIfMissing('events', 'events_status_index', ['status']);
        $this->addIndexIfMissing('registrations', 'registrations_status_index', ['status']);
        $this->addIndexIfMissing('payments', 'payments_status_index', ['status']);
        $this->addIndexIfMissing('users', 'users_is_active_index', ['is_active']);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $this->dropIndexIfExists('events', 'events_status_index');
        $this->dropIndexIfExists('registrations', 'registrations_status_index');
        $this->dropIndexIfExists('payments', 'payments_status_index');
        $this->dropIndexIfExists('users', 'users_is_active_index');
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $index) {
            $table->index($columns, $index);
        });
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!$this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($index) {
            $table->dropIndex($index);
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
