<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ImportSeeder extends Seeder
{
    public function run(bool $sync = false, string $filename = 'import.csv'): void
    {
        $command = 'app:import-users';
        
        if ($sync) {
            $command .= ' --sync';
        }
        
        if ($filename !== 'import.csv') {
            $command .= ' --file=' . $filename;
        }
        
        Artisan::call($command);
    }
} 