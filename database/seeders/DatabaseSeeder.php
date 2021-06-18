<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        Department::insert(json_decode(file_get_contents(database_path('seeders/DepartmentsSeed.json')), true)['seed']);
    }
}
