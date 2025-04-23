<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UMISService;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ImportUsersFromUMIS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users-from-umis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from UMIS';

    /**
     * The UMIS service instance.
     *
     * @var \App\Services\UMISService
     */
    protected $umisService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\UMISService $umisService
     * @return void
     */
    public function __construct(UMISService $umisService)
    {
        parent::__construct();
        $this->umisService = $umisService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting import of users from UMIS...');

        $response = $this->umisService->getUsers();

        if (!$response) {
            $this->error('Failed to fetch users from UMIS');
            return Command::FAILURE;
        }

        // extract data from the response
        $usersData = isset($response['data']) ? $response['data'] : null;
        
        if (!$usersData || !is_array($usersData)) {
            $this->error('Invalid data format received from UMIS.');
            return Command::FAILURE;
        }

        $this->info('Received ' . count($usersData) . ' users from UMIS.');

        try {
            // Start a transaction
            DB::beginTransaction();

            // Clear existing users (if needed based on sync strategy)
            // Using delete() instead of truncate() to avoid implicit commits
            User::query()->delete();

            $successCount = 0;
            $errorCount = 0;

            // Populate users from UMIS
            foreach ($usersData as $user) {
                try {
                    User::updateOrCreate(
                        ['umis_employee_profile_id' => $user['employee_profile_id']],
                        [
                            'name' => trim($user['name']),
                            'email' => $user['email'],
                            'profile_url' => isset($user['profile_url']) ? $user['profile_url'] : null,
                            'is_active' => true,
                        ]
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Error processing user " . (isset($user['employee_profile_id']) ? $user['employee_profile_id'] : 'unknown') . ": " . $e->getMessage());
                    $errorCount++;
                }
            }

            DB::commit();

            $this->info("Import completed. Processed users: $successCount success, $errorCount errors.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to import users from UMIS: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
