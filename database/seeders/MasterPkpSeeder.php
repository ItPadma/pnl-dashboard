<?php

namespace Database\Seeders;

use App\Imports\MasterPkpImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MasterPkpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Excel::import(new MasterPkpImport, database_path('seeders/MasterPKP.xlsx'));
    }
}
