<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Contact;
use App\Models\Education;
use App\Models\Financial;
use App\Models\Permission;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Mail\ForgotPassword;
use App\Mail\WelcomeEmail;
use App\CustomClass\Rh;
class GuestController extends Controller
{

 

    public function emailtoallfreelancers()
    {


die('2');
 


      $scach = User::where(["client_id"=>162])->get();

    foreach ($scach as $row)
     {
        echo $row->id;
    }



die('2');
      $user = User::where(["user_type"=>"EMPLOYEE"])->get();
      $emails=array();

      foreach ($user as $row) 
      {
          array_push($emails,$row->email);
      }


    $details = [
                'title' => "Beste ZPC’ers,",
                'body1' => "Het is weer tijd voor een welverdiend uitje! We zijn blij met jullie inzet en enthousiasme en dat willen we nog eens benadrukken. 
Voor diegene die via ZPC minimaal 1 dienst hebben gedraaid, wij willen jullie bij deze uitnodigen voor ons gezellig uitje!

Het uitje zal plaatsvinden op vrijdag 2 december van 20:00 tot +/- 2:00. Locatie wordt nog bekend gemaakt. 

Tijdens deze avond is er een dj aanwezig en zijn ook lekkere hapjes en drankjes.

Graag horen wij VOOR 24 oktober of je aanwezig kan zijn, dit kan aan de hand van de onderstaande datum prikker in te vullen op aanwezig of afwezig!
Als je aangeeft dat je komt, dan verwachten we ook dat je komt! Hier zijn namelijk kosten aan verbonden

Allen nogmaals enorm bedankt voor jullie inzet!

",
                'body2' => "Voor alle vragen over het uitje stuur een bericht naar Aysa Faraji (06-83560131)",
                'body3' => "Team ZPC",
                'body4' => "https://datumprikker.nl/pcxkvaxhppm4sjq2",
            ];
    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to("mail@mijnzpc.com")->bcc($emails)->subject("Een welverdiend uitje!"));





    }







  public function downloadd($id,$table) {

if ($table=='mydic')
 {
  
$image = Image::where(["id"=>$id])->get();


     $images=json_decode(@$image[0]->url);
    $tt= @$images->images;

 

 }



    $file_path = public_path($tt);
    return response()->download($file_path);
  }






    public function checkexperationdocumenttoday()
    {
 


        $currentDateTime = Carbon::now();

        $newDateTime = Carbon::now();
 
       $ex=(explode(" ",$newDateTime));
       
       $todayinemail=$ex[0];
       $today=$ex[0]." 00:00:00";



$extrtr=(explode("-",$todayinemail));
 
$todayinemail=$extrtr[2]."-".$extrtr[1]."-".$extrtr[0];



      $emails=array();



      $images = Image::where(["expires_at"=>$today,'document_title'=>@$_GET['document_title']])->get();

$emailstoadmin="";


      foreach ($images as $row) 
      {
        $user = User::where(["id"=>$row->imageable_id])->get();

          array_push($emails,$user[0]->email);


        User::where(["id"=>$user[0]->id])
       ->update([
           'is_activated' =>1,
        ]);


       $emailstoadmin.=$user[0]->email.",";


      }
 
 
if ($emailstoadmin!="")
 {

//Account deactivated deu to document experation freelancer to admin

$details = [
            'title' => "Beste beheerder,",
            'body1' => "Account van ".$emailstoadmin." is gedeactiveerd vanwege verloopdatum van documenten.",
            'body2' => "Log in op het portaal om de documenten te controleren.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "Team ZPC",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to('beheerderzpc@gmail.com')->subject("Users gedeactiveerd!. Type document: ".@$_GET['document_title']));

}





$details = [
            'title' => "Beste ZPC-er,",
            'body1' => "Jouw account is gedeactiveerd vanwege verloopdatum van jou documenten.",
            'body2' => "Neem contact op met ons voor meer informatie.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "Team ZPC",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($emails)->subject("Account gedeactiveerd! Document: ".@$_GET['document_title']));




    }




    public function sendtimes()
    {

      $user = User::where(["user_type"=>"EMPLOYEE"])->get();
      $emails=array();

      foreach ($user as $row) 
      {
          array_push($emails,$row->email);
      }


$details = [
            'title' => "Beste ZPC-er,",
            'body1' => "Jouw gewerkte uren van de afgelopen maand kunnen verstuurd worden.",
            'body2' => "Log in op het portaal om de uren te bekijken en op te sturen.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($emails)->subject("Uren accortdatie"));





    }




    public function checkexperationdocument1week()
    {
 


        $currentDateTime = Carbon::now();

        $newDateTime = Carbon::now()->addDays(6);
 
       $ex=(explode(" ",$newDateTime));
       
       $todayinemail=$ex[0];
       $today=$ex[0]." 00:00:00";



        $extrtr=(explode("-",$todayinemail));
         
        $todayinemail=$extrtr[2]."-".$extrtr[1]."-".$extrtr[0];



      $emails=array();


if ($_GET['document_title']=="4") 
{
   $_GET['document_title']="Klachtenportal  (WKKGZ)";
}

      $emailstoadmin="";
      $images = Image::where(["expires_at"=>$today,'document_title'=>@$_GET['document_title']])->get();
      foreach ($images as $row) 
      {
        $user = User::where(["id"=>$row->imageable_id])->get();

          array_push($emails,$user[0]->email);

            $emailstoadmin.=$user[0]->email.",";


      }
 
 

$details = [
            'title' => "Beste ZPC-er,",
            'body1' => "Jouw document verloopt op ".$todayinemail.". Log in op het portaal om een actueel document toe te voegen.",
            'body2' => "Let op! Na het verlopen  van de datum wordt jouw account automatisch gedeactiveerd.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($emails)->subject("Herinnering Verloopdatum. Type document: ".@$_GET['document_title']));






 
 
 
if ($emailstoadmin!="")
 {

//Account deactivated deu to document experation freelancer to admin


$details = [
            'title' => "Beste beheerder,",
            'body1' => "Een document van ".$emailstoadmin." verloopt over 7 dagen.",
            'body2' => "Met vriendelijke groet,",
            'body3' => "Team ZPC",
           
        ];

 
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to('beheerderzpc@gmail.com')->subject("Verloopdatum document. Type document: ".@$_GET['document_title']));

}











    }

    public function checkexperationdocument1month()
    {


     $currentDateTime = Carbon::now();

        $newDateTime = Carbon::now()->addDays(30);
 
       $ex=(explode(" ",$newDateTime));
       
       $todayinemail=$ex[0];
       $today=$ex[0]." 00:00:00";



$extrtr=(explode("-",$todayinemail));
 
$todayinemail=$extrtr[2]."-".$extrtr[1]."-".$extrtr[0];



      $emails=array();

 $emailstoadmin="";

      $images = Image::where(["expires_at"=>$today,'document_title'=>@$_GET['document_title']])->get();
      foreach ($images as $row) 
      {
        $user = User::where(["id"=>$row->imageable_id])->get();

          array_push($emails,$user[0]->email);

          $emailstoadmin.=$user[0]->email.",";


      }
 
 

$details = [
            'title' => "Beste ZPC-er,",
            'body1' => "Jouw document verloopt op ".$todayinemail.".",
            'body2' => "Log in op het portaal om een actueel document toe te voegen.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($emails)->subject("Verloopdatum document. Type document: ".@$_GET['document_title']));



if ($emailstoadmin!="")
 {

//Account deactivated deu to document experation freelancer to admin


$details = [
            'title' => "Beste beheerder,",
            'body1' => "Een document van ".$emailstoadmin." verloopt over 1 maand.",
            'body2' => "Met vriendelijke groet,",
            'body3' => "Team ZPC",
           
        ];

 
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to('beheerderzpc@gmail.com')->subject("Verloopdatum document. Type document: ".@$_GET['document_title']));

}




 
    }


    public function formsIndex()
    {
        return view('forms');
    }

    public function termsAndConditionsIndex()
    {
        return view('legals.termsAndConditions');
    }

    public function privacyAndPolicyIndex()
    {
        return view('legals.privacyAndPolicy');
    }

    /* employee section */
    public function employeeIndex()
    {
        $education_levels = Education::select("title")->get();
        return view('forms.employee')->with(['education_levels' => $education_levels]);
    }

    public function createEmployee(Request $request)
    {


$dateOfBirth = $request->date_of_birth;
$today = date("Y-m-d");
$diff = date_diff(date_create($dateOfBirth), date_create($today));
 

     if ($diff->format('%y') < 16)
      {
          return redirect()->back()->withInput()->with('error',"date of birth must be more than 16 years old.");
      }



$str = $request->kvk_number;
$len = strlen($str);

if ($len!=8 and $len!=12) 
{
    return redirect()->back()->withInput()->with('error',"kvk_number must be 8 or 12 length.");
}

 




    $res=Rh::checkIBAN($request->iban_number);
     if (!$res)
      {
          return redirect()->back()->withInput()->with('error',"IBAN Number Is Invalid");
      }


        $registeras[0]="";
        $registeras[1]="";
        $registeras[2]="";
        $registeras[3]="";
        $registeras[4]="";
        //return $request;
        $tt=count($request->registeras);
        //echo $request->registeras[0];


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
        //echo $request->registeras[0];


        for ($i=0; $i < $tt; $i++) 
        { 
            if (isset($request->educationtitle[$i]) and !empty($request->educationtitle[$i])) 
            {
            $educationtitle[$i]=$request->educationtitle[$i];
            }
        }


    

        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', "confirmed", 'unique:users'],
            'password' => ['required', 'string', 'min:1', 'confirmed'],
        ]);
        if ($user_validator->fails()) {
            return back()
                ->withErrors($user_validator)
                ->withInput();
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
            return back()
                ->withErrors($profile_validator)
                ->withInput();
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
            return back()
                ->withErrors($financial_validator)
                ->withInput();
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
            return back()
                ->withErrors($address_validator)
                ->withInput();
        }
        // Retrieve the validated input...
        $address_validated = $address_validator->validated();



        try {
            DB::beginTransaction();
            $emoloyee_user = new User();
            $emoloyee_user->fill($user_validated);
            $emoloyee_user->save();

            // $profile_validated['user_id'] = $emoloyee_user->id;
            // $emoloyee_profile = new Profile();
            // $emoloyee_profile->fill($profile_validated);
            // $emoloyee_profile->save();

        // return $request->registeras;

         Profile::create([
            'user_id' =>$emoloyee_user->id,
            'company_name' =>$request->company_name,
            'first_name' =>$request->first_name,
            'last_name' =>$request->last_name,
            'phone' =>$request->phone,
            'mobile' =>$request->mobile,
            'education_title' =>$educationtitle[0],
            'kvk_number' =>$request->kvk_number,
            'btw_number' =>$request->btw_number,
            'payrate' =>NULL,
            'role' =>NULL,
            'profile_url' =>NULL,
            'profile_thumbnail_url' =>NULL,
            'date_of_birth' =>$request->date_of_birth,
            'gender' =>$request->gender,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
 
        ]);

        $lastinsertedid= DB::getPdo()->lastInsertId();

        Profile::where(['id'=>$lastinsertedid])
       ->update([
           'registeras' =>$registeras[0],
           'registeras1' =>$registeras[1],
           'registeras2' =>$registeras[2],
           'registeras3' =>$registeras[3],
           'registeras4' =>$registeras[4],
           'educationtitle1' =>$educationtitle[1],
           'educationtitle2' =>$educationtitle[2],
           'educationtitle3' =>$educationtitle[3],
           'educationtitle4' =>$educationtitle[4],
           'educationtitle5' =>$educationtitle[5],
           'educationtitle6' =>$educationtitle[6],
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






            return redirect()->back()->with('success', "New employee created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error',"Unfortunately, one of your data is not valid. Your submitted date may not be correct.");
        }
    }


    /* client section */
    public function clientIndex()
    {
        return view('forms.client');
    }

    public function financialIndex()
    {
        return view('forms.financial');
    }

    public function mediatorIndex()
    {
        $education_levels = Education::select("title")->get();
        return view('forms.mediator')->with(['education_levels' => $education_levels]);
    }
    public function createmediator(Request $request)
    {

 
 




    $res=Rh::checkIBAN($request->iban_number);
     if (!$res)
      {
          return redirect()->back()->withInput()->with('error',"IBAN Number Is Invalid");
      }


       $str = $request->kvk_number;
$len = strlen($str);

if ($len!=8 and $len!=12) 
{
    return redirect()->back()->withInput()->with('error',"kvk_number must be 8 or 12 length.");
}


    

        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', "confirmed", 'unique:users'],
            'password' => ['required', 'string', 'min:1', 'confirmed'],
        ]);
        if ($user_validator->fails()) {
            return back()
                ->withErrors($user_validator)
                ->withInput();
        }
        // Retrieve the validated input...
        $user_validated = $user_validator->validated();
        $user_validated["password"]  = Hash::make($request['password']);
        $user_validated["user_type"] = "MEDIATOR";

        // return $request->all();
        // validating profile data
        $profile_validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            // 'registeras' => ['nullable', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
            'company_name' => ['nullable', 'string', 'min:1', 'max:100', 'unique:profiles,company_name'],
            'phone' => ['required', 'string', 'min:1', 'max:50'],
            'mobile' => ['required', 'string', 'min:1', 'max:50'],
            'education_title' => ['nullable', 'string', 'min:1', 'max:100', "exists:educations,title"],
            'kvk_number' => ['required', 'string', 'min:1', 'max:100'],
            'btw_number' => ['nullable', 'string', 'min:1', 'max:100'],
            'role' => ['nullable', 'string', 'min:1', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:tomorrow'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
        ]);
        if ($profile_validator->fails()) {
            return back()
                ->withErrors($profile_validator)
                ->withInput();
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
            return back()
                ->withErrors($financial_validator)
                ->withInput();
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
            return back()
                ->withErrors($address_validator)
                ->withInput();
        }
        // Retrieve the validated input...
        $address_validated = $address_validator->validated();



        try {
            DB::beginTransaction();
            $emoloyee_user = new User();
            $emoloyee_user->fill($user_validated);
            $emoloyee_user->save();

            // $profile_validated['user_id'] = $emoloyee_user->id;
            // $emoloyee_profile = new Profile();
            // $emoloyee_profile->fill($profile_validated);
            // $emoloyee_profile->save();

// return $request->registeras;

         Profile::create([
            'user_id' =>$emoloyee_user->id,
            'company_name' =>$request->company_name,
            'first_name' =>$request->first_name,
            'last_name' =>$request->last_name,
            'phone' =>$request->phone,
            'mobile' =>$request->mobile,
            'kvk_number' =>$request->kvk_number,
            'btw_number' =>$request->btw_number,
            'payrate' =>NULL,
            'role' =>NULL,
            'profile_url' =>NULL,
            'profile_thumbnail_url' =>NULL,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
 
        ]);

        $lastinsertedid= DB::getPdo()->lastInsertId();

       


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

 
 




            return redirect()->back()->with('success', "New mediator created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error',"Unfortunately, one of your data is not valid. Your submitted date may not be correct.");
        }
    }











    public function accountantIndex()
    {
        return view('forms.accountant');
    }

    public function createaccountant(Request $request)
    {
        $creator_user_type = "CLIENT";
        // "SCHEDULE", "FINANCIAL"

        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'confirmed', 'unique:users'],
            'password' => ['required', 'string', 'min:1', 'confirmed'],
        ]);
        if ($user_validator->fails()) {
            return back()
                ->withErrors($user_validator)
                ->withInput();
        }
        // Retrieve the validated input...
        $user_validated = $user_validator->validated();
        $user_validated["password"]  = Hash::make($request['password']);


        $creator_user_type = "Accountant";
        $user_validated["user_type"] = $creator_user_type;

        switch ($creator_user_type) {
            case 'Accountant':
                $role = Role::where('slug', 'financial')->first();
                $permission = Permission::where('slug', 'crud-financial')->first();
                break;
        }
       



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
            return back()
                ->withErrors($profile_validator)
                ->withInput();
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
            return back()
                ->withErrors($address_validator)
                ->withInput();
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
            return back()
                ->withErrors($contact_validator)
                ->withInput();
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



           

 
           $address_validated['address']="-";
           $address_validated['city']="-";
         


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


 







            return redirect()->back()->with('success', "New accountant created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }








    public function scheduleIndex()
    {
        return view('forms.schedule');
    }

    public function createClient(Request $request)
    {


 



        
        $creator_user_type = "CLIENT";
        // "SCHEDULE", "FINANCIAL"

        // validating user data
        $user_validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'confirmed', 'unique:users'],
            'password' => ['required', 'string', 'min:1', 'confirmed'],
        ]);
        if ($user_validator->fails()) {
            return back()
                ->withErrors($user_validator)
                ->withInput();
        }
        // Retrieve the validated input...
        $user_validated = $user_validator->validated();
        $user_validated["password"]  = Hash::make($request['password']);



        $client_validator = Validator::make($request->all(), [
            'client_company_name' => ['nullable', 'string', 'max:255'],
            'user_type' => ['required', 'in:CLIENT,SCHEDULE,FINANCIAL'],
        ]);
        if ($client_validator->fails()) {
            return back()
                ->withErrors($client_validator)
                ->withInput();
        }
        // Retrieve the validated input...
        $client_validator = $client_validator->validated();
        if (array_key_exists("client_company_name", $client_validator)) {
            //$the_client = User::where("email", $client_validator["client_email"])->where("is_activated", true)->where("user_type", "CLIENT")->first();
            $the_client_profile = Profile::where("company_name", $client_validator["client_company_name"])->first();
            if (!$the_client_profile) {
                return redirect()->back()->withInput()->with('error', "Either client with this email did not find or this client is not activated yet!");
            }

            /* $the_client = User::where("email", $client_validator["client_company_name"])->where("is_activated", true)->where("user_type", "CLIENT")->first();
            if (!$the_client) {
                return redirect()->back()->withInput()->with('error', "Either client with this email did not find or this client is not activated yet!");
            } */


            // maximum number of each departments are 3
            $already_have_one =  User::where("client_id", $the_client_profile->user_id)->where("user_type", $client_validator["user_type"])->count();
            if ($already_have_one >= 9) {
                return redirect()->back()->withInput()->with('error', "This client already has this department!");
            }

            $user_validated["client_id"] = $the_client_profile->user_id;
        } else if ($client_validator["user_type"] == "FINANCIAL" || $client_validator["user_type"] == "SCHEDULE") {
            return redirect()->back()->withInput()->with('error', "Email address of the client is required!");
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


if ($user_validated["user_type"]=="CLIENT") 
{
            $str = $request->kvk_number;
        $len = strlen($str);

        if ($len!=8 and $len!=12) 
        {
            return redirect()->back()->withInput()->with('error',"kvk_number must be 8 or 12 length.");
        }

}










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
            return back()
                ->withErrors($profile_validator)
                ->withInput();
        }
        // Retrieve the validated input...
        $profile_validated = $profile_validator->validated();



