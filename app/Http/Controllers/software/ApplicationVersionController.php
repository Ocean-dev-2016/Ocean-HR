<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Models\ApplicationVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ApplicationVersionController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Application Version',
            'folder_path' => 'software.modules.application-version',
            'route' => 'application-version',
            'table_name' => (new ApplicationVersion())->getTable(),
            'permisstion_prefix' => 'application-version',
            'module_name' => 'application-version',
        ];
    }

    public function index()
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        View::share('modules', $modules);
        try {
            $edit = ApplicationVersion::latest()->first();
            return view($modules['folder_path'] . '.form', compact('edit'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.form')->withErrors($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $modules = $this->modules;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $authLoginUserDetail = $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $company_Id = $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        View::share('modules', $modules);

        $validator = Validator::make($request->all(), [
            'version' => 'required|string|max:10|unique:application_version,version',
            'apk_file' => 'required|file|max:50000', // 50MB
            'update_message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)   // sends error bag to Blade
                ->withInput();             // keeps old input
        }
        try {
            $filename_path = null;
            if ($request->hasFile('apk_file')) {
                $file = $request->file('apk_file');
                $extension = $file->getClientOriginalExtension();
                $filename = now()->format('dmYHis') . rand() . '.' . $extension;

                $year = now()->format('Y');
                $month = now()->format('m');
                $folder = "uploads/application_version/{$year}-{$month}/apk-file";
                $sub_folder_path = $folder;
                $uploadedPath = public_path($sub_folder_path);
                if (!file_exists($uploadedPath)) {
                    mkdir($uploadedPath, 0777, true);
                }
                $file->move($uploadedPath, $filename);

                $filename_path = "{$folder}/{$filename}";
            }

            $is_force_update = $request->has('is_force_update') ? 1 : 0;

            ApplicationVersion::create([
                'version' => $request->version,
                'apk_file' => $filename_path,
                'is_force_update' => $is_force_update,
                'update_message' => $request->update_message,
            ]);
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
