<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use App\Models\TeamAttendance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoPunchOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-punch-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto punch out team person if punchout is pending based on working_end_time time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        return $this->call('attendance:auto-punch-out');
    }
}
