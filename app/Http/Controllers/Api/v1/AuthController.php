<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use DateTime;
use DatePeriod;
use DateInterval;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\CustomClass\Rh;
use App\CustomClass\AppRh;
use App\Mail\ForgotPassword;
use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Models\Profile;
use Session;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Education;
use App\Models\Financial;
use App\Models\Address;
use App\Models\Contact;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{





    public function createClient(Request $request)
    {

header('Content-Type: application/json');


        $creator_user_type = "CLIENT";
        // "SCHEDULE", "FINANCIAL"

        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:1'],
        ]);
        if ($user_validator->fails()) {
            $result = json_encode(array('data'=>array('msg'=>$user_validator->errors(),'err'=>-1),'status'=>'fail')); echo $result; exit;
        }


        // Retrieve the validated input...
        $user_validated = $user_validator->validated();
        $user_validated["password"]  = Hash::make($request['password']);


        $client_validator = Validator::make($request->all(), [
            'client_company_name' => ['nullable', 'string', 'max:255'],
            'user_type' => ['required', 'in:CLIENT,SCHEDULE,FINANCIAL'],
        ]);
        if ($client_validator->fails()) {
            $result = json_encode(array('data'=>array('msg'=>$client_validator->errors(),'err'=>-1),'status'=>'fail')); echo $result; exit;
        }


        // Retrieve the validated input...
        $client_validator = $client_validator->validated();
        if (array_key_exists("client_company_name", $client_validator)) {
            //$the_client = User::where("email", $client_validator["client_email"])->where("is_activated", true)->where("user_type", "CLIENT")->first();
            $the_client_profile = Profile::where("company_name", $client_validator["client_company_name"])->first();
            if (!$the_client_profile) {
                 $result = json_encode(array('data'=>array('msg'=>"Either client with this email did not find or this client is not activated yet!",'err'=>-1),'status'=>'fail')); echo $result; exit;
            }

 
 


            // maximum number of each departments are 3
            $already_have_one =  User::where("client_id", $the_client_profile->user_id)->where("user_type", $client_validator["user_type"])->count();
            if ($already_have_one >= 9) {
                 $result = json_encode(array('data'=>array('msg'=>"This client already has this department!",'err'=>-1),'status'=>'fail')); echo $result; exit;
            }

            $user_validated["client_id"] = $the_client_profile->user_id;
        } else if ($client_validator["user_type"] == "FINANCIAL" || $client_validator["user_type"] == "SCHEDULE") {
            $result = json_encode(array('data'=>array('msg'=>"Email address of the client is required!",'err'=>-1),'status'=>'fail')); echo $result; exit;
        }

        $creator_user_type = $client_validator["user_type"];
        $user_validated["user_type"] = $creator_user_type;

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


        $result = json_encode(array('data'=>array('msg'=>"This client already has this department!",'err'=>-1),'status'=>'fail')); echo $result; exit;

        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['nullable', 'string', 'min:1', 'max:100'],
            'last_name' => ['nullable', 'string', 'min:1', 'max:100'],
            'company_name' => ['nullable', 'string', 'min:1', 'max:100', 'unique:profiles,company_name'],
            'phone' => ['nullable', 'string', 'min:1', 'max:50'],
            'mobile' => ['nullable', 'string', 'min:1', 'max:50'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:tomorrow'],
        ]);
        if ($profile_validator->fails()) {
            $result = json_encode(array('data'=>array('msg'=>$profile_validator->errors(),'err'=>-1),'status'=>'fail')); echo $result; exit;
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
            $result = json_encode(array('data'=>array('msg'=>$address_validator->errors(),'err'=>-1),'status'=>'fail')); echo $result; exit;
        }
        // Retrieve the validated input...
        $address_validated = $address_validator->validated();




        // validating contact data
        $contact_validator = Validator::make($request->all(), [
            'contact_name' => ['nullable', 'string', 'min:1', 'max:100'],
            'contact_role' => ['nullable', 'string', 'min:1', 'max:100'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'min:1', 'max:100'],
        ]);
        if ($contact_validator->fails()) {
            $result = json_encode(array('data'=>array('msg'=>$contact_validator->errors(),'err'=>-1),'status'=>'fail')); echo $result; exit;
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
            /* $role = Role::where('slug', 'client')->first();
            $permission = Permission::where('slug', 'crud-client')->first(); */
            $client_user->roles()->attach($role);
            $client_user->permissions()->attach($permission);


            $profile_validated['user_id'] = $client_user->id;
            $client_profile = new Profile();
            $client_profile->fill($profile_validated);
            $client_profile->save();



           


if ($creator_user_type=='FINANCIAL' or $creator_user_type=='SCHEDULE') 
{
   $address_validated['address']="-";
   $address_validated['city']="-";


if ($creator_user_type=='SCHEDULE')
 {
    //after registration to schedule

$details = [
            'title' => "Beste planner,",
            'body1' => "Top dat uw bedrijf zich heeft geregistreerd bij Mijn ZPC.",
            'body2' => "Uw account is aangemaakt en word door de beheerders gecontroleerd.",
            'body3' => "U wordt over de verloop per email geïnformeerd.",
            'body4' => "Met vriendelijke groet,",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user_validated['email'])->subject("Welkom!"));


//registration new schedule admin

$details = [
            'title' => "Beste beheerder,",
            'body1' => "Een planner heeft een account aangemaakt.",
            'body2' => "Log in op het portaal om dit account te controleren.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to('beheerderzpc@gmail.com')->subject("Nieuwe registratie!"));
 




}
else
{
   // after registration to finanvial

$details = [
            'title' => "Beste financiële administratie,",
            'body1' => "Top dat uw bedrijf zich heeft geregistreerd bij Mijn ZPC.",
            'body2' => "Uw account is aangemaakt en word door de beheerders gecontroleerd.",
            'body3' => "U wordt over de verloop per email geïnformeerd.",
            'body4' => "Met vriendelijke groet,",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user_validated['email'])->subject("Welkom!"));


//registration new financial to admin

$details = [
            'title' => "Beste beheerder,",
            'body1' => "Een financiële administrateur heeft een account aangemaakt.",
            'body2' => "Log in op het portaal om dit account te controleren.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to('beheerderzpc@gmail.com')->subject("Nieuwe registratie!"));





}







}
else
{

  //after registration to client

$details = [
            'title' => "Beste opdrachtgever,",
            'body1' => "Top dat uw bedrijf zich heeft geregistreerd bij Mijn ZPC.",
            'body2' => "Uw account is aangemaakt en word door de beheerders gecontroleerd.",
            'body3' => "U wordt over de verloop per email geïnformeerd.",
            'body4' => "Met vriendelijke groet,",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user_validated['email'])->subject("Welkom!"));


//registration new Clinet to admin

$details = [
            'title' => "Beste beheerder,",
            'body1' => "Een opdrachtgever heeft een account aangemaakt.",
            'body2' => "Log in op het portaal om dit account te controleren.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to('beheerderzpc@gmail.com')->subject("Nieuwe registratie!"));



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





$result = json_encode(array('data'=>array('msg'=>"New client created successfully.",'err'=>1),'status'=>'fail')); echo $result; exit;


      
        } catch (\Exception $e) {
            DB::rollBack();
            $result = json_encode(array('data'=>array('msg'=>"Unfortunately, one of your data is not valid. Your submitted date may not be correct.",'err'=>-1),'status'=>'fail')); echo $result; exit;
        }
    }

































    public function createEmployee(Request $request)
    {

header('Content-Type: application/json');
        $dateOfBirth = $request->date_of_birth;
        $today = date("Y-m-d");
        $diff = date_diff(date_create($dateOfBirth), date_create($today));


        if ($diff->format('%y') < 16)
        {
          $result = json_encode(array('data'=>array('msg'=>'date of birth must be more than 16 years old.','err'=>-1),'status'=>'fail')); echo $result; exit;
      }



      $res=Rh::checkIBAN($request->iban_number);
      if (!$res)
      {
          $result = json_encode(array('data'=>array('msg'=>'IBAN Number Is Invalid','err'=>-1),'status'=>'fail')); echo $result; exit;
      }



      $healthcarehas=0;
      $healthcaresechas=0;
      $registeras1="";
      $registeras2="";
      $edu1="";
      $edu2="";
      $edu3="";
      $edu4="";
      $edu5="";
      if ($request->page=='employee') 
      {
         if ($request->registeras1=='healthcare' or $request->registeras2=='healthcare')
         {
            $healthcarehas=1;
        }
        if ($request->registeras1=='healthcare security' or $request->registeras2=='healthcare security')
        {
            $healthcaresechas=1;
        }
        if ($healthcarehas==1) 
        {
            $registeras1="healthcare";
            if ($healthcaresechas==1) 
            {
                $registeras2="healthcare security"; 
            }
        }
        else 
            if ($healthcaresechas==1) 
            {
                $registeras1="healthcare security"; 
            }

            $edu1=$request->edu1;
            $edu2=$request->edu2;
            $edu3=$request->edu3;
            $edu4=$request->edu4;
            $edu5=$request->edu5;
        }




        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);
        if ($user_validator->fails()) {
           $result = json_encode(array('data'=>array('msg'=>"Email or password is incorrect",'err'=>-1),'status'=>'fail')); echo $result; exit;
       }


        // Retrieve the validated input...
       $user_validated = $user_validator->validated();
       $user_validated["password"]  = Hash::make($request['password']);
       $user_validated["user_type"] = "EMPLOYEE";

        // return $request->all();
        // validating profile data
       $profile_validator = Validator::make($request->all(), [
        'first_name' => ['nullable', 'string', 'min:1', 'max:100'],
            // 'registeras' => ['nullable', 'string', 'min:1', 'max:100'],
        'last_name' => ['nullable', 'string', 'min:1', 'max:100'],
        'company_name' => ['nullable', 'string', 'min:1', 'max:100', 'unique:profiles,company_name'],
        'phone' => ['nullable', 'string', 'min:1', 'max:50'],
        'mobile' => ['nullable', 'string', 'min:1', 'max:50'],
        'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
        'kvk_number' => ['nullable', 'string', 'min:1', 'max:100'],
        'btw_number' => ['nullable', 'string', 'min:1', 'max:100'],
        'role' => ['nullable', 'string', 'min:1', 'max:255'],
        'date_of_birth' => ['nullable', 'date', 'before:tomorrow'],
        'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
    ]);
       if ($profile_validator->fails()) {
        $result = json_encode(array('data'=>array('msg'=>$profile_validator->errors(),'err'=>-1),'status'=>'fail')); echo $result; exit;
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
     $result = json_encode(array('data'=>array('msg'=>$financial_validator->errors(),'err'=>-1),'status'=>'fail')); echo $result; exit;
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
    $result = json_encode(array('data'=>array('msg'=>$address_validator->errors(),'err'=>-1),'status'=>'fail')); echo $result; exit;
}
        // Retrieve the validated input...
