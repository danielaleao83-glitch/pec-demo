<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Garante que só roda em PostgreSQL
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "btree_gin";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "citext";');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP EXTENSION IF EXISTS "pgcrypto";');
        DB::statement('DROP EXTENSION IF EXISTS "uuid-ossp";');
        DB::statement('DROP EXTENSION IF EXISTS "pg_trgm";');
        DB::statement('DROP EXTENSION IF EXISTS "btree_gin";');
        DB::statement('DROP EXTENSION IF EXISTS "citext";');
    }
};