if ($user_validated["user_type"]=="CLIENT") 
{
        // validating address data
        $address_validator = Validator::make($request->all(), [
            'address' => ['required', 'string', 'min:1', 'max:255'],
            'city' => ['nullable', 'string', 'min:1', 'max:255'],
            'address_extra' => ['nullable', 'string', 'min:1', 'max:100'],
            'state' => ['nullable', 'string', 'min:1', 'max:100'],
            'postcode' => ['nullable', 'string', 'min:1', 'max:100000'],
            'country' => ['nullable', 'string', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
}
else
{
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
}





        if ($address_validator->fails()) {
            return back()
                ->withErrors($address_validator)
                ->withInput();
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
            return back()
                ->withErrors($contact_validator)
                ->withInput();
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



            /* $financial_validated['user_id'] = $client_user->id;
            $client_financial = new Financial();
            $client_financial->fill($financial_validated);
            $client_financial->save(); */


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



// client welcome
// $details = [
//             'title' => "Beste,",
//             'body1' => "Leuk dat je interesse hebt om een deel van ons team te worden.",
//             'body2' => "Jouw account is aangemaakt en word door de beheerders gecondoleerd.",
//             'body3' => "Zodra er wijziging zijn word je per email geïnformeerd.",
//             'body4' => "Met vriendelijke groet,",
//             'body5' => "Team ZPC",
//         ];
// \Mail::send((new \App\Mail\WelcomeEmail($details))
//     ->to($user_validated['email'])->subject("Welkom!")); 










            return redirect()->back()->with('success', "New client created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
