<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use App\Mail\GenericMail;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

// Auth::routes();
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('optimize', function () {
    Artisan::call('optimize:clear');
    return Redirect::back()->with('success', 'Optimize the site, cleare all cache');
})->name('optimize');

Route::get('/test-firebase', function () {
    $service = new \App\Services\FirebaseService();
    return 'Firebase Service initialized!';
});

Route::get('/run-cron', function () {
    Log::info('run-cron on web request ' . now());
    try {
        // Run the scheduler
        Artisan::call('schedule:run');
        
        // Log successful execution
        Log::info('Schedule run completed successfully at ' . now());
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cron executed successfully',
            'timestamp' => now()->toDateTimeString()
        ]);
    } catch (\Exception $e) {
        Log::error('Schedule run failed: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Cron execution failed: ' . $e->getMessage(),
            'timestamp' => now()->toDateTimeString()
        ], 500);
    }
});

/** CRM Software / Master Routes */
// Route::prefix('{key?}')->group(function () {
Route::group(['prefix' => 'software'], function () {
    include  base_path("routes/software.php");
});
// });

Route::match(['get', 'post'], 'thank-you', function () {
    return view('thankyou');
})->name('geast.thankyou');


Route::get('/nimit', function () {
    return view('welcome');
});

Route::get('/testing/map', function () {
    $coordinates = [
        ['lat' => 22.254813, 'lng' => 70.788579],
        ['lat' => 22.256449, 'lng' => 70.788464],
        ['lat' => 22.257347, 'lng' => 70.786675],
        ['lat' => 22.260689, 'lng' => 70.781521],
    ];

    return view('testing.map', compact('coordinates'));
});

Route::get('/checkSendMail', function () {
    $folder = "uploads/4-ocean-software/quotations/2025-05/";
    $filename = "Quotation_29_2025-05-30_11-18-34.pdf";
    $attachmentPath = $folder . '/' . $filename;
    Helper::applyDynamicMailConfig(1);
    $toMail = [];
    $toMail[] = "nimit.ocean@gmail.com";
    $toMail[] = "chandani1994.oceaninfotech@gmail.com";

    $response = Mail::to("nimit.ocean@gmail.com")->send(
        new GenericMail(
            'Your Subject',
            '<h1>Body content nimit</h1>'
            // [
            //     [
            //         'file' => $attachmentPath,
            //         'options' => [
            //             'as' => 'Quotation_29.pdf',
            //             'mime' => 'application/pdf',
            //         ]
            //     ]
            // ]
        )
    );
    dd("L-85", Config::get('mail.mailers.smtp'), $response);
});



Route::get('/dashboard', function () {
    return redirect('software/dashboard', 301);
});

Route::get('/', function () {
    return redirect('software/dashboard', 301);
});
