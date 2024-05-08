<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
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
use App\CustomClass\AppRh;
use App\Mail\ForgotPassword;
use App\Mail\WelcomeEmail;
use App\Models\Beforeinvoice;
use Illuminate\Support\Facades\Storage;
use File;
use App\Models\Image;
use Session;
use Redirect;
use Illuminate\Support\Facades\Hash;
class AssginmentController extends Controller
{

public function timesByAssginment($times_id, $year, $month)
{
    $Times=Times::where(["id"=>$times_id])->get();
    $assignments=Assignment::where(["times_id"=>$times_id,'status'=>'EMPLOYEE_ACCEPTED'])->orderBy("start_date","asc")->get();




    if ($assignments->isEmpty())
    {
      $Times=Times::where(["id"=>$times_id])->delete();  
      return redirect()->back()->with('message', "Invoice Is Invalid");
  }

  return view('dashboard.assignments.timesbydepartments')
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






public function creditnote($assignments_id)
{
header('Content-Type: application/json');
$user=AppRh::checkuser($_GET['token']);
if ($user->user_type!="ADMIN") 
{
$result = json_encode(array('data'=>array('err'=>-1,"msg"=>"You have no Permision",'status'=>'fail'))); echo $result; exit; 
}
$credit_note =  Credit_note::where("assignments_id",$assignments_id)->get();
$result = json_encode(array('data'=>array('credit_note'=>$credit_note),'status'=>'success')); echo $result; exit;
}


public function assignmentfilterclient()
{
header('Content-Type: application/json');
$user=AppRh::checkuser($_GET['token']);

$clients=null;
if ($user->user_type=="EMPLOYEE") 
{

 $clients = DB::table('joinclient')
  ->join('profiles', 'profiles.user_id', '=', 'joinclient.client_id')
  ->where(["joinclient.user_id"=> $user->id])
  ->select('profiles.first_name','profiles.last_name','joinclient.client_id')
  ->orderBy("profiles.first_name","asc")
  ->get()->unique('client_id');


}
else
if ($user->user_type=="CLIENT" or $user->user_type=="SCHEDULE" or $user->user_type=="FINANCIAL" ) 
{
if ($user->user_type=="CLIENT"){$client_id=$user->id;}
else{$client_id=$user->client_id;}

$clients = DB::table('users')
->join('profiles', 'users.id', '=', 'profiles.user_id')
->where(['users.id'=>$client_id])
->select('users.id','profiles.first_name','profiles.last_name',)
->get();
}
else
if ($user->user_type=="ADMIN") 
{
$clients = DB::table('users')
->join('profiles', 'users.id', '=', 'profiles.user_id')
->where(['users.user_type'=>"CLIENT"])
->select('users.id','profiles.first_name','profiles.last_name',)
->get();
}
$result = json_encode(array('data'=>array('clients'=>$clients),'status'=>'success')); echo $result; exit;
}


public function index($sort_upcoming = "asc",$jobtitle, $client_id = -1, $department_id = -1, $employee_id = -1, $status = -1,$year=-1,$month=-1,$start_date=-1)
{

header('Content-Type: application/json');
$user=AppRh::checkuser($_GET['token']);

$query = Assignment::query();
$query = $query->where("type", "ASSIGNMENT");

switch ($user->user_type) 
{
case 'EMPLOYEE':$query = $query->where("employee_id",$user->id);break;
case 'CLIENT':$query = $query->where("client_id",$user->id);break;
case 'SCHEDULE': 
case 'FINANCIAL':
$query = $query->where("client_id",$user->client_id);break;
default:
break;
}

if ($sort_upcoming == 'desc') {$query = $query->orderBy("id", "desc");}
else
if ($sort_upcoming == 'asc'){$query = $query->orderBy("id", $sort_upcoming);}
else{$query = $query->orderBy("start_date", "desc");}

if ($department_id !=-1){$query = $query->where("department_id", $department_id);}
if ($jobtitle!=-1){$query = $query->where("registeras",$jobtitle);}
if ($year!=-1){$query = $query->where("year",$year);}
if ($month!=-1){$query = $query->where("month",$month);}
if ($status!=-1){$query = $query->where("status",$status);}

// set client for filter
if ($user->user_type == "EMPLOYEE" or $user->user_type == "ADMIN")
{
    if ($client_id !=-1){$query = $query->where("client_id",$client_id);}
}
// set client for filter

// set EMPLOYEE for filter
if ($user->user_type != "EMPLOYEE")
{
    if ($employee_id !=-1){$query = $query->where("employee_id",$employee_id);}
}
// set EMPLOYEE for filter

$assignments = $query->select('id','status','type','time_from','time_to','start_date','end_date','education_title','payrate','client_id','department_id','employee_id','created_at','updated_at','surchargeassignment','invoice','registeras','client_payrate')->paginate(9);
$result = json_encode(array('data'=>array('clients'=>$assignments),'status'=>'success')); echo $result; exit;
}




public function getaddress()
{
if(isset($_GET['tt']))
{
echo file_get_contents("https://api.geoapify.com/v1/geocode/autocomplete?text=".$_GET['tt']."&limit=5&lang=nl&apiKey=5252e533f5e644a6adcb5bfc641b9be8");
exit;
}

if(isset($_GET['ttt']))
{
echo file_get_contents("https://api.geoapify.com/v2/place-details?apiKey=5252e533f5e644a6adcb5bfc641b9be8&id=".$_GET['ttt']);
exit;
}
}




}
