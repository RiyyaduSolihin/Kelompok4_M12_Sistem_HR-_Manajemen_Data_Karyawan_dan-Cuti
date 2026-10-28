<?php

use App\Support\DatabaseSchemaGuard;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:sync-m12-schema', function () {
    DatabaseSchemaGuard::ensureSchema(true);
    $cols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'employees' ORDER BY ordinal_position");
    $this->info('Skema employees berhasil disinkronkan: ' . implode(', ', array_map(fn ($c) => $c->column_name, $cols)));
})->purpose('Sinkronisasi kolom M12, M13, dan M14 pada tabel employees dan users');
