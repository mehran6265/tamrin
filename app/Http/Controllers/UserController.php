<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Document;
use App\Models\Education;
use App\Models\Financial;
use App\Models\Image;
use App\Models\Permission;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use App\Models\User_log;
use App\Models\Step_log;
use App\Models\Email_log;
use App\Models\Joinclient;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use App\Models\Joindepartment;
use App\Mail\ForgotPassword;
use App\Mail\WelcomeEmail;
use App\CustomClass\Rh;
use DateTime;
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


 public function indexclientsurcharges($languege,$id)
    {

        $Clientsurcharge=Clientsurcharge::where(['user_id'=>$id])->get();
        return view('dashboard.users.joindepartmentlist')->with(['Clientsurcharge'=>$Clientsurcharge]);
    }


 public function indexclientsurchargesstore($languege,$id)
    {

    }







  public function changepaginationnumber($languege)
    {

     if (isset($_GET['pag'])) 
{
    User::where(['id'=>Auth::user()->id])
    ->update([
     'paginationnum' =>(int)$_GET['pag'],
 ]);

  echo 1;exit;


} 


    }




    public function joindepartmentdelete($languege,$id)
    {
        if (Auth::user()->hasRole('admin') || (!Auth::user()->hasRole('employee'))) 
        {
           $res=joindepartment::where(['id'=>$id])->delete();      
        }
    return back();
    }



    public function joindepartmentlist($languege,$id)
    {


        if (Auth::user()->hasRole('admin') || (!Auth::user()->hasRole('employee'))) 
        {
       
         

 
        $jobtitle=1;

         
        if (isset($_GET['jobtitle'])) 
        {
            if ($_GET['jobtitle']!=1) 
            {
                $jobtitle=$_GET['jobtitle'];
            }
            
        }



         $client_id=1;

         
        if (isset($_GET['client_id'])) 
        {
            $client_id=$_GET['client_id'];
        }




         $department_id=1;

         
        if (isset($_GET['department_id'])) 
        {
            $department_id=$_GET['department_id'];
        }

 $user = User::findOrFail($id);

 $departments = Department::where("client_id",  $user->client_id)->select(["id", "title"])->orderBy("title","asc")->get();

  $clients = User::where("user_type", "CLIENT")->get();
 $profiles = Profile::get();


if ($department_id==1 and $jobtitle==1) 
{
        $joindepartment = DB::table('joindepartment')
      ->join('profiles', 'profiles.user_id', '=', 'joindepartment.client_id')
      ->join('departments', 'departments.id', '=', 'joindepartment.department_id')
      ->where(['joindepartment.user_id'=>$id])
      ->select('joindepartment.*', 'profiles.first_name', 'profiles.last_name','departments.title')
      ->paginate(Auth::user()->paginationnum);
}
else
if ($department_id>1 and $jobtitle>1) 
{
        $joindepartment = DB::table('joindepartment')
      ->join('profiles', 'profiles.user_id', '=', 'joindepartment.client_id')
      ->join('departments', 'departments.id', '=', 'joindepartment.department_id')
      ->where(['joindepartment.user_id'=>$id])
      ->where(['joindepartment.registeras'=>$jobtitle])
      ->where(['joindepartment.department_id'=>$department_id])
      ->select('joindepartment.*', 'profiles.first_name', 'profiles.last_name','departments.title')
      ->paginate(Auth::user()->paginationnum);
}
else
if ($department_id>1) 
{
        $joindepartment = DB::table('joindepartment')
      ->join('profiles', 'profiles.user_id', '=', 'joindepartment.client_id')
      ->join('departments', 'departments.id', '=', 'joindepartment.department_id')
      ->where(['joindepartment.user_id'=>$id])
      ->where(['joindepartment.department_id'=>$department_id])
      ->select('joindepartment.*', 'profiles.first_name', 'profiles.last_name','departments.title')
      ->paginate(Auth::user()->paginationnum);
}
else
if ($jobtitle!=1) 
{
        $joindepartment = DB::table('joindepartment')
      ->join('profiles', 'profiles.user_id', '=', 'joindepartment.client_id')
      ->join('departments', 'departments.id', '=', 'joindepartment.department_id')
      ->where(['joindepartment.user_id'=>$id])
      ->where(['joindepartment.registeras'=>$jobtitle])
      ->select('joindepartment.*', 'profiles.first_name', 'profiles.last_name','departments.title')
      ->paginate(Auth::user()->paginationnum);
}


  
 
                  return view('dashboard.users.joindepartmentlist')->with(["page_slug" => "users-client", "page_title" => "join client", "joindepartment" => $joindepartment,'user_id'=>$id,'profiles'=>$profiles,'clients'=>$clients,'departments'=>$departments,'department_id'=>$department_id,'jobtitle'=>$jobtitle,'client_id'=>$client_id]);

        
      
    }
    }
    public function createjoindepartment(Request $request)
    {


        if (Auth::user()->hasRole('admin') || (!Auth::user()->hasRole('employee'))) {
        // validating user data
        $user_validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'registeras' => 'required',
        ]);

        if ($user_validator->fails()) {
            return back()
                ->withErrors($user_validator)
                ->withInput();
        }
     
     

 $user = User::findOrFail($request->user_id);

