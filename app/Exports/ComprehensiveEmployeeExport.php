<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\EmployeesDetailsSheet;
use App\Exports\Sheets\EmployeeAssignAssetsSheet;
use App\Exports\Sheets\EmployeeIncrementDetailsSheet;
use App\Exports\Sheets\EmployeeEducationExperienceSheet;
use App\Exports\Sheets\EmployeeSalaryDetailsSheet;

class ComprehensiveEmployeeExport implements WithMultipleSheets
{
    protected $filter_params;
    protected $user;
    protected $modules;

    public function __construct($filter_params = null, $user = null, $modules = [])
    {
        $this->filter_params = $filter_params;
        $this->user = $user;
        $this->modules = $modules;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new EmployeesDetailsSheet($this->filter_params, $this->user, $this->modules);
        $sheets[] = new EmployeeAssignAssetsSheet($this->filter_params, $this->user, $this->modules);
        $sheets[] = new EmployeeIncrementDetailsSheet($this->filter_params, $this->user, $this->modules);
        $sheets[] = new EmployeeEducationExperienceSheet($this->filter_params, $this->user, $this->modules);
        $sheets[] = new EmployeeSalaryDetailsSheet($this->filter_params, $this->user, $this->modules);

        return $sheets;
    }
}
