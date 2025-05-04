<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckRolesTable extends Command
{
    protected $signature = 'check:roles-table';
    protected $description = 'Check if roles table exists and show its data';

    public function handle()
    {
        // Check if table exists
        if (!Schema::hasTable('roles')) {
            $this->error('Roles table does not exist!');
            return;
        }

        $this->info('Roles table exists. Checking structure...');
        
        // Get table structure
        $columns = Schema::getColumnListing('roles');
        $this->line("\nTable columns:");
        foreach ($columns as $column) {
            $this->line("- {$column}");
        }

        // Get table data
        $roles = DB::table('roles')->get();
        
        if ($roles->isEmpty()) {
            $this->warn("\nNo roles found in the table!");
            return;
        }

        $this->info("\nRoles in database:");
        foreach ($roles as $role) {
            $this->line("- ID: {$role->id}, Name: {$role->name}, Guard: {$role->guard_name}");
        }
    }
}