if ($request->department==1)
 {
    $departments = Department::where('client_id',$user->client_id)->get();

foreach ($departments as $row)
 {
    
    $check = joindepartment::where([
            'user_id' =>$request->user_id,
            'client_id' =>$user->client_id,
            'registeras' =>$request->registeras,
            'department_id' =>$row->id,
        ])->get();

 if ($check->isEmpty())
         {

         joindepartment::create([
            'user_id' =>$request->user_id,
            'client_id' =>$user->client_id,
            'registeras' =>$request->registeras,
            'department_id' =>$row->id,
        ]);

       }
 

}


 }
 else
 {

    $check = joindepartment::where([
            'user_id' =>$request->user_id,
            'client_id' =>$user->client_id,
            'registeras' =>$request->registeras,
            'department_id' =>$request->department,
        ])->get();

         if ($check->isEmpty())
         {

         joindepartment::create([
            'user_id' =>$request->user_id,
            'client_id' =>$user->client_id,
            'registeras' =>$request->registeras,
            'department_id' =>$request->department,
        ]);

         }
         else
         {
            return redirect()->back()->with('message', "This join exist.");
         }
 }





  


      return redirect()->back()->with('message', "The join inserted successfully");

 
    }
    }




    public function joindepartment($language, $id)
    {

        if (Auth::user()->hasRole('admin') || (!Auth::user()->hasRole('employee')) ){
            try {
              
              $user = User::findOrFail($id);

                $education_levels = Department::where('client_id',$user->client_id)->orderBy("title","asc")->get();

  
 

                return view('dashboard.users.joinclientdepartment')->with(["education_levels" => $education_levels,'user_id'=>$id]);
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        } else {
            abort(403);
        }
    }


 


//////////////////////////////////////////////////////////////////////////////////

    public function joinclientdelete($languege,$id)
    {
        
           $res=joinclient::where(['id'=>$id])->delete();      
         
    echo 1;
    }



    public function joinclientlist($languege,$id)
    {

      


        $jobtitle=1;

         
        if (isset($_GET['jobtitle'])) 
        {
            $jobtitle=$_GET['jobtitle'];
        }



         $client_id=1;

         
        if (isset($_GET['client_id'])) 
        {
            $client_id=$_GET['client_id'];
        }




         $department_id=1;

         
        if (isset($_GET['department_id'])) 
        {
            $department_id=$_GET['department_id'];
        }


 $query = Joinclient::query();



$query = $query->where("user_id",$id);


            if ($jobtitle != 1) 
            {
                $query = $query->where("registeras", $jobtitle);
            }


            if ($department_id != 1) 
            {
                $query = $query->where("department_id", $department_id);
            }

            if ($client_id != 1) 
            {
                $query = $query->where("client_id", $client_id);
            }

            

$joinclient = $query->paginate(Auth::user()->paginationnum);


     // $joinclient = DB::table('joinclient')
     //  ->join('profiles', 'profiles.user_id', '=', 'joinclient.client_id')
     //  ->where(['joinclient.user_id'=>$id])
     //  ->select('joinclient.*', 'profiles.first_name', 'profiles.last_name', 'profiles.company_name')
     //  ->paginate(Auth::user()->paginationnum);







 $departments = Department::select(["id", "title"])->orderBy("title","asc")->get();

  // $clients = User::where("user_type", "CLIENT")->get();


   $clients = Joinclient::where("user_id",$id)->get()->unique('client_id');


 
 

// dd($clients);



 $profiles = Profile::get();


                  return view('dashboard.users.joinclientlist')->with(["page_slug" => "users-client", "page_title" => "join client", "joinclient" => $joinclient,'user_id'=>$id,'jobtitle'=>$jobtitle,'client_id'=>$client_id,'department_id'=>$department_id,'clients'=>$clients,'departments'=> $departments,'profiles'=>$profiles]);

        
      
      



    }
    public function createjoinclient(Request $request)
    {

 

      
        // validating user data
        $user_validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'client_id' => 'required|numeric|exists:users,id',
            'payrate' => 'required',
            'registeras' => 'required',
            'clientpayrate' => 'required',
        ]);

        if ($user_validator->fails()) {
            $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
     
       


$clientpayrate= str_replace(",",".",$request->clientpayrate);
$payrate= str_replace(",",".",$request->payrate);

// echo $clientpayrate;exit;
// $clientpayrate=$request->clientpayrate;
// $payrate=$request->payrate;


if ($request->department_id==1)
 {
    $departments = Department::where('client_id',$request->client_id)->get();

 
     
foreach ($departments as $row)
 {
      

         $Joinclienttest=Joinclient::where([
            'user_id' =>$request->user_id,
            'client_id' =>$request->client_id,
            'registeras' =>$request->registeras,
            'department_id' =>$row->id,
          ])->get();

         if ($Joinclienttest->isEmpty())
         {
          joinclient::create([
            'user_id' =>$request->user_id,
            'client_id' =>$request->client_id,
            'registeras' =>$request->registeras,
            'payrate' =>$payrate,
            'client_payrate' =>$clientpayrate,
            'department_id' =>$row->id,
         ]);   
         }


}


 }
 else
 {

         $Joinclienttest=Joinclient::where([
            'user_id' =>$request->user_id,
            'client_id' =>$request->client_id,
            'registeras' =>$request->registeras,
            'department_id' =>$request->department_id,
          ])->get();

         if ($Joinclienttest->isEmpty())
         {
         joinclient::create([
            'user_id' =>$request->user_id,
            'client_id' =>$request->client_id,
            'registeras' =>$request->registeras,
            'payrate' =>$payrate,
            'client_payrate' =>$clientpayrate,
            'department_id' =>$request->department_id,
        ]);  
         }
         else
         {
           echo "This join exist";exit;
         }





 }





    echo 1;exit;

 
    
    }




    public function joinclient($language, $id)
    {

 

   
                $user = User::where("id", ">", 3)->where("user_type", "EMPLOYEE")->with(["profile", "financial", "client"])->findOrFail($id);

                $clients = User::where("user_type", "CLIENT")->select("id")->with("profile:user_id,first_name,last_name,company_name")->get();
                $education_levels = Education::select("title")->get();

   $profile=Profile::where("user_id",$id)->get();




                return view('dashboard.users.joinemploee')->with(["user" => $user, "education_levels" => $education_levels, "clients" => $clients, "profile" => $profile]);
           
    }



    public function index()
    {
        return view('dashboard.users.index')->with(["page_slug" => "", "page_title" => ""]);
    }

    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /* admin functions */
    public function adminsIndex()
    {
        $admins = User::where("user_type", "ADMIN")->paginate(Auth::user()->paginationnum);
        return view('dashboard.users.index_admin')->with(["page_slug" => "users-admins", "page_title" => "Admins", "admins" => $admins]);
    }















    public function createAdminIndex()
    {
        return view('dashboard.users.create_admin');
    }

    public function createAdmin(Request $request)
    {
        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:1', 'confirmed'],
        ]);
        if ($user_validator->fails()) {
              $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $user_validated = $user_validator->validated();
        $user_validated["password"]  = Hash::make($request['password']);
        $user_validated["is_activated"]  = true;
        $user_validated["user_type"]  = "ADMIN";


        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['nullable', 'string', 'min:1', 'max:100'],
            'last_name' => ['nullable', 'string', 'min:1', 'max:100'],
            'phone' => ['nullable', 'string', 'min:1', 'max:50'],
            'mobile' => ['nullable', 'string', 'min:1', 'max:50'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:tomorrow'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
        ]);
        if ($profile_validator->fails()) {
           $tt="";
   foreach($profile_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();


        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['nullable', 'string', 'min:1', 'max:255'],
            'city' => ['nullable', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['nullable', 'string', 'min:1', 'max:100'],
            'postcode' => ['nullable', 'string', 'min:1', 'max:100000'],
            'country' => ['nullable', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
             $tt="";
   foreach($address_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $address_validated = $address_validator->validated();

        // validating financial data
        $financial_validator = Validator::make($request->all(), [
            'bank_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_number' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_holder' => ['nullable', 'string', 'min:1', 'max:255'],
            'tax_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'terms_of_payment' => ['nullable', 'string', 'max:4000'],
        ]);
        if ($financial_validator->fails()) {
            $tt="";
   foreach($financial_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $financial_validated = $financial_validator->validated();


        try {
            DB::beginTransaction();

            $admin_user = new User();
            $admin_user->fill($user_validated);
            $admin_user->save();

            $lastinsertediduser= DB::getPdo()->lastInsertId();

        User::where(['id'=>$lastinsertediduser])
       ->update([
           'is_activated' =>0,
        ]);



            $admin_role = Role::where('slug', 'admin')->first();
            $admin_permission = Permission::where('slug', 'crud-admin')->first();
            $admin_user->roles()->attach($admin_role);
            $admin_user->permissions()->attach($admin_permission);

            $profile_validated['user_id'] = $admin_user->id;
            $admin_profile = new Profile();
            $admin_profile->fill($profile_validated);
            $admin_profile->save();

            /* $financial_validated['user_id'] = $admin_user->id;
            $admin_financial = new Financial();
            $admin_financial->fill($financial_validated);
            $admin_financial->save();

            $admin_address = new Address();
            $admin_address->addressable()->associate($admin_user);
            $admin_address->fill($address_validated);
            $admin_address->save(); */

            DB::commit();

           echo 1;exit;
        } catch (\Exception $e) {
            DB::rollBack();
            echo  $e->getMessage();exit;
        }
    }

    public function profileusers()
    {
        return view('dashboard.users.profile_admin')->with(['user' => Auth::user()]);
    }

    public function updateAdminIndex($language, $id)
    {
        try {
            $user = User::where("user_type", "ADMIN")->findOrFail($id);
            return view('dashboard.users.update_admin')->with(['user' => $user]);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', "Not allowed to edit it at the moment!");
        }
    }

    public function toggleUserStatus(Request $request)
    {
        // return $request; 
        // validating user data
        $user_validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:users']
        ]);
        if ($user_validator->fails()) {
            return back()
                ->withErrors($user_validator)
                ->withInput();
        }
        // Retrieve the validated input...
        $user_validated = $user_validator->validated();

        try {
            $user = User::where("id", ">", 1)->findOrFail($user_validated["id"]);

            if (($user->uset_type == "EMPLOYEE" || $user->uset_type == "SCHEDULE" || $user->uset_type == "FINANCIAL") && $user->client_id == null) {
                return redirect()->back()->withInput()->with('error', "First assign a client to this employee.");
            }

if ($user->user_type == "EMPLOYEE") 
{
                if ($user->is_activated==0) 
            {
                $user->is_activated = 1;
            } 
            else
            if ($user->is_activated==1)
             {
                $user->is_activated = 2;
             }
            else
            if ($user->is_activated==2)
             {
                $user->is_activated = 0;
            }
}
else
{
            if ($user->is_activated==0 or $user->is_activated==1) 
            {
                $user->is_activated = 2;
            } 
            else
            if ($user->is_activated==2)
             {
                $user->is_activated = 0;
            }

}






            $user->save();

 if ($user->user_type == "FINANCIAL")
  {
     if ($user->is_activated==2)
    {
        //Account activated financial

$details = [
            'title' => "Beste financiële administratie,",
            'body1' => "Jouw account is geactiveerd.",
            'body2' => "Log in op het portaal om jouw account te bekijken.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Account geactiveerd!"));
    }
    else
        if ($user->is_activated==0) 
        {
           // Account deactivated financial

$details = [
            'title' => "Beste financiële administratie,",
            'body1' => "Jouw account is gedeactiveerd.",
            'body2' => "Neem contact op met ons voor meer informatie.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Account gedeactiveerd!"));
        }
  }




 if ($user->user_type == "SCHEDULE")
  {
 if ($user->is_activated==2)
    {
        //Account activated schedule

$details = [
            'title' => "Beste planner,",
            'body1' => "Jouw account is geactiveerd.",
            'body2' => "Log in op het portaal om jouw account te bekijken.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Account geactiveerd!"));
    }
        else
        if ($user->is_activated==0) 
        {
            
//Account deactivated schedule

$details = [
            'title' => "Beste planner,",
            'body1' => "Jouw account is gedeactiveerd.",
            'body2' => "Neem contact op met ons voor meer informatie.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Account gedeactiveerd!"));
        }

}




 if ($user->user_type == "CLIENT")
  {
    if ($user->is_activated==2)
    {
        //Account activated client

$details = [
            'title' => "Beste opdrachtgever,",
            'body1' => "Jouw account is geactiveerd.",
            'body2' => "Log in op het portaal om jouw account te bekijken.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Account geactiveerd!"));
    }
        else
        if ($user->is_activated==0) 
        {
           //Account deactivated client

$details = [
            'title' => "Beste opdrachtgever,",
            'body1' => "Jouw account is gedeactiveerd.",
            'body2' => "Neem contact op met ons voor meer informatie.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Account gedeactiveerd!"));
        }
  }



 if ($user->user_type == "ADMIN")
  {
    if ($user->is_activated==2)
    {
      //Account activated admin

$details = [
            'title' => "Beste beheerder,",
            'body1' => "Jouw account is geactiveerd.",
            'body2' => "Log in op het portaal om jouw account te bekijken.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Account geactiveerd!"));  
    }
        else
        if ($user->is_activated==0) 
        {
           // Account deactivated admin

$details = [
            'title' => "Beste planner,",
            'body1' => "Jouw account is gedeactiveerd.",
            'body2' => "Neem contact op met ons voor meer informatie.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Account gedeactiveerd!"));

        }

  }




 if ($user->user_type == "EMPLOYEE")
  {
     if ($user->is_activated==0)
 {


//Account deactivated freelancer

$details = [
            'title' => "Beste ZPC-er,",
            'body1' => "Jouw account is gedeactiveerd.",
            'body2' => "Neem contact op met ons voor meer informatie.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Account gedeactiveerd!"));




}
else
     if ($user->is_activated==1)
 {
    $details = [
            'title' => "Beste ZPC-er,",
            'body1' => "ZPC heeft jouw account vrijgegeven voor het uploaden van documenten.",
            'body2' => "Log in op het portaal en je kunt jouw documenten uploaden.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Uploaden documenten"));
}
else
if ($user->is_activated==2)
{
//Account deactivated freelancer

$details = [
            'title' => "Beste ZPC-er,",
            'body1' => "Jouw account is geactiveerd.",
            'body2' => "Log in op het portaal om jouw account te bekijken.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user->email)->subject("Account geactiveerd!"));
}
 }







            return redirect()->back()->with('message', "Updated successfully.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateAdmin(Request $request)
    {
        // validating user data
        $user_validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:1', 'confirmed'],
        ]);
        if ($user_validator->fails()) {
            $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $user_validated = $user_validator->validated();

        $user_validated = array_filter($user_validated, function ($a) {
            return $a !== null;
        });
        if (isset($user_validated["password"])) {
            $user_validated["password"]  = Hash::make($request['password']);
        }

        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['nullable', 'string', 'min:1', 'max:100'],
            'last_name' => ['nullable', 'string', 'min:1', 'max:100'],
            'phone' => ['nullable', 'string', 'min:1', 'max:50'],
            'mobile' => ['nullable', 'string', 'min:1', 'max:50'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:tomorrow'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
        ]);
        if ($profile_validator->fails()) {
         $tt="";
   foreach($profile_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();


        try {
            DB::beginTransaction();
            $admin_user = User::where("id", ">", 0)->where("user_type", "ADMIN")->findOrFail($request['user_id']);
            $admin_user->update($user_validated);

            $admin_user->profile()->update($profile_validated);

            DB::commit();

            echo 1;exit;
        } catch (\Exception $e) {
            DB::rollBack();
            echo  $e->getMessage();exit;
        }
    }
    /* end of admin functions */


    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /* client functions */
    public function clientsIndex()
    {
        $clients = User::whereIn("user_type", ["CLIENT"])->paginate(Auth::user()->paginationnum);
        return view('dashboard.users.index_client')->with(["page_slug" => "users-clients", "page_title" => "Clients", "clients" => $clients]);
    }

    public function clientFinancialDepartmentsIndex()
    {
       if (Auth::user()->user_type=="ADMIN")
       {
        $clients = User::whereIn("user_type", ["FINANCIAL"])->paginate(Auth::user()->paginationnum);
        return view('dashboard.users.index_financial_departments')->with(["page_slug" => "users-clients-departments-financial", "page_title" => "Financial departments", "clients" => $clients]);
       }
       else
       if (Auth::user()->user_type=="CLIENT")
        {
        $clients = User::where(["client_id"=>Auth::user()->id,'user_type'=>"FINANCIAL"])->paginate(Auth::user()->paginationnum);
        return view('dashboard.users.index_financial_departments')->with(["page_slug" => "users-clients-departments-financial", "page_title" => "Financial departments", "clients" => $clients]);
        }

    }

    public function clientsScheduleDepartmentsIndex()
    {

 if (Auth::user()->user_type=="ADMIN")
       {
                $clients = User::whereIn("user_type", ["SCHEDULE"])->paginate(Auth::user()->paginationnum);
        return view('dashboard.users.index_schedule_departments')->with(["page_slug" => "users-clients-departments-schedule", "page_title" => "Schedule departments", "clients" => $clients]);
       }
       else
       if (Auth::user()->user_type=="CLIENT")
       {
        $clients = User::where(["client_id"=>Auth::user()->id,'user_type'=>"SCHEDULE"])->paginate(Auth::user()->paginationnum);
        return view('dashboard.users.index_schedule_departments')->with(["page_slug" => "users-clients-departments-schedule", "page_title" => "Schedule departments", "clients" => $clients]);
       }



    }

    public function createClientIndex()
    {
        return view('dashboard.users.create_client');
    }

    public function createClientFinancialIndex()
    {
        return view('dashboard.users.create_client_financial');
    }

    public function createClientScheduleIndex()
    {
        return view('dashboard.users.create_client_schedule');
    }

    public function createClient(Request $request)
    {






        $creator_user_type = "CLIENT";
        // "SCHEDULE", "FINANCIAL"

        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:1', 'confirmed'],
        ]);
        if ($user_validator->fails()) {
          $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $user_validated = $user_validator->validated();
        $user_validated["password"]  = Hash::make($request['password']);


        $client_validator = Validator::make($request->all(), [
            /* 'client_email' => ['nullable', 'string', 'email', 'max:255'], */
            'client_company_name' => ['nullable', 'string', 'max:255'],
            'user_type' => ['required', 'in:CLIENT,SCHEDULE,FINANCIAL'],
        ]);
        if ($client_validator->fails()) {
             $tt="";
   foreach($client_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $client_validator = $client_validator->validated();

        if (array_key_exists("client_company_name", $client_validator)) {

            //$the_client = User::where("email", $client_validator["client_email"])->where("is_activated", true)->where("user_type", "CLIENT")->first();
            $the_client_profile = Profile::where("company_name", $client_validator["client_company_name"])->first();
            if (!$the_client_profile) {
                echo "Either client with this email did not find or this client is not activated yet!";exit;
            }

            /* $the_client = User::where("email", $client_validator["client_company_name"])->where("is_activated", true)->where("user_type", "CLIENT")->first();
            if (!$the_client) {
                return redirect()->back()->withInput()->with('error', "Either client with this email did not find or this client is not activated yet!");
            } */

            // maximum number of each departments are 3
            $already_have_one =  User::where("client_id", $the_client_profile->user_id)->where("user_type", $client_validator["user_type"])->count();
            if ($already_have_one >= 9) {
              echo "This client already has this department!";exit;
            }

            $user_validated["client_id"] = $the_client_profile->user_id;
        } else if ($client_validator["user_type"] == "FINANCIAL" || $client_validator["user_type"] == "SCHEDULE") {
            echo "Email address of the client is required!";exit;
        }

        $creator_user_type = $client_validator["user_type"];
        $user_validated["user_type"] = $creator_user_type;


        if ($user_validated["user_type"]=="CLIENT") 
        {
            
            if ( !is_numeric($request->kvk_number) ) {
                echo "kvk_number must be number.";exit;
            } 

             $str = $request->kvk_number;
            $len = strlen($str);

            if ($len!=8 and $len!=12) 
            {
                echo "kvk_number must be 8 or 12 length.";exit;
            }


        }






        switch ($creator_user_type) {
            case 'FINANCIAL':
                $role = Role::where('slug', 'financial')->first();
                $permission = Permission::where('slug', 'crud-financial')->first();
                break;
            case 'SCHEDULE':
                $role = Role::where('slug', 'schedule')->first();
                $permission = Permission::where('slug', 'crud-schedule')->first();
                break;

            default:
                $role = Role::where('slug', 'client')->first();
                $permission = Permission::where('slug', 'crud-client')->first();
                break;
        }


        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
            'company_name' => ['nullable', 'string', 'min:1', 'max:100', 'unique:profiles,company_name'],
            'phone' => ['nullable', 'string', 'min:1', 'max:50'],
            'mobile' => ['nullable', 'string', 'min:1', 'max:50'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:tomorrow'],
        ]);
        if ($profile_validator->fails()) {
           $tt="";
   foreach($profile_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();

        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['nullable', 'string', 'min:1', 'max:255'],
            'city' => ['nullable', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['nullable', 'string', 'min:1', 'max:100'],
            'postcode' => ['nullable', 'string', 'min:1', 'max:100000'],
            'country' => ['nullable', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
            $tt="";
   foreach($address_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $address_validated = $address_validator->validated();

        /* // validating financial data
        $financial_validator = Validator::make($request->all(), [
            'bank_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_number' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_holder' => ['nullable', 'string', 'min:1', 'max:255'],
            'tax_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'terms_of_payment' => ['nullable', 'string', 'max:4000'],
        ]);
        if ($financial_validator->fails()) {
            return back()
                ->withErrors($financial_validator)
                ->withInput();
        }
        // Retrieve the validated input...
        $financial_validated = $financial_validator->validated(); */



        // validating contact data
        $contact_validator = Validator::make($request->all(), [
            'contact_name' => ['nullable', 'string', 'min:1', 'max:100'],
            'contact_role' => ['nullable', 'string', 'min:1', 'max:100'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'min:1', 'max:100'],
        ]);
        if ($contact_validator->fails()) {
              $tt="";
   foreach($contact_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }


        // Retrieve the validated input...
        $contact_validated = $contact_validator->validated();
        if (array_key_exists("contact_name", $contact_validated))
            $contact_validated["name"] = $contact_validated["contact_name"];
        if (array_key_exists("contact_role", $contact_validated))
            $contact_validated["role"] = $contact_validated["contact_role"];
        if (array_key_exists("contact_email", $contact_validated))
            $contact_validated["email"] = $contact_validated["contact_email"];
        if (array_key_exists("contact_phone", $contact_validated))
            $contact_validated["phone"] = $contact_validated["contact_phone"];


        try {
            DB::beginTransaction();
            $client_user = new User();
            $client_user->fill($user_validated);
            $client_user->save();

            $client_user->roles()->attach($role);
            $client_user->permissions()->attach($permission);

            $profile_validated['user_id'] = $client_user->id;
            $client_profile = new Profile();
            $client_profile->fill($profile_validated);
            $client_profile->save();

            /* $financial_validated['user_id'] = $client_user->id;
            $client_financial = new Financial();
            $client_financial->fill($financial_validated);
            $client_financial->save(); */



            if ($creator_user_type=='FINANCIAL' or $creator_user_type=='SCHEDULE') 
            {
                
               $address_validated['address']="-";
               $address_validated['city']="-";
            }



            $client_address = new Address();
            $client_address->addressable()->associate($client_user);
            $client_address->fill($address_validated);
            $client_address->save();

            $lastinsertedidaddress= DB::getPdo()->lastInsertId();

        Address::where(['id'=>$lastinsertedidaddress])
       ->update([
           'home_number' =>$request->home_number,
        ]);


            $contact_validated['user_id'] = $client_user->id;
            $client_contact = new Contact();
            $client_contact->fill($contact_validated);
            $client_contact->save();


            DB::commit();

       echo 1;exit;
        } catch (\Exception $e) {
            DB::rollBack();
           echo  $e->getMessage();exit;
        }
    }

 

    public function updateClientIndex($language, $id)
    {
 

        try {
            $user = User::whereIn("user_type", ["CLIENT", "FINANCIAL", "SCHEDULE"])->findOrFail($id);

            $has_contact = 0;
            if ($user->contact != null) {
                if ($user->contact->name != null || $user->contact->role != null || $user->contact->phone != null || $user->contact->email != null) {
                    $has_contact = 1;
                }
            }

            return view('dashboard.users.update_client')->with(["user" => $user, "has_contact" => $has_contact]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['error' => "No result found!"]);
        }
    }

    public function updateClient(Request $request)
    {






        // validating user data
        $user_validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric'
        ]);




        if ($user_validator->fails()) {
            $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $user_validated = $user_validator->validated();


  

        $user_validated = array_filter($user_validated, function ($a) {
            return $a !== null;
        });

        if (isset($user_validated["password"])) {
            $user_validated["password"]  = Hash::make($request['password']);
        }


        try {
            $client_user = User::whereIn("user_type", ["CLIENT", "FINANCIAL", "SCHEDULE"])->findOrFail($request['user_id']);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }


        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
            'company_name' => ['nullable', 'string', 'min:1', 'max:100', 'unique:profiles,company_name,' . $client_user->profile->id],
            'phone' => ['nullable', 'string', 'min:1', 'max:50'],
            'mobile' => ['nullable', 'string', 'min:1', 'max:50'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
        ]);
        if ($profile_validator->fails()) {
           $tt="";
   foreach($profile_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }



        if($client_user->user_type=="CLIENT")
        {

        if ( !is_numeric($request->kvk_number) ) {
            echo "kvk_number must be number.";exit;
        } 

         $str = $request->kvk_number;
        $len = strlen($str);

        if ($len!=8 and $len!=12) 
        {
            echo "kvk_number must be 8 or 12 length.";exit;
        }

        }


        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();

        $profile_validated = array_filter($profile_validated, function ($a) {
            return $a !== null;
        });


        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['nullable', 'string', 'min:1', 'max:255'],
            'city' => ['nullable', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['nullable', 'string', 'min:1', 'max:100'],
            'postcode' => ['nullable', 'string', 'min:1', 'max:100000'],
            'country' => ['nullable', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
           $tt="";
   foreach($address_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $address_validated = $address_validator->validated();
        $address_validated = array_filter($address_validated, function ($a) {
            return $a !== null;
        });


        // validating contact data
        $contact_validator = Validator::make($request->all(), [
            'contact_name' => ['nullable', 'string', 'min:1', 'max:100'],
            'contact_role' => ['nullable', 'string', 'min:1', 'max:100'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'min:1', 'max:100'],
        ]);
        if ($contact_validator->fails()) {
           $tt="";
   foreach($contact_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $contact_validated = $contact_validator->validated();
        if (array_key_exists("contact_name", $contact_validated))
            $contact_validated["name"] = $contact_validated["contact_name"];
        if (array_key_exists("contact_role", $contact_validated))
            $contact_validated["role"] = $contact_validated["contact_role"];
        if (array_key_exists("contact_email", $contact_validated))
            $contact_validated["email"] = $contact_validated["contact_email"];
        if (array_key_exists("contact_phone", $contact_validated))
            $contact_validated["phone"] = $contact_validated["contact_phone"];

        unset($contact_validated["contact_name"]);
        unset($contact_validated["contact_role"]);
        unset($contact_validated["contact_email"]);
        unset($contact_validated["contact_phone"]);

        $contact_validated = array_filter($contact_validated, function ($a) {
            return $a !== null;
        });

        try {
            DB::beginTransaction();
            $client_user->update($user_validated);

 
        User::where(['id'=>$request->user_id])
       ->update([
           'email' =>$request->email,
        ]);


            $client_user->profile()->update($profile_validated);
            $client_user->address()->update($address_validated);


            
        Address::where(['addressable_id'=>$request->user_id,'addressable_type'=>'App\Models\User'])
       ->update([
           'home_number' =>$request->home_number,
        ]);




            $client_user->contact()->update($contact_validated);

            DB::commit();

          echo 1;exit;
        } catch (\Exception $e) {
            DB::rollBack();
            echo  $e->getMessage();exit;
        }
    }
    /* end of client functions */


    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /*  */
    /* employee functions */

  public function documentmanagement()
    {



            if (isset($_GET['idchecker']))
        {
              $users= User::where(["id"=>$_GET['useridssss']])->get();

    $tt=0;
    if ($users[0]->identification==0) 
    {
         $tt=1;
    }


       User::where(['id'=>$_GET['useridssss']])
         ->update([
            'identification' =>$tt,
        ]); 




echo "12";

              exit;
        }

 

 

 


    if (isset($_GET['editexdate']))
        {

              $now = date("Y-m-d");
 
if($_GET['expires_at'] < $now) {
    echo 'date Cant be in the past';exit;
}

              $test= Image::where(["id"=>$_GET['exid']])->update([
                  'expires_at' =>$_GET['expires_at'],
                ]);

              echo 1;exit;
        }



    if (isset($_GET['checkboxdiplome']))
        {
              $test= Image::where(["id"=>$_GET['imageid']])->get();

              $emploee_id=$test[0]->imageable_id;
          

              $test= Image::where(["id"=>$_GET['imageid']])->update([
                  'diplomecheck' =>$_GET['checkboxdiplome'],
                ]);


          $Rh = new Rh;
               
          $Rh::checkalldocconfirmed($emploee_id);


             echo "12";

              exit;
        }


        
    if (isset($_GET['checkbox']))
        {
             
              $test= Image::where(["id"=>$_GET['imageid']])->get();

              $emploee_id=$test[0]->imageable_id;

              $test= Image::where(["id"=>$_GET['imageid']])->update([
                  'documentverified' =>$_GET['checkbox'],
                ]);


          $Rh = new Rh;
               
          $Rh::checkalldocconfirmed($emploee_id);




              exit;
        }


    }


    public function mediatorIndex()
    {

        switch (Auth::user()->user_type) {
            case 'CLIENT':


         die('ooops');


                break;

            case 'SCHEDULE':
            case 'FINANCIAL':
            case 'EMPLOYEE':
           
              die('ooops');
 
                break;

          case 'ADMIN':

        @$first_name = @$_GET['first_name'];
        @$last_name = @$_GET['last_name'];
        @$email = @$_GET['email'];
        @$type = 'MEDIATOR';

   

 
         $mediators = DB::table('users')
    
       ->Where('users.user_type','LIKE','%'.@$type.'%')
       ->join('profiles', 'profiles.user_id', '=', 'users.id')
       ->where('profiles.first_name','LIKE','%'.@$first_name.'%')
       ->Where('profiles.last_name','LIKE','%'.@$last_name.'%')
       ->Where('users.email','LIKE','%'.@$email.'%')
       ->orderBy('profiles.first_name', 'asc')
       ->select('users.*','profiles.first_name','profiles.last_name')
       ->paginate(Auth::user()->paginationnum)->appends(request()->except('page'));  
 

break;

            default:

abort(404);

          break;


 
                break;
        }
     
        return view('dashboard.users.index_mediator')->with(["page_slug" => "users-mediators", "page_title" => "Accountant",
            'mediators'=>@$mediators,
            'first_name'=>@$_GET['first_name'],
            'last_name'=>@$_GET['last_name'],
            'email'=>@$_GET['email'],
       
          

    ]);
    }



    public function accountantIndex()
    {

        switch (Auth::user()->user_type) {
            case 'CLIENT':


         die('ooops');


                break;

            case 'SCHEDULE':
            case 'FINANCIAL':
            case 'EMPLOYEE':
           
              die('ooops');
 
                break;

          case 'ADMIN':

        @$first_name = @$_GET['first_name'];
        @$last_name = @$_GET['last_name'];
        @$email = @$_GET['email'];
        @$type = 'ACCOUNTANT';

   

 
         $accountants = DB::table('users')
    
       ->Where('users.user_type','LIKE','%'.@$type.'%')
       ->join('profiles', 'profiles.user_id', '=', 'users.id')
       ->where('profiles.first_name','LIKE','%'.@$first_name.'%')
       ->Where('profiles.last_name','LIKE','%'.@$last_name.'%')
       ->Where('users.email','LIKE','%'.@$email.'%')
       ->orderBy('profiles.first_name', 'asc')
       ->select('users.*','profiles.first_name','profiles.last_name')
       ->paginate(Auth::user()->paginationnum)->appends(request()->except('page'));  
 

break;

            default:

abort(404);

          break;


 
                break;
        }
     
        return view('dashboard.users.index_accountant')->with(["page_slug" => "users-accountants", "page_title" => "Accountant",
            'accountants'=>@$accountants,
            'first_name'=>@$_GET['first_name'],
            'last_name'=>@$_GET['last_name'],
            'email'=>@$_GET['email'],
       
          

    ]);
    }






















    public function employeesIndex()
    {

if (isset($_GET['deletalljoin'])) 
{
  $res=Joinclient::where(['user_id'=>(int)$_GET['user_id']])->delete();

  return redirect()->back()->with('message', "All List Deleted");
}





  
        $registeras="";
        $registeras1="";

        if (@$_GET['jobtitle']=='healthcare security')
         {
            $registeras="healthcare security";
            $registeras1="";
         }

        if (@$_GET['jobtitle']=='healthcare')
         {
            $registeras="healthcare";
            $registeras1="";
         }


        if (@$_GET['jobtitle']=='twohealthcare')
         {
            $registeras="healthcare";
            $registeras1="healthcare security";
         }

        if (@$_GET['jobtitle']=='twohealthcares')
         {
            $registeras="healthcare security";
            $registeras1="healthcare";
         }

 

        switch (Auth::user()->user_type) {
            case 'CLIENT':


        @$first_name = @$_GET['first_name'];
        @$last_name = @$_GET['last_name'];
        @$email = @$_GET['email'];
        @$status = @$_GET['status'];
        @$user_id = Auth::user()->id;


           



        if (@$status==3) 
        {
               $employees = DB::table('joinclient')

               // ->Where('users.is_activated','LIKE','%'.@$status.'%')
               ->Where('joinclient.client_id','LIKE','%'.@$user_id.'%')
               // ->groupBy('joinclient.user_id')
               ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
               ->join('users', 'joinclient.user_id', '=', 'users.id')
               // ->Where('user_type','EMPLOYEE')
               ->where('profiles.first_name','LIKE','%'.@$first_name.'%')
               ->Where('profiles.last_name','LIKE','%'.@$last_name.'%')
               ->Where('users.email','LIKE','%'.@$email.'%')
               // ->Where('users.user_type','LIKE','%'.@$type.'%')
               //->orderByDesc('users.id')
               ->distinct('users.email')
               ->select(DB::raw('DISTINCT (users.email),profiles.last_name,profiles.first_name,users.is_activated,profiles.registeras,profiles.registeras1'))
               // ->groupBy('joinclient.user_id')
               ->paginate(Auth::user()->paginationnum)->appends(request()->except('page'));

               // dd($employees);
        }
        else
        {
               $employees = DB::table('joinclient')

               ->Where('joinclient.client_id','LIKE','%'.@$user_id.'%')
               ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
               ->join('users', 'joinclient.user_id', '=', 'users.id')
               ->Where('users.is_activated','LIKE','%'.@$status.'%')
               // ->Where('user_type','EMPLOYEE')
               ->where('profiles.first_name','LIKE','%'.@$first_name.'%')
               ->Where('profiles.last_name','LIKE','%'.@$last_name.'%')
               ->Where('users.email','LIKE','%'.@$email.'%')
               // ->Where('users.user_type','LIKE','%'.@$type.'%')
               //->orderByDesc('users.id')
               ->distinct('users.email')
               ->select(DB::raw('DISTINCT (users.email),profiles.last_name,profiles.first_name,users.is_activated,profiles.registeras,profiles.registeras1'))
               ->paginate(Auth::user()->paginationnum)->appends(request()->except('page')); 
        }



                break;

            case 'SCHEDULE':
            case 'FINANCIAL':
           


        @$first_name = @$_GET['first_name'];
        @$last_name = @$_GET['last_name'];
        @$email = @$_GET['email'];
        @$status = @$_GET['status'];
        @$user_id = Auth::user()->client_id;

             


        if (@$status==3) 
        {
               $employees = DB::table('joinclient')

               // ->Where('users.is_activated','LIKE','%'.@$status.'%')
               ->Where('joinclient.client_id','LIKE','%'.@$user_id.'%')
               // ->groupBy('joinclient.user_id')
               ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
               ->join('users', 'joinclient.user_id', '=', 'users.id')
               // ->Where('user_type','EMPLOYEE')
               ->where('profiles.first_name','LIKE','%'.@$first_name.'%')
               ->Where('profiles.last_name','LIKE','%'.@$last_name.'%')
               ->Where('users.email','LIKE','%'.@$email.'%')
               // ->Where('users.user_type','LIKE','%'.@$type.'%')
               //->orderByDesc('users.id')
               ->distinct('users.email')
               ->select(DB::raw('DISTINCT (users.email),profiles.last_name,profiles.first_name,users.is_activated,profiles.registeras,profiles.registeras1'))
               // ->groupBy('joinclient.user_id')
               ->paginate(Auth::user()->paginationnum)->appends(request()->except('page'));

               // dd($employees);
        }
        else
        {
               $employees = DB::table('joinclient')

               ->Where('joinclient.client_id','LIKE','%'.@$user_id.'%')
               ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
               ->join('users', 'joinclient.user_id', '=', 'users.id')
               ->Where('users.is_activated','LIKE','%'.@$status.'%')
               // ->Where('user_type','EMPLOYEE')
               ->where('profiles.first_name','LIKE','%'.@$first_name.'%')
               ->Where('profiles.last_name','LIKE','%'.@$last_name.'%')
               ->Where('users.email','LIKE','%'.@$email.'%')
               // ->Where('users.user_type','LIKE','%'.@$type.'%')
               //->orderByDesc('users.id')
               ->distinct('users.email')
               ->select(DB::raw('DISTINCT (users.email),profiles.last_name,profiles.first_name,users.is_activated,profiles.registeras,profiles.registeras1'))
               ->paginate(Auth::user()->paginationnum)->appends(request()->except('page')); 
        }




                // $employees = Joinclient::where("client_id",Auth::user()->client_id)
                //     // ->where("client_id", Auth::user()->client_id)->where("id", ">", 3)
                //     // ->with(["client", "client.profile"])
                //     ->get()->unique('email');
                break;

          case 'ADMIN':
                  @$first_name = @$_GET['first_name'];
        @$last_name = @$_GET['last_name'];
        @$email = @$_GET['email'];
        @$status = @$_GET['status'];
        @$type = 'EMPLOYEE';

   

if (@$status==3) 
{





          $employees = DB::table('users')
       ->Where('users.user_type','LIKE','%'.@$type.'%')
       ->join('profiles', 'profiles.user_id', '=', 'users.id')
       ->where('profiles.first_name','LIKE','%'.@$first_name.'%')
       ->Where('profiles.last_name','LIKE','%'.@$last_name.'%')
       ->Where('profiles.registeras','LIKE','%'.@$registeras.'%')
       ->Where('profiles.registeras1','LIKE','%'.@$registeras1.'%')
       ->Where('users.email','LIKE','%'.@$email.'%')
       ->orderBy('profiles.first_name', 'asc')
       ->select('users.*','profiles.first_name','profiles.last_name','profiles.registeras','profiles.registeras1')
       ->paginate(Auth::user()->paginationnum)->appends(request()->except('page'));
    




}
else
{
         $employees = DB::table('users')
       ->Where('users.is_activated','LIKE','%'.@$status.'%')
       ->Where('users.user_type','LIKE','%'.@$type.'%')
       ->join('profiles', 'profiles.user_id', '=', 'users.id')
       ->where('profiles.first_name','LIKE','%'.@$first_name.'%')
       ->Where('profiles.registeras','LIKE','%'.@$registeras.'%')
       ->Where('profiles.registeras1','LIKE','%'.@$registeras1.'%')
       ->Where('profiles.last_name','LIKE','%'.@$last_name.'%')
       ->Where('users.email','LIKE','%'.@$email.'%')
       ->orderBy('profiles.first_name', 'asc')
       ->select('users.*','profiles.first_name','profiles.last_name','profiles.registeras','profiles.registeras1')
       ->paginate(Auth::user()->paginationnum)->appends(request()->except('page'));  
}

break;

            default:

abort(404);

          break;


 // dd($employees);

                // $employees = User::where("user_type", "EMPLOYEE")->where("id", ">", 1)
                //     ->with(["client", "client.profile"])->paginate(Auth::user()->paginationnum);
                break;
        }
        // dd($employees[0]->user_id);
        return view('dashboard.users.index_employee')->with(["page_slug" => "users-employees", "page_title" => "Employees",
            "employees" => $employees,
            'jobtitle'=>@$_GET['jobtitle'],
            'first_name'=>@$_GET['first_name'],
            'last_name'=>@$_GET['last_name'],
            'email'=>@$_GET['email'],
            'status'=>@$_GET['status'],
          

    ]);
    }

    public function newEmployeesIndex()
    {
        $employees = User::where("id", ">", 3)->where("user_type", "EMPLOYEE")
            ->with(["client", "client.profile"])->where("is_activated", false)->with(["profile"])->with(["profile", "client", "images"])->paginate(10);
        return view('dashboard.users.index_employee')->with(["page_slug" => "users-new-employees", "page_title" => "New employees", "employees" => $employees]);
    }

 

     public function createmediator()
    {
        return view('dashboard.users.create_mediator');
    }

    public function createmediatorcreate(Request $request)
    {

 
    $res=Rh::checkIBAN($request->iban_number);
     if (!$res)
      {
         echo "IBAN Number Is Invalid";exit;
      }



if ( !is_numeric($request->kvk_number) ) {
    echo "kvk_number must be number.";exit;
} 

 $str = $request->kvk_number;
$len = strlen($str);

if ($len!=8 and $len!=12) 
{
    echo "kvk_number must be 8 or 12 length.";exit;
}


 
        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:1', 'confirmed'],
        ]);
        if ($user_validator->fails()) {
         $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $user_validated = $user_validator->validated();
        $user_validated["password"]  = Hash::make($request['password']);
        $user_validated["user_type"] = "MEDIATOR";

        if (Auth::user()->user_type == "CLIENT") {
            $user_validated["client_id"] = Auth::user()->id;
        } else if (Auth::user()->user_type == "FINANCIAL" || Auth::user()->user_type == "SCHEDULE") {
            $user_validated["client_id"] = Auth::user()->client_id;
        }

        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
            'company_name' => ['required', 'string', 'min:1', 'max:100', 'unique:profiles,company_name'],
            'phone' => ['required', 'string', 'min:1', 'max:50'],
            'mobile' => ['required', 'string', 'min:1', 'max:50'],
            'payrate' => ['required', 'integer', 'min:1'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['required', 'string', 'min:1', 'max:100'],
            'btw_number' => ['required', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:tomorrow'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
        ]);
        if ($profile_validator->fails()) {
            $tt="";
   foreach($profile_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();

        // validating financial data
        $financial_validator = Validator::make($request->all(), [
            'bank_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_number' => ['required', 'string', 'min:1', 'max:255'],
            'iban_holder' => ['required', 'string', 'min:1', 'max:255'],
            'tax_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'terms_of_payment' => ['nullable', 'string', 'max:4000'],
        ]);
        if ($financial_validator->fails()) {

           $tt="";
   foreach($financial_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $financial_validated = $financial_validator->validated();



        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['required', 'string', 'min:1', 'max:255'],
            'city' => ['required', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['required', 'string', 'min:1', 'max:100'],
            'postcode' => ['required', 'string', 'min:1', 'max:100000'],
            'country' => ['required', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
            $tt="";
   foreach($address_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $address_validated = $address_validator->validated();



        try {
            DB::beginTransaction();
            $emoloyee_user = new User();
            $emoloyee_user->fill($user_validated);
            $emoloyee_user->save();

            $role = Role::where('slug', 'employee')->first();
            $permission = Permission::where('slug', 'crud-employee')->first();
            $emoloyee_user->roles()->attach($role);
            $emoloyee_user->permissions()->attach($permission);

            $profile_validated['user_id'] = $emoloyee_user->id;
            $emoloyee_profile = new Profile();
            $emoloyee_profile->fill($profile_validated);
            $emoloyee_profile->save();

            $lastinsertedid= DB::getPdo()->lastInsertId();

           

        //     Financial::where(['user_id'=>$emoloyee_user->id])
        //     ->update([
        //    'iban_number' =>$request->iban_number,
        //    'iban_holder' =>$request->iban_holder,
    
 
        
        // ]);

            $financial_validated['user_id'] = $emoloyee_user->id;
            $emoloyee_financial = new Financial();
            $emoloyee_financial->fill($financial_validated);
            $emoloyee_financial->save();

            $emoloyee_address = new Address();
            $emoloyee_address->addressable()->associate($emoloyee_user);
            $emoloyee_address->fill($address_validated);
            $emoloyee_address->save();

            $lastinsertedidaddress= DB::getPdo()->lastInsertId();

        Address::where(['id'=>$lastinsertedidaddress])
       ->update([
           'home_number' =>$request->home_number,
        ]);



            DB::commit();
              echo 1;exit;
     
        } catch (\Exception $e) {
            DB::rollBack();
           echo  $e->getMessage();
        }
    }





    public function createEmployeeIndex()
    {
        $education_levels = Education::select("title")->get();
        return view('dashboard.users.create_employee')->with(["education_levels" => $education_levels]);
    }


    public function createEmployee(Request $request)
    {

 
    $res=Rh::checkIBAN($request->iban_number);
     if (!$res)
      {
         echo "IBAN Number Is Invalid";exit;
      }


        $registeras[0]="";
        $registeras[1]="";
        $registeras[2]="";
        $registeras[3]="";
        $registeras[4]="";
        //return $request;

  
       $tt=count($request->registeras);
         
// $registeras[0]=$request->registeras;

        for ($i=0; $i < $tt; $i++) 
        { 
            if (isset($request->registeras[$i]) and !empty($request->registeras[$i])) 
            {
            $registeras[$i]=$request->registeras[$i];
            }
        }
      
       
        $educationtitle[0]="";
        $educationtitle[1]="";
        $educationtitle[2]="";
        $educationtitle[3]="";
        $educationtitle[4]="";
        $educationtitle[5]="";
        $educationtitle[6]="";

       $tt=count($request->educationtitle);
        


        for ($i=0; $i < $tt; $i++) 
        { 
            if (isset($request->educationtitle[$i]) and !empty($request->educationtitle[$i])) 
            {
            $educationtitle[$i]=$request->educationtitle[$i];
            }
        }
   // $educationtitle[0]=$request->educationtitle;

 
if ( !is_numeric($request->kvk_number) ) {
    echo "kvk_number must be number.";exit;
} 

 $str = $request->kvk_number;
$len = strlen($str);

if ($len!=8 and $len!=12) 
{
    echo "kvk_number must be 8 or 12 length.";exit;
}




        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:1', 'confirmed'],
        ]);
        if ($user_validator->fails()) {
         $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $user_validated = $user_validator->validated();
        $user_validated["password"]  = Hash::make($request['password']);
        $user_validated["user_type"] = "EMPLOYEE";

        if (Auth::user()->user_type == "CLIENT") {
            $user_validated["client_id"] = Auth::user()->id;
        } else if (Auth::user()->user_type == "FINANCIAL" || Auth::user()->user_type == "SCHEDULE") {
            $user_validated["client_id"] = Auth::user()->client_id;
        }

        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
            'company_name' => ['required', 'string', 'min:1', 'max:100', 'unique:profiles,company_name'],
            'phone' => ['required', 'string', 'min:1', 'max:50'],
            'mobile' => ['required', 'string', 'min:1', 'max:50'],
            'payrate' => ['required', 'integer', 'min:1'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['required', 'string', 'min:1', 'max:100'],
            'btw_number' => ['required', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:tomorrow'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
        ]);
        if ($profile_validator->fails()) {
            $tt="";
   foreach($profile_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();

        // validating financial data
        $financial_validator = Validator::make($request->all(), [
            'bank_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_number' => ['required', 'string', 'min:1', 'max:255'],
            'iban_holder' => ['required', 'string', 'min:1', 'max:255'],
            'tax_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'terms_of_payment' => ['nullable', 'string', 'max:4000'],
        ]);
        if ($financial_validator->fails()) {

           $tt="";
   foreach($financial_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $financial_validated = $financial_validator->validated();



        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['required', 'string', 'min:1', 'max:255'],
            'city' => ['required', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['required', 'string', 'min:1', 'max:100'],
            'postcode' => ['required', 'string', 'min:1', 'max:100000'],
            'country' => ['required', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
            $tt="";
   foreach($address_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $address_validated = $address_validator->validated();



        try {
            DB::beginTransaction();
            $emoloyee_user = new User();
            $emoloyee_user->fill($user_validated);
            $emoloyee_user->save();

            $role = Role::where('slug', 'employee')->first();
            $permission = Permission::where('slug', 'crud-employee')->first();
            $emoloyee_user->roles()->attach($role);
            $emoloyee_user->permissions()->attach($permission);

            $profile_validated['user_id'] = $emoloyee_user->id;
            $emoloyee_profile = new Profile();
            $emoloyee_profile->fill($profile_validated);
            $emoloyee_profile->save();

            $lastinsertedid= DB::getPdo()->lastInsertId();

            Profile::where(['id'=>$lastinsertedid])
            ->update([
           'registeras' =>$registeras[0],
           'registeras1' =>$registeras[1],
           'registeras2' =>$registeras[2],
           'registeras3' =>$registeras[3],
           'registeras4' =>$registeras[4],
           'educationtitle1' =>$educationtitle[0],
           'educationtitle2' =>$educationtitle[1],
           'educationtitle3' =>$educationtitle[2],
           'educationtitle4' =>$educationtitle[3],
           'educationtitle5' =>$educationtitle[4],
           'educationtitle6' =>$educationtitle[5],
        
        ]);


        //     Financial::where(['user_id'=>$emoloyee_user->id])
        //     ->update([
        //    'iban_number' =>$request->iban_number,
        //    'iban_holder' =>$request->iban_holder,
    
 
        
        // ]);

            $financial_validated['user_id'] = $emoloyee_user->id;
            $emoloyee_financial = new Financial();
            $emoloyee_financial->fill($financial_validated);
            $emoloyee_financial->save();

            $emoloyee_address = new Address();
            $emoloyee_address->addressable()->associate($emoloyee_user);
            $emoloyee_address->fill($address_validated);
            $emoloyee_address->save();

            $lastinsertedidaddress= DB::getPdo()->lastInsertId();

        Address::where(['id'=>$lastinsertedidaddress])
       ->update([
           'home_number' =>$request->home_number,
        ]);



            DB::commit();
              echo 1;exit;
     
        } catch (\Exception $e) {
            DB::rollBack();
           echo  $e->getMessage();
        }
    }



    public function updateaccountant($language, $id)
    {
    $user = User::where("id", ">", 3)->where("user_type", "ACCOUNTANT")->with(["profile", "financial", "client"])->findOrFail($id);
    return view('dashboard.users.update_accountant')->with(["user" => $user]);
    }



    public function updateaccountantupdate(Request $request)
    {

 
 



        // return $request;
        // validating user data
        $user_validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            // 'client_id' => 'required|numeric|exists:users,id',
        ]);


        if ($user_validator->fails()) {
            $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $user_validated = $user_validator->validated();

        $user_validated = array_filter($user_validated, function ($a) {
            return $a !== null;
        });
        /* if (isset($user_validated["password"])) {
            $user_validated["password"]  = Hash::make($request['password']);
        } */

        unset($user_validated["password"]);
        $user_validated["user_type"] = "ACCOUNTANT";


        try {
            $employee_user = User::where("user_type", "ACCOUNTANT")->findOrFail($request['user_id']);
        } catch (\Exception $e) {
           echo  $e->getMessage();
        }
 
        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
         
            'phone' => ['nullable', 'string', 'min:1', 'max:50'],
            'mobile' => ['nullable', 'string', 'min:1', 'max:50'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'btw_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            // 'payrate' => ['required', 'integer', 'min:1'],
            'date_of_birth' => ['nullable', 'date', 'before:tomorrow'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
        ]);
        if ($profile_validator->fails()) {
            $tt="";
   foreach($profile_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();

        if (!Auth::user()->hasRole("admin")) {
            unset($profile_validated["payrate"]);
        }

        $profile_validated = array_filter($profile_validated, function ($a) {
            return $a !== null;
        });

        // validating financial data
        $financial_validator = Validator::make($request->all(), [
            'bank_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_number' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_holder' => ['nullable', 'string', 'min:1', 'max:255'],
            'tax_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'terms_of_payment' => ['nullable', 'string', 'max:4000'],
        ]);
        if ($financial_validator->fails()) {
          $tt="";
   foreach($financial_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $financial_validated = $financial_validator->validated();

        $financial_validated = array_filter($financial_validated, function ($a) {
            return $a !== null;
        });



        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['nullable', 'string', 'min:1', 'max:255'],
            'city' => ['nullable', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['nullable', 'string', 'min:1', 'max:100'],
            'postcode' => ['nullable', 'string', 'min:1', 'max:100000'],
            'country' => ['nullable', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
            $tt="";
   foreach($address_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $address_validated = $address_validator->validated();
        $address_validated = array_filter($address_validated, function ($a) {
            return $a !== null;
        });


        try {
            DB::beginTransaction();
            $employee_user->update($user_validated);
            $employee_user->profile()->update($profile_validated);


       


 


if (empty($request->email_verified)) 
{
  $request->email_verified=0;
}


        User::where(['id'=>$request->user_id])
       ->update([
           'email_verified' =>$request->email_verified,
           'caninsertassignment' =>$request->caninsertassignment,
           'email' =>$request->email,
        ]);



            $employee_user->financial()->update($financial_validated);
            $employee_user->address()->update($address_validated);



            $lastinsertedidaddress= DB::getPdo()->lastInsertId();

        Address::where(['addressable_id'=>$request->user_id,'addressable_type'=>'App\Models\User'])
       ->update([
           'home_number' =>$request->home_number,
        ]);




            DB::commit();

            echo  1;exit;
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
    }



    public function createaccountant($language)
    {
     return view('dashboard.users.create_accountant');
    }

    public function createaccountantcreate(Request $request)
    {

 
        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:1', 'confirmed'],
        ]);
        if ($user_validator->fails()) {
         $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $user_validated = $user_validator->validated();
        $user_validated["password"]  = Hash::make($request['password']);
        $user_validated["user_type"] = "ACCOUNTANT";

        if (Auth::user()->user_type == "CLIENT") {
            $user_validated["client_id"] = Auth::user()->id;
        } else if (Auth::user()->user_type == "FINANCIAL" || Auth::user()->user_type == "SCHEDULE") {
            $user_validated["client_id"] = Auth::user()->client_id;
        }

        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
            'phone' => ['nullable', 'string', 'min:1', 'max:50'],
            'mobile' => ['nullable', 'string', 'min:1', 'max:50'],
            'payrate' => ['nullable', 'integer', 'min:1'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'btw_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:tomorrow'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
        ]);
        if ($profile_validator->fails()) {
            $tt="";
   foreach($profile_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();

        // validating financial data
        $financial_validator = Validator::make($request->all(), [
            'bank_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_number' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_holder' => ['nullable', 'string', 'min:1', 'max:255'],
            'tax_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'terms_of_payment' => ['nullable', 'string', 'max:4000'],
        ]);
        if ($financial_validator->fails()) {

           $tt="";
   foreach($financial_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $financial_validated = $financial_validator->validated();



        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['nullable', 'string', 'min:1', 'max:255'],
            'city' => ['nullable', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['nullable', 'string', 'min:1', 'max:100'],
            'postcode' => ['nullable', 'string', 'min:1', 'max:100000'],
            'country' => ['nullable', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
            $tt="";
   foreach($address_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }
        // Retrieve the validated input...
        $address_validated = $address_validator->validated();



        try {
            DB::beginTransaction();
            $emoloyee_user = new User();
            $emoloyee_user->fill($user_validated);
            $emoloyee_user->save();

            $role = Role::where('slug', 'employee')->first();
            $permission = Permission::where('slug', 'crud-employee')->first();
            $emoloyee_user->roles()->attach($role);
            $emoloyee_user->permissions()->attach($permission);

            $profile_validated['user_id'] = $emoloyee_user->id;
            $emoloyee_profile = new Profile();
            $emoloyee_profile->fill($profile_validated);
            $emoloyee_profile->save();

            $lastinsertedid= DB::getPdo()->lastInsertId();

           


        //     Financial::where(['user_id'=>$emoloyee_user->id])
        //     ->update([
        //    'iban_number' =>$request->iban_number,
        //    'iban_holder' =>$request->iban_holder,
    
 
        
        // ]);

            $financial_validated['user_id'] = $emoloyee_user->id;
            $emoloyee_financial = new Financial();
            $emoloyee_financial->fill($financial_validated);
            $emoloyee_financial->save();

            $emoloyee_address = new Address();
            $emoloyee_address->addressable()->associate($emoloyee_user);
            $emoloyee_address->fill($address_validated);
            $emoloyee_address->save();

            $lastinsertedidaddress= DB::getPdo()->lastInsertId();

        Address::where(['id'=>$lastinsertedidaddress])
       ->update([
           'home_number' =>$request->home_number,
        ]);



            DB::commit();
              echo 1;exit;
     
        } catch (\Exception $e) {
            DB::rollBack();
           echo  $e->getMessage();
        }
    }




    public function updatemediator($language, $id)
    {
       
          
                $user = User::where("id", ">", 3)->where("user_type", "MEDIATOR")->with(["profile", "financial", "client"])->findOrFail($id);

           

                return view('dashboard.users.update_mediator')->with(["user" => $user]);
           
        
    }

    public function updatemediatorupdate(Request $request)
    {

 
 

    $res=Rh::checkIBAN($request->iban_number);
     if (!$res)
      {
        echo "IBAN Number Is Invalid";exit;
      }



if ( !is_numeric($request->kvk_number) ) {
    echo "kvk_number must be number.";exit;
} 

 $str = $request->kvk_number;
$len = strlen($str);

if ($len!=8 and $len!=12) 
{
    echo "kvk_number must be 8 or 12 length.";exit;
}
      
        // return $request;
        // validating user data
        $user_validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            // 'client_id' => 'required|numeric|exists:users,id',
        ]);


        if ($user_validator->fails()) {
            $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $user_validated = $user_validator->validated();

        $user_validated = array_filter($user_validated, function ($a) {
            return $a !== null;
        });
        /* if (isset($user_validated["password"])) {
            $user_validated["password"]  = Hash::make($request['password']);
        } */

        unset($user_validated["password"]);
        $user_validated["user_type"] = "MEDIATOR";


        try {
            $employee_user = User::where("user_type", "MEDIATOR")->findOrFail($request['user_id']);
        } catch (\Exception $e) {
           echo  $e->getMessage();
        }
 
        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
            'company_name' => ['required', 'string', 'min:1', 'max:100', 'unique:profiles,company_name,' . $employee_user->profile->id],
            'phone' => ['required', 'string', 'min:1', 'max:50'],
            'mobile' => ['required', 'string', 'min:1', 'max:50'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['required', 'string', 'min:1', 'max:100'],
            'btw_number' => ['required', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            // 'payrate' => ['required', 'integer', 'min:1'],
          
        ]);
        if ($profile_validator->fails()) {
            $tt="";
   foreach($profile_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();

        if (!Auth::user()->hasRole("admin")) {
            unset($profile_validated["payrate"]);
        }

        $profile_validated = array_filter($profile_validated, function ($a) {
            return $a !== null;
        });

        // validating financial data
        $financial_validator = Validator::make($request->all(), [
            'bank_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_number' => ['required', 'string', 'min:1', 'max:255'],
            'iban_holder' => ['required', 'string', 'min:1', 'max:255'],
            'tax_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'terms_of_payment' => ['nullable', 'string', 'max:4000'],
        ]);
        if ($financial_validator->fails()) {
          $tt="";
   foreach($financial_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $financial_validated = $financial_validator->validated();

        $financial_validated = array_filter($financial_validated, function ($a) {
            return $a !== null;
        });



        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['required', 'string', 'min:1', 'max:255'],
            'city' => ['required', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['required', 'string', 'min:1', 'max:100'],
            'postcode' => ['required', 'string', 'min:1', 'max:100000'],
            'country' => ['required', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
            $tt="";
   foreach($address_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $address_validated = $address_validator->validated();
        $address_validated = array_filter($address_validated, function ($a) {
            return $a !== null;
        });


        try {
            DB::beginTransaction();
            $employee_user->update($user_validated);
            $employee_user->profile()->update($profile_validated);


        



    



if (empty($request->email_verified)) 
{
  $request->email_verified=0;
}


        User::where(['id'=>$request->user_id])
       ->update([
           'email_verified' =>$request->email_verified,
           'caninsertassignment' =>$request->caninsertassignment,
           'email' =>$request->email,
        ]);



            $employee_user->financial()->update($financial_validated);
            $employee_user->address()->update($address_validated);



            $lastinsertedidaddress= DB::getPdo()->lastInsertId();

        Address::where(['addressable_id'=>$request->user_id,'addressable_type'=>'App\Models\User'])
       ->update([
           'home_number' =>$request->home_number,
        ]);




            DB::commit();

            echo  1;exit;
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
    }

 

    public function updateEmployeeIndex($language, $id)
    {
       
            try {
                $user = User::where("id", ">", 3)->where("user_type", "EMPLOYEE")->with(["profile", "financial", "client"])->findOrFail($id);

                $clients = User::where("user_type", "CLIENT")->select("id")->with("profile:user_id,first_name,last_name,company_name")->get();
                $education_levels = Education::select("title")->get();

                return view('dashboard.users.update_employee')->with(["user" => $user, "education_levels" => $education_levels, "clients" => $clients]);
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        
    }

    public function updateEmployee(Request $request)
    {

 

  if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
      echo "Invalid email format";exit;
    }

    
if ($request->educationtitle==null) 
{
     echo "education title Is Invalid";exit;
}

if ($request->registeras==null) 
{
     echo "registeras Is Invalid";exit;
}


    $res=Rh::checkIBAN($request->iban_number);
     if (!$res)
      {
        echo "IBAN Number Is Invalid";exit;
      }


       $registeras[0]="";
        $registeras[1]="";
        $registeras[2]="";
        $registeras[3]="";
        $registeras[4]="";
     
        $tt=count($request->registeras);
         
   //$registeras[0]=$request->registeras;

        for ($i=0; $i < $tt; $i++) 
        { 
            if (isset($request->registeras[$i]) and !empty($request->registeras[$i])) 
            {
            $registeras[$i]=$request->registeras[$i];
            }
        }
      
       
        $educationtitle[0]="";
        $educationtitle[1]="";
        $educationtitle[2]="";
        $educationtitle[3]="";
        $educationtitle[4]="";
        $educationtitle[5]="";
        $educationtitle[6]="";
       
       $tt=count($request->educationtitle);
         //  $educationtitle[0]=$request->educationtitle;


        for ($i=0; $i < $tt; $i++) 
        { 
            if (isset($request->educationtitle[$i]) and !empty($request->educationtitle[$i])) 
            {
            $educationtitle[$i]=$request->educationtitle[$i];
            }
        }


if ( !is_numeric($request->kvk_number) ) {
    echo "kvk_number must be number.";exit;
} 

 $str = $request->kvk_number;
$len = strlen($str);

if ($len!=8 and $len!=12) 
{
    echo "kvk_number must be 8 or 12 length.";exit;
}

        // return $request;
        // validating user data
        $user_validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            // 'client_id' => 'required|numeric|exists:users,id',
        ]);


        if ($user_validator->fails()) {
            $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $user_validated = $user_validator->validated();

        $user_validated = array_filter($user_validated, function ($a) {
            return $a !== null;
        });
        /* if (isset($user_validated["password"])) {
            $user_validated["password"]  = Hash::make($request['password']);
        } */

        unset($user_validated["password"]);
        $user_validated["user_type"] = "EMPLOYEE";


        try {
            $employee_user = User::where("user_type", "EMPLOYEE")->findOrFail($request['user_id']);
        } catch (\Exception $e) {
           echo  $e->getMessage();
        }
 
        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
            'company_name' => ['required', 'string', 'min:1', 'max:100', 'unique:profiles,company_name,' . $employee_user->profile->id],
            'phone' => ['required', 'string', 'min:1', 'max:50'],
            'mobile' => ['required', 'string', 'min:1', 'max:50'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['required', 'string', 'min:1', 'max:100'],
            'btw_number' => ['required', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            // 'payrate' => ['required', 'integer', 'min:1'],
            'date_of_birth' => ['required', 'date', 'before:tomorrow'],
            'gender' => ['required', 'in:MALE,FEMALE,OTHER'],
        ]);
        if ($profile_validator->fails()) {
            $tt="";
   foreach($profile_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();

        if (!Auth::user()->hasRole("admin")) {
            unset($profile_validated["payrate"]);
        }

        $profile_validated = array_filter($profile_validated, function ($a) {
            return $a !== null;
        });

        // validating financial data
        $financial_validator = Validator::make($request->all(), [
            'bank_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'iban_number' => ['required', 'string', 'min:1', 'max:255'],
            'iban_holder' => ['required', 'string', 'min:1', 'max:255'],
            'tax_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'terms_of_payment' => ['nullable', 'string', 'max:4000'],
        ]);
        if ($financial_validator->fails()) {
          $tt="";
   foreach($financial_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $financial_validated = $financial_validator->validated();

        $financial_validated = array_filter($financial_validated, function ($a) {
            return $a !== null;
        });



        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['required', 'string', 'min:1', 'max:255'],
            'city' => ['required', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['required', 'string', 'min:1', 'max:100'],
            'postcode' => ['required', 'string', 'min:1', 'max:100000'],
            'country' => ['required', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($address_validator->fails()) {
            $tt="";
   foreach($address_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
        }

        // Retrieve the validated input...
        $address_validated = $address_validator->validated();
        $address_validated = array_filter($address_validated, function ($a) {
            return $a !== null;
        });


        try {
            DB::beginTransaction();
            $employee_user->update($user_validated);
            $employee_user->profile()->update($profile_validated);


        Profile::where(['user_id'=>$request->user_id])
       ->update([
           'registeras' =>"",
           'registeras1' =>"",
           'registeras2' =>"",
           'registeras3' =>"",
           'registeras4' =>"",
           'educationtitle1' =>"",
           'educationtitle2' =>"",
           'educationtitle3' =>"",
           'educationtitle4' =>"",
           'educationtitle5' =>"",
           'educationtitle6' =>"",
        ]);



        Profile::where(['user_id'=>$request->user_id])
       ->update([
           'registeras' =>$registeras[0],
           'registeras1' =>$registeras[1],
           'registeras2' =>$registeras[2],
           'registeras3' =>$registeras[3],
           'registeras4' =>$registeras[4],
           'educationtitle1' =>$educationtitle[0],
           'educationtitle2' =>$educationtitle[1],
           'educationtitle3' =>$educationtitle[2],
           'educationtitle4' =>$educationtitle[3],
           'educationtitle5' =>$educationtitle[4],
           'educationtitle6' =>$educationtitle[5],
        ]);



if (empty($request->email_verified)) 
{
  $request->email_verified=0;
}


        User::where(['id'=>$request->user_id])
       ->update([
           'email_verified' =>$request->email_verified,
           'caninsertassignment' =>$request->caninsertassignment,
           'email' =>$request->email,
        ]);



            $employee_user->financial()->update($financial_validated);
            $employee_user->address()->update($address_validated);



            $lastinsertedidaddress= DB::getPdo()->lastInsertId();

        Address::where(['addressable_id'=>$request->user_id,'addressable_type'=>'App\Models\User'])
       ->update([
           'home_number' =>$request->home_number,
        ]);




            DB::commit();

            echo  1;exit;
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
    }



    public function Checkemailcode(Request $request)
    {

        // validating profile data
        $code_validator = Validator::make($request->all(), [
            'code' => ['nullable', 'string', 'min:6', 'max:6'],
        ]);

        if ($code_validator->fails()) {
            return back()
                ->withErrors($code_validator)
                ->withInput();
        }


$mycode = User::where(["id"=>Auth::user()->id])->get();


$mycodeis=0;

foreach($mycode as $row)
 {
    $mycodeis=$row['email_verify_code'];
} 


 

if ($mycodeis==(int)$request->code) 
{
     User::where(['id'=>Auth::user()->id])
       ->update([
           'email_verified' =>1,
           'email_verified_at' =>carbon::now(),
        ]);
}
else
{


    return redirect()->back()->with('message', "code is invalid");
 
}


return redirect("/home");

    }




  function Checkemail($language)
    {

$can=0;

$cantime=Auth::user()->email_verify_time+300;


 

if ($cantime<time()) 
{
         $t=rand(111111,999999);

       User::where(['id'=>Auth::user()->id])
       ->update([
           'email_verify_code' =>$t,
           'email_verify_time' =>time(),
        ]);



        $details = [
            'title' => "Beste,",
            'body1' =>  "Jouw bevestigingscode is: ",
            'body2' => $t,
            'body3' => "Voer deze code in op het portaal om jouw e-mailadres te bevestigen.",
            'body4' => "Team ZPC",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to(Auth::user()->email)->subject("Email confirmition code"));

}




  return view('getemailcode');





    }



    function activateIndex($language)
    {
 
 
 
        if (Auth::user()->is_activated==2) 
        {
         $user = Auth::user();
        if ($user->user_type == "EMPLOYEE")
         {
            $user = Auth::user();
            $documents = Document::all();
            // dd($documents);
            return view('activate')->with(["user" => $user, "documents" => $documents]);
        }
        else
        {
          return redirect($language . "/home");
        }

            
        }
        else
        if (Auth::user()->is_activated==1) 
        {
             $user = Auth::user();
            // $documents = Document::all();
            // // dd($documents);
              $user = User::where("user_type", "EMPLOYEE")->findOrFail($user->id);
                $documents = Document::all();
                return view('dashboard.users.update_employee_documents')->with(["user" => $user, "documents" => $documents]);

           // return view('activate')->with(["user" => $user, "documents" => $documents]);
        }
        else
        if (Auth::user()->is_activated==0) 
        {
             return view('firstactivate');
        }


        // try {
        //     $user = Auth::user();
        //     $documents = Document::all();
        //     return view('activate')->with(["user" => $user, "documents" => $documents]);
        // } catch (\Exception $e) {
        //     return view('activate');
        // }
    }

    function updateEmployeeDocumentsIndex($language, $id)
    {



if ( Auth::user()->user_type== 'EMPLOYEE') 
{
    if (Auth::user()->id!=$id)
     {
       abort(403);
    }
}

if ( Auth::user()->user_type== 'CLIENT') 
{
   $is=0;
           $res=joinclient::where(['user_id'=>$id,'client_id'=>Auth::user()->id])->get();      

foreach ($res as $row)
 {
    $is=1;
}

if ($is==0) 
{
   abort(403);
}

}

if ( Auth::user()->user_type== 'SCHEDULE' or Auth::user()->user_type== 'FINANCIAL') 
{
    
   $is=0;
           $res=joinclient::where(['user_id'=>$id,'client_id'=>Auth::user()->client_id])->get();      
 
foreach ($res as $row)
 {
    $is=1;
}

if ($is==0) 
{
   abort(403);
}




}
        
       
            try {
                $user = User::where("user_type", "EMPLOYEE")->findOrFail($id);
                $documents = Document::all();
                return view('dashboard.users.update_employee_documents')->with(["user" => $user, "documents" => $documents]);
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        
    }

    function deleteEmployeeDocuments(Request $request)
    {
        if (Auth::user()->hasRole('admin') || (Auth::user()->hasRole('employee') && !Auth::user()->is_activated)) {
            
            $user_validator = Validator::make($request->all(), [
                'id' => 'required|exists:images',
            ]);
            if ($user_validator->fails()) {
                return back()
                    ->withErrors($user_validator)
                    ->withInput();
            }

            $user_validated = $user_validator->validated();

            try {
                DB::beginTransaction();

                $image = Image::findOrFail($user_validated["id"]);

                if (Auth::user()->hasRole('employee') && Auth::user()->id != $image->imageable_id) {
                    abort(403);
                }

                // deleting from server
                // Storage::disk('s3')->delete(config("app.s3_bucket_delete") . $image->url);

                // delete from database
                $image->delete();

                DB::commit();
                return redirect()->back()->with('message', "File deleted successfully.");
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        } else {
            abort(403);
        }
    }

    function updateUserProfile(Request $request)
    {






        $user_validator = Validator::make($request->all(), [
            'image' => 'required|mimes:jpeg,png,jpg|max:2048',
        ]);
        if ($user_validator->fails()) {
            return back()
                ->withErrors($user_validator)
                ->withInput();
        }

        $user_validator = $user_validator->validated();

        try {
            DB::beginTransaction();
            $user = Auth::user();

            // if ($user->profile->profile_url != null) {
            //     // deleting from server
            //     Storage::disk('s3')->delete(config("app.s3_bucket_delete") . $user->profile->profile_url);
            // }

            // $path = Storage::disk('s3')->put('documents', $request->image);
            // $path = Storage::disk('s3')->url($path);
            // $fileName = str_replace(config('app.s3_path'), "", $path);




        $imagesUrl = $this->uploadImage($request->file('image'));
        $imagesUrl=json_encode($imagesUrl);


            $user->profile->profile_url = $imagesUrl;
            // $user->profile->profile_url = $fileName;
            $user->profile->save();

            DB::commit();

            return redirect()->back()->with('message', "Profile photo updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    function updateEmployeeDocuments(Request $request)
    {

             $user_validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'document' => 'required',
                'image' => 'required|mimes:jpeg,png,jpg,pdf,docx|max:2048',
            ]);




            if ($user_validator->fails()) {

                $tt="";
   foreach($user_validator->errors()->all() as $error){
    $tt=$error;
     }
             echo $tt;exit;
            }


$document = $request->document;



 



            $user_validator = $user_validator->validated();


        // if (Auth::user()->hasRole('admin') || (Auth::user()->hasRole('employee') && Auth::user()->is_activated)) {

// if ( Auth::user()->user_type== 'EMPLOYEE' ) 
// {

//         if (Auth::user()->id!=$user_validator['user_id'])
//      {
//        abort(403);
//     }

//     if (Auth::user()->is_activated == 0 )
//      {
//        abort(403);
//     }
// }

if ( Auth::user()->user_type== 'CLIENT') 
{
    abort(403);
   $is=0;
           $res=joinclient::where(['user_id'=>$user_validator['user_id'],'client_id'=>Auth::user()->id])->get();      

foreach ($res as $row)
 {
    $is=1;
}

if ($is==0) 
{
   abort(403);
}

}

if ( Auth::user()->user_type== 'SCHEDULE' or Auth::user()->user_type== 'FINANCIAL') 
{
    abort(403);
   $is=0;
           $res=joinclient::where(['user_id'=>$user_validator['user_id'],'client_id'=>Auth::user()->client_id])->get();      
 
foreach ($res as $row)
 {
    $is=1;
}

if ($is==0) 
{
   abort(403);
}




}

// $date = new DateTime($event['date']);
  $now = date("Y-m-d");
 

if (isset($request->expire_date)) 
{
    if($request->expire_date < $now) {
    echo 'date Cant be in the past';exit;
}
}






 

            try {

                DB::beginTransaction();
                $user = User::where("user_type", "EMPLOYEE")->findOrFail($user_validator['user_id']);
               

                // check for unique data
                // if (Image::where("document_title", $document->title)->where("imageable_id", $user->id)->exists()) {
                //     return redirect()->back()->with('error', "You have already uploaded this file.");
                // }
 

 $imagesUrl = $this->uploadImage($request->file('image'));
        $imagesUrl=json_encode($imagesUrl);
// dd($imagesUrl);

                // $imageName = time() . '.' . $request->image->extension();
             //   $path = Storage::disk('s3')->put('documents', $request->image);
               // $path = Storage::disk('s3')->url($path);
               // $fileName = str_replace(config('app.s3_path'), "", $path);
                
                $image = new Image();
                $image->document_title = $document;
                $image->url = $imagesUrl;
                $image->thumbnail_url = $imagesUrl;
                $image->expires_at = $request->expire_date;
                $image->imageable()->associate($user);
                $image->save();

                DB::commit();


if (Auth::user()->is_activated==2 and Auth::user()->user_type=="EMPLOYEE")
 {
        
$details = [
            'title' => "Beste beheerder,",
            'body1' =>  Auth::user()->email." heeft een actueel document toegevoegd.",
            'body2' => "Log in op het portaal om dit document te controleren.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "Team ZPC",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to("BeheerderZPC@gmail.com")->subject("Nieuwe document"));
}




 






               echo 1;exit;
            } catch (\Exception $e) {
                DB::rollBack();
                echo "Unfortunately, one of your data is not valid. Your submitted date may not be correct.";exit;
            }
       
    }
    /* end of employee functions */


    public function profileIndex($language, $id)
    {
        try {
            $user = User::findOrFail($id);
            switch ($user->user_type) {
                case 'ADMIN':
                    if (!Auth::user()->hasRole('admin')) {
                        abort(403);
                    } else {
                        return view('dashboard.users.profile_admin')->with(['user' => $user]);
                    }
                    break;
                case 'CLIENT':
                case 'SCHEDULE':
                case 'FINANCIAL':
                    if (Auth::user()->hasRole('employee')) {
                        abort(403);
                        exit();
                    } else {
                        $others = User::whereIn("user_type", ["FINANCIAL", "SCHEDULE"])->where("client_id", $user->id)->get();

                        return view('dashboard.users.profile_client')->with(['user' => $user, "others" => $others,'client_idd'=>$user->id]);
                    }
                    break;
                case 'EMPLOYEE':

                    // TODO: add check
                    if (Auth::user()->hasRole('employee') && $user->id != Auth::user()->id) {
                        abort(403);
                    } else {
                        return view('dashboard.users.profile_employee')->with(['user' => $user]);
                    }

                    break;

                default:
                    # code...
                    break;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => "No result found!"]);
        }
    }

 

        protected function uploadImage($file)
    {
        $year = carbon::now()->year;
        $month = carbon::now()->month;
        $day = carbon::now()->day;
        $now = time();
        $imagePath = "/upload/images/{$year}/{$month}/{$day}/{$now}/";
        $filename = $file->getClientOriginalName();
//        $filename =  Carbon::now()->timestamp . '' . $file->getClientOriginalName();
//        $filename =  Carbon::now()->timestamp . $ext;
        $file = $file->move(public_path($imagePath) , $filename);
      
        $sizes = [ "300" , "600" , "900"];
       // $url['images'] = $this->resize($file->getRealPath() , $sizes ,$imagePath ,$filename );
// dd($imagePath.$filename);
        $url['images'] =$imagePath.$filename;
        //$url['thumb'] = $url['images'][$sizes[0]];

        return $url;
    }

    private function resize($path , $sizes , $imagePath , $filename)
    {
        $images['original'] = $imagePath . $filename;

        foreach($sizes as $size)
        {

            $images[$size] = $imagePath . "{$size}_" . $filename;
            Image::make($path)->resize($size, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path($images[$size]));
        }
        return $images;
    }

}


