<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Address;
use App\Models\Joinclient;
use App\Models\Department;
use App\Models\Education;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
 header('Content-Type: application/json');
if (!isset($_GET['client_id'])) 
{
    $_GET['client_id']="all";
}
if (!isset($_GET['title'])) 
{
    $_GET['title']="all";
}
if (!isset($_GET['cost'])) 
{
    $_GET['cost']="all";
}



$query = Department::query();

if (Auth::user()->user_type=='CLIENT')
 {
 $query = $query->where("client_id",Auth::user()->id);
 $alldepartments = Department::where(['client_id'=>Auth::user()->id])->orderBy("title","asc")->get();
 $allcost = Department::where(['client_id'=>Auth::user()->id])->orderBy("cost","asc")->get()->unique('cost');
 $clients=array();
 $profiles = Profile::get();
}
else
if (Auth::user()->user_type=='SCHEDULE' or Auth::user()->user_type=='FINANCIAL')
{
     $query = $query->where("client_id",Auth::user()->client_id);
     $alldepartments = Department::where(['client_id'=>Auth::user()->client_id])->orderBy("title","asc")->get();
     $allcost = Department::where(['client_id'=>Auth::user()->client_id])->orderBy("cost","asc")->get()->unique('cost');
     $clients=array();
     $profiles = Profile::get();
}
else
if (Auth::user()->user_type=='ADMIN')
{
           $clients = User::where("user_type", "CLIENT")->get();
           $profiles = Profile::get();
           $alldepartments = Department::orderBy("title","asc")->get();
           $allcost = Department::orderBy("cost","asc")->get()->unique('cost');
           if ($_GET['client_id']!='all') 
            {
                 $query = $query->where("client_id",$_GET['client_id']);
            }

}





if ($_GET['title']!='all') 
{
     $query = $query->where("id",$_GET['title']);
}

