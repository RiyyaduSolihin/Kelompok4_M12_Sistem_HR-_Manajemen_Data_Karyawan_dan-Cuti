<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DatabaseSchemaGuard
{
    private static bool $checkedInProcess = false;

    /**
     * Jalankan operasi database dengan proteksi self-healing otomatis.
     * Jika terjadi error SQLSTATE[42703] (Undefined column) atau SQLSTATE[23502] (Not null violation)
     * akibat modul lain mengubah struktur tabel di database bersama, otomatis perbaiki skema lalu ulangi query.
     */
    public static function run(callable $callback)
    {
        try {
            return $callback();
        } catch (QueryException $e) {
            $code = (string) $e->getCode();
            $msg = $e->getMessage();

            if (
                in_array($code, ['42703', '23502'], true) ||
                str_contains($msg, 'Undefined column') ||
                str_contains($msg, 'does not exist') ||
                str_contains($msg, 'null value in column')
            ) {
                self::ensureSchema(true);
                return $callback();
            }

            throw $e;
        }
    }

    /**
     * Memastikan skema tabel `employees` dan `users` di database bersama (Neon PostgreSQL)
     * memiliki seluruh kolom yang dibutuhkan oleh M12, M13, dan M14, meskipun modul lain
     * sempat me-reset / membuat ulang tabel dengan kolom versi mereka saja.
     */
    public static function ensureSchema(bool $force = false): void
    {
        if (self::$checkedInProcess && ! $force) {
            return;
        }

        self::$checkedInProcess = true;

        try {
            if (DB::getDriverName() === 'pgsql') {
                self::ensurePostgresSchema();
            } else {
                self::ensureGenericSchema();
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    private static function ensurePostgresSchema(): void
    {
        // 1. Pastikan seluruh kolom M12, M13 & M14 ada pada tabel employees
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS nama VARCHAR(100)");
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS jabatan VARCHAR(50)");
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS divisi VARCHAR(50)");
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS status_kerja VARCHAR(20) DEFAULT 'aktif'");
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS tanggal_berhenti DATE NULL");
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS name VARCHAR(100)");
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS position VARCHAR(50)");
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS department VARCHAR(50)");
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS status VARCHAR(30) DEFAULT 'aktif'");
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
        DB::statement("ALTER TABLE employees ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");

        // Lepas constraint NOT NULL pada semua kolom opsional lintas modul (kecuali id dan nik)
        DB::statement("
            DO \$\$
            DECLARE
                col RECORD;
            BEGIN
                FOR col IN
                    SELECT column_name
                    FROM information_schema.columns
                    WHERE table_schema = 'public'
                      AND table_name = 'employees'
                      AND is_nullable = 'NO'
                      AND column_name NOT IN ('id', 'nik')
                LOOP
                    EXECUTE format('ALTER TABLE employees ALTER COLUMN %I DROP NOT NULL', col.column_name);
                END LOOP;
            END \$\$;
        ");

        // Sinkronkan data yang sudah ada (antara kolom Indonesia M12 & kolom Inggris M13/M14)
        DB::statement("
            UPDATE employees
            SET nama = COALESCE(nama, name, ''),
                name = COALESCE(name, nama, ''),
                jabatan = COALESCE(jabatan, position, ''),
                position = COALESCE(position, jabatan, ''),
                divisi = COALESCE(divisi, department, ''),
                department = COALESCE(department, divisi, ''),
                status_kerja = COALESCE(
                    status_kerja,
                    CASE WHEN LOWER(COALESCE(status, '')) IN ('inactive', 'nonaktif', 'non-aktif') THEN 'nonaktif' ELSE 'aktif' END,
                    'aktif'
                ),
                status = COALESCE(status, status_kerja, 'aktif')
            WHERE nama IS NULL
               OR name IS NULL
               OR jabatan IS NULL
               OR position IS NULL
               OR divisi IS NULL
               OR department IS NULL
               OR status_kerja IS NULL
               OR status IS NULL
        ");

        // Pasang trigger sinkronisasi 2 arah M12 <-> M13/M14
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
                    NEW.status_kerja := COALESCE(
                        NEW.status_kerja,
                        CASE WHEN LOWER(COALESCE(NEW.status, '')) IN ('inactive', 'nonaktif', 'non-aktif') THEN 'nonaktif' ELSE 'aktif' END,
                        'aktif'
                    );
                    NEW.status := COALESCE(NEW.status, NEW.status_kerja, 'aktif');
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

                    IF NEW.status_kerja IS DISTINCT FROM OLD.status_kerja THEN
                        NEW.status := NEW.status_kerja;
                    ELSIF NEW.status IS DISTINCT FROM OLD.status THEN
                        NEW.status_kerja := CASE WHEN LOWER(COALESCE(NEW.status, '')) IN ('inactive', 'nonaktif', 'non-aktif') THEN 'nonaktif' ELSE 'aktif' END;
                    END IF;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::statement("DROP TRIGGER IF EXISTS trg_sync_employee_m12_m14 ON employees");
        DB::statement("
            CREATE TRIGGER trg_sync_employee_m12_m14
            BEFORE INSERT OR UPDATE ON employees
            FOR EACH ROW
            EXECUTE FUNCTION sync_employee_m12_m14_columns()
        ");

        // 2. Pastikan seluruh kolom Auth M12/M13/M14 ada pada tabel users
        DB::statement("ALTER TABLE users ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL");
        DB::statement("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'Employee'");
        DB::statement("ALTER TABLE users ADD COLUMN IF NOT EXISTS employee_id BIGINT NULL");
        DB::statement("ALTER TABLE users ADD COLUMN IF NOT EXISTS api_token VARCHAR(80) NULL");
        DB::statement("ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_token VARCHAR(100) NULL");
        DB::statement("ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
        DB::statement("ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");

        DB::statement("
            DO \$\$
            DECLARE
                col RECORD;
            BEGIN
                FOR col IN
                    SELECT column_name
                    FROM information_schema.columns
                    WHERE table_schema = 'public'
                      AND table_name = 'users'
                      AND is_nullable = 'NO'
                      AND column_name NOT IN ('id', 'email')
                LOOP
                    EXECUTE format('ALTER TABLE users ALTER COLUMN %I DROP NOT NULL', col.column_name);
                END LOOP;
            END \$\$;
        ");

        self::ensureDefaultDemoAccounts();
    }

    private static function ensureGenericSchema(): void
    {
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function ($table) {
                if (! Schema::hasColumn('employees', 'nama')) {
                    $table->string('nama', 100)->nullable();
                }
                if (! Schema::hasColumn('employees', 'jabatan')) {
                    $table->string('jabatan', 50)->nullable();
                }
                if (! Schema::hasColumn('employees', 'divisi')) {
                    $table->string('divisi', 50)->nullable();
                }
                if (! Schema::hasColumn('employees', 'status_kerja')) {
                    $table->string('status_kerja', 20)->default('aktif');
                }
                if (! Schema::hasColumn('employees', 'tanggal_berhenti')) {
                    $table->date('tanggal_berhenti')->nullable();
                }
                if (! Schema::hasColumn('employees', 'name')) {
                    $table->string('name', 100)->nullable();
                }
                if (! Schema::hasColumn('employees', 'position')) {
                    $table->string('position', 50)->nullable();
                }
                if (! Schema::hasColumn('employees', 'department')) {
                    $table->string('department', 50)->nullable();
                }
            });
        }
    }

    private static function ensureDefaultDemoAccounts(): void
    {
        $defaultPasswordHash = Hash::make('password123');
        $firstEmp = DB::table('employees')->orderBy('id', 'asc')->first();
        $secondEmp = DB::table('employees')->where('status_kerja', 'aktif')->orderBy('id', 'asc')->skip(1)->first() ?? $firstEmp;

        // Pastikan akun HR memiliki password dan employee_id
        $hrUser = DB::table('users')->where('email', 'hr@araya.com')->first();
        if (! $hrUser) {
            DB::table('users')->insert([
                'name' => $firstEmp->nama ?? $firstEmp->name ?? 'Rani K. (Admin HR)',
                'email' => 'hr@araya.com',
                'password' => $defaultPasswordHash,
                'role' => 'Admin HR',
                'employee_id' => $firstEmp->id ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (empty($hrUser->password) || empty($hrUser->employee_id)) {
            DB::table('users')->where('id', $hrUser->id)->update([
                'password' => $hrUser->password ?: $defaultPasswordHash,
                'role' => 'Admin HR',
                'employee_id' => $hrUser->employee_id ?: ($firstEmp->id ?? null),
            ]);
        }

        // Pastikan akun Employee demo (andi@araya.com) memiliki password dan employee_id
        $empUser = DB::table('users')->where('email', 'andi@araya.com')->first();
        if (! $empUser) {
            DB::table('users')->insert([
                'name' => $secondEmp->nama ?? $secondEmp->name ?? 'Andi Pratama',
                'email' => 'andi@araya.com',
                'password' => $defaultPasswordHash,
                'role' => 'Employee',
                'employee_id' => $secondEmp->id ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (empty($empUser->password) || empty($empUser->employee_id)) {
            DB::table('users')->where('id', $empUser->id)->update([
                'password' => $empUser->password ?: $defaultPasswordHash,
                'role' => 'Employee',
                'employee_id' => $empUser->employee_id ?: ($secondEmp->id ?? null),
            ]);
        }
    }
}
