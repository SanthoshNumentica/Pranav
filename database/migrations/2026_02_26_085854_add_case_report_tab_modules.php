<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;
use App\Models\Action;
use App\Models\Permission;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $modules = [
            'case-report-patient-details',
            'case-report-referer-details',
            'case-report-case-info',
            'case-report-invoice',
            'case-report-files'
        ];

        $actions = Action::whereIn('name', ['view', 'edit'])->get();

        foreach ($modules as $moduleName) {
            $module = Module::firstOrCreate(['name' => $moduleName]);

            foreach ($actions as $action) {
                \Illuminate\Support\Facades\DB::table('permissions')->updateOrInsert([
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
            'case-report-patient-details',
            'case-report-referer-details',
            'case-report-case-info',
            'case-report-invoice',
            'case-report-files'
        ];

        $moduleIds = Module::whereIn('name', $modules)->pluck('id');

        Permission::whereIn('module_id', $moduleIds)->delete();
        Module::whereIn('name', $modules)->delete();
    }
};
