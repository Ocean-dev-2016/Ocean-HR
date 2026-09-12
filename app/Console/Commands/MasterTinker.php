<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DocumentType;
use App\Models\DocumentList;
use App\Models\Designation;
use App\Models\Company;


class MasterTinker extends Command
{
    // The name and signature of the console command.
    protected $signature = 'tinker:masters';

    // The console command description.
    protected $description = 'Insert 5 new MasterUnit and MasterWarehouse records using factories';

    // Execute the console command.
    public function handle()
    {
        /*----------------- Document Type Details Tinker Call-------------------------*/
        DocumentType::truncate();
        DocumentType::factory()->count(5)->create();


        /*----------------- Document List Details Tinker Call-------------------------*/
        DocumentList::truncate();
        DocumentList::factory()->count(5)->create();


        /*----------------- Designation Details Tinker Call-------------------------*/
        Designation::truncate();
        Designation::factory()->count(5)->create();


        // Fetch all companies
        $companies = Company::all();


        $this->info("✅Masters Record Added Succuess Fully.");

        /*----------------- TeamRole Tinker Call -------------------------*/
        \App\Models\TeamRole::truncate(); // Clear existing data

        foreach ($companies as $company) {
            // Add default Super Admin role
            \App\Models\TeamRole::create([
                'company_id' => $company->id,
                'parent_id' => 0,
                'name' => 'Super Admin',
                'status' => 'active',
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
            ]);

            // Add one company-specific role
            \App\Models\TeamRole::create([
                'company_id' => $company->id,
                'parent_id' => 1, // Assuming '1' refers to Super Admin or use actual ID if needed
                'name' => $company->name . ' Role',
                'status' => 'active',
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
            ]);

            $this->info("✅ Created roles for Company ID: {$company->id} ({$company->name})");
        }
    }
}
