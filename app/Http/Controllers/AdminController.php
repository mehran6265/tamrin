<?php

namespace App\Http\Controllers;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Models\Application;
use App\Models\Assignment;
use App\Models\Cancellation;
use App\Models\Configuration;
use App\Models\Suggestionassignments;
use App\Models\Department;
use App\Models\Preaggrement;
use App\Models\User;
use App\Models\Times;
use App\Models\Financial;
use App\Models\Credit_note;
use App\Models\Address;
use App\Models\Profile;
use App\Models\Invoice;
use App\Models\Joinclient;
use App\Models\Joindepartment;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\CustomClass\Rh;
use App\Mail\ForgotPassword;
use App\Mail\WelcomeEmail;
use App\Models\Beforeinvoice;
use Illuminate\Support\Facades\Storage;
use File;
use App\Models\Image;
use Session;
use Redirect;
class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


         public function sendingemail()
    {
        return view('dashboard.sendingemail');
    }

        public function sendingemailcreate(Request $request)
    {
      
 

  

if ($request->type=="Contact")
 {
             $details = [
                'title' => @$request->title,
                'body1' => @$request->text1,
                'body2' => @$request->text2,
                'body3' => @$request->text3,
                'body4' => @$request->text4,
            ];
    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to($request->email)->subject($request->subject));    

}


 
 


if ($request->type=="Employee")
 {
    $User = User::where('user_type','EMPLOYEE')->select("email")->get();
    $emails=array();
 
    foreach ($User as $row) 
    {
       array_push($emails,$row->email);
    }


    $details = [
        'title' => @$request->title,
        'body1' => @$request->text1,
        'body2' => @$request->text2,
        'body3' => @$request->text3,
        'body4' => @$request->text4,
    ];

    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to("mail@mijnzpc.com")->bcc($emails)->subject($request->subject));   
}




if ($request->type=="Clients")
 {
    $User = User::where('user_type','CLIENT')->select("email")->get();
    $emails=array();

    foreach ($User as $row) 
    {
       array_push($emails,$row->email);
    }


    $details = [
        'title' => @$request->title,
        'body1' => @$request->text1,
        'body2' => @$request->text2,
        'body3' => @$request->text3,
        'body4' => @$request->text4,
    ];

    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to("mail@mijnzpc.com")->bcc($emails)->subject($request->subject)); 
}



if ($request->type=="SCHEDULE")
 {
    $User = User::where('user_type','SCHEDULE')->select("email")->get();
    $emails=array();

    foreach ($User as $row) 
    {
       array_push($emails,$row->email);
    }


    $details = [
        'title' => @$request->title,
        'body1' => @$request->text1,
        'body2' => @$request->text2,
        'body3' => @$request->text3,
        'body4' => @$request->text4,
    ];

    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to("mail@mijnzpc.com")->bcc($emails)->subject($request->subject));   
}


if ($request->type=="FINANCIAL")
 {
    $User = User::where('user_type','FINANCIAL')->select("email")->get();
    $emails=array();

    foreach ($User as $row) 
    {
       array_push($emails,$row->email);
    }


    $details = [
        'title' => @$request->title,
        'body1' => @$request->text1,
        'body2' => @$request->text2,
        'body3' => @$request->text3,
        'body4' => @$request->text4,
    ];

    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to("mail@mijnzpc.com")->bcc($emails)->subject($request->subject));    
}



if ($request->type=="Healthcare")
 {


  $User = DB::table('profiles')
               ->Where('profiles.registeras',"healthcare")
               ->join('users', 'profiles.user_id', '=', 'users.id')
               ->distinct('users.email')
               ->select(DB::raw('DISTINCT (users.email),users.id'))
               ->get();


    $emails=array();

    foreach ($User as $row) 
    {
       array_push($emails,$row->email);
    }


    $details = [
        'title' => @$request->title,
        'body1' => @$request->text1,
        'body2' => @$request->text2,
        'body3' => @$request->text3,
        'body4' => @$request->text4,
    ];

    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to("mail@mijnzpc.com")->bcc($emails)->subject($request->subject));    
}