$address_validated = $address_validator->validated();
$address_validated["addressable_type"]="App\Models\User";
try {
    DB::beginTransaction();
    $emoloyee_user = new User();
    $emoloyee_user->fill($user_validated);
    $emoloyee_user->save();

    Profile::create([
        'user_id' =>@$emoloyee_user->id,
        'company_name' =>@$request->company_name,
        'first_name' =>@$request->first_name,
        'last_name' =>@$request->last_name,
        'phone' =>@$request->phone,
        'mobile' =>@$request->mobile,
        'education_title' =>@$edu1,
        'kvk_number' =>@$request->kvk_number,
        'btw_number' =>@$request->btw_number,
        'payrate' =>NULL,
        'role' =>NULL,
        'profile_url' =>NULL,
        'profile_thumbnail_url' =>NULL,
            'date_of_birth' =>@$request->date_of_birth,////like this 1998-10-12
            'gender' =>@$request->gender,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),

        ]);

    $lastinsertedid= DB::getPdo()->lastInsertId();

    Profile::where(['id'=>$lastinsertedid])
    ->update([
     'registeras' =>$registeras1,
     'registeras1' =>$registeras2,
     'registeras2' =>@$registeras2,
     'educationtitle1' =>$edu2,
     'educationtitle2' =>$edu3,
     'educationtitle3' =>$edu4,
     'educationtitle4' =>$edu5,
 ]);

    $role = Role::where('slug', 'employee')->first();
    $permission = Permission::where('slug', 'crud-employee')->first();
    $emoloyee_user->roles()->attach($role);
    $emoloyee_user->permissions()->attach($permission);
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

