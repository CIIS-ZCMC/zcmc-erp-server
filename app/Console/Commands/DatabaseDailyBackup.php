<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Summary of BackupDatabase
 * 
 * Done mock test, status PASEED.
 */
class DatabaseDailyBackup extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Backup the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $backupDirectory = storage_path('app/backups');

        // Ensure the backups directory exists
        if (!is_dir($backupDirectory)) {
            mkdir($backupDirectory, 0755, true);
            $this->info('Created directory: ' . $backupDirectory);
        }
        
        $filename = 'erp_db_' . date('m-d-Y') . '.sql';
        $path = $backupDirectory . '/' . $filename;
        
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg(env('DB_USERNAME')),
            escapeshellarg(env('DB_PASSWORD')),
            escapeshellarg(env('DB_HOST')),
            escapeshellarg(env('DB_DATABASE')),
            escapeshellarg($path)
        );

        $result = null;
        $output = null;
        
        exec($command, $output, $result);

        if ($result === 0) {
            $this->info('Database backup created successfully: ' . $filename);
        } else {
            $this->error('Failed to create database backup. Check your configurations and permissions.');
        }
    }
}