if ($_GET['cost']!='all') 
{
     $query = $query->where("cost",$_GET['cost']);
}



 $query = $query->orderBy("title", "asc");
 $departments = $query->paginate(Auth::user()->paginationnum);


 


 

       if (Auth::user()->user_type=='EMPLOYEE')
        {
            
            $hide_cost_section = false;
                $departments =  Joinclient::where("user_id", Auth::user()->id)->get()->unique('client_id');
                $profiles = Profile::get();
                $clients=array();
                $educations=array();
                $education_title=array();
                return view('dashboard.departments.index')->with(['departments' => $departments, "hide_cost_section" => $hide_cost_section,'client_id'=>@$_GET['client_id'],'profiles'=>$profiles,'clients'=>$clients,'educations'=>$educations,'education_title'=>$education_title,'educations'=>$educations,'education_title'=>$education_title]);
              
        }
        else
        {
 

                return view('dashboard.departments.indexx')->with(['departments' => $departments,'client_id'=>@$_GET['client_id'],'profiles'=>$profiles,'clients'=>$clients,'cost'=>@$_GET['cost'],'title'=>@$_GET['title'],'alldepartments'=>$alldepartments,'allcost'=>$allcost]);  
        }



        
    }

    public function profileIndex($language, $id)
    {
         header('Content-Type: application/json');
        try {
            $department = Department::findOrFail($id);
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => "No result found!"]);
        }

        $not_allowed = false;

        if (Auth::user()->hasRole('client') && $department->client_id != Auth::user()->id) {
            $not_allowed = true;
        } else if (!Auth::user()->hasRole('admin') && $department->client_id != Auth::user()->client_id) {
            $not_allowed = false;
        }

        if ($not_allowed) {
            abort(403);
        } else {
            return view('dashboard.departments.profile_department')->with(['department' => $department]);
        }
    }

    public function createIndex()
    {
        header('Content-Type: application/json');
        $education_levels = Education::select("title")->get();
        $departments = Department::all();
        $clients = User::where("user_type", "CLIENT")->get();

        return view('dashboard.departments.create')->with(["departments" => $departments, 'education_levels' => $education_levels, "clients" => $clients]);
    }

    public function updateIndex($id)
    {
         header('Content-Type: application/json');
        try {
            $department = Department::findOrFail($id);
            $clients = User::where("user_type", "CLIENT")->get();
            $client_id = $department->client_id;
            // $departments = Department::where("user_id", Auth::user()->id)->get();
            $education_levels = Education::select("title")->get();
            return view('dashboard.departments.update')->with(['department' => $department, 'client_id' => $client_id, 'education_levels' => $education_levels, "clients" => $clients]);
        } catch (\Exception $e) {
            // return view('dashboard.departments.update')->withErorr( $e->getMessage());
            return view('dashboard.departments.update')->with(['error' => "No result found!"]);
        }
    }

    public function createDepartment(Request $request)
    {
 header('Content-Type: application/json');
        $validator = Validator::make($request->all(), [
            'client_id' => ['nullable', 'integer', "exists:users,id"],
            'cost' => 'nullable|numeric',
            'title' => 'required|string|min:2',
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'time_from' => 'nullable|string|min:0000|max:2400',
            'time_to' => 'nullable|string|min:0000|max:2400',
            'website' => 'nullable|url|nullable',
            'description' => 'nullable|string|max:4000',
            'requirements' => 'nullable|string|max:4000',
            'conditions' => 'nullable|string|max:4000',
            'start_date' => 'nullable|date',
            'driving_licence' => 'nullable|boolean',
            'phone' => ['nullable', 'string', 'min:1', 'max:50'],
            'is_available' => 'nullable|boolean'
        ]);

        // date("H:i", strtotime( $request["time_to"] ))

        if ($validator->fails()) {

            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Retrieve the validated input...
        $validated = $validator->validated();
        $validated['user_id'] = Auth::user()->id;
        $validated['admin_id'] = Auth::user()->id;
        /* $validated['time_from'] =  strtotime($request["time_from"]);
        $validated['time_to'] =  strtotime($request["time_to"]); */
        if ($request->has("driving_licence")) {
            $validated['driving_licence'] = true;
        } else {
            $validated['driving_licence'] = false;
        }

        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['required', 'string', 'min:1', 'max:255'],
            'city' => ['required', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['nullable', 'string', 'min:1', 'max:100'],
            'postcode' => ['nullable', 'string', 'min:1', 'max:100000'],
            'country' => ['required', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
            return back()
                ->withErrors($address_validator)
                ->withInput();
        }

        // Retrieve the validated input...
        $address_validated = $address_validator->validated();

        if (!array_key_exists("cost", $validated)) {
            $validated['cost'] = null;
        }

        try {

 
        

            DB::beginTransaction();
            $department = new Department();
            $department->fill($validated);
            $department->save();

            $lastinsertedid= DB::getPdo()->lastInsertId();

            $department_address = new Address();
            $department_address->addressable()->associate($department);
            $department_address->fill($address_validated);
            $department_address->save();


            $lastinsertedidaddress= DB::getPdo()->lastInsertId();

        Address::where(['id'=>$lastinsertedidaddress])
       ->update([
           'home_number' =>$request->home_number,
        ]);


            Department::where(["id"=>$lastinsertedid])
           ->update([
               'phone' =>$request->phone,
            ]);


            DB::commit();

            return redirect()->back()->with('message', "New department created successfully.");
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateDepartment(Request $request)
    {
         header('Content-Type: application/json');
        $validator = Validator::make($request->all(), [
            'client_id' => ['nullable', 'integer', "exists:users,id"],
            'department_id' => 'required|numeric',
            'cost' => 'nullable|numeric',
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'department_id' => 'required|numeric',
            'title' => 'required|string|min:2',
            'website' => 'nullable|url|nullable',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'conditions' => 'nullable|string',
            'start_date' => 'nullable|date',
            'driving_licence' => 'nullable|boolean',
            'is_available' => 'nullable|boolean'
        ]);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Retrieve the validated input...
        $validated = $validator->validated();
        $validated['user_id'] = Auth::user()->id;
        $validated['time_from'] =  strtotime($request["time_from"]);
        $validated['time_to'] =  strtotime($request["time_to"]);
        if ($request->has("driving_licence")) {
            $validated['driving_licence'] = true;
        } else {
            $validated['driving_licence'] = false;
        }
        if ($request->has("is_available")) {
            $validated['is_available'] = true;
        } else {
            $validated['is_available'] = false;
        }

        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['required', 'string', 'min:1', 'max:255'],
            'city' => ['required', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['nullable', 'string', 'min:1', 'max:100'],
            'postcode' => ['nullable', 'string', 'min:1', 'max:100000'],
            'country' => ['required', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
            return back()
                ->withErrors($address_validator)
                ->withInput();
        }
        // Retrieve the validated input...
        $address_validated = $address_validator->validated();
        $address_validated = array_filter($address_validated, function ($a) {
            return $a !== null;
        });

        if (!array_key_exists("cost", $validated)) {
            $validated['cost'] = null;
        }

        try {
            DB::beginTransaction();
            $department = Department::findOrFail($validated["department_id"]);
            $department->update($validated);

            Department::where(["id"=>$validated["department_id"]])
           ->update([
               'phone' =>$request->phone,
            ]);



            $department->address()->update($address_validated);


            $lastinsertedidaddress= DB::getPdo()->lastInsertId();

        Address::where(['addressable_id'=>$validated["department_id"],'addressable_type'=>'App\Models\Department'])
       ->update([
           'home_number' =>$request->home_number,
        ]);



            DB::commit();
            return redirect()->back()->with('message', "Department updated successfully.");
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
