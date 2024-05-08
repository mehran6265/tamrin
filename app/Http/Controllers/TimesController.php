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
class TimesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


public function addtimesrejectcomment($language)
{
  
if (isset($_POST['com'])) 
{
   


       $_GET['emploeeconfirm']=$_POST['com'];
        if (Auth::user()->user_type=='CLIENT' or Auth::user()->user_type=='SCHEDULE' or Auth::user()->user_type=='ADMIN')
         {


            if (Auth::user()->user_type=='SCHEDULE') 
            {
             $client_id=Auth::user()->client_id;
            }
            else
            {
             $client_id=Auth::user()->id;
            }


if (Auth::user()->user_type=='ADMIN')
 {
       $Assignmenttttttt=Assignment::where(['id'=>(int)$_GET['emploeeconfirm']])
           ->get();
}
else
{
      $Assignmenttttttt=Assignment::where(['id'=>(int)$_GET['emploeeconfirm'],'client_id'=>$client_id])
           ->get(); 
}



     


$Times=Times::where(["employee_id"=>$Assignmenttttttt[0]->employee_id,'year'=>$Assignmenttttttt[0]->year,'month'=>$Assignmenttttttt[0]->month,'registeras'=>$Assignmenttttttt[0]->registeras,'client_id'=>$Assignmenttttttt[0]->client_id,'department_id'=>$Assignmenttttttt[0]->department_id,'type'=>$Assignmenttttttt[0]->type])->get();



           // $Times=Times::where(['id'=>(int)$_GET['emploeeconfirm'],'client_id'=>$client_id])
           // ->get();


if (Auth::user()->user_type=='ADMIN')
 {
                  Assignment::where(['id'=>(int)$_GET['emploeeconfirm']])
               ->update([
                   'comment' =>@$_POST['comment'],
                   'times_status' =>"CLIENT_CANCELED",
                ]);
           
 
        // Times::where(['id'=>(int)$Times[0]->id])
        //        ->update([
        //            'status' =>'CLIENT_CANCELED',
        //         ]);
}
else
{
               Assignment::where(['id'=>(int)$_GET['emploeeconfirm'],'client_id'=>$client_id])
               ->update([
                   'comment' =>@$_POST['comment'],
                   'times_status' =>"CLIENT_CANCELED",
                ]);
           
 
        // Times::where(['id'=>(int)$Times[0]->id,'client_id'=>$client_id])
        //        ->update([
        //            'status' =>'CLIENT_CANCELED',
        //         ]);
}





 
 

    
       return redirect()->back()->with('message', "Successfuly inserted!");
         }

}





}











public function confirmtimestoemployee($language)
{
  

 

if (isset($_GET['clientconfirm']))
{

    if (Auth::user()->user_type=='CLIENT')
    {
     $client_id=Auth::user()->id;
 }
 else
    if (Auth::user()->user_type=='SCHEDULE')
    {
     $client_id=Auth::user()->client_id; 
 }


 if (Auth::user()->user_type=='CLIENT' or Auth::user()->user_type=='ADMIN' or Auth::user()->user_type=='SCHEDULE')
 {

    if (Auth::user()->user_type=='ADMIN') 
    {
        $Times=Times::where(['id'=>(int)$_GET['clientconfirm']])->get();
    }
    else
    {
      $Times=Times::where(['id'=>(int)$_GET['clientconfirm'],'client_id'=>$client_id])->get();
  }


}
else
{
    echo 'Oops';exit;
}




        if (@$Times[0]->status=='INVOICE_SENT') 
        {
           echo 'Oops';exit;
        }






Times::where([
    "id"=>(int)$_GET['clientconfirm'],
])
->update([
 'status' =>'CONFIRMED',
]);


Assignment::where([
    "times_id"=>(int)$_GET['clientconfirm'],
])
->update([
 'times_status' =>'CONFIRMED',
]);



$Rh=new Rh;

//confirm times

$user_id=Auth::user()->id;
$page="times";
$function="confirm times";
$description="when the confirm times-button is clicked";
$assignments_id=1;
$invoice_id=1;
$times_id=(int)$_GET['clientconfirm'];
$agreement_id=1;


$Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);





// Uren goedgekeurd door planning to freelancer
$useremail=Rh::getuseremail($Times[0]->employee_id);

$details = [
    'title' => "Beste ZPC-er,",
    'body1' => "Jouw gewerkte uren van de afgelopen maand zijn goedgekeurd.",
    'body2' => "Log in op het portaal om een factuur aan te maken en op te sturen.",
    'body3' => "Met vriendelijke groet,",
    'body4' => "Team ZPC",
];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($useremail['0']->email)->subject("Uren goedgekeurd door planning"));


                    $Rh = new Rh;

               $function="Uren goedgekeurd door planning-confirm times";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=1;
               $times_id=@$_GET['clientconfirm'];
               $agreement_id=1;


               $Rh::emaillog($useremail['0']->email,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);


              $Rh = new Rh;

               $tite="Factuur aanmaken en verzenden";
               $description='Diensten goedkeuren-id:'.@$_GET['clientconfirm'].' is goedgekeurd. De factuur kan aangemaakt en verzonden worden.';
               $key='key';
               $value='invoice to freelancer';
               $assignment_id=1;
               $times_id=1;
               $agreement_id=1;
               $invoice_id=1;
           
  


               $Rh::sendnotification($useremail['0']->fcmtoken,$tite,$description,$key,$value,$assignment_id,$times_id,$agreement_id,$invoice_id,$useremail['0']->id,"Times approved",@$_GET['clientconfirm']);







