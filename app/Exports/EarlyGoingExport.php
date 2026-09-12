<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EarlyGoingExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $modules;

    public function __construct($data, $modules)
    {
        $this->data = $data;
        $this->modules = $modules;
    }

    public function view(): View
    {
        return view($this->modules['folder_path'] . '.print', [
            'data' => $this->data,
            'is_excel' => true
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