// freelancer welcome
    $details = [
        'title' => "Beste ZZP-er,",
        'body1' => "Leuk dat je interesse hebt om een deel van ons team te worden.",
        'body2' => "Jouw account is aangemaakt en word door de beheerders gecontroleerd.",
        'body3' => "Je wordt over de verloop per email geïnformeerd.",
        'body4' => "Met vriendelijke groet,",
    ];
    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to($user_validated['email'])->subject("Welkom!"));



// registration new freelancer to admin

    $details = [
        'title' => "Beste beheerder,",
        'body1' => "Een ZZP-er heeft een account aangemaakt.",
        'body2' => "Log in op het portaal om dit account te controleren.",
        'body3' => "Met vriendelijke groet,",
        'body4' => "",
    ];
    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to('beheerderzpc@gmail.com')->subject("Nieuwe registratie!"));


    $result = json_encode(array('data'=>array('msg'=>"New employee created successfully.",'err'=>1),'status'=>'success')); echo $result; exit;
} catch (\Exception $e) {
    DB::rollBack();
    $result = json_encode(array('data'=>array ('msg'=>"Unfortunately, one of your data is not valid. Your submitted date may not be correct.",'err'=>-1),'status'=>'fail')); echo $result; exit;
}
}






public function login()
{

  header('Content-Type: application/json');

      // $_POST['email']="sajiuk122@gmail.com";
      // $_POST['password']="123456";

      //check input cant be null
  $inputsfornullcheck=array("email"=>$_POST['email'],"password"=>$_POST['password']);
  AppRh::checknullinpurs($inputsfornullcheck);
      //check input cant be null

  $user = User::where("email",$_POST['email'])->get();//its by model
  if ($user->isEmpty())
  {
     $result = json_encode(array('data'=>array('msg'=>'There is no such user.'),'status'=>'fail')); echo $result; exit;  
 }
 else
 {
  $pass = Hash::check($_POST['password'],$user[0]->password); 
  if ($pass) 
  {
     $token = openssl_random_pseudo_bytes(32);
     $token = bin2hex($token);
     User::where(['id'=>$user[0]->id])
     ->update([
       'token' =>$token,
   ]);


     $Profiles = Profile::where("user_id",$user[0]->id)->get();

     $images=json_decode($Profiles[0]->profile_url);
     $pic= "https://mijnzpc.com".$images->images;



     $result = json_encode(array('data'=>array ("pic"=>$pic,"first_name"=>$Profiles[0]->first_name,"last_name"=>$Profiles[0]->last_name,"email"=>$user[0]->email,"token"=>$token,"is_activated"=>$user[0]->is_activated,"email_verified"=>$user[0]->email_verified),'status'=>'success')); echo $result; exit;
 } 
 else 
 {
     $result = json_encode(array('data'=>array ('msg'=>'There is no such user.'),'status'=>'fail')); echo $result; exit;
 }
}


}















}
