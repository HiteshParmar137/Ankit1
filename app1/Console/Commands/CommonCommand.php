<?php

namespace App\Console\Commands;

use App\Enums\UserTypes;
use App\Models\Permission;
use App\Models\Role;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common:command';

    /**
     * The name and module.
     *
     * @var string
     */
    protected $module = 'Common Script';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for run the general scripts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {           
            // $hrAdminRole = UserTypes::getFormattedCaseKey(UserTypes::HR_ADMIN->value);
 
            // $hrAdmin = Role::whereName($hrAdminRole)->first();

            // DB::beginTransaction();

            // $displayName = $hrAdmin->display_name;

            // $hrAdmin->update([
            //     'display_name' => 'HR Admin',
            //     'description' => 'Hr admin role'
            // ]);

            // DB::commit();

            // Log::info("Module: " . $this->module . ", " . "Display name changed from " . $displayName . " -> " . $hrAdmin->display_name);
            // $this->info("Module: " . $this->module . ", " . "Display name changed from " . $displayName . " -> " . $hrAdmin->display_name);


            /**
             * Date: 1st June
             * Use: For remove the work-log permissions from the DB
             */

            $worklogPermissions = [
                "worklog-list",
                "worklog-details",
                "worklog-add",
                "worklog-update",
                "worklog-delete"
            ];
            
            DB::beginTransaction();

            $WorklogPermissionIds = Permission::whereIn('name', $worklogPermissions)->pluck('id');
            
            Permission::whereIn('id', $WorklogPermissionIds)->delete();

            DB::commit();

            Log::info("Module: " . $this->module . ", Total removed permissions are " . $WorklogPermissionIds->count() .  " and permission ids are: " . $WorklogPermissionIds->implode(', '));
            $this->info("Module: " . $this->module . ", Total removed permissions are " . $WorklogPermissionIds->count() .  " and permission ids are: " . $WorklogPermissionIds->implode(', '));

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Module : " . $this->module . " Error msg - " . $e->getMessage());
            $this->error("Module : " . $this->module . ' Error msg - ' . $e->getMessage());
        }
    }
}
