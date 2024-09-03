<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class CustomMigrateFresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:fresh:custom {--tables-exclude=* : Tables to exclude from the fresh migration} {--seed : Seed the database after migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the migrate:fresh command while excluding specific tables and optionally seed the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the tables to exclude
        $tablesToExclude = $this->option('tables-exclude');

        // Get all table names
        $allTables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        // Filter tables to exclude
        $tablesToDrop = array_diff($allTables, $tablesToExclude);

        // Drop all tables except the excluded ones
        Schema::disableForeignKeyConstraints();
        foreach ($tablesToDrop as $table) {
            Schema::drop($table);
            $this->info("Table {$table} dropped.");
        }
        Schema::enableForeignKeyConstraints();

        // Run the migrations
        Artisan::call('migrate');
        $this->info('Migrations have been refreshed while excluding specific tables.');

        // Check if the --seed option is present
        if ($this->option('seed')) {
            Artisan::call('db:seed');
            $this->info('Database has been seeded.');
        }

        return Command::SUCCESS;
    }
}