echo 1;exit;


}  


}

 

public function rejecttimestoemployee($language)
{
   

 if (isset($_GET['clientrejectforemployee']))
 {



     $Times=Times::find($_GET['clientrejectforemployee']);


        if (@$Times->status=='INVOICE_SENT') 
        {
           echo 'Oopss';exit;
        }



if (Auth::user()->user_type=='ADMIN') 
{
     
}
else
{
       if (Auth::user()->user_type=='CLIENT')
     {
         $client_id=Auth::user()->id;
     }
     else
        if (Auth::user()->user_type=='SCHEDULE')
        {
         $client_id=Auth::user()->client_id; 
     }




     if ($Times->client_id!=$client_id)
     {
          echo 'Oopss';exit;
     }  
}







     Times::where([
        "id"=>(int)$_GET['clientrejectforemployee']
    ])->update([
     'status' =>'CLIENT_CANCELED',
 ]);




 


    $Rh=new Rh;
//reject times

    $user_id=Auth::user()->id;
    $page="times";
    $function="reject to employee";
    $description="wehn the reject to employee-button is clicked";
    $assignments_id=1;
    $invoice_id=1;
    $times_id=(int)$_GET['clientrejectforemployee'];
    $agreement_id=1;


    $Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);


    $department=Department::where(["id"=>$Times->department_id])->get();



// Afkeur urenregistratie (department) to freelancer
    $useremail=Rh::getuseremail($Times->employee_id);

    $details = [
        'title' => "Beste ZPC-er,",
        'body1' => "Jouw gewerkte uren van de afgelopen maand zijn niet goedgekeurd door de roostermakers.",
        'body2' => "Log in op het portaal om de notitie van de roostermaker te bekijken.",
        'body3' => "Met vriendelijke groet,",
        'body4' => "Team ZPC",
    ];
    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to($useremail[0]->email)->subject("Afkeur urenregistratie ".$department[0]->title.""));


                    $Rh = new Rh;

               $function="Afkeur urenregistratie-reject to employee";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=1;
               $times_id=@$_GET['clientrejectforemployee'];
               $agreement_id=1;


               $Rh::emaillog($useremail[0]->email,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);





               $Rh = new Rh;

               $tite="Diensten afgekeurd";
               $description='Diensten goedkeuren-id:'.@$_GET['clientrejectforemployee'].' is afgekeurd.';
               $key='key';
               $value='rejected to freelancer';
               $assignment_id=1;
               $times_id=1;
               $agreement_id=1;
               $invoice_id=1;
           
  


               $Rh::sendnotification($useremail[0]->fcmtoken,$tite,$description,$key,$value,$assignment_id,$times_id,$agreement_id,$invoice_id,$useremail[0]->id,"Times rejected",@$_GET['clientrejectforemployee']);











echo 1;exit;


    //return redirect("/" . $language . '/assignmentstimes/asc/-1/-1/-1/-1/-1/-1/-1')->with('message', "You Have Rejected This Timeplan.");




}












}

