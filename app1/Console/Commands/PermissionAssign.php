<?php

namespace App\Console\Commands;

use App\Enums\UserTypes;
use App\Models\Permission;
use Exception;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PermissionAssign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:assign';
    protected $module = 'Console:Permission:Assignment';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To assign new permissions to super admin user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {

            $superAdminFormattedCaseKey = UserTypes::getFormattedCaseKey(UserTypes::SUPER_ADMIN->value);

            $superAdminUser = User::role($superAdminFormattedCaseKey)->first();
            $superAdminRole = Role::where('name', $superAdminFormattedCaseKey)->first();
            $permissions = Permission::get();

            if (!empty($superAdminRole)) {
                $superAdminRole->givePermissionTo($permissions);
            }

            if (!empty($superAdminUser)) {
                $superAdminUser->givePermissionTo($permissions);
            }

            Log::info("Super admin permission updated");
            $this->info("Super admin permission updated");
        } catch (Exception $e) {
            Log::error("Module : " . $this->module . " Error msg - " . $e->getMessage());
            $this->error("Module : " . $this->module . ' Error msg - ' . $e->getMessage());
        }
    }
}
