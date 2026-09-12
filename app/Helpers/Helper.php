<?php

namespace App\Helpers;

use App\Models\Company;
use App\Models\SalesStatus;
use App\Models\EmailLog;
use App\Models\MainMenu;
use App\Models\ManageEmail;
use App\Models\ProductStockHistory;
use App\Models\MasterModules;
use App\Models\RolePermission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use App\Mail\GenericMail;
use App\Models\AdminSoftware;
use App\Models\Notification;
use App\Models\RolePermissionsApp;
use App\Models\SubMenu;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class Helper
{
    private const DEFAULT_DATE_FORMAT = 'd/m/Y';
    private const DEFAULT_TIME_FORMAT = 'H:i';

    private static array $companyFormatCache = [];
    static function randomToken()
    {
        return sha1(md5(time()) . time() . rand());
    }

    //For Validation Message
    static function validation_message($message, $keys = array())
    {
        return $message->all()[0];
    }

    static function make_slug(string $string)
    {
        return Str::slug(strtolower($string));
    }

    static function generateSP($string)
    {
        if ($string) {
            return Str::random(4) . $string . Str::random(3);
        }
        return null;
    }

    static function getLoginUser($guard = null)
    {
        if (!empty($guard)) {
            return Auth::guard($guard)?->user();
        }
        return Auth::user();
    }

    static function getLoginUserRole()
    {
        $login_user_role = Auth::user()->getRoleNames()->first();
        return $login_user_role;
    }

    /** Get Current Guard */
    static function getCurrentGuard()
    {
        try {
            // dd(config('auth.guards'), Auth::guard('team_person-api')->check());

            foreach (array_keys(config('auth.guards')) as $guard) {
                // Skip API guards (passport/jwt)
                // if (str_contains($guard, '-api')) {
                //     continue;
                // }
                if (str_contains($guard, '-api') && Auth::guard($guard)->check()) {
                    return $guard; // return guard name (web, admin, api, etc.)
                } else if (Auth::guard($guard)->check()) {
                    return $guard; // return guard name (web, admin, api, etc.)
                }
            }
            return 'system'; // fallback if no guard
        } catch (\Exception $e) {
            return Auth::getDefaultDriver();
            // dd(Auth::getDefaultDriver(), $e->getMessage(), config('auth.guards'), array_keys(config('auth.guards')), $e);
            return $e->getMessage();
        }

        return true;
    }


    static function convert_date($dateString = "", $current_format = 'd/m/Y', $new_format = 'd/m/Y')
    {
        if ($dateString == "") {
            $dateString = \Carbon\Carbon::now();
            return $dateString->format($new_format);
        }
        /*if($current_format == 'Y-m-d H:i:s'){
            return \Carbon\Carbon::createFromFormat($current_format, $dateString)->format($new_format);
        }*/
        return \Carbon\Carbon::createFromFormat($current_format, $dateString)->format($new_format);
    }

    public static function getSupportedDateFormats(): array
    {
        return [
            'd/m/Y' => 'DD/MM/YYYY (e.g. 25/12/2025)',
            'm/d/Y' => 'MM/DD/YYYY (e.g. 12/25/2025)',
            'Y-m-d' => 'YYYY-MM-DD (e.g. 2025-12-25)',
            'd M Y' => 'DD Mon YYYY (e.g. 25 Dec 2025)',
            'M d, Y' => 'Mon DD, YYYY (e.g. Dec 25, 2025)',
        ];
    }

    public static function getSupportedTimeFormats(): array
    {
        return [
            'H:i' => '24-hour (e.g. 17:45)',
            'H:i:s' => '24-hour with seconds (e.g. 17:45:30)',
            'h:i A' => '12-hour (e.g. 05:45 PM)',
            'h:i:s A' => '12-hour with seconds (e.g. 05:45:30 PM)',
        ];
    }

    public static function getDefaultDateFormat(): string
    {
        return self::DEFAULT_DATE_FORMAT;
    }

    public static function getDefaultTimeFormat(): string
    {
        return self::DEFAULT_TIME_FORMAT;
    }

    public static function getCompanyDateFormat($company = null): string
    {
        $record = self::resolveCompanyForFormat($company);
        return $record?->date_format ?: self::getDefaultDateFormat();
    }

    public static function getCompanyTimeFormat($company = null): string
    {
        $record = self::resolveCompanyForFormat($company);
        return $record?->time_format ?: self::getDefaultTimeFormat();
    }

    public static function formatCompanyDate($value, $company = null, ?string $inputFormat = null): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            $carbon = $inputFormat
                ? Carbon::createFromFormat($inputFormat, $value)
                : Carbon::parse($value);

            return $carbon->format(self::getCompanyDateFormat($company));
        } catch (\Throwable $th) {
            return is_scalar($value) ? (string) $value : null;
        }
    }

    public static function getCompanyBranchType($company_id)
    {
        $company = Company::select('branch_type')->where('id', $company_id)->first();
        return $company?->branch_type;
    }

    public static function formatCompanyTime($value, $company = null, ?string $inputFormat = null): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            $carbon = $inputFormat
                ? Carbon::createFromFormat($inputFormat, $value)
                : Carbon::parse($value);

            return $carbon->format(self::getCompanyTimeFormat($company));
        } catch (\Throwable $th) {
            return is_scalar($value) ? (string) $value : null;
        }
    }

    public static function formatCompanyDateTime($value, $company = null, ?string $inputFormat = null): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            $carbon = $inputFormat
                ? Carbon::createFromFormat($inputFormat, $value)
                : Carbon::parse($value);

            $format = trim(self::getCompanyDateFormat($company) . ' ' . self::getCompanyTimeFormat($company));
            return $carbon->format($format);
        } catch (\Throwable $th) {
            return is_scalar($value) ? (string) $value : null;
        }
    }

    private static function resolveCompanyForFormat($company = null): ?Company
    {
        if ($company instanceof Company) {
            return $company;
        }

        if (is_numeric($company)) {
            $companyId = (int) $company;
        } else {
            $authUserCompany = Auth::user()?->company_id;
            $companyId = $authUserCompany ? (int) $authUserCompany : null;
        }

        if (!$companyId) {
            return null;
        }

        if (!array_key_exists($companyId, self::$companyFormatCache)) {
            self::$companyFormatCache[$companyId] = Company::find($companyId);
        }

        return self::$companyFormatCache[$companyId];
    }

    /** Current Date */
    public static function trait_current_date($format = 'Y-m-d H:i:s')
    {
        return Carbon::now()->format($format);
    }

    /** Generate Random String */
    public static function RandomString($length = 40)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $timestamp = now()->format('YmdHis');
        return $timestamp . $randomString;
    }

    /** Existing image convert to webp */
    static function existingImageConverToWebp($extension, $root_path, $file_name, $upload_file_name, $quality = 100, $imagedestroy = false)
    {
        try {
            $file_path = rtrim($root_path, '/') . '/' . $file_name;

            if (!file_exists($file_path)) {
                dd("Helper existingImageConverToWebp NR-108", $extension, $root_path, $file_name, $upload_file_name, $quality, $imagedestroy);
                throw new \Exception("File not found at path: {$file_path}");
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $getFileMimeType = finfo_file($finfo, $file_path); // image/png | image/jpeg | image/jpg
            finfo_close($finfo);

            if (strtolower($getFileMimeType) == 'image/png') {
                $image = @imagecreatefrompng($file_path);
                imagepalettetotruecolor($image);
            } else if (strtolower($getFileMimeType) == "image/jpg" || strtolower($getFileMimeType) == "image/jpeg") {
                $image = imagecreatefromjpeg($file_path);
            } else if (strtolower($getFileMimeType) == "image/webp") {
                $image = imagecreatefromwebp($file_path);
            } else {
                if (strtolower($extension) == 'png') {
                    $image = @imagecreatefrompng($file_path);
                    imagepalettetotruecolor($image);
                } else if (strtolower($extension) == "jpg" || strtolower($extension) == "jpeg") {
                    $image = imagecreatefromjpeg($file_path);
                } else {
                    return '';
                }
            }
            // return
            $upload_file_name = self::make_slug($upload_file_name) . '.webp';
            // dd("L-288", 'exist', $extension, $root_path, $dir, $file_name, $upload_file_name, $quality, $imagedestroy, $getFileMimeType, $image, $upload_file_name);

            imagewebp($image, $root_path . $upload_file_name, $quality);

            // If file is not store in proper folder copy file
            // copy(public_path($upload_file_name), $dir.$upload_file_name);

            // dd("L-295", 'exist', $extension, $root_path, $dir, $file_name, $upload_file_name, $quality, $imagedestroy, $root_path.$upload_file_name . '.webp', $getFileMimeType, $image);
            //delete initial uploaded png image
            if ($imagedestroy) {
                unlink($file_path);
                // imagedestroy($file_path);
            }
            return $upload_file_name;
        } catch (\Exception $e) {
            dd("Helper existingImageConverToWebp NR-106", $e);
            return null;
            dump($e);
        }
        dd("L-320", 'not exist', $root_path, $file_name, $upload_file_name, $imagedestroy);
    }

    /** encryptString */
    static function encryptString($string)
    {
        return Crypt::encryptString($string);
    }

    /** decryptString */
    static function decryptString($string)
    {
        return Crypt::decryptString($string);
    }

    /** Static cURL Request */
    static function cURLRequest($method = "GET", $url, $payload)
    {
        // https://serpapi.com/blog/how-to-use-curl-in-php/#simple-post-request
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            if (ucfirst($method) == "POST") {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, "param1=value1&param2=value2");
            }
            $jsonData = json_encode($payload);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /** Check the table exist in the database */
    static function checkTableExist($tableName)
    {
        try {
            return DB::connection()->getSchemaBuilder()->hasTable($tableName);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /** Get the table wise get auto increment id */
    static function getTableWiseGetAutoIncrementId($tableName)
    {
        try {
            if (self::checkTableExist($tableName)) {
                $databaseName = DB::getDatabaseName();

                $autoIncrementInfo = DB::table('information_schema.tables')
                    ->where('table_schema', $databaseName)
                    ->where('table_name', $tableName)
                    ->value('AUTO_INCREMENT');

                if ($autoIncrementInfo) {
                    return $autoIncrementInfo;
                } else {
                    return self::RandomString(4);
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get toSQl and bindings
     * ->toSql()
     * ->getBindings()
     * */
    static function interpolateQuery($query, $bindings)
    {
        foreach ($bindings as $binding) {
            $binding = is_numeric($binding) ? $binding : "'$binding'";
            $query = preg_replace('/\?/', $binding, $query, 1);
        }
        return $query;
    }

    /** Get Address by latitude and longitude */
    static function getAddressFromLatLong($latitude, $longitude, $returnResponse = 'address')
    {
        // $addressJson = "https://nominatim.openstreetmap.org/reverse?lat=" . $latitude . "&lon=" . $longitude . "&format=json";
        // Call OpenStreetMap Nominatim API
        $response = Http::get("https://nominatim.openstreetmap.org/reverse", [
            'lat' => $latitude,
            'lon' => $longitude,
            'format' => 'json',
        ]);

        if ($response->successful()) {
            $addressJson = $response->json();

            // $addressJson = (object)$addressJson;
            dd($addressJson);
            if ($returnResponse == "address" && $addressJson?->display_name) {
                return $addressJson?->display_name;
            }
            return $addressJson;
        }
        return null;
    }

    /** Get Main Menu */
    static function getMainMenu($wheres = [], $status = "active", $orderBy = [], $recordType = "all")
    {
        try {
            $main_menu = MainMenu::with(['sub_menu'])->whereNotIn('name', ["Plan Master", "Admin Settings", "Manage Email"]);
            if (count($wheres) > 0) {
                foreach ($wheres as $key => $condition) {
                    // Handle different types of where conditions
                    if (is_array($condition) && isset($condition['type']) && isset($condition['values'])) {
                        // Support for whereIn
                        if ($condition['type'] == 'in' && is_array($condition['values'])) {
                            $main_menu = $main_menu->whereIn($key, $condition['values']);
                        }
                        // Support for custom raw where
                        elseif ($condition['type'] == 'raw') {
                            $main_menu = $main_menu->whereRaw($condition['query'], $condition['bindings'] ?? []);
                        }
                        // Support for other operators
                        elseif (isset($condition['operator']) && isset($condition['value'])) {
                            $main_menu = $main_menu->where($key, $condition['operator'], $condition['value']);
                        }
                    } else {
                        // Default simple key-value where
                        $main_menu = $main_menu->where($key, $condition);
                    }
                }
            }

            if ($status != "all") {
                $main_menu = $main_menu->where('status', $status);
            }

            if (count($orderBy) > 0) {
                foreach ($orderBy as $orderBy_key => $orderBy_value) {
                    // dd($orderBy, $orderBy_key, $orderBy_value);
                    $main_menu = $main_menu->orderBy($orderBy_key, $orderBy_value);
                }
                $main_menu = $main_menu->orderBy('order_by', 'asc');
            } else {
                $main_menu = $main_menu->orderBy('order_by', 'asc');
            }

            if ($recordType == "all") {
                $main_menu = $main_menu->get();
            } else {
                $main_menu = $main_menu->first();
            }
            return $main_menu;
        } catch (\Throwable $th) {
            return Redirect::route('software.dashboard')->withErrors($th?->getMessage());
        }
    }

    /** Get Sub Menu */
    static function getSubMenu($wheres = [], $status = "active", $orderBy = [], $recordType = "all")
    {
        try {
            $sub_menu = SubMenu::with(['mainMenu'])->whereNotIn('name', ["Plan Master", "Admin Settings", "Manage Email"]);
            if (count($wheres) > 0) {
                foreach ($wheres as $key => $condition) {
                    // Handle different types of where conditions
                    if (is_array($condition) && isset($condition['type']) && isset($condition['values'])) {
                        // Support for whereIn
                        if ($condition['type'] == 'in' && is_array($condition['values'])) {
                            $sub_menu = $sub_menu->whereIn($key, $condition['values']);
                        }
                        // Support for custom raw where
                        elseif ($condition['type'] == 'raw') {
                            $sub_menu = $sub_menu->whereRaw($condition['query'], $condition['bindings'] ?? []);
                        }
                        // Support for other operators
                        elseif (isset($condition['operator']) && isset($condition['value'])) {
                            $sub_menu = $sub_menu->where($key, $condition['operator'], $condition['value']);
                        }
                    } else {
                        // Default simple key-value where
                        $sub_menu = $sub_menu->where($key, $condition);
                    }
                }
            }

            if ($status != "all") {
                $sub_menu = $sub_menu->where('status', $status);
            }

            if (count($orderBy) > 0) {
                foreach ($orderBy as $orderBy_key => $orderBy_value) {
                    // dd($orderBy, $orderBy_key, $orderBy_value);
                    $sub_menu = $sub_menu->orderBy($orderBy_key, $orderBy_value);
                }
                $sub_menu = $sub_menu->orderBy('order_by', 'asc');
            } else {
                $sub_menu = $sub_menu->orderBy('order_by', 'asc');
            }

            if ($recordType == "all") {
                $sub_menu = $sub_menu->get();
            } else {
                $sub_menu = $sub_menu->first();
            }
            return $sub_menu;
        } catch (\Throwable $th) {
            return Redirect::route('software.dashboard')->withErrors($th?->getMessage());
        }
    }

    static function getModuleMenu($status = "active", $wheres = [], $orderBy = [])
    {
        try {
            $team_role_menu_permission = [];

            if (Auth::guard('employees')->check()) {
                $company = Company::where('id', Auth::guard('employees')->user()->company_id)->first();

                $modules_sub_menu_id = $company?->panel_right;
                // return $company;

                $team_role_menu_permission = RolePermission::where('team_role_id', Auth::guard('employees')->user()->role_id)
                    ->where('company_id', $company->id)
                    // ->whereIn('main_menu_id', $modules_main_menu_id)
                    ->whereIn('sub_menu_id', $modules_sub_menu_id)
                    ->where('view_flag', '1')
                    ->get();

                // dd("L-338 getModuleMenu", Auth::user()->toArray(), Auth::id(), $team_role_menu_permission, $company?->panel_right);

                if (count($team_role_menu_permission) <= 0) {
                    return [];
                }
            }

            $main_menu = MainMenu::with([
                'sub_menu' => function ($query) use ($team_role_menu_permission, $status) {
                    if (count($team_role_menu_permission) > 0) {
                        $query->whereIn('id', $team_role_menu_permission->pluck('sub_menu_id'));
                    }

                    if ($status != 'all') {
                        $query->where('status', $status);
                    }
                }
            ])
                ->where(function ($query) use ($team_role_menu_permission) {
                    if (count($team_role_menu_permission) > 0) {
                        $query->whereIn('id', $team_role_menu_permission->pluck('main_menu_id'));
                    }
                });
            if ($status != 'all') {
                $main_menu = $main_menu->where('status', $status);
            }

            if (count($wheres) > 0) {
                foreach ($wheres as $key => $temp_where) {
                    // dd("L-265", $key,  $temp_where);
                    $main_menu = $main_menu->where($key, $temp_where);
                }
            }

            if (count($orderBy) > 0) {
                foreach ($orderBy as $orderBy_key => $orderBy_value) {
                    // dd($orderBy, $orderBy_key, $orderBy_value);
                    $main_menu = $main_menu->orderBy($orderBy_key, $orderBy_value);
                }
                $main_menu = $main_menu->orderBy('order_by', 'asc');
            } else {
                $main_menu = $main_menu->orderBy('order_by', 'asc');
            }

            $main_menu = $main_menu->get();

            foreach ($main_menu as $main_menu_key => $main_menu_value) {

                $main_menu_routes = [];
                foreach ($main_menu_value->sub_menu as $sub_menu_key => $sub_menu_value) {
                    if (!empty($sub_menu_value->route_name) && $sub_menu_value->route_name != null) {
                        $route_name = explode('.', $sub_menu_value->route_name)[0];
                        $main_menu_routes[] = $route_name;
                    }
                }
                $main_menu_value->main_menu_routes = array_unique($main_menu_routes);
            }
            // dd("L-393 getModuleMenu", Auth::user()->toArray(), Auth::id(), $team_role_menu_permission, $main_menu);

            return $main_menu;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /** Get the file upload path  */
    static function fileUploadPath($loginUser, $modulePath, $comapny_id = null)
    {
        $fileUploadPath = "/uploads";
        if ($loginUser?->company_id) {
            $company_dir = $loginUser?->company_id . ' ' . $loginUser?->company?->company_name;
            $fileUploadPath .= '/' . self::make_slug($company_dir);
        }
        if (!$loginUser?->company_id && $comapny_id) {
            $company = Company::find($comapny_id);
            $company_dir = $comapny_id . ' ' . $company?->company_name;
            $fileUploadPath .= '/' . self::make_slug($company_dir);
        }

        if ($modulePath) {
            $fileUploadPath .= '/' . $modulePath . self::trait_current_date("Y-m");
        }
        return $fileUploadPath . '/';
    }

    /** Get the Time Difference  */
    static function getTimeDifference($start, $end)
    {

        $punchIn = Carbon::parse($start);
        $punchOut = Carbon::parse($end);

        /*
        // Difference in hours and minutes
        $diffInMinutes = $punchIn->diffInMinutes($punchOut);
        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;
        */

        // Get total difference in seconds
        $diffInSeconds = $punchIn->diffInSeconds($punchOut);

        // Convert to hours, minutes, seconds
        $hours = floor($diffInSeconds / 3600);
        $minutes = floor(($diffInSeconds % 3600) / 60);
        $seconds = $diffInSeconds % 60;

        $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);

        // Combine into MySQL TIME format
        $combineTimeFormat = "{$hours}:{$minutes}:{$seconds}";

        // dd($start, $end, "Total working time: {$hours} hours and {$minutes} minutes", $diffInSeconds, $combineTimeFormat);

        return $combineTimeFormat;
    }

    /** GEt Rendered Email Funcation */
    static function getRenderedEmailTemplate($slug, $companyId, $data = [])
    {
        $template = ManageEmail::where('company_id', $companyId)
            ->where('slug', $slug)
            ->first();

        if (!$template) {
            throw new \Exception("Email template not found.");
        }

        // Compile blade content from DB
        $compiledBody = Blade::render($template->body, $data);
        $compiledSubject = Blade::render($template->subject, $data);

        return [
            'subject' => $compiledSubject,
            'body' => $compiledBody,
        ];
    }

    public static function applyDynamicMailConfig($companyId)
    {
        $settings = DB::table('mail_settings')->where('company_id', $companyId)->first();

        if (!$settings) {
            throw new \Exception('Mail settings not found for company.');
        }

        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $settings->host);
        Config::set('mail.mailers.smtp.port', $settings->port);
        Config::set('mail.mailers.smtp.username', $settings->username);
        Config::set('mail.mailers.smtp.password', $settings->password);
        Config::set('mail.mailers.smtp.encryption', $settings->encryption);

        Config::set('mail.from.address', $settings->from_address);
        Config::set('mail.from.name', $settings->from_name);

        // Optional: set default mailer dynamically
        Config::set('mail.default', $settings->mailer);
    }

    public static function getCompanyDetailById($company_id)
    {
        try {
            return Company::find($company_id);
        } catch (\Exception $e) {
            dd("L-373", $e->getMessage());
            //throw $th;
        }
    }


    /* Convert number to word */
    public static function numberToWords($number)
    {
        $ones = array(
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen'
        );

        $tens = array(
            0 => '',
            2 => 'Twenty',
            3 => 'Thirty',
            4 => 'Forty',
            5 => 'Fifty',
            6 => 'Sixty',
            7 => 'Seventy',
            8 => 'Eighty',
            9 => 'Ninety'
        );

        if ($number == 0) {
            return 'Zero';
        }

        $words = '';

        // Handle crores (10,000,000)
        if ($number >= 10000000) {
            $crores = intval($number / 10000000);
            $words .= Helper::numberToWords($crores) . ' Crore ';
            $number %= 10000000;
        }

        // Handle lakhs (100,000)
        if ($number >= 100000) {
            $lakhs = intval($number / 100000);
            $words .= Helper::numberToWords($lakhs) . ' Lakh ';
            $number %= 100000;
        }

        // Handle thousands (1,000)
        if ($number >= 1000) {
            $thousands = intval($number / 1000);
            $words .= Helper::numberToWords($thousands) . ' Thousand ';
            $number %= 1000;
        }

        // Handle hundreds
        if ($number >= 100) {
            $hundreds = intval($number / 100);
            $words .= $ones[$hundreds] . ' Hundred ';
            $number %= 100;
        }

        // Handle tens and ones
        if ($number >= 20) {
            $tensDigit = intval($number / 10);
            $onesDigit = $number % 10;
            $words .= $tens[$tensDigit];
            if ($onesDigit > 0) {
                $words .= ' ' . $ones[$onesDigit];
            }
        } elseif ($number > 0) {
            $words .= $ones[$number];
        }

        return trim($words);
    }

    public static function convertToRupees($amount)
    {
        // Split amount into rupees and paise
        $parts = explode('.', number_format($amount, 2, '.', ''));
        $rupees = intval($parts[0]);
        $paise = intval($parts[1]);

        $result = '';

        if ($rupees > 0) {
            $result .= Helper::numberToWords($rupees) . ' Rupees';
        }

        if ($paise > 0) {
            if ($rupees > 0) {
                $result .= ' and ';
            }
            $result .= Helper::numberToWords($paise) . ' Paise';
        }

        if ($rupees == 0 && $paise == 0) {
            $result = 'Zero Rupees';
        }

        return $result . ' Only';
    }

    /* for send mail dynamic function */
    public static function mail_new($company_id, $type, $ntype, $user_email, $user_id, $api_name, $data, $attachments = [])
    {
        try {
            $companyData = Company::findOrFail($company_id);
            // dd('hello');

            $manageEmailData = ManageEmail::where('company_id', $company_id)
                //->where('slug', $type)
                ->where('ntype', $ntype)
                ->first();

            if (!$manageEmailData) {
                //dd("0000");
                throw new \Exception("Email template not found for company_id: $company_id, slug: $type, ntype: $ntype");
            }

            Helper::applyDynamicMailConfig($company_id);

            $template_name = $manageEmailData->name;
            $template_subject = $manageEmailData->subject;
            $template_body = $manageEmailData->body;

            // Replace placeholders in subject and body
            $replacements = [];
            // Flatten array data to allow [product_name_0], [product_name_1], etc.
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $i => $subvalue) {
                        $placeholder = "[$key" . "_$i]";
                        $boldPlaceholder = "[<strong>$key" . "_$i</strong>]";
                        $replacements[$placeholder] = $subvalue;
                        $replacements[$boldPlaceholder] = $subvalue;
                    }
                } else {
                    $replacements["[$key]"] = $value;
                    $replacements["[<strong>$key</strong>]"] = $value;
                }
            }

            $template_subject = strtr($template_subject, $replacements);
            $template_body = strtr($template_body, $replacements);

            // $settings = DB::table('mail_settings')->where('company_id', $company_id)->first();
            // if (!$settings) {
            //     throw new \Exception('Mail settings not found for company.');
            // }
            // $bcc = $settings->bcc ?? '';

            //dd($user_email,$template_subject,$template_body,$attachments);
            Log::info(" GenericMail constructor called");
            $response = Mail::to($user_email)->send(new GenericMail($template_subject, $template_body, $attachments));
            // $email_log_data = [
            //     'company_id' => $company_id,
            //     'user_id' => $user_id,
            //     'user_type' => 'team',
            //     'user_email' => $user_email,
            //     'org_subject' => $manageEmailData->subject,
            //     'org_body_template' => $manageEmailData->body,
            //     'subject' => $template_subject,
            //     'body_template' => $template_body,
            //     'send_status' => 'send',
            //     'send_date' => date('d-m-Y h:i A'),
            //     'created_at' => date('d-m-Y h:i A'),
            //     'api_name' => $api_name,
            // ];
            //dd($email_log_data);
            // EmailLog::create($email_log_data);
            // dd($response);

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    static function checkPermissionModule($company_id, $menu_name)
    {
        try {
            $sub_menu = SubMenu::with(['mainMenu'])->where('name', $menu_name)->where("platform", 'panel')->where("status", "active")->first();
            if ($sub_menu) {
                $main_menu_id = $sub_menu->main_menu_id;
                $sub_menu_id = $sub_menu->id;
                $checkPermission = RolePermission::where('company_id', $company_id)
                    ->where('main_menu_id', $main_menu_id)
                    ->where('sub_menu_id', $sub_menu_id)
                    ->where('status', 'active')
                    ->first();

                return $checkPermission;
            }
            return null;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    /** Application Permission */
    static function checkAppPermission($platform = 'panel', $table_name, $company_id, $team_id, $menu_name, $field = null)
    {
        // dd("L-782",$table_name, $company_id, $team_id, $menu_name);
        try {
            $sub_menu = SubMenu::with(['mainMenu'])->where('name', $menu_name)->where("platform", $platform)->where("status", "active")->first();
            if ($sub_menu) {
                $main_menu_id = $sub_menu->main_menu_id;
                $sub_menu_id = $sub_menu->id;
                $checkAppPermission = RolePermission::query();
                if ($table_name == 'role_permissions_app') {
                    $checkAppPermission = RolePermissionsApp::query();
                } else {
                    return null;
                }
                $checkAppPermission = $checkAppPermission->where('company_id', $company_id);
                $checkAppPermission = $checkAppPermission->where('main_menu_id', $main_menu_id);
                $checkAppPermission = $checkAppPermission->where('sub_menu_id', $sub_menu_id);
                $checkAppPermission = $checkAppPermission->where('team_id', $team_id);
                $checkAppPermission = $checkAppPermission->where('status', 'active');
                if ($field) {
                    $checkAppPermission = $checkAppPermission->where($field, 1);
                    $checkAppPermission = $checkAppPermission->first();
                    return $checkAppPermission ? true : false;
                }
                // dd("L-798", self::interpolateQuery($checkAppPermission?->toSql(), $checkAppPermission?->getBindings()), $table_name, $company_id, $team_id, $menu_name);
                $checkAppPermission = $checkAppPermission->first();

                // dd("L-802", $table_name, $company_id, $team_id, $menu_name, $field, $checkAppPermission);
                return $checkAppPermission;
            }
            return null;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function sendPushNotification(array $data)
    {
        try {
            // Validate required fields
            if (empty($data['title']) || empty($data['body'])) {
                Log::warning('Push Notification skipped: Title or Body missing.', $data);
                return;
            }


            // Create DB notification entry
            $notification = Notification::create([
                'user_id' => $data['user_id'] ?? null,
                'user_type' => $data['user_type'] ?? null,
                'title' => $data['title'],
                'body' => $data['body'],
                'module_name' => $data['module_name'] ?? null,
                'module_id' => $data['module_id'] ?? null,
                'module_action' => $data['module_action'] ?? null,
                'notify_read' => 0,
                'created_by' => $data['created_by'] ?? null,
                'created_type' => $data['created_type'] ?? null,
                'status' => $data['status'] ?? null,
                'send_status' => 'pending',
            ]);

            // Check device token and send push notification
            $token = null;

            if ($data['user_type'] == 'Team') {
                $user = Employee::find($data['user_id'] ?? null);
            } else {
                $user = AdminSoftware::find($data['user_id'] ?? null);
            }

            if ($user && !empty($user->device_token)) {
                $token = $user->device_token;
            }

            if (!$token) {
                Log::info("No device token found for user ID: " . ($data['user_id'] ?? 'unknown') . ". Notification not sent via Firebase.", $data);
                $notification->send_status = 'failed';
                $notification->save();
                return;
            }

            $firebase = app(FirebaseService::class);
            $results = $firebase->sendNotification([$token], $data['title'], $data['body']);

            // Check Firebase response and update DB status
            foreach ($results as $result) {
                if ($result['token'] === $token) {
                    $notification->send_status = $result['status'] === 'success' ? 'success' : 'failed';
                    if ($result['status'] === 'failed') {
                        Log::error('Firebase sending failed for token: ' . $token, ['error' => $result['error'] ?? 'Unknown error']);
                    }
                    $notification->save();
                }
            }
        } catch (\Throwable $e) {
            Log::error('Push Notification error: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /** calculate the distance */
    public static function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius in km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // return $earthRadius * $c; // Distance in km

        $distanceKm = $earthRadius * $c;
        return $distanceKm * 1000; // convert to meters
    }


    public static function webpToBase64($imagePath)
    {
        if (!str_ends_with(strtolower($imagePath), '.webp')) {
            return $imagePath;
        }

        // Try to convert .webp to base64 JPEG
        try {

            $data = file_get_contents($imagePath);

            return 'data:image/jpeg;base64,' . base64_encode($data);
        } catch (\Exception $e) {
            Log::error("Image conversion failed: " . $e->getMessage());
            return $imagePath; // fallback to original if fails
        }
    }
}
