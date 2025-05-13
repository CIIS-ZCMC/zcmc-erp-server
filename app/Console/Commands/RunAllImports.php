<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * RunAllImports Command
 * 
 * This command executes all UMIS import commands sequentially.
 * It serves as a convenience wrapper to run all data synchronization
 * operations in the correct order.
 */
class RunAllImports extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'import:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all import-related commands';

    /**
     * Execute the console command.
     * 
     * Runs all import commands in the proper sequence:
     * 1. Areas (divisions, departments, sections, units)
     * 2. Designations (job titles)
     * 3. Users (employees)
     * 4. Assigned Areas (user-area relationships)
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting all UMIS imports...');
        
        $this->call('import:users-from-umis');
        $this->call('import:areas-from-umis');
        $this->call('import:designations-from-umis');
        $this->call('import:assigned-areas-from-umis');
        
        $this->info('All imports completed successfully.');
        
        return Command::SUCCESS;
    }
}
