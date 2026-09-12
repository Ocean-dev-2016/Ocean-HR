<?php

namespace App\Exports;

use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalaryBankTransferExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithEvents, WithCustomValueBinder
{
    protected $request;
    protected $authUser;
    protected $modules;

    public function __construct($request, $authUser, $modules)
    {
        $this->request = $request;
        $this->authUser = $authUser;
        $this->modules = $modules;
    }

    public function collection()
    {
        $modules = $this->modules;
        $loginUserId = ($this->authUser && $this->authUser->id) ? $this->authUser->id : null;
        $request = (object) $this->request;

        $data = Salary::with(['employee.employmentDetail', 'company'])
            ->whereHas('employee', function ($q) {
                $q->where('status', 'active');
            })
            ->where(function ($query) use ($modules, $loginUserId) {
                if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                    $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                    $query->where('company_id', $companyId);

                    if (!empty($modules['personal_data_permission']) && empty($modules['all_data_permission'])) {
                        $query->where('created_by', $loginUserId);
                    }
                }
            });

        if (isset($modules['restore_permission']) && $modules['restore_permission']) {
            $data = $data->withTrashed();
        }

        if (isset($request->filter_company) && $request->filter_company) {
            $data->where('company_id', $request->filter_company);
        }
        if (isset($request->filter_branch) && $request->filter_branch) {
            $data->where('branch_id', $request->filter_branch);
        }
        if (isset($request->filter_department) && $request->filter_department) {
            $data->where('department_id', $request->filter_department);
        }
        if (isset($request->filter_employee) && $request->filter_employee) {
            $data->where('employee_id', $request->filter_employee);
        }
        if (isset($request->filter_year) && $request->filter_year) {
            $data->where('year', $request->filter_year);
        }
        if (isset($request->filter_month) && $request->filter_month) {
            $data->where('month', $request->filter_month);
        }
        if (isset($request->status) && $request->status !== null && $request->status !== 'all') {
            $data->where('status', $request->status);
        }

        return $data->orderBy('employee_id', 'ASC')->orderBy('id', 'ASC')->get();
    }

    public function headings(): array
    {
        return [
            'PYMT_PROD_TYPE_CODE',
            'PYMT_MODE',
            'DEBIT_ACC_NO',
            'BNF_NAME',
            'BENE_ACC_NO',
            'BENE_IFSC',
            'AMOUNT',
            'CREDIT_NARR',
            'PYMT_DATE',
            'MOBILE_NUM',
            'EMAIL_ID',
            'REMARK',
            'REF_NO',
        ];
    }

    public function map($row): array
    {
        $month = (int) ($row->month ?? 0);
        $year = (int) ($row->year ?? 0);
        $monthName = $month > 0 ? strtoupper(Carbon::createFromDate(2000, $month, 1)->format('M')) : '';
        $creditNarr = trim('SALARY ' . $monthName . substr((string) $year, -2));

        return [
            'PAB_VENDOR',
            $this->resolvePaymentMode($row),
            $this->resolveDebitAccountNo($row),
            $row->employee?->full_name ?? '-',
            $this->resolveBeneficiaryAccountNo($row),
            $this->resolveBeneficiaryIfscCode($row),
            (float) ($row->net_bank_pay ?? 0),
            $creditNarr,
            Carbon::now()->format('d-m-Y'),
            $row->employee?->contact_number ?? $row->employee?->other_number ?? '',
            $row->employee?->email ?? '',
            '',
            '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = 'O';

                $sheet->getDefaultRowDimension()->setRowHeight(-1);

                $sheet->getColumnDimension('A')->setWidth(27.5703125);
                $sheet->getColumnDimension('B')->setWidth(26.7109375);
                $sheet->getColumnDimension('C')->setWidth(26.7109375);
                $sheet->getColumnDimension('D')->setWidth(43.5703125);
                $sheet->getColumnDimension('E')->setWidth(26.7109375);
                $sheet->getColumnDimension('F')->setWidth(26.7109375);
                $sheet->getColumnDimension('G')->setWidth(26.7109375);
                $sheet->getColumnDimension('H')->setWidth(26.7109375);
                $sheet->getColumnDimension('I')->setWidth(26.7109375);
                $sheet->getColumnDimension('J')->setWidth(26.7109375);
                $sheet->getColumnDimension('K')->setWidth(26.7109375);
                $sheet->getColumnDimension('L')->setWidth(26.7109375);
                $sheet->getColumnDimension('M')->setWidth(26.7109375);
                $sheet->getColumnDimension('N')->setWidth(26.7109375);
                $sheet->getColumnDimension('O')->setWidth(11.5703125);

                $sheet->getRowDimension(1)->setRowHeight(102.75);

                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'font' => [
                        'name' => 'Mulish SemiBold',
                        'size' => 11,
                    ],
                ]);

                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_NONE,
                    ],
                    'font' => [
                        'name' => 'Mulish SemiBold',
                        'size' => 11,
                        'bold' => true,
                    ],
                    'numberFormat' => [
                        'formatCode' => NumberFormat::FORMAT_GENERAL,
                    ],
                ]);

                if ($lastRow >= 2) {
                    $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_BOTTOM,
                            'wrapText' => false,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_NONE,
                        ],
                        'font' => [
                            'name' => 'Mulish SemiBold',
                            'size' => 11,
                        ],
                    ]);

                    foreach (['A', 'B', 'C', 'D', 'E', 'F', 'H', 'I', 'J', 'K', 'L', 'M'] as $col) {
                        $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_TEXT);
                    }

                    $sheet->getStyle("G2:G{$lastRow}")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_GENERAL);
                }
            },
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), ['C', 'E', 'F', 'H', 'I', 'J', 'K', 'L', 'M', 'N'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    private function resolveDebitAccountNo($row): string
    {
        return '239451000033';
    }

    private function resolvePaymentMode($row): string
    {
        $value = trim((string) ($row->employee?->employmentDetail?->payment_mode ?? ''));
        return $value !== '' ? $value : '';
    }

    private function resolveBeneficiaryAccountNo($row): string
    {
        $value = trim((string) ($row->employee?->bank_account_number ?? ''));
        return $value !== '' ? $value : '';
    }

    private function resolveBeneficiaryIfscCode($row): string
    {
        $value = trim((string) ($row->employee?->ifsc_code ?? ''));
        return $value !== '' ? $value : '';
    }
}
