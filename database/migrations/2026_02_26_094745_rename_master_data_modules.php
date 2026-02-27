<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Module::where('name', 'referer-type')->update(['name' => 'referer-types']);
        Module::where('name', 'payment-method')->update(['name' => 'payment-methods']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Module::where('name', 'referer-types')->update(['name' => 'referer-type']);
        Module::where('name', 'payment-methods')->update(['name' => 'payment-method']);
    }
};