public function sendtimesbyemployee($language)
{


if (isset($_GET['emploeeconfirm']))
{



    if (Auth::user()->user_type=='EMPLOYEE' or Auth::user()->user_type=='ADMIN')
    {

        if (Auth::user()->user_type=='ADMIN') 
        {
            $Times=Times::where(['id'=>(int)$_GET['emploeeconfirm']])
            ->get();
        }
        else
        {
            $Times=Times::where(['id'=>(int)$_GET['emploeeconfirm'],'employee_id'=>Auth::user()->id])
            ->get(); 
        }



        if ($Times[0]->status!='PENDING') 
        {
            echo 'Oopss';exit;
        }


        $todaym = date("m");
        $todayy = date("Y");


      // if (Auth::user()->email!='sajiuk12@gmail.com') 
      //   {
        if (Auth::user()->email!='zzper001@gmail.com' and Auth::user()->email!='zorgtag001@gmail.com' and Auth::user()->email!='sajiuk122@gmail.com' and Auth::user()->email!='sajiuk12@gmail.com') 
        {

            if ($Times[0]->year > $todayy)
             {
                 echo "Wait until the end of the month to send the invoice";exit;
                  exit;
            }
            else
            if ($Times[0]->year > $todayy or  $Times[0]->year == $todayy)
            {


                if ($todaym==$Times[0]->month or  $todaym < $Times[0]->month) 
                {
                    echo "Wait until the end of the month to send the invoice";exit;
                    exit;
                }



            }


        }




 

        Times::where(["id"=>$Times[0]->id])->update([
           'status' =>'EMPLOYEE_ACCEPTED',
       ]);






       //  Assignment::where(["employee_id"=>$Times[0]->employee_id,'year'=>$Times[0]->year,'month'=>$Times[0]->month,'registeras'=>$Times[0]->registeras,'client_id'=>$Times[0]->client_id,'department_id'=>$Times[0]->department_id,['times_id', '=', 0],])->update([
       //     'comment' =>'',
       //     'times_id' =>$Times[0]->id,
       // ]);

        $Rh=new Rh;

        //send times

        $user_id=Auth::user()->id;
        $page="times";
        $function="send times";
        $description="wehn the send times-button is clicked";
        $assignments_id=1;
        $invoice_id=1;
        $times_id=$Times[0]->id;
        $agreement_id=1;


        $Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);

 

        echo 1;exit;
    }
}











}



