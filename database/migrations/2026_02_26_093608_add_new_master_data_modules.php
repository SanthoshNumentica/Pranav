<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;
use App\Models\Action;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $modules = [
            'referer-type',
            'discounts',
            'payment-method'
        ];

        $actions = Action::all();

        foreach ($modules as $moduleName) {
            $module = Module::firstOrCreate(['name' => $moduleName]);

            foreach ($actions as $action) {
                DB::table('permissions')->updateOrInsert([
                    'module_id' => $module->id,
                    'action_id' => $action->id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $modules = [
            'referer-type',
            'discounts',
            'payment-method'
        ];

        $moduleIds = Module::whereIn('name', $modules)->pluck('id');

        Permission::whereIn('module_id', $moduleIds)->delete();
        Module::whereIn('name', $modules)->delete();
    }
};