if ($request->type=="HealthcareSecurity")
 {


               $User1 = DB::table('profiles')
               ->Where('profiles.registeras',"healthcare security")
               ->join('users', 'profiles.user_id', '=', 'users.id')
               ->distinct('users.email')
               ->select(DB::raw('DISTINCT (users.email),users.id'))
               ->get();


               $User2 = DB::table('profiles')
               ->Where('profiles.registeras1',"healthcare security")
               ->join('users', 'profiles.user_id', '=', 'users.id')
               ->distinct('users.email')
               ->select(DB::raw('DISTINCT (users.email),users.id'))
               ->get();



    $emails=array();

    foreach ($User1 as $row) 
    {
       array_push($emails,$row->email);
    }

    foreach ($User2 as $row) 
    {
       array_push($emails,$row->email);
    }

    
    $details = [
        'title' => @$request->title,
        'body1' => @$request->text1,
        'body2' => @$request->text2,
        'body3' => @$request->text3,
        'body4' => @$request->text4,
    ];

    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to("mail@mijnzpc.com")->bcc($emails)->subject($request->subject));    
}
 echo 1;exit;

    }


       public function userlogIndex()
    {
        $User_log = User_log::paginate(Auth::user()->paginationnum);
        return view('dashboard.users.userlog')->with(["User_log" => $User_log]);
    }



      public function usersteplogIndex()
    {

        @$first_name = @$_GET['first_name'];
        @$last_name = @$_GET['last_name'];
        @$page = @$_GET['page'];
        @$function = @$_GET['function'];
        @$description = @$_GET['description'];
        @$assignments_id = @$_GET['assignments_id'];
        @$invoice_id = @$_GET['invoice_id'];
        @$times_id = @$_GET['times_id'];
        @$agreement_id = @$_GET['agreement_id'];
        @$created_at = @$_GET['created_at'];
        @$updated_at = @$_GET['updated_at'];



                $Step_log = DB::table('step_log')

               // ->Where('joinclient.client_id','LIKE','%'.@$user_id.'%')
               ->join('profiles', 'profiles.user_id', '=', 'step_log.user_id')

               ->where('profiles.first_name','LIKE','%'.@$first_name.'%')
               ->Where('profiles.last_name','LIKE','%'.@$last_name.'%')

                
               ->Where('step_log.function','LIKE','%'.@$function.'%')
               ->Where('step_log.description','LIKE','%'.@$description.'%')
               ->Where('step_log.assignments_id','LIKE','%'.@$assignments_id.'%')
               ->Where('step_log.invoice_id','LIKE','%'.@$invoice_id.'%')
               ->Where('step_log.times_id','LIKE','%'.@$times_id.'%')
               ->Where('step_log.agreement_id','LIKE','%'.@$agreement_id.'%')
               ->Where('step_log.created_at','LIKE','%'.@$created_at.'%')
               ->Where('step_log.updated_at','LIKE','%'.@$updated_at.'%')
               ->orderByDesc('step_log.id')
                ->select(DB::raw('DISTINCT (step_log.id),profiles.last_name,profiles.first_name,step_log.*'))
               ->paginate(Auth::user()->paginationnum)->appends(request()->except('page'));





        // $Step_log = Step_log::orderByDesc('id')->paginate(Auth::user()->paginationnum);
        return view('dashboard.usersteplog')->with(["Step_log" => $Step_log]);
    }



          public function charts()
    {


if (!isset($_GET['year'])) 
{
    return redirect("/".app()->getLocale()."/users/charts?year=".date('Y'));
}

if (!isset($_GET['month'])) 
{
    return redirect("/".app()->getLocale()."/users/charts?year=".date('Y')."&month=".date('m'));
}



        return view('dashboard.charts');
    }





      public function useremaillogIndex()
    {


 
        @$email = @$_GET['Emails'];
        @$function = @$_GET['function'];
        @$description = @$_GET['description'];
        @$assignments_id = @$_GET['assignments_id'];
        @$invoice_id = @$_GET['invoice_id'];
        @$times_id = @$_GET['times_id'];
        @$agreement_id = @$_GET['agreement_id'];
        @$created_at = @$_GET['created_at'];
        @$updated_at = @$_GET['updated_at'];



                $Email_log = DB::table('email_log')

 
 

                
               ->Where('email_log.emails','LIKE','%'.@$email.'%')
               ->Where('email_log.function','LIKE','%'.@$function.'%')
               ->Where('email_log.description','LIKE','%'.@$description.'%')
               ->Where('email_log.assignments_id','LIKE','%'.@$assignments_id.'%')
               ->Where('email_log.invoice_id','LIKE','%'.@$invoice_id.'%')
               ->Where('email_log.times_id','LIKE','%'.@$times_id.'%')
               ->Where('email_log.agreement_id','LIKE','%'.@$agreement_id.'%')
               ->Where('email_log.created_at','LIKE','%'.@$created_at.'%')
               ->Where('email_log.updated_at','LIKE','%'.@$updated_at.'%')
               ->orderByDesc('email_log.id')
                ->select(DB::raw('DISTINCT (email_log.id),email_log.*'))
               ->paginate(Auth::user()->paginationnum)->appends(request()->except('page'));





 


        // $Email_log = Email_log::orderByDesc('id')->paginate(Auth::user()->paginationnum);
        return view('dashboard.useremaillog')->with(["Email_log" => $Email_log]);
    }




      public function help($languege)
    {
       return view('dashboard.help');
    }

    

}