public function timesByAssginment($language, $times_id, $year, $month)
{



    $Times=Times::where(["id"=>$times_id])->get();

 

    $assignments=Assignment::where(["times_id"=>$times_id,'status'=>'EMPLOYEE_ACCEPTED'])->orderBy("start_date","asc")->get();




    if ($assignments->isEmpty())
    {
      $Times=Times::where(["id"=>$times_id])->delete();  
      echo "Invoice Is Invalid";exit;
  }

  return view('dashboard.times.timesbydepartments')
  ->with([
    "departments" => null,
    "employees" => null,
    "clients" => null,
    "assignments" => $assignments,
    "year" => $year,
    "month" => $month,
    "client_id" => -1,
    "employee_id" => -1,
    "department_id" => $Times[0]->department_id,
    'times'=>$Times,
]);
}




    public function index($language, $sort_upcoming = "asc",$jobtitle, $client_id = -1, $department_id = -1, $employee_id = -1, $status = -1,$year=-1,$month=-1)
    {

        $query = Times::query();

        $employee_id_array = [];
        switch (Auth::user()->user_type) {
            case 'EMPLOYEE':
            $employee_id = Auth::user()->id;
            $departments = [];
            $clients = null;
            $employees = null;
            $query = $query->where("status", '!=' , "INVOICE_SENT");
            break;
            case 'CLIENT':
            $client_id = Auth::user()->id;
            $clients = null;
            $departments = Department::where("client_id", $client_id)->select(["id", "title"])->orderBy("title","asc")->get();
            $query = $query->where("status", '!=' , "PENDING");
            $query = $query->where("status", '!=' , "CONFIRMED");
            $query = $query->where("status", '!=' , "INVOICE_SENT");

            if (Auth::user()->user_type=="CLIENT") 
            {

              $employees = DB::table('joinclient')
              ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
              ->where(["joinclient.client_id"=> Auth::user()->id])
              ->select('profiles.first_name','profiles.last_name','joinclient.*')
              ->orderBy("profiles.first_name","asc")
              ->get()->unique('user_id');

          }
          break;
          case 'SCHEDULE':
          case 'FINANCIAL':
          $query = $query->where("client_id",Auth::user()->client_id);
          $query = $query->where("status", '!=' , "PENDING");
          $query = $query->where("status", '!=' , "CONFIRMED");
          $query = $query->where("status", '!=' , "INVOICE_SENT");
          $client_id = Auth::user()->client_id;
          $clients = null;
          $departments = Department::where("client_id", $client_id)->select(["id", "title"])->orderBy("title","asc")->get();

          if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL") 
          {

              $employees = DB::table('joinclient')
              ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
              ->where(["joinclient.client_id"=> Auth::user()->client_id])
              ->select('profiles.first_name','profiles.last_name','joinclient.*')
              ->orderBy("profiles.first_name","asc")
              ->get()->unique('user_id');

          }
          break;
          default:

          $query = $query->where("status", '!=' , "INVOICE_SENT");
          if ($client_id==-1) 
          {    
            $departments = Department::select(["id", "title"])->orderBy("title","asc")->get();
        }
        else
        {
            $departments = Department::where("client_id",$client_id)->select(["id", "title"])->orderBy("title","asc")->get();
        }

        $users = User::select(["id", "email", "user_type"])->whereIn("user_type", ["CLIENT", "EMPLOYEE"])->where("is_activated", true)->with(['profile:user_id,first_name,last_name'])->get();
        $clients = User::where("user_type", "CLIENT")->get();

        if ($client_id!=-1) 
        {
          $employees = DB::table('joinclient')

          ->Where('joinclient.client_id','LIKE','%'.@$client_id.'%')
          ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
          ->join('users', 'joinclient.user_id', '=', 'users.id')
          ->distinct('users.email')
          ->select(DB::raw('DISTINCT (users.email),profiles.last_name,profiles.first_name,users.is_activated'))
          ->paginate(Auth::user()->paginationnum)->appends(request()->except('page'));   
      }
      else
      {
         $employees = User::where("user_type", "EMPLOYEE")->get();  
     }

     if ($employee_id > 0) {
        $employee_id_array = [$employee_id];
    }
    break;
}


if ($sort_upcoming == 'iddesc') 
{
    $query = $query->orderBy("id", "desc");
}
else
{
    $sort_upcoming = "asc";
    $query = $query->orderBy("id","asc");
}

if ($client_id > 0) {
    $query = $query->where("client_id", $client_id);
}

if ($department_id > 0) {
    $query = $query->where("department_id", $department_id);
}
$query = $query->where("type", "ASSIGNMENT");

if (Auth::user()->user_type=="EMPLOYEE") 
{
   $query = $query->where("employee_id",Auth::user()->id);

}

if (Auth::user()->user_type == "EMPLOYEE") {

} else {
    if ($status == "ASSIGNED") {
        $query = $query->where("employee_id", '>', 3);
    } else if ($status == "EMPLOYEE_ACCEPTED") {
        $query = $query->where("status", "EMPLOYEE_ACCEPTED");
        $employee_id_array = array_diff($employee_id_array, [2]);
    } else if ($status == "PENDING") {

        $query = $query->whereIn("status", ["PENDING"]);

    }else if ($status == "EMPLOYEE_CANCELED"){
        $query = $query->whereIn("status", ["EMPLOYEE_CANCELED"]);


    } else if ($status == "CONFIRMED"){
        $query = $query->whereIn("status", ["CONFIRMED"]);


    }else if ($status == "CLIENT_CANCELED"){
        $query = $query->whereIn("status", ["CLIENT_CANCELED"]);


    }
    else {

    }
}

if ($jobtitle!="-1") 
{
   $query = $query->where("registeras",$jobtitle);
}

if ($year!="-1") 
{

   $query = $query->where("year",$year);
}

if ($month!="-1") 
{
   $query = $query->where("month",$month);
}

if (Auth::user()->user_type != "EMPLOYEE")
{

    if ($employee_id > 0) 
    {

        $query = $query->where("employee_id", $employee_id);
    }

}

  if (isset($_GET['id']) and $_GET['id']!=0) //for show open assighnment
  {
     $query = $query->where("id",(int)$_GET['id']);
 }


 $assignments = $query->with(["client", "client.profile", "employee", "employee.profile", "department"])->paginate(Auth::user()->paginationnum);

 $configuration = Configuration::where("slug", "CREATE_ASSIGNMENT_AS_EMPLOYEE_ACCEPTED")->first();
 $profiles = Profile::get();

 foreach ($assignments as $row) 
 {

    $Times=Times::where(["id"=>$row->id])->get();
    $chass=Assignment::where(["employee_id"=>$Times[0]->employee_id,'year'=>$Times[0]->year,'month'=>$Times[0]->month,'registeras'=>$Times[0]->registeras,'client_id'=>$Times[0]->client_id,'department_id'=>$Times[0]->department_id])->get();

    if ($chass->isEmpty())
    {
      $Times=Times::where(["id"=>$row->id])->delete();  

      //return redirect("/" . $language . '/assignmentstimes/asc/-1/-1/-1/-1/-1/-1/-1');

  }
}

return view('dashboard.times.index')
->with([
    "departments" => $departments,
    "profiles" => $profiles,
    "employees" => $employees,
    "clients" => $clients,
    "assignments" => $assignments,
    "sort_upcoming" => $sort_upcoming,
    "client_id" => $client_id,
    "employee_id" => $employee_id,
    "department_id" => $department_id,
    "status" => $status,
    "year" => $year,
    "month" => $month,
    'jobtitle'=>$jobtitle,
]);

echo 1;exit;

}










}
