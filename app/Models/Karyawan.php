<?php

namespace App\Models;

/**
 * Alias Model Karyawan yang identik 100% dengan Employee (Poin 7: Employee = Karyawan)
 */
class Karyawan extends Employee
{
    protected $table = 'employees';
}
