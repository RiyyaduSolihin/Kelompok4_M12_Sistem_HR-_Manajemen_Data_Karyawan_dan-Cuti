<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sesuaikan tabel employees agar kompatibel 2 arah antara M12 (nama, divisi, jabatan) & M14 (name, department, position)
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'name')) {
                $table->string('name', 100)->nullable();
            }
            if (! Schema::hasColumn('employees', 'department')) {
                $table->string('department', 50)->nullable();
            }
            if (! Schema::hasColumn('employees', 'position')) {
                $table->string('position', 50)->nullable();
            }
        });

        // Buat trigger sinkronisasi otomatis di PostgreSQL antara kolom M12 dan M14 pada tabel employees
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE employees ALTER COLUMN nama DROP NOT NULL');
            DB::statement('ALTER TABLE employees ALTER COLUMN divisi DROP NOT NULL');
            DB::statement('ALTER TABLE employees ALTER COLUMN jabatan DROP NOT NULL');

            DB::statement("
                UPDATE employees
                SET name = COALESCE(name, nama),
                    department = COALESCE(department, divisi),
                    position = COALESCE(position, jabatan)
            ");

            DB::statement("
                CREATE OR REPLACE FUNCTION sync_employee_m12_m14_columns()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    IF TG_OP = 'INSERT' THEN
                        NEW.nama := COALESCE(NEW.nama, NEW.name, '');
                        NEW.name := COALESCE(NEW.name, NEW.nama, '');
                        NEW.divisi := COALESCE(NEW.divisi, NEW.department, '');
                        NEW.department := COALESCE(NEW.department, NEW.divisi, '');
                        NEW.jabatan := COALESCE(NEW.jabatan, NEW.position, '');
                        NEW.position := COALESCE(NEW.position, NEW.jabatan, '');
                        NEW.status_kerja := COALESCE(NEW.status_kerja, 'aktif');
                        NEW.created_at := COALESCE(NEW.created_at, CURRENT_TIMESTAMP);
                        NEW.updated_at := COALESCE(NEW.updated_at, CURRENT_TIMESTAMP);
                    ELSIF TG_OP = 'UPDATE' THEN
                        IF NEW.nama IS DISTINCT FROM OLD.nama THEN
                            NEW.name := NEW.nama;
                        ELSIF NEW.name IS DISTINCT FROM OLD.name THEN
                            NEW.nama := NEW.name;
                        END IF;

                        IF NEW.divisi IS DISTINCT FROM OLD.divisi THEN
                            NEW.department := NEW.divisi;
                        ELSIF NEW.department IS DISTINCT FROM OLD.department THEN
                            NEW.divisi := NEW.department;
                        END IF;

                        IF NEW.jabatan IS DISTINCT FROM OLD.jabatan THEN
                            NEW.position := NEW.jabatan;
                        ELSIF NEW.position IS DISTINCT FROM OLD.position THEN
                            NEW.jabatan := NEW.position;
                        END IF;
                    END IF;
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql;
            ");

            DB::statement('DROP TRIGGER IF EXISTS trg_sync_employee_m12_m14 ON employees');
            DB::statement('
                CREATE TRIGGER trg_sync_employee_m12_m14
                BEFORE INSERT OR UPDATE ON employees
                FOR EACH ROW
                EXECUTE FUNCTION sync_employee_m12_m14_columns()
            ');
        }

        // 2. Tabel salary_components (Komponen Gaji Utama - AC1 M14)
        if (! Schema::hasTable('salary_components')) {
            Schema::create('salary_components', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')
                    ->unique()
                    ->constrained('employees')
                    ->cascadeOnDelete();
                $table->bigInteger('basic_salary');
                $table->bigInteger('fixed_allowance')->default(0);
                $table->bigInteger('deduction')->default(0);
                $table->string('effective_from', 50);
                $table->string('updated_at', 50)->default(DB::raw('CURRENT_TIMESTAMP'));
            });
        }

        // 3. Tabel salary_items (Rincian Tunjangan & Potongan - AC4 M14)
        if (! Schema::hasTable('salary_items')) {
            Schema::create('salary_items', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->foreignId('salary_component_id')
                    ->constrained('salary_components')
                    ->cascadeOnDelete();
                $table->string('type', 50); // "allowance" atau "deduction"
                $table->string('name', 255);
                $table->bigInteger('amount');
            });
        }

        // 4. Tabel salary_history (Audit Log Riwayat Perubahan Gaji - M14)
        if (! Schema::hasTable('salary_history')) {
            Schema::create('salary_history', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->foreignId('employee_id')
                    ->constrained('employees')
                    ->cascadeOnDelete();
                $table->text('old_value')->nullable();
                $table->text('new_value');
                $table->string('changed_by', 255);
                $table->string('changed_at', 50)->default(DB::raw('CURRENT_TIMESTAMP'));
            });
        }

        // 5. Tabel users (Tambahkan kolom role RBAC untuk M14)
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->default('Employee');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN password DROP NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS trg_sync_employee_m12_m14 ON employees');
            DB::statement('DROP FUNCTION IF EXISTS sync_employee_m12_m14_columns()');
        }

        Schema::dropIfExists('salary_history');
        Schema::dropIfExists('salary_items');
        Schema::dropIfExists('salary_components');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            $cols = array_filter(['name', 'department', 'position'], fn ($c) => Schema::hasColumn('employees', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
