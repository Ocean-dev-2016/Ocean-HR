<?php

namespace Database\Seeders;

use App\Helpers\Helper;
use App\Http\Controllers\software\CompanyController;
use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanyDetails;
use App\Models\CompanySubscriptionPlan;
use App\Models\Designation;
use App\Models\DocumentType;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\MainMenu;
use App\Models\MasterCity;
use App\Models\MasterCountry;
use App\Models\MasterState;
use App\Models\PlanMaster;
use App\Models\RolePermission;
use App\Models\SubMenu;
use App\Models\TeamRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (MasterCountry::count()) {
            MasterCountry::truncate();
        }
        $country = MasterCountry::create(['name' => 'India', 'short_name' => 'IND', 'code' => '91']);

        $this->command->info("MasterCountry Done.");

        if (MasterState::count()) {
            MasterState::truncate();
        }
        $state = MasterState::create(
            [
                'country_id' => $country?->id, // This will be set dynamically from the command
                'name' => "Gujarat", // Generates a unique state name
                'status' => 'active', // Random status
                'created_by' => 1, // Static creator ID
                'updated_by' => 1, // Static updater ID
                'deleted_by' => null, // Static deleted_by field
            ]
        );
        $this->command->info("MasterState Done.");

        if (MasterCity::count()) {
            MasterCity::truncate();
        }
        $city = MasterCity::create([
            'country_id' => $country?->id, // Will be set dynamically in the seeder/command
            'state_id' => $state->id,   // Will be set dynamically in the seeder/command
            'name' => "Rajkot",
            'status' => 'active',
            'created_by' => 1,
            'updated_by' => 1,
            'deleted_by' => null,
        ]);
        $this->command->info("MasterCity Done.");

        if (Company::count()) {
            Company::truncate();
        }
        $planMaster = PlanMaster::firstWhere('status', 'active');

        $companyData = [
            'gst_no' => null,
            'company_name' => "Ocean Infotech",
            'person_name' => "Sandipbhai",
            'whatsapp_number' => "8000853781",
            'email' => 'info@oceaninfotech.co.in',
            'password' => Hash::make("Ocean@2025"),
            'sp' => "Ocean@2025",
            'otp' => "2025",
            'pan_card' => null,
            'address' => "Rajkot",
            'country_id' => $country?->id ?? 1,
            'state_id' => $state?->id ?? 1,
            'city_id' => $city?->id ?? 1,
            'plan_id' => $planMaster->id,
            'app_right' => implode(",", $planMaster->app_right),
            'panel_right' => implode(",", $planMaster->panel_right),
            'plan_from' => \Carbon\Carbon::now(),
            'plan_to' => \Carbon\Carbon::now(),
            'app_key' => "Ocean@2025",
            'panel_url' => route('software.login'),
            'database_name' => null,
            'database_user' => null,
            'database_password' => null,
            'max_employee_user_count' => $planMaster->id,
            'mobile_min' => 10,
            'mobile_max' => 10,
            'branch_type' => 'single',
            'is_copyright_view' => 'yes',
            'default_password' => "Default@2025",
            'reset_password' => "Reset@2025",
            'phonecode' => $country->code,
            'status' => 'active'
        ];

        if (Company::count()) {
            Company::truncate();
        }
        $company = Company::create($companyData);
        $this->command->info("Company created");

        $company_id = $company->id;
        $company = Company::findOrFail($company_id);

        $team_role = TeamRole::create([
            'company_id' => $company->id,
            'parent_id' => '0',
            'name' => $company->company_name,
            'created_type' => 'admin_software',
            'created_by' => $company->created_by,
            // 'updated_by' => $company->created_by,
        ]);
        $this->command->info("TeamRole Done.");

        if (CompanyDetails::count()) {
            CompanyDetails::truncate();
        }
        CompanyDetails::create([
            'company_id' => $company?->id ?? '1',
        ]);
        $this->command->info("CompanyDetails Done.");

        if (CompanySubscriptionPlan::count()) {
            CompanySubscriptionPlan::truncate();
        }
        CompanySubscriptionPlan::create([
            'company_id' => $company?->id ?? '1',
            'plan_id' => $planMaster?->id ?? '1',
            'plan_from' => \Carbon\Carbon::now(),
            'plan_to' => \Carbon\Carbon::now()->addDay($planMaster?->plan_valid_day),
            'extra_detail' => json_encode($planMaster),
            'plan_expiry_date' => \Carbon\Carbon::now()->addDay($planMaster?->plan_valid_day),
            'subscription_status' => 'active',
        ]);
        $this->command->info("CompanySubscriptionPlan Done.");

        if (RolePermission::count()) {
            RolePermission::truncate();
        }
        $mainMenus = MainMenu::where('status', 'active')->get();

        foreach ($mainMenus as $mainMenu) {
            $subMenus = SubMenu::where('main_menu_id', $mainMenu->id)
                ->where('status', 'active')
                ->get();

            foreach ($subMenus as $subMenu) {
                if (in_array((string) $subMenu->id, $planMaster->panel_right)) {  // Match by submenu ID
                    RolePermission::create([
                        'company_id' => $company?->id,
                        'team_role_id' => $team_role->id,
                        'main_menu_id' => $mainMenu->id,
                        'sub_menu_id' => $subMenu->id,
                        'view_flag' => 1,
                        'add_flag' => 1,
                        'update_flag' => 1,
                        'delete_flag' => 1,
                        'restore_flag' => 1,
                        'print_flag' => 1,
                        'excel_flag' => 1,
                        'approval_flag' => 1,
                        'all_data_flag' => 1,
                        'status' => 'active',
                        'created_by' => $company->created_by,
                    ]);
                }
            }
        }

        if (Designation::count()) {
            Designation::truncate();
        }
        // Designation
        $defaultDesignations = ['Main HR', 'Department Head', 'Supervisor', 'Employee'];
        foreach ($defaultDesignations as $designationName) {
            Designation::create([
                'company_id' => $company_id,
                'name' => $designationName,
                'status' => 'active',
                'created_by' => $company->created_by,
            ]);
        }
        $this->command->info("Designations Done.");

        if (DocumentType::count()) {
            DocumentType::truncate();
        }
        // Document Type
        DocumentType::create([
            'company_id' => $company_id,
            'name' => 'General Documents',
            'status' => 'active',
            'created_by' => $company->created_by,
        ]);
        $this->command->info("DocumentType Done.");

        // Start => Branch
        if (Branch::count()) {
            Branch::truncate();
        }
        Branch::create([
            'company_id' => $company_id,
            'name' => 'Rajkot',
            'prefix' => 'RJK',
            'canteen_max_time' => "12:00:00",
            'canteen_min_time' => "13:30:00",
            'branch_address' => "Rajkot",
            'status' => 'active',
            'created_by' => $company->created_by,
        ]);
        $this->command->info("Branch Done.");
        // End => Branch


        if (LeaveType::count()) {
            LeaveType::truncate();
        }
        // Leave Type
        LeaveType::create([
            'company_id' => $company_id,
            'sort_name' => 'SL',
            'full_name' => "Sick Leave",
            'count' => 6,
            'mode' => 0,
            'status' => 'active',
            'created_by' => $company->created_by,
        ]);
        LeaveType::create([
            'company_id' => $company_id,
            'sort_name' => 'LWP',
            'full_name' => "Leave Without Pay",
            'count' => 24,
            'mode' => 1,
            'status' => 'active',
            'created_by' => $company->created_by,
        ]);
        $this->command->info("Leave Done.");

        // Insert team person
        $defulatEmployeeCreate = [
            'company_id' => $company_id,
            'branch_id' => 0,
            'parent_id' => 0,

            'employee_code' => "EMP-001",

            'first_name' => $company->person_name,
            'full_name' => $company->person_name,

            'username' => $company->whatsapp_number,
            'password' => Hash::make("Ocean@2025"),
            'sp' => Helper::generateSP($company->sp),
            'role_id' => $team_role->id,

            'country_id' => $company->country_id,
            'state_id' => $company->state_id,
            'city_id' => $company->city_id,
            'email' => $company->email,
            'contact_number' => $company->whatsapp_number,
            'other_number' => $company->whatsapp_number,
            // 'created_by' => $validated['created_by'],
            // 'updated_by' => $validated['created_by'],
        ];
        // dd($defulatEmployeeCreate);
        $teamPerson = Employee::create($defulatEmployeeCreate);
        $this->command->info("Employee created");

        $this->command->info("Company Seeder Done");
        // dd($planMaster->toArray(), implode(",", $planMaster->panel_right), $companyData);
    }
}
