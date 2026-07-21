<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |----------------------------------------------------------
        | 🔐 FUNÇÃO
        |----------------------------------------------------------
        */
        DB::statement("
            CREATE OR REPLACE FUNCTION prevent_logs_forenses_change()
            RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'Tabela logs_forenses é imutável';
            END;
            $$ LANGUAGE plpgsql;
        ");

        /*
        |----------------------------------------------------------
        | 🔒 TRIGGER UPDATE
        |----------------------------------------------------------
        */
        DB::statement('DROP TRIGGER IF EXISTS logs_forenses_no_update ON logs_forenses;');

        DB::statement('
            CREATE TRIGGER logs_forenses_no_update
            BEFORE UPDATE ON logs_forenses
            FOR EACH ROW
            EXECUTE FUNCTION prevent_logs_forenses_change();
        ');

        /*
        |----------------------------------------------------------
        | 🔒 TRIGGER DELETE
        |----------------------------------------------------------
        */
        DB::statement('DROP TRIGGER IF EXISTS logs_forenses_no_delete ON logs_forenses;');

        DB::statement('
            CREATE TRIGGER logs_forenses_no_delete
            BEFORE DELETE ON logs_forenses
            FOR EACH ROW
            EXECUTE FUNCTION prevent_logs_forenses_change();
        ');

        /*
        |----------------------------------------------------------
        | 🚫 HARDENING EXTRA
        |----------------------------------------------------------
        */
        DB::statement('REVOKE TRUNCATE ON TABLE logs_forenses FROM PUBLIC;');
        DB::statement('REVOKE UPDATE, DELETE ON TABLE logs_forenses FROM PUBLIC;');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS logs_forenses_no_update ON logs_forenses;');
        DB::statement('DROP TRIGGER IF EXISTS logs_forenses_no_delete ON logs_forenses;');
        DB::statement('DROP FUNCTION IF EXISTS prevent_logs_forenses_change;');
    }
};
