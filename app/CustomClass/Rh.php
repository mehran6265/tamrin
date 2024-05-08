<?php
namespace App\CustomClass;
use App\Models\Department;
use App\Models\Profile;
use App\Models\Preaggrement;
use App\Models\User;
use App\Models\Assignment;
use App\Models\Document;
use App\Models\Image;
use App\Models\Invoice;
use App\Models\Beforeinvoice;
use App\Models\Times;
use App\Models\Joinclient;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Joindepartment;
use App\Models\Financial;
use App\Models\Notifs;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\t_log;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User_log;
use DateTime;
use App\Models\Step_log;
use App\Models\Email_log;
use App\Models\Totla_invoice;
use App\Models\Assignment_details;
use Illuminate\Support\Facades\Http;
use App\Models\Rehisterases;
use App\Models\Agreementsetting;
use App\Models\Documentsetting;
class Rh
{


  public static function  getuserid()
    {
        return Auth::user()->id;exit;
    }



  public static function  getalluserdocuments($id)
    {
 $items=Image::where(["imageable_id"=>$id])->get();

 return $items;

    }


  public static function  checkmandetoryooptional($title,$id)
    {

       $users= Profile::where(['user_id'=>$id])->get();
 
       $registeras1=$users[0]->registeras;
       $registeras2=$users[0]->registeras1;
       $registeras3=$users[0]->registeras2;
       $registeras4=$users[0]->registeras3;
       $registeras5=$users[0]->registeras4;
 
       $mandetory="no";

       $items=Documentsetting::where(['registeras'=>$registeras1,'title'=>$title])->get();
        foreach ($items as $row) 
        {
           if ($row->type=="Mandetory")
            {
               $mandetory="yes";
           }
        }

       $items=Documentsetting::where(['registeras'=>$registeras2,'title'=>$title])->get();
        foreach ($items as $row) 
        {
           if ($row->type=="Mandetory")
            {
               $mandetory="yes";
           }
        }

        $items=Documentsetting::where(['registeras'=>$registeras3,'title'=>$title])->get();
        foreach ($items as $row) 
        {
           if ($row->type=="Mandetory")
            {
               $mandetory="yes";
           }
        }


       $items=Documentsetting::where(['registeras'=>$registeras4,'title'=>$title])->get();
        foreach ($items as $row) 
        {
           if ($row->type=="Mandetory")
            {
               $mandetory="yes";
           }
        }

        $items=Documentsetting::where(['registeras'=>$registeras5,'title'=>$title])->get();
        foreach ($items as $row) 
        {
           if ($row->type=="Mandetory")
            {
               $mandetory="yes";
           }
        }

        if ($mandetory=="yes")
         {
            return "Mandetory";
        }
        else
        {
            return "Optional";
        }

    }



public static function  checkalldocconfirmed($id)
{

$all=0;
$cheched=0;


    $items=Image::where(["imageable_id"=>$id])->get();

    foreach ($items as $row) 
    {
       $all++;
    }

    $items2=Image::where(["imageable_id"=>$id,'documentverified'=>1])->get();

    foreach ($items2 as $row) 
    {
       $cheched++;
    }


  




    if ($all==$cheched) 
    {

  $test= User::where(["id"=>$id])->update([
           'documentverified' =>1,
       ]);



        return 1;
    }
    else
    {

          $test= User::where(["id"=>$id])->update([
           'documentverified' =>0,
       ]);

       return 0; 
    }



}




    public static function  getshowabledocuments($id)
    {

       $users= Profile::where(['user_id'=>$id])->get();
 
     $registeras1=$users[0]->registeras;
     $registeras2=$users[0]->registeras1;
     $registeras3=$users[0]->registeras2;
     $registeras4=$users[0]->registeras3;
     $registeras5=$users[0]->registeras4;
 
     $doscuments=array();
     $doscumentscheck=array();






    $items=Documentsetting::where(['registeras'=>$registeras1,'status'=>1])->get();
    foreach ($items as $row)
    {
        $newarray=array('title'=>$row->title,'type'=>$row->type,'checkbox'=>$row->checkbox,'Namecheckbox'=>$row->Namecheckbox,'Expirationdate'=>$row->Expirationdate);
        $newarraycheck=array('title'=>$row->title);
        if (!in_array($newarraycheck, $doscumentscheck))
        {
            array_push($doscuments,$newarray); 
            array_push($doscumentscheck,$newarraycheck); 
        }
    }

    $items=Documentsetting::where(['registeras'=>$registeras2,'status'=>1])->get();
     foreach ($items as $row)
    {
        $newarray=array('title'=>$row->title,'type'=>$row->type,'checkbox'=>$row->checkbox,'Namecheckbox'=>$row->Namecheckbox,'Expirationdate'=>$row->Expirationdate);
        $newarraycheck=array('title'=>$row->title);
        if (!in_array($newarraycheck, $doscumentscheck))
        {
            array_push($doscuments,$newarray); 
            array_push($doscumentscheck,$newarraycheck); 
        }
    }

    $items=Documentsetting::where(['registeras'=>$registeras3,'status'=>1])->get();
     foreach ($items as $row)
    {
        $newarray=array('title'=>$row->title,'type'=>$row->type,'checkbox'=>$row->checkbox,'Namecheckbox'=>$row->Namecheckbox,'Expirationdate'=>$row->Expirationdate);
        $newarraycheck=array('title'=>$row->title);
        if (!in_array($newarraycheck, $doscumentscheck))
        {
            array_push($doscuments,$newarray); 
            array_push($doscumentscheck,$newarraycheck); 
        }
    }

    $items=Documentsetting::where(['registeras'=>$registeras4,'status'=>1])->get();
     foreach ($items as $row)
    {
        $newarray=array('title'=>$row->title,'type'=>$row->type,'checkbox'=>$row->checkbox,'Namecheckbox'=>$row->Namecheckbox,'Expirationdate'=>$row->Expirationdate);
        $newarraycheck=array('title'=>$row->title);
        if (!in_array($newarraycheck, $doscumentscheck))
        {
            array_push($doscuments,$newarray); 
            array_push($doscumentscheck,$newarraycheck); 
        }
    }

    $items=Documentsetting::where(['registeras'=>$registeras5,'status'=>1])->get();
     foreach ($items as $row)
    {
         $newarray=array('title'=>$row->title,'type'=>$row->type,'checkbox'=>$row->checkbox,'Namecheckbox'=>$row->Namecheckbox,'Expirationdate'=>$row->Expirationdate);
        $newarraycheck=array('title'=>$row->title);
        if (!in_array($newarraycheck, $doscumentscheck))
        {
            array_push($doscuments,$newarray); 
            array_push($doscumentscheck,$newarraycheck); 
        }
    }

return $doscuments;



    }




 public static function  getalldoc($type,$id)
    {
    $items=Document::get();
    return $items;
    }


public static function getcheckdoc($documentverified)
{

    if ($documentverified==1) 
    {
        return "checked";
    }
    else
    {
        return "";
    }
}




public static function  sendnotification($fcmtoken,$tite,$description,$key,$value,$assignment_id,$times_id,$agreement_id,$invoice_id,$user_id=0,$routes="none",$routesid="0")
{

   $key=$routes;
   $value=$routesid;


 
if($user_id!=0)
{
  $User= User::where(["id"=>$user_id])->get();
}
else
{
  $User= User::where(["fcmtoken"=>$fcmtoken])->get();
}

        $currentDateTime = Carbon::now();
        $newDateTime = Carbon::now();
        $ex=(explode(" ",$newDateTime));
        $todayinemail=$ex[0];
        $today=$ex[0]." 00:00:00";
        $maindate=$ex[0];
        $ex=(explode("-",$maindate));
        $year=$ex[0];
        $month=$ex[1];
        $day=$ex[2];
        Notifs::create([
            'user_id' =>$User[0]->id,
            'title' =>$tite,
            'description' =>$description,
            'assignments_id' =>$assignment_id,
            'invoice_id' =>$invoice_id,
            'times_id' =>$times_id,
            'agreement_id' =>$agreement_id,
            'seen' =>0,
            'key' =>$key,
            'value' =>$value,
            'year' =>$year,
            'month' =>$month,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
 
        ]);

   define( 'API_ACCESS_KEY', 'AAAAfj67E-o:APA91bEvgEeQnbANwtYbwAlEm3140BwbMOcf-3viL3achunSsxvljGhBmn2AC0VhJt1uIltszGCriT7WZJQB7i8RvPBMEu2vf8OqzxsvdpRqPq_VzG_2H2GgBiU0yow-dBfEYqWxmMoE');

    $fcm_token= 'DEVICE_FCM_TOKEN';
    $url = 'https://fcm.googleapis.com/fcm/send';
    $data = array
     (
         'body'   => $description,
         'title'  => $tite,
     );
  $fields = array
    (
   'to'=>$fcmtoken,
    'notification'  => $data,
       "data"=> [
    "click_action"=>  "FLUTTER_NOTIFICATION_CLICK",
    "sound"=>  "default", 
    "status"=>  $routesid,
    "screen"=> $routes,
    "assignments_id"=>  $assignment_id,
    "invoice_id"=> $invoice_id,
    "agreement_id"=> $agreement_id,
    "key"=> $key,
    "value"=> $value,
    'title'  => $tite,
  ],
       'apns' => [
        'payload' => [
            'aps' => [
                'notification_count' => 9
            ]
        ]
    ],
    );
  $headers = array
    (
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );
    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL, $url );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
    $result = curl_exec($ch );
        if ($result === FALSE) {
            die('Oops! FCM Send Error: ' . curl_error($ch));
         }
     curl_close( $ch );
   return $result;
}

public static function  timetotapayrate($times_id)
{

   $sumpayrate=0; 
 $Rh = new Rh;
  $assignments = Assignment::where(["times_id"=>$times_id,'status'=>'EMPLOYEE_ACCEPTED'])->get();

   foreach ($assignments as $assignment)
{ 


   $firstprice=$Rh::calculationinvoicepayrate($assignment->time_to,$assignment->time_from,$assignment->payrate,$assignment->sleepshift,$assignment->surchargeassignment,$assignment->sleeptime,$assignment->registeras,$assignment->start_date,$assignment->end_date,$assignment->break);  


    $sumpayrate=$sumpayrate+$firstprice;

}


Times::where(['id'=>$times_id])
->update([
 'total' =>number_format($sumpayrate,2,',','.'),
]);



return $sumpayrate;




}
 public static function getallagreementsetting()
{
   $agreementsetting = Agreementsetting::get();
      return $agreementsetting;
}

public static function getallregisteras()
{
    $Rehisterases = Rehisterases::where(['status'=>1])->get();
    return $Rehisterases;
}

public static function checkselectedpagination($id)
{
      if (Auth::user()->paginationnum ==$id) 
      {
        return "selected";
      }
}



public static function filteremployees()
{
          if (Auth::user()->user_type=="CLIENT") 
       {

          $employees = DB::table('joinclient')
          ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
          ->where(["joinclient.client_id"=> Auth::user()->id])
          ->select('profiles.first_name','profiles.last_name','joinclient.*')
          ->orderBy("profiles.first_name","asc")
          ->get()->unique('user_id');

      }
      if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL") 
      {

          $employees = DB::table('joinclient')
          ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
          ->where(["joinclient.client_id"=> Auth::user()->client_id])
          ->select('profiles.first_name','profiles.last_name','joinclient.*')
          ->orderBy("profiles.first_name","asc")
          ->get()->unique('user_id');

      }
      if (Auth::user()->user_type=="ADMIN") 
      {
         $employees = User::where("user_type", "EMPLOYEE")->get();
     }
     if (Auth::user()->user_type=="EMPLOYEE") 
     {
         $employees = null;
     }


     return $employees;
}



public static function filterdepartments()
{
    $departments = Department::select(["id", "title", "cost"])->orderBy("title","asc")->get();
    return $departments;
}

public static function filteragreements()
{
    $Preaggrement = Preaggrement::select(["id", "title"])->orderBy("title","asc")->get();
    return $Preaggrement;
}


public static function filterclient()
{
 
$clients=null;
if (Auth::user()->user_type=="EMPLOYEE") 
{

  $clients = DB::table('joinclient')
  ->join('profiles', 'profiles.user_id', '=', 'joinclient.client_id')
  ->where(["joinclient.user_id"=> Auth::user()->id])
  ->select('profiles.first_name','profiles.last_name','joinclient.client_id','profiles.company_name','profiles.user_id as client_id')
  ->orderBy("profiles.first_name","asc")
  ->get()->unique('client_id');


}
else
if (Auth::user()->user_type=="CLIENT" or Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL" ) 
{
if (Auth::user()->user_type=="CLIENT"){$client_id=Auth::user()->id;}
else{$client_id=Auth::user()->client_id;}

$clients = DB::table('users')
->join('profiles', 'users.id', '=', 'profiles.user_id')
->where(['users.id'=>$client_id])
->select('users.id','profiles.first_name','profiles.last_name','profiles.company_name','profiles.user_id as client_id')
->get();
}
else
if (Auth::user()->user_type=="ADMIN") 
{
$clients = DB::table('users')
->join('profiles', 'users.id', '=', 'profiles.user_id')
->where(['users.user_type'=>"CLIENT"])
->select('users.id','profiles.first_name','profiles.last_name','profiles.company_name','profiles.user_id as client_id')
->get();
}
return $clients;
}



public static function  assignmentsdats()
{
    $startdates=Assignment::select('start_date')->get()->unique('start_date');
    return $startdates;
}
public static function  mintohourse($allmintimecal)
{

$min=($allmintimecal % 60);
$hour=intdiv($allmintimecal, 60);

if ($min<10) 
{
    $min="0".$min;
}




$hours = $hour.':'. $min;

return $hours;

}



public static function  lastinvoicetotalclientpayrate($department_id,$year,$month,$registeras,$client_id)
{

 $clientpayrate= Assignment_details::where(['year'=>$year,'month'=>$month,'department_id'=>$department_id,'registeras'=>$registeras,'client_id'=>$client_id,['beforeinvoice_id','!=',0]])->sum('totalclientpayrate');
   return $clientpayrate; 


}



public static function  lastinvoicetotalpayrate($department_id,$year,$month,$registeras,$client_id)
{

 $payrate= Assignment_details::where(['year'=>$year,'month'=>$month,'department_id'=>$department_id,'registeras'=>$registeras,'client_id'=>$client_id,['beforeinvoice_id','!=',0]])->sum('totalpayrate');


 
   return $payrate; 


}



public static function  lastinvoicetotaleffecteddurationinmine($department_id,$year,$month,$registeras,$client_id)
{

 $effecteddurationinmine= Assignment_details::where(['year'=>$year,'month'=>$month,'department_id'=>$department_id,'registeras'=>$registeras,'client_id'=>$client_id,['beforeinvoice_id','!=',0]])->sum('effecteddurationinmine');
   return $effecteddurationinmine; 
}


public static function  lastinvoicetotaldurationinmin($department_id,$year,$month,$registeras,$client_id)
{

 $durationinmine= Assignment_details::where(['year'=>$year,'month'=>$month,'department_id'=>$department_id,'registeras'=>$registeras,'client_id'=>$client_id,['beforeinvoice_id','!=',0]])->sum('durationinmine');
   return $durationinmine; 
}



public static function  getlastfinclient($id)
{



    $allfinancials = User::where(["client_id"=>$id,'user_type'=>'FINANCIAL'])->get();

if (time()>1668888136) 
{
    return $allfinancials[0]->email;
}
else
{
    return $id;
}
    


}





public static function  getassdet($id)
{
    $Assignment =  Assignment::where(["id"=>$id])->get();
    return $Assignment;
}



public static function  getmyregisterasbyid($registeras,$id)
{

$has=0;
$profile=Profile::where("user_id",$id)->get();
    

if ($profile[0]->registeras==$registeras)
 {
    $has=1;
 }

if ($profile[0]->registeras1==$registeras)
 {
    $has=1;
 }

return $has;
}




public static function  getmyregisteras($registeras)
{

$has=0;
$profile=Profile::where("user_id",Auth::user()->id)->get();
    

if ($profile[0]->registeras==$registeras)
 {
    $has=1;
 }

if ($profile[0]->registeras1==$registeras)
 {
    $has=1;
 }

return $has;
}



public static function  opentimealert()
{

$thismonth=date('m');
$thisyear=date('Y');

if ($thismonth==01 or $thismonth==1)
 {
    $checkmonth=12;
    $thisyear=$thisyear-1;
 }
 else
 {
    $checkmonth=$thismonth-1;
 }

 
$Times =  Times::where(["year"=>$thisyear,'month'=>$checkmonth,'employee_id'=>Auth::user()->id,['status','!=','INVOICE_SENT']])->get();


      $User = User::findOrFail(Auth::user()->id);

        if ($User->alertseen==0)
         {
                if ($Times->isEmpty())
    {
        $returnnum=0;
    }
    else
    {
        $returnnum=1;
    }

         
            $User->alertseen=1;
            $User->save();
        }
        else
        {
            $returnnum=0; 
        }





 
        return $returnnum;
    


}



public static function  duplicatetimeupdate($time_from,$time_to,$start_date,$employee_id,$department_id,$assignments_id)
{

$time_from=$time_from+1;
$time_to=$time_to-1;

$nex_date = date('Y-m-d', strtotime($start_date . ' +1 day'));
$pre_date = date('Y-m-d', strtotime($start_date . ' -1 day'));

$rozghabl =  Assignment::where(["start_date"=>$pre_date,'employee_id'=>$employee_id,['id','!=',$assignments_id]])->get();
$hamonroz =  Assignment::where(["start_date"=>$start_date,'employee_id'=>$employee_id,['id','!=',$assignments_id]])->get();
$rozbad =  Assignment::where(["start_date"=>$nex_date,'employee_id'=>$employee_id,['id','!=',$assignments_id]])->get();

$cant=0;

foreach ($rozghabl as $row)
{
for ($i=$row->time_from; $i <= $row->time_to  ; $i++) 
{ 
    if ($i==$time_from) 
    {
        $cant=1;
    }
}

for ($i=$time_from; $i <= $time_to  ; $i++) 
{ 
    if ($i==$row->time_from) 
    {
        $cant=1;
    }
}
}


foreach ($hamonroz as $row)
{
for ($i=$row->time_from; $i <= $row->time_to  ; $i++) 
{ 
    if ($i==$time_from) 
    {
        $cant=1;
    }
}

for ($i=$time_from; $i <= $time_to  ; $i++) 
{ 
    if ($i==$row->time_from) 
    {
        $cant=1;
    }
}
}

foreach ($rozbad as $row)
{
for ($i=$row->time_from; $i <= $row->time_to  ; $i++) 
{ 
    if ($i==$time_from) 
    {
        $cant=1;
    }
}

for ($i=$time_from; $i <= $time_to  ; $i++) 
{ 
    if ($i==$row->time_from) 
    {
        $cant=1;
    }
}
}


return $cant;

exit;



}



public static function  getcashflow($year,$month)
{

 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare'])->select('totalpayrate','totalclientpayrate')->get();


$totalclientpayratehc=0;

foreach ($Assignment_details as $row)
{
  $totalclientpayratehc=$totalclientpayratehc+$row->totalclientpayrate;
}




 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare security'])->select('totalpayrate','totalclientpayrate')->get();
 
 
$totalclientpayratehcs=0;

foreach ($Assignment_details as $row)
{
 
  $totalclientpayratehcs=$totalclientpayratehcs+$row->totalclientpayrate;
}


$c=$totalclientpayratehc+$totalclientpayratehcs;


return $c;


}


public static function  totalhoursassdelete($assignments_id)
{
    $Assignment_details= Assignment_details::where(['assignments_id'=>$assignments_id])->delete();
}




public static function  getallearnhealthcarebyinvoice($year,$month)
{

 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare',['beforeinvoice_id','!=',0]])->select('totalpayrate','totalclientpayrate')->get();
 
$totalclientpayrate=0;

foreach ($Assignment_details as $row)
{
  $totalclientpayrate=$totalclientpayrate+$row->totalclientpayrate;
}

return $totalclientpayrate;

}

public static function  getallearnhealthcaresecuritybyinvoic($year,$month)
{

 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare security',['beforeinvoice_id','!=',0]])->select('totalpayrate','totalclientpayrate')->get();
 
$totalpayrate=0;
$totalclientpayrate=0;

foreach ($Assignment_details as $row)
{
  $totalpayrate=$totalpayrate+$row->totalpayrate;
  $totalclientpayrate=$totalclientpayrate+$row->totalclientpayrate;
}

return $totalclientpayrate-$totalpayrate;


}



public static function  getallearnhealthcaresecuritybyinvoicwithout($year,$month)
{

 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare security',['beforeinvoice_id','!=',0]])->select('totalpayrate','totalclientpayrate')->get();
 
$totalpayrate=0;
$totalclientpayrate=0;

foreach ($Assignment_details as $row)
{
  $totalpayrate=$totalpayrate+$row->totalpayrate;
  $totalclientpayrate=$totalclientpayrate+$row->totalclientpayrate;
}

return $totalclientpayrate;


}





public static function  sumpayrates($year,$month)
{

    $Assignment_detailshc= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare'])->select('totalpayrate','totalclientpayrate')->get();

        $Assignment_detailshcs= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare security'])->select('totalpayrate','totalclientpayrate')->get();

$totalpayrateh=0;
$totalclientpayrateh=0;

foreach ($Assignment_detailshc as $row)
{
  $totalpayrateh=$totalpayrateh+$row->totalpayrate;
  $totalclientpayrateh=$totalclientpayrateh+$row->totalclientpayrate;
}
$hc=$totalpayrateh+$totalclientpayrateh;


$totalpayratehcs=0;
$totalclientpayratehcs=0;

foreach ($Assignment_detailshcs as $row)
{
  $totalpayratehcs=$totalpayratehcs+$row->totalpayrate;
  $totalclientpayratehcs=$totalclientpayratehcs+$row->totalclientpayrate;
}


return $hc+$totalclientpayratehcs;
}



public static function  getallearnhealthcarewithoutinvoice($year,$month)
{

 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare','beforeinvoice_id'=>0])->select('totalpayrate','totalclientpayrate')->get();
 
$totalclientpayrate=0;

foreach ($Assignment_details as $row)
{
  $totalclientpayrate=$totalclientpayrate+$row->totalclientpayrate;
}

return $totalclientpayrate;

}


public static function  getallearnhealthcare($year,$month)
{

 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare'])->select('totalpayrate','totalclientpayrate')->get();
 
$totalclientpayrate=0;

foreach ($Assignment_details as $row)
{
  $totalclientpayrate=$totalclientpayrate+$row->totalclientpayrate;
}

return $totalclientpayrate;

}

public static function  getallearnhealthcaresecurity($year,$month)
{

 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare security'])->select('totalpayrate','totalclientpayrate')->get();
 
$totalpayrate=0;
$totalclientpayrate=0;

foreach ($Assignment_details as $row)
{
  $totalpayrate=$totalpayrate+$row->totalpayrate;
  $totalclientpayrate=$totalclientpayrate+$row->totalclientpayrate;
}

return $totalclientpayrate-$totalpayrate;


}



public static function  getallearnhealthcaresecuritywithout($year,$month)
{

 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare security','beforeinvoice_id'=>0])->select('totalpayrate','totalclientpayrate')->get();
 
$totalpayrate=0;
$totalclientpayrate=0;

foreach ($Assignment_details as $row)
{
  $totalpayrate=$totalpayrate+$row->totalpayrate;
  $totalclientpayrate=$totalclientpayrate+$row->totalclientpayrate;
}

return $totalclientpayrate;


}







public static function  getallfreelancer()
{
    $count = User::where(['user_type'=>'EMPLOYEE'])->get()->count();
    return $count;
}
public static function  curenttotalhours($year,$month)
{
 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month])->select('durationintime')->get();
  
$time0="00:00:00";


  foreach ($Assignment_details as $row) 
  {

    $time1 = $row->durationintime.":00";
    $matches0 = explode(':',$time0); // split up the string
    $matches1 = explode(':',$time1);
    $sec0 = $matches0[0]*60*60+$matches0[1]*60+$matches0[2];
    $sec1 = $sec0+ $matches1[0]*3600+$matches1[1]*60+$matches1[2]; // get total seconds
    $h = intval(($sec1)/3600);
    $m = intval(($sec1-$h*3600)/60);
    $s = $sec1-$h*3600-$m*60;
    $str = str_pad($h, 2, '0', STR_PAD_LEFT).':'.str_pad($m, 2, '0', STR_PAD_LEFT).':'.str_pad($s, 2, '0', STR_PAD_LEFT);
    $time0=$str;

  }
 


$time0 = explode(':', $time0);

if ($time0[0]<10)
 {
   $time0[0]=$time0[0]+1;
   $time0[0]=$time0[0]-1;
}



if ($time0[1]=="00")
 {
  return $time0[0];
}
else
{
    return $time0[0].".".$time0[1];
}



}


public static function  curenttotaleffectedhours($year,$month)
{
 $Assignment_details= Assignment_details::where(['year'=>$year,'month'=>$month])->select('effecteddurationintime')->get();
  
$time0="00:00:00";


  foreach ($Assignment_details as $row) 
  {

    $time1 = $row->effecteddurationintime.":00";
    $matches0 = explode(':',$time0); // split up the string
    $matches1 = explode(':',$time1);
    $sec0 = $matches0[0]*60*60+$matches0[1]*60+$matches0[2];
    $sec1 = $sec0+ $matches1[0]*3600+$matches1[1]*60+$matches1[2]; // get total seconds
    $h = intval(($sec1)/3600);
    $m = intval(($sec1-$h*3600)/60);
    $s = $sec1-$h*3600-$m*60;
    $str = str_pad($h, 2, '0', STR_PAD_LEFT).':'.str_pad($m, 2, '0', STR_PAD_LEFT).':'.str_pad($s, 2, '0', STR_PAD_LEFT);
    $time0=$str;

  }
 


$time0 = explode(':', $time0);
if ($time0[0]<10)
 {
   $time0[0]=$time0[0]+1;
   $time0[0]=$time0[0]-1;
}


if ($time0[1]=="00")
 {
  return $time0[0];
}
else
{
    return $time0[0].".".$time0[1];
}






}




public static function  curenttotalpayrate($year,$month)
{
 $payrate= Assignment_details::where(['year'=>$year,'month'=>$month,])->sum('payrate');
   return $payrate; 
}

public static function  curenttotalclientpayrate($year,$month)
{
   $clientpayrate= Assignment_details::where(['year'=>$year,'month'=>$month,])->sum('clientpayrate');
   return $clientpayrate;   
}





public static function  updatetotalhoursass($times_id,$invoice_id)
{

   
     Assignment_details::where([
        "times_id"=>(int)$times_id
    ])->update([
     'beforeinvoice_id' =>$invoice_id,
 ]);

return 1;

}


public static function  totalhoursass($assignments_id,$times_id)
{


    $Assignment_details= Assignment_details::where(['assignments_id'=>$assignments_id])->delete();

    $Assignment =  Assignment::where(["id"=>$assignments_id])->get();
    $Rh = new Rh;


    $durationintime=$Rh::getduration($Assignment[0]->time_from,$Assignment[0]->time_to,$Assignment[0]->start_date,$Assignment[0]->end_date);

    $arr = explode(':', $durationintime);
     if (count($arr) === 3) {
    return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
    }
    $durationinmine= $arr[0] * 60 + $arr[1];


    $effecteddurationintime=$Rh::calculatednumberhours($Assignment[0]->time_to,$Assignment[0]->time_from,$Assignment[0]->payrate,$Assignment[0]->sleepshift,$Assignment[0]->surchargeassignment,$Assignment[0]->sleeptime,$Assignment[0]->registeras,$Assignment[0]->start_date,$Assignment[0]->end_date,$Assignment[0]->break);

  
    $arr = explode(':', $effecteddurationintime);
     if (count($arr) === 3) {
    return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
    }
    $effecteddurationinmine= $arr[0] * 60 + $arr[1];



    $totalpayrate=$Rh::calculationinvoicepayrate($Assignment[0]->time_to,$Assignment[0]->time_from,$Assignment[0]->payrate,$Assignment[0]->sleepshift,$Assignment[0]->surchargeassignment,$Assignment[0]->sleeptime,$Assignment[0]->registeras,$Assignment[0]->start_date,$Assignment[0]->end_date,$Assignment[0]->break);



  $totalclientpayrate=$Rh::calculationinvoiceclientpayrate($Assignment[0]->time_to,$Assignment[0]->time_from,$Assignment[0]->client_payrate,$Assignment[0]->sleepshift,$Assignment[0]->surchargeassignment,$Assignment[0]->sleeptime,$Assignment[0]->registeras,$Assignment[0]->start_date,$Assignment[0]->end_date,$Assignment[0]->break); 





    $x=0;

        Assignment_details::create([
            'assignments_id' =>@$assignments_id,
            'times_id' =>@$times_id,
            'registeras' =>@$Assignment[0]->registeras,
            'beforeinvoice_id' =>0,
            'time_from' =>@$Assignment[0]->time_from,
            'time_to' =>@$Assignment[0]->time_to,
            'start_date' =>@$Assignment[0]->start_date,
            'end_date' =>@$Assignment[0]->end_date,
            'payrate' =>@$Assignment[0]->payrate,
            'clientpayrate' =>@$Assignment[0]->client_payrate,
            'durationintime' =>@$durationintime,
            'durationinmine' =>@$durationinmine,
            'effecteddurationintime' =>@$effecteddurationintime,
            'effecteddurationinmine' =>@$effecteddurationinmine,
            'totalpayrate' =>@$totalpayrate,
            'totalclientpayrate' =>@$totalclientpayrate,
            'total' =>0,
            'education_title' =>@$Assignment[0]->education_title,
            'client_id' =>@$Assignment[0]->client_id,
            'department_id' =>@$Assignment[0]->department_id,
            'employee_id' =>@$Assignment[0]->employee_id,
            'year' =>@$Assignment[0]->year,
            'month' =>@$Assignment[0]->month,
            'speedasignment' =>@$Assignment[0]->speedasignment,
            'surchargeassignment' =>@$Assignment[0]->surchargeassignment,
            'sleepshift' =>@$Assignment[0]->sleepshift,
            'break' =>@$Assignment[0]->break,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);



}













public static function  curentinvoicetotal($year,$month)
{
    $count = Beforeinvoice::where(['year'=>$year,'month'=>$month])->get()->count();
    return $count;
}
public static function  curentinvoicehealth($year,$month)
{
    $count = Beforeinvoice::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare'])->get()->count();
    return $count;
}
public static function  curentinvoicehealthsec($year,$month)
{
    $count = Beforeinvoice::where(['year'=>$year,'month'=>$month,'registeras'=>'healthcare security'])->get()->count();
    return $count;
}




public static function  curenttimestotal($year,$month)
{
    $count = Times::where(['year'=>$year,'month'=>$month,['status','!=','INVOICE_SENT']])->get()->count();
    return $count;
}


public static function  curenttimespending($year,$month)
{
    $count = Times::where(['year'=>$year,'month'=>$month,'status'=>'PENDING'])->get()->count();
    return $count;
}



public static function  curenttimessent($year,$month)
{
    $count = Times::where(['year'=>$year,'month'=>$month,'status'=>'EMPLOYEE_ACCEPTED'])->get()->count();
    return $count;
}

public static function  curenttimesconfirmed($year,$month)
{
    $count = Times::where(['year'=>$year,'month'=>$month,'status'=>'CONFIRMED'])->get()->count();
    return $count;
}

public static function  curenttimesclientcanceled($year,$month)
{
    $count = Times::where(['year'=>$year,'month'=>$month,'status'=>'CLIENT_CANCELED'])->get()->count();
    return $count;
}


public static function  curentmonthassigrejectedcount($year,$month)
{
    $count = Assignment::where(['year'=>$year,'month'=>$month,'status'=>'EMPLOYEE_CANCELED'])->get()->count();
    return $count;
}
public static function  curentmonthassigacceptedcount($year,$month)
{
    $count = Assignment::where(['year'=>$year,'month'=>$month,'status'=>'EMPLOYEE_ACCEPTED'])->get()->count();
    return $count;
}
public static function  curentmonthassigpendingcount($year,$month)
{
    $count = Assignment::where(['year'=>$year,'month'=>$month,'status'=>'PENDING'])->get()->count();
    return $count;
}

public static function  curentmonthassigcount($year,$month)
{
    $count = Assignment::where(['year'=>$year,'month'=>$month])->get()->count();
    return $count;
}





public static function  gettotalinvoice($beforeinvoice_id)
{
$Totla_invoice =  Totla_invoice::where(["beforeinvoice_id"=>$beforeinvoice_id])->get();
return @$Totla_invoice[0]->total;
}

public static function  totalinvoice($beforeinvoice_id,$total)
{


     $Totla_invoice= Totla_invoice::where(['beforeinvoice_id'=>$beforeinvoice_id])->delete();


        Totla_invoice::create([
            'beforeinvoice_id' =>@$beforeinvoice_id,
            'total' =>@$total,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);



}




public static function  emaillog($emils,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id)
{

            Email_log::create([
            'emails' =>@$emils,
            'function' =>@$function,
            'description' =>@$description,
            'assignments_id' =>@$assignments_id,
            'invoice_id'=>@$invoice_id,
            'times_id'=>@$times_id,
            'agreement_id' =>@$agreement_id,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);

}



public static function  sendemail($emils,$title=null,$body1=null,$body2=null,$body3=null,$body4=null,$bbc=0,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id)
{

if ($bbc==0) 
{


    try 
    {
             $details = [
                'title' => @$title,
                'body1' => @$body1,
                'body2' => @$body2,
                'body3' => @$body3,
                'body4' => @$body4,
            ];
    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to($emils)->subject("Een welverdiend uitje!"));   

    } 
    catch (Throwable $e)
     {
        report($e);
 
        return false;
    }




}
else
{
    try 
    {
             $details = [
                'title' => $title,
                'body1' => $body1,
                'body2' => $body2,
                'body3' => $body3,
                'body4' => $body4,
            ];
    \Mail::send((new \App\Mail\WelcomeEmail($details))
        ->to("mail@mijnzpc.com")->bcc($emails)->subject("Een welverdiend uitje!"));   

    } 
    catch (Throwable $e)
     {
        report($e);
 
        return false;
    }


}




        Email_log::create([
            'emails' =>@$emils,
            'function' =>@$function,
            'description' =>@$description,
            'assignments_id' =>@$assignments_id,
            'invoice_id'=>@$invoice_id,
            'times_id'=>@$times_id,
            'agreement_id' =>@$agreement_id,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);









}


public static function  getdepartmentincreateassignment($time_from,$time_to,$start_date)
{

}




public static function  duplicatetime($time_from,$time_to,$start_date,$employee_id,$department_id)
{

$time_from=$time_from+1;
$time_to=$time_to-1;

$nex_date = date('Y-m-d', strtotime($start_date . ' +1 day'));
$pre_date = date('Y-m-d', strtotime($start_date . ' -1 day'));

$rozghabl =  Assignment::where(["start_date"=>$pre_date,'employee_id'=>$employee_id,['status','!=',"EMPLOYEE_CANCELED"]])->get();
$hamonroz =  Assignment::where(["start_date"=>$start_date,'employee_id'=>$employee_id,['status','!=',"EMPLOYEE_CANCELED"]])->get();
$rozbad =  Assignment::where(["start_date"=>$nex_date,'employee_id'=>$employee_id,['status','!=',"EMPLOYEE_CANCELED"]])->get();

$cant=0;

foreach ($rozghabl as $row)
{
for ($i=$row->time_from; $i <= $row->time_to  ; $i++) 
{ 
    if ($i==$time_from) 
    {
        $cant=1;
    }
}

for ($i=$time_from; $i <= $time_to  ; $i++) 
{ 
    if ($i==$row->time_from) 
    {
        $cant=1;
    }
}
}


foreach ($hamonroz as $row)
{
    
for ($i=$row->time_from; $i <= $row->time_to  ; $i++) 
{ 
    if ($i==$time_from) 
    {
        $cant=1;
    }
}

for ($i=$time_from; $i <= $time_to  ; $i++) 
{ 
    if ($i==$row->time_from) 
    {
        $cant=1;
    }
}
}

foreach ($rozbad as $row)
{
for ($i=$row->time_from; $i <= $row->time_to  ; $i++) 
{ 
    if ($i==$time_from) 
    {
        $cant=1;
    }
}

for ($i=$time_from; $i <= $time_to  ; $i++) 
{ 
    if ($i==$row->time_from) 
    {
        $cant=1;
    }
}
}


return $cant;

exit;



}


  public static function  steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id)
    {

            //date_default_timezone_set('Europe/Amsterdam');
            Step_log::create([
            'user_id' =>$user_id,
            'page' =>$page,
            'function' =>$function,
            'description' =>$description,
            'assignments_id' =>$assignments_id,
            'invoice_id' =>$invoice_id,
            'times_id' =>$times_id,
            'agreement_id' =>$agreement_id,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
            return 1;
    }

 public static function  getduration($time_from,$time_to,$start_date,$end_date)
    {

        $time_from=date('H:i:s',$time_from);
        $time_to=date('H:i:s',$time_to);

        $assigned_time = "{$start_date} {$time_from}";
        $completed_time= "{$end_date} {$time_to}"; 

        // $assigned_time = "2012-05-21 22:00:00";
        // $completed_time= "2012-05-22 22:00:00";   

        $d1 = new DateTime($assigned_time);
        $d2 = new DateTime($completed_time);
        $interval = $d2->diff($d1);



        $d=$interval->format('%d');
        $h=$interval->format('%H');
        $i=$interval->format('%I');
        $s=$interval->format('%S');

        $d=$d*24;
        $h=$h+$d;


 


 
        return $h.":".$i;

    }



  public static function  userlog($user_id,$description)
    {
            User_log::create([
            'user_id' =>$user_id,
            'description' =>$description,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
            return 1;
    }



  public static function  checkcandelete($assignment_id)
    {

    $Assignment=Assignment::find($assignment_id);

if ($Assignment->status=="EMPLOYEE_CANCELED") 
{
    return 1;
}


if ($Assignment->status=="PENDING") 
{
    return 1;
}



    $Beforeinvoice=Beforeinvoice::where(["times_id"=>$Assignment->times_id])->get();

    if ($Beforeinvoice->isEmpty())
    {
        return 1;
    }
    else
    {
        return 0;
    }


    }




    public static function  getfinemail($client_id,$department_id,$registeras)
    {

        $scdep=Joindepartment::where(['client_id'=>$client_id,'registeras'=>$registeras,'department_id'=>$department_id])
         ->get();

// foreach ($scdep as $row)
//  {
  
// $user=User::where(['id'=>$row->user_id])
//          ->get();

// if ($user[0]->user_type=="FINANCIAL") 
// {
//  return $user[0]->email;
// }

 
// }

    }


  public static function  getclientnameforcal($id,$all)
    {


   foreach ($all as $row)
    {
     if ($row->user_id==$id) 
     {
        return $row->company_name;
     }
   }

    }



    public static function  getdepartmentname($id)
    {
        $department=Department::where("id",$id)->get();
        return @$department[0]->title;
    }



    public static function  getalltimewithpayrateinvoice($client_id,$department,$year,$month)
    {

$Rh = new Rh;

// $mo= date("m",strtotime("-1 month"));
// $year=date("Y");
$mo= $month;
$year=$year;
 
     $Assignments=Assignment::where(["client_id"=>$client_id,'department_id'=>$department,'year'=>$year,'month'=>$mo])->get();

 

$sum=0;

foreach ($Assignments as $assignment)
 {
    
$seccondprice=$Rh::calculationinvoiceclientpayrate($assignment->time_to,$assignment->time_from,$assignment->payrate,$assignment->sleepshift,$assignment->surchargeassignment,$assignment->sleeptime,$assignment->registeras,$assignment->start_date,$assignment->end_date,$assignment->break);

$sum=$sum+$seccondprice;



}


return $sum;

    }





    public static function  getalltimeinvoice($client_id,$department)
    {

$Rh = new Rh;

$mo= date("m",strtotime("-1 month"));
$year=date("Y");
 
     $Assignments=Assignment::where(["client_id"=>$client_id,'department_id'=>$department,'year'=>$year,'month'=>$mo])->get();

 

$sum=0;

foreach ($Assignments as $assignment)
 {
    
 

       $alltimestamp=  $assignment->time_to - $assignment->time_from ;
       $alltime= date('H:i', $alltimestamp);
       $arr = explode(':', $alltime);

       $alltimemin= $arr[0] * 60 + $arr[1];

$sum=$sum+$alltimemin;


}

$hours = $sum / 60;
return $hours;

    }





    public static function  getuseridbyemail($email)
    {
   $user=User::where("email",$email)->get();
    return $user;
    }




public static function checkdocument($emploee_id)
{

  $items=Image::where(["imageable_id"=>$emploee_id])->get();

 
$Resume=0;
$ChamberofCommerceKVK=0;
$Diploma=0;
$Insurace=0;
$Klachtenportaal_WKKGZ=0;
$CopyIDFrontBack=0;
$VerklaringOmtrenthetGedragVOG=0;
$AssuranceStatement=0;
$Reference=0;






foreach ($items as $row)
 {
    
    if ($row->document_title=='Resume') 
    {
        $Resume=1;
    }
    if ($row->document_title=='Chamber of Commerce KVK') 
    {
        $ChamberofCommerceKVK=1;
    }
    if ($row->document_title=='Diploma') 
    {
        $Diploma=1;
    }
    if ($row->document_title=='Insurace') 
    {
        $Insurace=1;
    }
    if ($row->document_title=="Klachtenportaal  (WKKGZ)")
    {
        $Klachtenportaal_WKKGZ=1;
    }
    if ($row->document_title=='Copy ID Front & Back') 
    {
        $CopyIDFrontBack=1;
    }
    if ($row->document_title=='Verklaring Omtrent het Gedrag  (VOG)') 
    {
        $VerklaringOmtrenthetGedragVOG=1;
    }
    if ($row->document_title=='Assurance Statement') 
    {
        $AssuranceStatement=1;
    }
    if ($row->document_title=='Reference') 
    {
        $Reference=1;
    }



 }

 

if ($Resume==1 & $ChamberofCommerceKVK==1 & $Diploma==1 & $Insurace==1 & $Klachtenportaal_WKKGZ==1 & $CopyIDFrontBack==1 & $VerklaringOmtrenthetGedragVOG==1  & $AssuranceStatement==1 & $Reference==1 ) 
{
    return 1;
}
else
{
    return 0;

}




}



public static function checkIBAN($iban)
{
// Normalize input (remove spaces and make upcase)
$iban = strtoupper(str_replace(' ', '', $iban));

if (preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban)) {
    $country = substr($iban, 0, 2);
    $check = intval(substr($iban, 2, 2));
    $account = substr($iban, 4);

    // To numeric representation
    $search = range('A','Z');
    foreach (range(10,35) as $tmp)
        $replace[]=strval($tmp);
    $numstr=str_replace($search, $replace, $account.$country.'00');

    // Calculate checksum
    $checksum = intval(substr($numstr, 0, 1));
    for ($pos = 1; $pos < strlen($numstr); $pos++) {
        $checksum *= 10;
        $checksum += intval(substr($numstr, $pos,1));
        $checksum %= 97;
    }

    return ((98-$checksum) == $check);
} else
    return false;
}





 public static function  getcompanylogo($emploee_id)
    {
        $items=Image::where(["document_title"=>"Company Logo","imageable_id"=>$emploee_id])->get();
        return $items;
    }



 public static function  getcountdoc($type,$id)
    {
    $items=Image::where(["document_title"=>$type,"imageable_id"=>$id])->get();
    return $items;
    }



 public static function  getbreakstatus($id)
    {
   $assignment=Assignment::where("id",$id)->get();
   return $assignment[0]->break;

    }



  public static function  eurodateminezto2000($date)
    {

     $st=explode("-",$date);

     $year=$st[0]-2000;


     $stt=$st[2]."-".$st[1]."-".$year;

     return $stt;

    }


  public static function  eurodate($date)
    {

     $st=explode("-",$date);

      $stt=$st[2]."-".$st[1]."-".$st[0];

     return $stt;

    }

    public static function  leftstatus()
    {
   $user=User::where("id",Auth::user()->id)->get();
    return $user;
    }


    public static function  getjoinclientpayrates($user_id,$client_id,$registeras)
    {


         $joinclient = Joinclient::where(["client_id"=>$client_id,"registeras"=>$registeras,"user_id"=>$user_id])->get();




return $joinclient;
    }




   public static function  updateinvoce($assignments_id)
    {

   $assignment=Assignment::where("id",$assignments_id)->get();

    $st=explode("-",$assignment[0]->start_date);


        $curentyear=$st[0];
        $curentmonth=$st[1];
        $curentday=$st[2];


    $basket= Invoice::where(['assignments_id'=>$assignments_id])->delete();


       

if ($assignment[0]->registeras=="security" or $assignment[0]->registeras=="healthcare security" ) 
{
    

   $Beforeinvoice=Beforeinvoice::where(["year"=>$curentyear,'month'=>$curentmonth,'client_id'=>$assignment[0]->client_id,'employee_id'=>$assignment[0]->employee_id,'department_id'=>$assignment[0]->department_id,'registeras'=>$assignment[0]->registeras,'type' =>"zpcpaytoemploee",])->get();



        if ($Beforeinvoice->isEmpty())
        {
            Beforeinvoice::create([
            'client_id' =>$assignment[0]->client_id,
            'department_id' =>$assignment[0]->department_id,
            'employee_id' =>$assignment[0]->employee_id,
            'registeras' =>$assignment[0]->registeras,
            'year' =>$curentyear,
            'month' =>$curentmonth,
            'type' =>"zpcpaytoemploee",
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
 
        ]);
        }


   $Beforeinvoice=Beforeinvoice::where(["year"=>$curentyear,'month'=>$curentmonth,'client_id'=>$assignment[0]->client_id,'employee_id'=>$assignment[0]->employee_id,'department_id'=>$assignment[0]->department_id,'registeras'=>$assignment[0]->registeras,'type' =>"clientpaytozpc",])->get();



        if ($Beforeinvoice->isEmpty())
        {

            Beforeinvoice::create([
            'client_id' =>$assignment[0]->client_id,
            'department_id' =>$assignment[0]->department_id,
            'employee_id' =>$assignment[0]->employee_id,
            'registeras' =>$assignment[0]->registeras,
            'year' =>$curentyear,
            'month' =>$curentmonth,
            'type' =>"clientpaytozpc",
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
 
        ]);
}




DB::table('invoices')->insert(
     array(
            'status' =>"PENDING",
            'time_from' =>$assignment[0]->time_from,
            'time_to' =>$assignment[0]->time_to,
            'start_date' =>$assignment[0]->start_date,
            'department_id' =>$assignment[0]->department_id,
            'department_title' =>"-",
            'cost' =>1,
            'education_title' =>$assignment[0]->education_title,
            'description' =>$assignment[0]->description,
            'requirements' =>$assignment[0]->requirements,
            'conditions' =>$assignment[0]->conditions,
            'client_id' =>$assignment[0]->client_id,
            'employee_id' =>$assignment[0]->employee_id,
            'extra_description' =>$assignment[0]->extra_description,
            'assignments_id' =>0,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
            'assignments_id' =>$assignments_id,
            'year' =>$curentyear,
            'month' =>$curentmonth,
            'day' =>$curentday,
            'end_date' =>$assignment[0]->end_date,
            'payrate' =>$assignment[0]->payrate,
            'registeras' =>$assignment[0]->registeras,
            'client_payrate' =>$assignment[0]->client_payrate,
            'type' =>"zpcpaytoemploee",
            'speedasignment' =>$assignment[0]->speedasignment,
            'surchargeassignment' =>$assignment[0]->surchargeassignment,
            'sleepshift' =>$assignment[0]->sleepshift,
     )
);





 



DB::table('invoices')->insert(
     array(
            'status' =>"PENDING",
            'time_from' =>$assignment[0]->time_from,
            'time_to' =>$assignment[0]->time_to,
            'start_date' =>$assignment[0]->start_date,
            'department_id' =>$assignment[0]->department_id,
            'department_title' =>"-",
            'cost' =>1,
            'education_title' =>$assignment[0]->education_title,
            'description' =>$assignment[0]->description,
            'requirements' =>$assignment[0]->requirements,
            'conditions' =>$assignment[0]->conditions,
            'client_id' =>$assignment[0]->client_id,
            'employee_id' =>$assignment[0]->employee_id,
            'extra_description' =>$assignment[0]->extra_description,
            'assignments_id' =>0,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
            'assignments_id' =>$assignments_id,
            'year' =>$curentyear,
            'month' =>$curentmonth,
            'day' =>$curentday,
            'end_date' =>$assignment[0]->end_date,
            'payrate' =>$assignment[0]->payrate,
            'registeras' =>$assignment[0]->registeras,
            'client_payrate' =>$assignment[0]->client_payrate,
            'type' =>"clientpaytozpc",
            'speedasignment' =>$assignment[0]->speedasignment,
            'surchargeassignment' =>$assignment[0]->surchargeassignment,
            'sleepshift' =>$assignment[0]->sleepshift,
     )
);




 




}
else
{
    

   $Beforeinvoice=Beforeinvoice::where(["year"=>$curentyear,'month'=>$curentmonth,'client_id'=>$assignment[0]->client_id,'employee_id'=>$assignment[0]->employee_id,'department_id'=>$assignment[0]->department_id,'registeras'=>$assignment[0]->registeras,'type' =>"clientpaytoemploee",])->get();



        if ($Beforeinvoice->isEmpty())
        {
            Beforeinvoice::create([
            'client_id' =>$assignment[0]->client_id,
            'department_id' =>$assignment[0]->department_id,
            'employee_id' =>$assignment[0]->employee_id,
            'registeras' =>$assignment[0]->registeras,
            'year' =>$curentyear,
            'month' =>$curentmonth,
            'type' =>"clientpaytoemploee",
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
 
        ]);
        }


   $Beforeinvoice=Beforeinvoice::where(["year"=>$curentyear,'month'=>$curentmonth,'client_id'=>$assignment[0]->client_id,'employee_id'=>$assignment[0]->employee_id,'department_id'=>$assignment[0]->department_id,'registeras'=>$assignment[0]->registeras,'type' =>"clientpaytozpc",])->get();



        if ($Beforeinvoice->isEmpty())
        {

            Beforeinvoice::create([
            'client_id' =>$assignment[0]->client_id,
            'department_id' =>$assignment[0]->department_id,
            'employee_id' =>$assignment[0]->employee_id,
            'registeras' =>$assignment[0]->registeras,
            'year' =>$curentyear,
            'month' =>$curentmonth,
            'type' =>"clientpaytozpc",
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
 
        ]);
}



DB::table('invoices')->insert(
     array(
            'status' =>"PENDING",
            'time_from' =>$assignment[0]->time_from,
            'time_to' =>$assignment[0]->time_to,
            'start_date' =>$assignment[0]->start_date,
            'department_id' =>$assignment[0]->department_id,
            'department_title' =>"-",
            'cost' =>1,
            'education_title' =>$assignment[0]->education_title,
            'description' =>$assignment[0]->description,
            'requirements' =>$assignment[0]->requirements,
            'conditions' =>$assignment[0]->conditions,
            'client_id' =>$assignment[0]->client_id,
            'employee_id' =>$assignment[0]->employee_id,
            'extra_description' =>$assignment[0]->extra_description,
            'assignments_id' =>$assignments_id,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
            'year' =>$curentyear,
            'month' =>$curentmonth,
            'day' =>$curentday,
            'end_date' =>$assignment[0]->end_date,
            'payrate' =>$assignment[0]->payrate,
            'registeras' =>$assignment[0]->registeras,
            'client_payrate' =>$assignment[0]->client_payrate,
            'type' =>"clientpaytoemploee",
            'speedasignment' =>$assignment[0]->speedasignment,
            'surchargeassignment' =>$assignment[0]->surchargeassignment,
            'sleepshift' =>$assignment[0]->sleepshift,
     )
);

 





DB::table('invoices')->insert(
     array(
'status' =>"PENDING",
            'time_from' =>$assignment[0]->time_from,
            'time_to' =>$assignment[0]->time_to,
            'start_date' =>$assignment[0]->start_date,
            'department_id' =>$assignment[0]->department_id,
            'department_title' =>"-",
            'cost' =>1,
            'education_title' =>$assignment[0]->education_title,
            'description' =>$assignment[0]->description,
            'requirements' =>$assignment[0]->requirements,
            'conditions' =>$assignment[0]->conditions,
            'client_id' =>$assignment[0]->client_id,
            'employee_id' =>$assignment[0]->employee_id,
            'extra_description' =>$assignment[0]->extra_description,
            'assignments_id' =>$assignments_id,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
            'year' =>$curentyear,
            'month' =>$curentmonth,
            'day' =>$curentday,
            'end_date' =>$assignment[0]->end_date,
            'payrate' =>$assignment[0]->payrate,
            'registeras' =>$assignment[0]->registeras,
            'client_payrate' =>$assignment[0]->client_payrate,
            'type' =>"clientpaytozpc",
            'speedasignment' =>$assignment[0]->speedasignment,
            'surchargeassignment' =>$assignment[0]->surchargeassignment,
            'sleepshift' =>$assignment[0]->sleepshift,
     )
 );

 



}




        Assignment::where(['id'=>(int)$assignment[0]->id])
       ->update([
           'invoice' =>1,
        ]);


    




    }




     public static function  assinmentinfo($client_id)
    {
 
     return $client_id;
    }



     public static function  checkclientismine($client_id)
    {
     $joinclient= Joinclient::where(["client_id"=>$client_id,'user_id'=>Auth::user()->id])->get();
     $ch=0;

     foreach ($joinclient as $row)
      {
         $ch=1;
     }
     return $ch;
    }



    public static function  getthisdepartemans($id)
    {
    $department=Department::where("client_id",$id)->get();
    return $department;
    }


     public static function  getclientfrelancer($id)
    {
     $profile= Joinclient::where("client_id",$id)->get();
     return $profile;
    }


    public static function  getfinancialinfo($id)
    {
    $profile= Financial::where("user_id",$id)->get();
    return $profile;
    }


    public static function  getcpmpanyname($id)
    {
    $profile=Profile::where("user_id",$id)->get();
    return $profile;
    }

    public static function  getallagreements($client_id)
    {
         $allclients = Preaggrement::where("client_id",$client_id)->get();
         return $allclients;
    }
    public static function  calculationinvoiceclientpayrate($time_to,$time_from,$payrate,$sleepshift,$surchargeassignment,$sleeptime,$registeras,$start_date=null,$end_date=null,$break=0)
    {

if ($start_date!=null)
 {
    $st=explode("-",$start_date);
$stt=$st[2]."-".$st[1];


$en=explode("-",$end_date);
$enn=$en[2]."-".$en[1];


$holyday=0;

if ($stt=='01-01' or $stt=='17-04' or $stt=='18-04' or  $stt=='27-04' or $stt=='26-05' or $stt=='05-06' or $stt=='06-06' or $stt=='25-12' or $stt=='26-12') 
{
    $holyday=1;
}

if ($enn=='01-01' or $enn=='17-04' or $enn=='18-04' or  $enn=='27-04' or $enn=='26-05' or $enn=='05-06' or $enn=='06-06' or $enn=='25-12' or $enn=='26-12') 
{
    $holyday=1;
}
}





        $os = array(
// "23:00",
"23:01",
"23:02",
"23:03",
"23:04",
"23:05",
"23:06",
"23:07",
"23:08",
"23:09",
"23:10",
"23:11",
"23:12",
"23:13",
"23:14",
"23:15",
"23:16",
"23:17",
"23:18",
"23:19",
"23:20",
"23:21",
"23:22",
"23:23",
"23:24",
"23:25",
"23:26",
"23:27",
"23:28",
"23:29",
"23:30",
"23:31",
"23:32",
"23:33",
"23:34",
"23:35",
"23:36",
"23:37",
"23:38",
"23:39",
"23:40",
"23:41",
"23:42",
"23:43",
"23:44",
"23:45",
"23:46",
"23:47",
"23:48",
"23:49",
"23:50",
"23:51",
"23:52",
"23:53",
"23:54",
"23:55",
"23:56",
"23:57",
"23:58",
"23:59",
"00:00",
"00:01",
"00:02",
"00:03",
"00:04",
"00:05",
"00:06",
"00:07",
"00:08",
"00:09",
"00:10",
"00:11",
"00:12",
"00:13",
"00:14",
"00:15",
"00:16",
"00:17",
"00:18",
"00:19",
"00:20",
"00:21",
"00:22",
"00:23",
"00:24",
"00:25",
"00:26",
"00:27",
"00:28",
"00:29",
"00:30",
"00:31",
"00:32",
"00:33",
"00:34",
"00:35",
"00:36",
"00:37",
"00:38",
"00:39",
"00:40",
"00:41",
"00:42",
"00:43",
"00:44",
"00:45",
"00:46",
"00:47",
"00:48",
"00:49",
"00:50",
"00:51",
"00:52",
"00:53",
"00:54",
"00:55",
"00:56",
"00:57",
"00:58",
"00:59",
"01:00",
"01:01",
"01:02",
"01:03",
"01:04",
"01:05",
"01:06",
"01:07",
"01:08",
"01:09",
"01:10",
"01:11",
"01:12",
"01:13",
"01:14",
"01:15",
"01:16",
"01:17",
"01:18",
"01:19",
"01:20",
"01:21",
"01:22",
"01:23",
"01:24",
"01:25",
"01:26",
"01:27",
"01:28",
"01:29",
"01:30",
"01:31",
"01:32",
"01:33",
"01:34",
"01:35",
"01:36",
"01:37",
"01:38",
"01:39",
"01:40",
"01:41",
"01:42",
"01:43",
"01:44",
"01:45",
"01:46",
"01:47",
"01:48",
"01:49",
"01:50",
"01:51",
"01:52",
"01:53",
"01:54",
"01:55",
"01:56",
"01:57",
"01:58",
"01:59",
"02:00",
"02:01",
"02:02",
"02:03",
"02:04",
"02:05",
"02:06",
"02:07",
"02:08",
"02:09",
"02:10",
"02:11",
"02:12",
"02:13",
"02:14",
"02:15",
"02:16",
"02:17",
"02:18",
"02:19",
"02:20",
"02:21",
"02:22",
"02:23",
"02:24",
"02:25",
"02:26",
"02:27",
"02:28",
"02:29",
"02:30",
"02:31",
"02:32",
"02:33",
"02:34",
"02:35",
"02:36",
"02:37",
"02:38",
"02:39",
"02:40",
"02:41",
"02:42",
"02:43",
"02:44",
"02:45",
"02:46",
"02:47",
"02:48",
"02:49",
"02:50",
"02:51",
"02:52",
"02:53",
"02:54",
"02:55",
"02:56",
"02:57",
"02:58",
"02:59",
"03:00",
"03:01",
"03:02",
"03:03",
"03:04",
"03:05",
"03:06",
"03:07",
"03:08",
"03:09",
"03:10",
"03:11",
"03:12",
"03:13",
"03:14",
"03:15",
"03:16",
"03:17",
"03:18",
"03:19",
"03:20",
"03:21",
"03:22",
"03:23",
"03:24",
"03:25",
"03:26",
"03:27",
"03:28",
"03:29",
"03:30",
"03:31",
"03:32",
"03:33",
"03:34",
"03:35",
"03:36",
"03:37",
"03:38",
"03:39",
"03:40",
"03:41",
"03:42",
"03:43",
"03:44",
"03:45",
"03:46",
"03:47",
"03:48",
"03:49",
"03:50",
"03:51",
"03:52",
"03:53",
"03:54",
"03:55",
"03:56",
"03:57",
"03:58",
"03:59",
"04:00",
"04:01",
"04:02",
"04:03",
"04:04",
"04:05",
"04:06",
"04:07",
"04:08",
"04:09",
"04:10",
"04:11",
"04:12",
"04:13",
"04:14",
"04:15",
"04:16",
"04:17",
"04:18",
"04:19",
"04:20",
"04:21",
"04:22",
"04:23",
"04:24",
"04:25",
"04:26",
"04:27",
"04:28",
"04:29",
"04:30",
"04:31",
"04:32",
"04:33",
"04:34",
"04:35",
"04:36",
"04:37",
"04:38",
"04:39",
"04:40",
"04:41",
"04:42",
"04:43",
"04:44",
"04:45",
"04:46",
"04:47",
"04:48",
"04:49",
"04:50",
"04:51",
"04:52",
"04:53",
"04:54",
"04:55",
"04:56",
"04:57",
"04:58",
"04:59",
"05:00",
"05:01",
"05:02",
"05:03",
"05:04",
"05:05",
"05:06",
"05:07",
"05:08",
"05:09",
"05:10",
"05:11",
"05:12",
"05:13",
"05:14",
"05:15",
"05:16",
"05:17",
"05:18",
"05:19",
"05:20",
"05:21",
"05:22",
"05:23",
"05:24",
"05:25",
"05:26",
"05:27",
"05:28",
"05:29",
"05:30",
"05:31",
"05:32",
"05:33",
"05:34",
"05:35",
"05:36",
"05:37",
"05:38",
"05:39",
"05:40",
"05:41",
"05:42",
"05:43",
"05:44",
"05:45",
"05:46",
"05:47",
"05:48",
"05:49",
"05:50",
"05:51",
"05:52",
"05:53",
"05:54",
"05:55",
"05:56",
"05:57",
"05:58",
"05:59",
"06:00",
"06:01",
"06:02",
"06:03",
"06:04",
"06:05",
"06:06",
"06:07",
"06:08",
"06:09",
"06:10",
"06:11",
"06:12",
"06:13",
"06:14",
"06:15",
"06:16",
"06:17",
"06:18",
"06:19",
"06:20",
"06:21",
"06:22",
"06:23",
"06:24",
"06:25",
"06:26",
"06:27",
"06:28",
"06:29",
"06:30",
"06:31",
"06:32",
"06:33",
"06:34",
"06:35",
"06:36",
"06:37",
"06:38",
"06:39",
"06:40",
"06:41",
"06:42",
"06:43",
"06:44",
"06:45",
"06:46",
"06:47",
"06:48",
"06:49",
"06:50",
"06:51",
"06:52",
"06:53",
"06:54",
"06:55",
"06:56",
"06:57",
"06:58",
"06:59",
"07:00",


        );


   

       $alltimestamp=  $time_to - $time_from;
       $alltime= date('H:i', $alltimestamp);

       $Rh = new Rh;
       $alltime=$Rh::getduration($time_from,$time_to,$start_date,$end_date);



       $arr = explode(':', $alltime);
       if (count($arr) === 3) {
        return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
       }
       $alltimemin= $arr[0] * 60 + $arr[1];
 


       $starttime=date('H:i',$time_from);



    if ($break==1  and  $sleepshift==1)
     {
        $startbreakmin=0;
    }
    else
    {
        $startbreakmin=ceil($alltimemin/2);
    }
        




        $endbreakmin=$startbreakmin+29;


        $starterdate=@$stt;



        $mininhokkyday=0;
        $mininsleeptime=0;
        $normaltime=0;

        $payrate=$payrate / 60;


         $mainpricenormaltime=0;
         $mainpricesleeptime=0;
         $mininhokkydayprice=0;


  


       

        $starttimeforcheck=$starttime;


        for ($i=1; $i <= $alltimemin ; $i++)
         { 

       $unbreak=0;



    if ($break==1  and  $sleepshift==1)
     {

      


    }
    else
    {
        if ($break==1) 
        {
             if ( ($startbreakmin <= $i) && ($i <= $endbreakmin)) 
             {
                 $unbreak=1;
                  $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));
             }
        }  
    }








 if ($unbreak==0) 
 {
       $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
          
                
               
  

            if ($starttimeforcheck=="00:00") 
            {
                $starterdate=@$enn;
            }


                if (in_array($starttimeforcheck, $os)) 
                {


                    $mininsleeptime++;
                    $mainpricesleeptimetmp=1*$payrate;
                 

                    if ($sleepshift==1) 
                    {
                        $mainpricesleeptimetmp=$mainpricesleeptimetmp/2;
                    }
                    $extraforhalcare=0;

                    if ($registeras=='healthcaree') 
                    {
                    $extraforhalcare=($mainpricesleeptimetmp*20)/100;
                    $mainpricesleeptimetmp=$mainpricesleeptimetmp+$extraforhalcare;
                    }
                    $mainpricesleeptime=$mainpricesleeptime+$mainpricesleeptimetmp;
                }
                else
                {

                  

                if ($starterdate=='01-01' or $starterdate=='17-04' or $starterdate=='18-04' or  $starterdate=='27-04' or $starterdate=='26-05' or $starterdate=='05-06' or $starterdate=='06-06' or $starterdate=='25-12' or $starterdate=='26-12') 
                {
                $mininhokkyday++;
                $mininhokkydaypricetmp=1 * $payrate;
                $extraforhalcaree=0;

                if ($registeras=='healthcaree') 
                {
                $extraforhalcaree=($mininhokkydaypricetmp*20)/100;
                $mininhokkydaypricetmp=$mininhokkydaypricetmp+$extraforhalcaree;
                }
                $mininhokkydayprice=$mininhokkydayprice+$mininhokkydaypricetmp;
                }  
                else
                {
                  $normaltime++; 
                  $mainpricenormaltimetmp=1 * $payrate; 
                  $mainpricenormaltime=$mainpricenormaltime + $mainpricenormaltimetmp; 
                }

 
                }
 }

             



        }

 
// return $normaltime;

  if ($break==1  and  $sleepshift==1)
     {
   
      $normaltime=$normaltime-30;

     $temppayrateforbreake=30 * $payrate; 

     $mainpricenormaltime=$mainpricenormaltime-$temppayrateforbreake;

     }



        /// $normaltime=$alltimemin-$mininsleeptime;
        /// $normaltime=$normaltime-$mininhokkyday;

 
        $mainprice=$mainpricesleeptime+$mainpricenormaltime+$mininhokkydayprice;









 




    if ($surchargeassignment>0) 
    {
        if ($registeras!='healthcare') 
          {
        $extra=($mainprice*$surchargeassignment)/100;
        $mainprice=$mainprice+$extra;
         }
    }


     return $mainprice;



    }




    public static function  calculationinvoicepayrate($time_to,$time_from,$payrate,$sleepshift,$surchargeassignment,$sleeptime,$registeras,$start_date=null,$end_date=null,$break=0)
    {


if ($start_date!=null)
 {
    $st=explode("-",$start_date);
$stt=$st[2]."-".$st[1];


$en=explode("-",$end_date);
$enn=$en[2]."-".$en[1];


$holyday=0;

if ($stt=='01-01' or $stt=='17-04' or $stt=='18-04' or  $stt=='27-04' or $stt=='26-05' or $stt=='05-06' or $stt=='06-06' or $stt=='25-12' or $stt=='26-12') 
{
    $holyday=1;
}

if ($enn=='01-01' or $enn=='17-04' or $enn=='18-04' or  $enn=='27-04' or $enn=='26-05' or $enn=='05-06' or $enn=='06-06' or $enn=='25-12' or $enn=='26-12') 
{
    $holyday=1;
}
}





        $os = array(
// "23:00",
"23:01",
"23:02",
"23:03",
"23:04",
"23:05",
"23:06",
"23:07",
"23:08",
"23:09",
"23:10",
"23:11",
"23:12",
"23:13",
"23:14",
"23:15",
"23:16",
"23:17",
"23:18",
"23:19",
"23:20",
"23:21",
"23:22",
"23:23",
"23:24",
"23:25",
"23:26",
"23:27",
"23:28",
"23:29",
"23:30",
"23:31",
"23:32",
"23:33",
"23:34",
"23:35",
"23:36",
"23:37",
"23:38",
"23:39",
"23:40",
"23:41",
"23:42",
"23:43",
"23:44",
"23:45",
"23:46",
"23:47",
"23:48",
"23:49",
"23:50",
"23:51",
"23:52",
"23:53",
"23:54",
"23:55",
"23:56",
"23:57",
"23:58",
"23:59",
"00:00",
"00:01",
"00:02",
"00:03",
"00:04",
"00:05",
"00:06",
"00:07",
"00:08",
"00:09",
"00:10",
"00:11",
"00:12",
"00:13",
"00:14",
"00:15",
"00:16",
"00:17",
"00:18",
"00:19",
"00:20",
"00:21",
"00:22",
"00:23",
"00:24",
"00:25",
"00:26",
"00:27",
"00:28",
"00:29",
"00:30",
"00:31",
"00:32",
"00:33",
"00:34",
"00:35",
"00:36",
"00:37",
"00:38",
"00:39",
"00:40",
"00:41",
"00:42",
"00:43",
"00:44",
"00:45",
"00:46",
"00:47",
"00:48",
"00:49",
"00:50",
"00:51",
"00:52",
"00:53",
"00:54",
"00:55",
"00:56",
"00:57",
"00:58",
"00:59",
"01:00",
"01:01",
"01:02",
"01:03",
"01:04",
"01:05",
"01:06",
"01:07",
"01:08",
"01:09",
"01:10",
"01:11",
"01:12",
"01:13",
"01:14",
"01:15",
"01:16",
"01:17",
"01:18",
"01:19",
"01:20",
"01:21",
"01:22",
"01:23",
"01:24",
"01:25",
"01:26",
"01:27",
"01:28",
"01:29",
"01:30",
"01:31",
"01:32",
"01:33",
"01:34",
"01:35",
"01:36",
"01:37",
"01:38",
"01:39",
"01:40",
"01:41",
"01:42",
"01:43",
"01:44",
"01:45",
"01:46",
"01:47",
"01:48",
"01:49",
"01:50",
"01:51",
"01:52",
"01:53",
"01:54",
"01:55",
"01:56",
"01:57",
"01:58",
"01:59",
"02:00",
"02:01",
"02:02",
"02:03",
"02:04",
"02:05",
"02:06",
"02:07",
"02:08",
"02:09",
"02:10",
"02:11",
"02:12",
"02:13",
"02:14",
"02:15",
"02:16",
"02:17",
"02:18",
"02:19",
"02:20",
"02:21",
"02:22",
"02:23",
"02:24",
"02:25",
"02:26",
"02:27",
"02:28",
"02:29",
"02:30",
"02:31",
"02:32",
"02:33",
"02:34",
"02:35",
"02:36",
"02:37",
"02:38",
"02:39",
"02:40",
"02:41",
"02:42",
"02:43",
"02:44",
"02:45",
"02:46",
"02:47",
"02:48",
"02:49",
"02:50",
"02:51",
"02:52",
"02:53",
"02:54",
"02:55",
"02:56",
"02:57",
"02:58",
"02:59",
"03:00",
"03:01",
"03:02",
"03:03",
"03:04",
"03:05",
"03:06",
"03:07",
"03:08",
"03:09",
"03:10",
"03:11",
"03:12",
"03:13",
"03:14",
"03:15",
"03:16",
"03:17",
"03:18",
"03:19",
"03:20",
"03:21",
"03:22",
"03:23",
"03:24",
"03:25",
"03:26",
"03:27",
"03:28",
"03:29",
"03:30",
"03:31",
"03:32",
"03:33",
"03:34",
"03:35",
"03:36",
"03:37",
"03:38",
"03:39",
"03:40",
"03:41",
"03:42",
"03:43",
"03:44",
"03:45",
"03:46",
"03:47",
"03:48",
"03:49",
"03:50",
"03:51",
"03:52",
"03:53",
"03:54",
"03:55",
"03:56",
"03:57",
"03:58",
"03:59",
"04:00",
"04:01",
"04:02",
"04:03",
"04:04",
"04:05",
"04:06",
"04:07",
"04:08",
"04:09",
"04:10",
"04:11",
"04:12",
"04:13",
"04:14",
"04:15",
"04:16",
"04:17",
"04:18",
"04:19",
"04:20",
"04:21",
"04:22",
"04:23",
"04:24",
"04:25",
"04:26",
"04:27",
"04:28",
"04:29",
"04:30",
"04:31",
"04:32",
"04:33",
"04:34",
"04:35",
"04:36",
"04:37",
"04:38",
"04:39",
"04:40",
"04:41",
"04:42",
"04:43",
"04:44",
"04:45",
"04:46",
"04:47",
"04:48",
"04:49",
"04:50",
"04:51",
"04:52",
"04:53",
"04:54",
"04:55",
"04:56",
"04:57",
"04:58",
"04:59",
"05:00",
"05:01",
"05:02",
"05:03",
"05:04",
"05:05",
"05:06",
"05:07",
"05:08",
"05:09",
"05:10",
"05:11",
"05:12",
"05:13",
"05:14",
"05:15",
"05:16",
"05:17",
"05:18",
"05:19",
"05:20",
"05:21",
"05:22",
"05:23",
"05:24",
"05:25",
"05:26",
"05:27",
"05:28",
"05:29",
"05:30",
"05:31",
"05:32",
"05:33",
"05:34",
"05:35",
"05:36",
"05:37",
"05:38",
"05:39",
"05:40",
"05:41",
"05:42",
"05:43",
"05:44",
"05:45",
"05:46",
"05:47",
"05:48",
"05:49",
"05:50",
"05:51",
"05:52",
"05:53",
"05:54",
"05:55",
"05:56",
"05:57",
"05:58",
"05:59",
"06:00",
"06:01",
"06:02",
"06:03",
"06:04",
"06:05",
"06:06",
"06:07",
"06:08",
"06:09",
"06:10",
"06:11",
"06:12",
"06:13",
"06:14",
"06:15",
"06:16",
"06:17",
"06:18",
"06:19",
"06:20",
"06:21",
"06:22",
"06:23",
"06:24",
"06:25",
"06:26",
"06:27",
"06:28",
"06:29",
"06:30",
"06:31",
"06:32",
"06:33",
"06:34",
"06:35",
"06:36",
"06:37",
"06:38",
"06:39",
"06:40",
"06:41",
"06:42",
"06:43",
"06:44",
"06:45",
"06:46",
"06:47",
"06:48",
"06:49",
"06:50",
"06:51",
"06:52",
"06:53",
"06:54",
"06:55",
"06:56",
"06:57",
"06:58",
"06:59",
"07:00",


        );

 

       $alltimestamp=  $time_to - $time_from ;
       $alltime= date('H:i', $alltimestamp);

              $Rh = new Rh;
       $alltime=$Rh::getduration($time_from,$time_to,$start_date,$end_date);

       $arr = explode(':', $alltime);
       if (count($arr) === 3) {
        return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
       }
       $alltimemin= $arr[0] * 60 + $arr[1];
 


       $starttime=date('H:i',$time_from);


 if ($break==1  and  $sleepshift==1)
     {
       $startbreakmin=0;
     }
     else
     {
        $startbreakmin=ceil($alltimemin/2);
     }
        




        $endbreakmin=$startbreakmin+29;







        $starterdate=@$stt;



        $mininhokkyday=0;
        $mininsleeptime=0;
        $normaltime=0;

        $payrate=$payrate / 60;


         $mainpricenormaltime=0;
         $mainpricesleeptime=0;
         $mininhokkydayprice=0;

 

        $starttimeforcheck=$starttime;


 




        for ($i=1; $i <= $alltimemin ; $i++)
         { 



       $unbreak=0;



    if ($break==1  and  $sleepshift==1)
     {

      


    }
    else
    {
        if ($break==1) 
        {

             if ( ($startbreakmin <= $i) && ($i <= $endbreakmin)) 
             {
            //      echo $i."---break";
            // echo "<br>";
                 $unbreak=1;
                   $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
             }
        }  
    }



 

 if ($unbreak==0) 
 {
       $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
          
                
 

            if ($starttimeforcheck=="00:00") 
            {
                $starterdate=@$enn;
            }


                if (in_array($starttimeforcheck, $os)) 
                {

                // echo $i."---sleep---".$starttimeforcheck;
                // echo "<br>";
                    $mininsleeptime++;
                    $mainpricesleeptimetmp=1*$payrate;


                    if ($sleepshift==1) 
                    {
                        $mainpricesleeptimetmp=$mainpricesleeptimetmp/2;
                    }
                    $extraforhalcare=0;

                    if ($registeras=='healthcare') 
                    {
                    $extraforhalcare=($mainpricesleeptimetmp*20)/100;
                    $mainpricesleeptimetmp=$mainpricesleeptimetmp+$extraforhalcare;
                    }
                    $mainpricesleeptime=$mainpricesleeptime+$mainpricesleeptimetmp;
                }
                else
                {

                  

                if ($starterdate=='01-01' or $starterdate=='17-04' or $starterdate=='18-04' or  $starterdate=='27-04' or $starterdate=='26-05' or $starterdate=='05-06' or $starterdate=='06-06' or $starterdate=='25-12' or $starterdate=='26-12') 
                {
                $mininhokkyday++;
                $mininhokkydaypricetmp=1 * $payrate;
                $extraforhalcaree=0;

                if ($registeras=='healthcare') 
                {
                $extraforhalcaree=($mininhokkydaypricetmp*20)/100;
                $mininhokkydaypricetmp=$mininhokkydaypricetmp+$extraforhalcaree;
                }
                $mininhokkydayprice=$mininhokkydayprice+$mininhokkydaypricetmp;
                }  
                else
                {
                //          echo $i."---normal---".$starttimeforcheck;
                // echo "<br>";
                  $normaltime++; 
                  $mainpricenormaltimetmp=1 * $payrate; 
                  $mainpricenormaltime=$mainpricenormaltime + $mainpricenormaltimetmp; 
                }

 
                }
 }

             



        }

 


  if ($break==1  and  $sleepshift==1)
     {
   
      $normaltime=$normaltime-30;

      $temppayrateforbreake=30 * $payrate; 

      $mainpricenormaltime=$mainpricenormaltime-$temppayrateforbreake;

     }
        /// $normaltime=$alltimemin-$mininsleeptime;
        /// $normaltime=$normaltime-$mininhokkyday;
 
 
        $mainprice=$mainpricesleeptime+$mainpricenormaltime+$mininhokkydayprice;









 




    if ($surchargeassignment>0) 
    {
        $extra=($mainprice*$surchargeassignment)/100;
        $mainprice=$mainprice+$extra;
    }


 

     return $mainprice;





    }

    public static function  getallemploee($client_id)
    {

//    if ($client_id!="-1") 
// {
//             $allclients = User::where(["user_type"=>"CLIENT",'id'=>$client_id])->select(["*"])->get();

// }
// else
// {
         $joinclient = Joinclient::where("client_id",$client_id)->get();

// }


return $joinclient;
    }



 public static function  getallusers()
    {
       $User = User::select(["id","is_activated"])->get(); 
       return $User;
    }


    public static function  getallclients($client_id)
    {


        if (Auth::user()->user_type=="EMPLOYEE") 
        {
           
           $allclients = Joinclient::where("user_id",Auth::user()->id)->select(["*"])->get()->unique('client_id'); 

        }
        else
        {
           $allclients = User::where("user_type","CLIENT")->select(["*"])->get(); 
        }


return $allclients;
}


    public static function  getdepartmanname($id)
    {
    $assignment=Department::where("id",$id)->get();
    return $assignment[0]->title;
    }



    public static function  alldepartmentsforclients($id)
    {
    $Department=Department::where(["client_id"=>$id,'is_available'=>1])->get();
    return $Department;
    }



    public static function  getdepartmancost($id)
    {
    $assignment=Department::where("id",$id)->get();
    return $assignment[0]->cost;
    }


    public static function  getusername($id)
    {
    $assignment=Profile::where("user_id",$id)->get();
    return $assignment[0]->first_name." ".$assignment[0]->last_name;
    }


    public static function  getuserinfoo($id)
    {
    $profile=Profile::where("user_id",$id)->get();


       if ($profile->isEmpty())
        {
            return "None";

        }
        else
        {
           return $profile[0]->first_name." ".$profile[0]->last_name;
        }


   
    }


    public static function  getuserinfo($id)
    {
    $profile=Profile::where("user_id",$id)->get();
    return $profile;
    }

      public static function  getuseremail($id)
    {
    $user=User::where("id",$id)->get();
    return $user;
    }

    public static function  getuseraddress($id)
    {
    $addresses=Address::where(["addressable_id"=>$id,"addressable_type"=>"App\Models\User"])->get();
    return $addresses;
    }







    public static function  getsleeptime($time_to,$time_from,$payrate,$sleepshift,$surchargeassignment,$sleeptime,$registeras,$start_date=null,$end_date=null,$break=0)
    {

if ($start_date!=null)
 {
    $st=explode("-",$start_date);
$stt=$st[2]."-".$st[1];


$en=explode("-",$end_date);
$enn=$en[2]."-".$en[1];


$holyday=0;

if ($stt=='01-01' or $stt=='17-04' or $stt=='18-04' or  $stt=='27-04' or $stt=='26-05' or $stt=='05-06' or $stt=='06-06' or $stt=='25-12' or $stt=='26-12') 
{
    $holyday=1;
}

if ($enn=='01-01' or $enn=='17-04' or $enn=='18-04' or  $enn=='27-04' or $enn=='26-05' or $enn=='05-06' or $enn=='06-06' or $enn=='25-12' or $enn=='26-12') 
{
    $holyday=1;
}
}





        $os = array(
// "23:00",
"23:01",
"23:02",
"23:03",
"23:04",
"23:05",
"23:06",
"23:07",
"23:08",
"23:09",
"23:10",
"23:11",
"23:12",
"23:13",
"23:14",
"23:15",
"23:16",
"23:17",
"23:18",
"23:19",
"23:20",
"23:21",
"23:22",
"23:23",
"23:24",
"23:25",
"23:26",
"23:27",
"23:28",
"23:29",
"23:30",
"23:31",
"23:32",
"23:33",
"23:34",
"23:35",
"23:36",
"23:37",
"23:38",
"23:39",
"23:40",
"23:41",
"23:42",
"23:43",
"23:44",
"23:45",
"23:46",
"23:47",
"23:48",
"23:49",
"23:50",
"23:51",
"23:52",
"23:53",
"23:54",
"23:55",
"23:56",
"23:57",
"23:58",
"23:59",
"00:00",
"00:01",
"00:02",
"00:03",
"00:04",
"00:05",
"00:06",
"00:07",
"00:08",
"00:09",
"00:10",
"00:11",
"00:12",
"00:13",
"00:14",
"00:15",
"00:16",
"00:17",
"00:18",
"00:19",
"00:20",
"00:21",
"00:22",
"00:23",
"00:24",
"00:25",
"00:26",
"00:27",
"00:28",
"00:29",
"00:30",
"00:31",
"00:32",
"00:33",
"00:34",
"00:35",
"00:36",
"00:37",
"00:38",
"00:39",
"00:40",
"00:41",
"00:42",
"00:43",
"00:44",
"00:45",
"00:46",
"00:47",
"00:48",
"00:49",
"00:50",
"00:51",
"00:52",
"00:53",
"00:54",
"00:55",
"00:56",
"00:57",
"00:58",
"00:59",
"01:00",
"01:01",
"01:02",
"01:03",
"01:04",
"01:05",
"01:06",
"01:07",
"01:08",
"01:09",
"01:10",
"01:11",
"01:12",
"01:13",
"01:14",
"01:15",
"01:16",
"01:17",
"01:18",
"01:19",
"01:20",
"01:21",
"01:22",
"01:23",
"01:24",
"01:25",
"01:26",
"01:27",
"01:28",
"01:29",
"01:30",
"01:31",
"01:32",
"01:33",
"01:34",
"01:35",
"01:36",
"01:37",
"01:38",
"01:39",
"01:40",
"01:41",
"01:42",
"01:43",
"01:44",
"01:45",
"01:46",
"01:47",
"01:48",
"01:49",
"01:50",
"01:51",
"01:52",
"01:53",
"01:54",
"01:55",
"01:56",
"01:57",
"01:58",
"01:59",
"02:00",
"02:01",
"02:02",
"02:03",
"02:04",
"02:05",
"02:06",
"02:07",
"02:08",
"02:09",
"02:10",
"02:11",
"02:12",
"02:13",
"02:14",
"02:15",
"02:16",
"02:17",
"02:18",
"02:19",
"02:20",
"02:21",
"02:22",
"02:23",
"02:24",
"02:25",
"02:26",
"02:27",
"02:28",
"02:29",
"02:30",
"02:31",
"02:32",
"02:33",
"02:34",
"02:35",
"02:36",
"02:37",
"02:38",
"02:39",
"02:40",
"02:41",
"02:42",
"02:43",
"02:44",
"02:45",
"02:46",
"02:47",
"02:48",
"02:49",
"02:50",
"02:51",
"02:52",
"02:53",
"02:54",
"02:55",
"02:56",
"02:57",
"02:58",
"02:59",
"03:00",
"03:01",
"03:02",
"03:03",
"03:04",
"03:05",
"03:06",
"03:07",
"03:08",
"03:09",
"03:10",
"03:11",
"03:12",
"03:13",
"03:14",
"03:15",
"03:16",
"03:17",
"03:18",
"03:19",
"03:20",
"03:21",
"03:22",
"03:23",
"03:24",
"03:25",
"03:26",
"03:27",
"03:28",
"03:29",
"03:30",
"03:31",
"03:32",
"03:33",
"03:34",
"03:35",
"03:36",
"03:37",
"03:38",
"03:39",
"03:40",
"03:41",
"03:42",
"03:43",
"03:44",
"03:45",
"03:46",
"03:47",
"03:48",
"03:49",
"03:50",
"03:51",
"03:52",
"03:53",
"03:54",
"03:55",
"03:56",
"03:57",
"03:58",
"03:59",
"04:00",
"04:01",
"04:02",
"04:03",
"04:04",
"04:05",
"04:06",
"04:07",
"04:08",
"04:09",
"04:10",
"04:11",
"04:12",
"04:13",
"04:14",
"04:15",
"04:16",
"04:17",
"04:18",
"04:19",
"04:20",
"04:21",
"04:22",
"04:23",
"04:24",
"04:25",
"04:26",
"04:27",
"04:28",
"04:29",
"04:30",
"04:31",
"04:32",
"04:33",
"04:34",
"04:35",
"04:36",
"04:37",
"04:38",
"04:39",
"04:40",
"04:41",
"04:42",
"04:43",
"04:44",
"04:45",
"04:46",
"04:47",
"04:48",
"04:49",
"04:50",
"04:51",
"04:52",
"04:53",
"04:54",
"04:55",
"04:56",
"04:57",
"04:58",
"04:59",
"05:00",
"05:01",
"05:02",
"05:03",
"05:04",
"05:05",
"05:06",
"05:07",
"05:08",
"05:09",
"05:10",
"05:11",
"05:12",
"05:13",
"05:14",
"05:15",
"05:16",
"05:17",
"05:18",
"05:19",
"05:20",
"05:21",
"05:22",
"05:23",
"05:24",
"05:25",
"05:26",
"05:27",
"05:28",
"05:29",
"05:30",
"05:31",
"05:32",
"05:33",
"05:34",
"05:35",
"05:36",
"05:37",
"05:38",
"05:39",
"05:40",
"05:41",
"05:42",
"05:43",
"05:44",
"05:45",
"05:46",
"05:47",
"05:48",
"05:49",
"05:50",
"05:51",
"05:52",
"05:53",
"05:54",
"05:55",
"05:56",
"05:57",
"05:58",
"05:59",
"06:00",
"06:01",
"06:02",
"06:03",
"06:04",
"06:05",
"06:06",
"06:07",
"06:08",
"06:09",
"06:10",
"06:11",
"06:12",
"06:13",
"06:14",
"06:15",
"06:16",
"06:17",
"06:18",
"06:19",
"06:20",
"06:21",
"06:22",
"06:23",
"06:24",
"06:25",
"06:26",
"06:27",
"06:28",
"06:29",
"06:30",
"06:31",
"06:32",
"06:33",
"06:34",
"06:35",
"06:36",
"06:37",
"06:38",
"06:39",
"06:40",
"06:41",
"06:42",
"06:43",
"06:44",
"06:45",
"06:46",
"06:47",
"06:48",
"06:49",
"06:50",
"06:51",
"06:52",
"06:53",
"06:54",
"06:55",
"06:56",
"06:57",
"06:58",
"06:59",
"07:00",


        );


 

       $alltimestamp=  $time_to - $time_from ;
       $alltime= date('H:i', $alltimestamp);

              $Rh = new Rh;
       $alltime=$Rh::getduration($time_from,$time_to,$start_date,$end_date);


       $arr = explode(':', $alltime);
       if (count($arr) === 3) {
        return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
       }
       $alltimemin= $arr[0] * 60 + $arr[1];
 


       $starttime=date('H:i',$time_from);


    if ($break==1  and  $sleepshift==1)
     {
        $startbreakmin=0;
    }
    else
    {
        $startbreakmin=ceil($alltimemin/2);
    }




      
        $endbreakmin=$startbreakmin+29;


        $starterdate=@$stt;



        $mininhokkyday=0;
        $mininsleeptime=0;
        $normaltime=0;

        $payrate=$payrate / 60;


         $mainpricenormaltime=0;
         $mainpricesleeptime=0;
         $mininhokkydayprice=0;


        $starttimeforcheck=$starttime;
        for ($i=1; $i <= $alltimemin ; $i++)
         { 

       $unbreak=0;



    if ($break==1  and  $sleepshift==1)
     {

      


    }
    else
    {

        if ($break==1) 
        {
             if ( ($startbreakmin <= $i) && ($i <= $endbreakmin)) 
             {
                 $unbreak=1;
                  $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));
             }
        }

  
    }










 if ($unbreak==0) 
 {
       $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
          
                
                

            if ($starttimeforcheck=="00:00") 
            {
                $starterdate=@$enn;
            }


                if (in_array($starttimeforcheck, $os)) 
                {


                    $mininsleeptime++;
                    $mainpricesleeptimetmp=1*$payrate;

                    if ($sleepshift==1) 
                    {
                        $mainpricesleeptimetmp=$mainpricesleeptimetmp/2;
                    }
                    $extraforhalcare=0;

                    if ($registeras=='healthcaree') 
                    {
                    $extraforhalcare=($mainpricesleeptimetmp*20)/100;
                    $mainpricesleeptimetmp=$mainpricesleeptimetmp+$extraforhalcare;
                    }
                    $mainpricesleeptime=$mainpricesleeptime+$mainpricesleeptimetmp;
                }
                else
                {

                  

                if ($starterdate=='01-01' or $starterdate=='17-04' or $starterdate=='18-04' or  $starterdate=='27-04' or $starterdate=='26-05' or $starterdate=='05-06' or $starterdate=='06-06' or $starterdate=='25-12' or $starterdate=='26-12') 
                {
                $mininhokkyday++;
                $mininhokkydaypricetmp=1 * $payrate;
                $extraforhalcaree=0;

                if ($registeras=='healthcaree') 
                {
                $extraforhalcaree=($mininhokkydaypricetmp*20)/100;
                $mininhokkydaypricetmp=$mininhokkydaypricetmp+$extraforhalcaree;
                }
                $mininhokkydayprice=$mininhokkydayprice+$mininhokkydaypricetmp;
                }  
                else
                {
                  $normaltime++; 
                  $mainpricenormaltimetmp=1 * $payrate; 
                  $mainpricenormaltime=$mainpricenormaltime + $mainpricenormaltimetmp; 
                }

 
                }
 }

             



        }



 






if ($sleepshift==1)
 {
    return date('H:i', mktime(0,$mininsleeptime/2));
}
else
{
   return date('H:i', mktime(0,$mininsleeptime)); 
}




 

        /// $normaltime=$alltimemin-$mininsleeptime;
        /// $normaltime=$normaltime-$mininhokkyday;

 
        $mainprice=$mainpricesleeptime+$mainpricenormaltime+$mininhokkydayprice;









 




    if ($surchargeassignment>0) 
    {
        if ($registeras!='healthcare') 
          {
                $extra=($mainprice*$surchargeassignment)/100;
                $mainprice=$mainprice+$extra;
         }
    }
     return $mainprice;








    }





 
    public static function  getdefaultsurcharge($time_to,$time_from,$payrate,$sleepshift,$surchargeassignment,$sleeptime,$registeras,$start_date=null,$end_date=null,$break=0)
    {


if ($start_date!=null)
 {
    $st=explode("-",$start_date);
$stt=$st[2]."-".$st[1];


$en=explode("-",$end_date);
$enn=$en[2]."-".$en[1];


$holyday=0;

if ($stt=='01-01' or $stt=='17-04' or $stt=='18-04' or  $stt=='27-04' or $stt=='26-05' or $stt=='05-06' or $stt=='06-06' or $stt=='25-12' or $stt=='26-12') 
{
    $holyday=1;
}

if ($enn=='01-01' or $enn=='17-04' or $enn=='18-04' or  $enn=='27-04' or $enn=='26-05' or $enn=='05-06' or $enn=='06-06' or $enn=='25-12' or $enn=='26-12') 
{
    $holyday=1;
}
}





        $os = array(
// "23:00",
"23:01",
"23:02",
"23:03",
"23:04",
"23:05",
"23:06",
"23:07",
"23:08",
"23:09",
"23:10",
"23:11",
"23:12",
"23:13",
"23:14",
"23:15",
"23:16",
"23:17",
"23:18",
"23:19",
"23:20",
"23:21",
"23:22",
"23:23",
"23:24",
"23:25",
"23:26",
"23:27",
"23:28",
"23:29",
"23:30",
"23:31",
"23:32",
"23:33",
"23:34",
"23:35",
"23:36",
"23:37",
"23:38",
"23:39",
"23:40",
"23:41",
"23:42",
"23:43",
"23:44",
"23:45",
"23:46",
"23:47",
"23:48",
"23:49",
"23:50",
"23:51",
"23:52",
"23:53",
"23:54",
"23:55",
"23:56",
"23:57",
"23:58",
"23:59",
"00:00",
"00:01",
"00:02",
"00:03",
"00:04",
"00:05",
"00:06",
"00:07",
"00:08",
"00:09",
"00:10",
"00:11",
"00:12",
"00:13",
"00:14",
"00:15",
"00:16",
"00:17",
"00:18",
"00:19",
"00:20",
"00:21",
"00:22",
"00:23",
"00:24",
"00:25",
"00:26",
"00:27",
"00:28",
"00:29",
"00:30",
"00:31",
"00:32",
"00:33",
"00:34",
"00:35",
"00:36",
"00:37",
"00:38",
"00:39",
"00:40",
"00:41",
"00:42",
"00:43",
"00:44",
"00:45",
"00:46",
"00:47",
"00:48",
"00:49",
"00:50",
"00:51",
"00:52",
"00:53",
"00:54",
"00:55",
"00:56",
"00:57",
"00:58",
"00:59",
"01:00",
"01:01",
"01:02",
"01:03",
"01:04",
"01:05",
"01:06",
"01:07",
"01:08",
"01:09",
"01:10",
"01:11",
"01:12",
"01:13",
"01:14",
"01:15",
"01:16",
"01:17",
"01:18",
"01:19",
"01:20",
"01:21",
"01:22",
"01:23",
"01:24",
"01:25",
"01:26",
"01:27",
"01:28",
"01:29",
"01:30",
"01:31",
"01:32",
"01:33",
"01:34",
"01:35",
"01:36",
"01:37",
"01:38",
"01:39",
"01:40",
"01:41",
"01:42",
"01:43",
"01:44",
"01:45",
"01:46",
"01:47",
"01:48",
"01:49",
"01:50",
"01:51",
"01:52",
"01:53",
"01:54",
"01:55",
"01:56",
"01:57",
"01:58",
"01:59",
"02:00",
"02:01",
"02:02",
"02:03",
"02:04",
"02:05",
"02:06",
"02:07",
"02:08",
"02:09",
"02:10",
"02:11",
"02:12",
"02:13",
"02:14",
"02:15",
"02:16",
"02:17",
"02:18",
"02:19",
"02:20",
"02:21",
"02:22",
"02:23",
"02:24",
"02:25",
"02:26",
"02:27",
"02:28",
"02:29",
"02:30",
"02:31",
"02:32",
"02:33",
"02:34",
"02:35",
"02:36",
"02:37",
"02:38",
"02:39",
"02:40",
"02:41",
"02:42",
"02:43",
"02:44",
"02:45",
"02:46",
"02:47",
"02:48",
"02:49",
"02:50",
"02:51",
"02:52",
"02:53",
"02:54",
"02:55",
"02:56",
"02:57",
"02:58",
"02:59",
"03:00",
"03:01",
"03:02",
"03:03",
"03:04",
"03:05",
"03:06",
"03:07",
"03:08",
"03:09",
"03:10",
"03:11",
"03:12",
"03:13",
"03:14",
"03:15",
"03:16",
"03:17",
"03:18",
"03:19",
"03:20",
"03:21",
"03:22",
"03:23",
"03:24",
"03:25",
"03:26",
"03:27",
"03:28",
"03:29",
"03:30",
"03:31",
"03:32",
"03:33",
"03:34",
"03:35",
"03:36",
"03:37",
"03:38",
"03:39",
"03:40",
"03:41",
"03:42",
"03:43",
"03:44",
"03:45",
"03:46",
"03:47",
"03:48",
"03:49",
"03:50",
"03:51",
"03:52",
"03:53",
"03:54",
"03:55",
"03:56",
"03:57",
"03:58",
"03:59",
"04:00",
"04:01",
"04:02",
"04:03",
"04:04",
"04:05",
"04:06",
"04:07",
"04:08",
"04:09",
"04:10",
"04:11",
"04:12",
"04:13",
"04:14",
"04:15",
"04:16",
"04:17",
"04:18",
"04:19",
"04:20",
"04:21",
"04:22",
"04:23",
"04:24",
"04:25",
"04:26",
"04:27",
"04:28",
"04:29",
"04:30",
"04:31",
"04:32",
"04:33",
"04:34",
"04:35",
"04:36",
"04:37",
"04:38",
"04:39",
"04:40",
"04:41",
"04:42",
"04:43",
"04:44",
"04:45",
"04:46",
"04:47",
"04:48",
"04:49",
"04:50",
"04:51",
"04:52",
"04:53",
"04:54",
"04:55",
"04:56",
"04:57",
"04:58",
"04:59",
"05:00",
"05:01",
"05:02",
"05:03",
"05:04",
"05:05",
"05:06",
"05:07",
"05:08",
"05:09",
"05:10",
"05:11",
"05:12",
"05:13",
"05:14",
"05:15",
"05:16",
"05:17",
"05:18",
"05:19",
"05:20",
"05:21",
"05:22",
"05:23",
"05:24",
"05:25",
"05:26",
"05:27",
"05:28",
"05:29",
"05:30",
"05:31",
"05:32",
"05:33",
"05:34",
"05:35",
"05:36",
"05:37",
"05:38",
"05:39",
"05:40",
"05:41",
"05:42",
"05:43",
"05:44",
"05:45",
"05:46",
"05:47",
"05:48",
"05:49",
"05:50",
"05:51",
"05:52",
"05:53",
"05:54",
"05:55",
"05:56",
"05:57",
"05:58",
"05:59",
"06:00",
"06:01",
"06:02",
"06:03",
"06:04",
"06:05",
"06:06",
"06:07",
"06:08",
"06:09",
"06:10",
"06:11",
"06:12",
"06:13",
"06:14",
"06:15",
"06:16",
"06:17",
"06:18",
"06:19",
"06:20",
"06:21",
"06:22",
"06:23",
"06:24",
"06:25",
"06:26",
"06:27",
"06:28",
"06:29",
"06:30",
"06:31",
"06:32",
"06:33",
"06:34",
"06:35",
"06:36",
"06:37",
"06:38",
"06:39",
"06:40",
"06:41",
"06:42",
"06:43",
"06:44",
"06:45",
"06:46",
"06:47",
"06:48",
"06:49",
"06:50",
"06:51",
"06:52",
"06:53",
"06:54",
"06:55",
"06:56",
"06:57",
"06:58",
"06:59",
"07:00",


        );


 

       $alltimestamp=  $time_to - $time_from ;
       $alltime= date('H:i', $alltimestamp);

       $Rh = new Rh;
       $alltime=$Rh::getduration($time_from,$time_to,$start_date,$end_date);


       $arr = explode(':', $alltime);
       if (count($arr) === 3) {
        return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
       }
       $alltimemin= $arr[0] * 60 + $arr[1];
 


       $starttime=date('H:i',$time_from);

        //$startbreakmin=ceil($alltimemin/2);


    if ($break==1  and  $sleepshift==1)
     {
        $startbreakmin=0;
    }
    else
    {
        $startbreakmin=ceil($alltimemin/2);
    }






        $endbreakmin=$startbreakmin+29;







        $starterdate=@$stt;



        $mininhokkyday=0;
        $mininsleeptime=0;
        $normaltime=0;

        $payrate=$payrate / 60;


         $mainpricenormaltime=0;
         $mainpricesleeptime=0;
         $mininhokkydayprice=0;

         $alldefsurch=0;



        $starttimeforcheck=$starttime;
        for ($i=1; $i <= $alltimemin ; $i++)
         { 



       $unbreak=0;



    // if ($break==1  and  $sleepshift==1)
    //  {

      


    // }
    // else
    // {

    //     if ($break==1) 
    //     {
    //          if ( ($startbreakmin <= $i) && ($i <= $endbreakmin)) 
    //          {
    //              $unbreak=1;
    //               $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));
    //          }
    //     }

  
    // }











        if ($break==1) 
        {

             if ( ($startbreakmin <= $i) && ($i <= $endbreakmin)) 
             {
            //      echo $i."---break";
            // echo "<br>";
                 $unbreak=1;
                   $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
             }
        }




 if ($unbreak==0) 
 {
       $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
          
                
                

            if ($starttimeforcheck=="00:00") 
            {
                $starterdate=@$enn;
            }


                if (in_array($starttimeforcheck, $os)) 
                {

                // echo $i."---sleep---".$starttimeforcheck;
                // echo "<br>";
                    $mininsleeptime++;
                    $mainpricesleeptimetmp=1*$payrate;

                    if ($sleepshift==1) 
                    {
                        $mainpricesleeptimetmp=$mainpricesleeptimetmp/2;
                    }
                    $extraforhalcare=0;

                    if ($registeras=='healthcare') 
                    {
                    $extraforhalcare=($mainpricesleeptimetmp*20)/100;
                    $alldefsurch=$alldefsurch+$extraforhalcare;
                    $mainpricesleeptimetmp=$mainpricesleeptimetmp+$extraforhalcare;
                    }
                    $mainpricesleeptime=$mainpricesleeptime+$mainpricesleeptimetmp;
                }
                else
                {

                  

                if ($starterdate=='01-01' or $starterdate=='17-04' or $starterdate=='18-04' or  $starterdate=='27-04' or $starterdate=='26-05' or $starterdate=='05-06' or $starterdate=='06-06' or $starterdate=='25-12' or $starterdate=='26-12') 
                {
                $mininhokkyday++;
                $mininhokkydaypricetmp=1 * $payrate;
                $extraforhalcaree=0;

                if ($registeras=='healthcare') 
                {
                $extraforhalcaree=($mininhokkydaypricetmp*20)/100;
                $alldefsurch=$alldefsurch+@$extraforhalcaree;
                $mininhokkydaypricetmp=$mininhokkydaypricetmp+$extraforhalcaree;
                }
                $mininhokkydayprice=$mininhokkydayprice+$mininhokkydaypricetmp;
                }  
                else
                {
                //          echo $i."---normal---".$starttimeforcheck;
                // echo "<br>";
                  $normaltime++; 
                  $mainpricenormaltimetmp=1 * $payrate; 
                  $mainpricenormaltime=$mainpricenormaltime + $mainpricenormaltimetmp; 
                }

 
                }
 }

             



        }

 

        /// $normaltime=$alltimemin-$mininsleeptime;
        /// $normaltime=$normaltime-$mininhokkyday;
 
 return $alldefsurch;
        $mainprice=$mainpricesleeptime+$mainpricenormaltime+$mininhokkydayprice;









 




    if ($surchargeassignment>0) 
    {
        $extra=($mainprice*$surchargeassignment)/100;
        $mainprice=$mainprice+$extra;
    }
     return $mainprice;





    }













    public static function  calculatednumberhours($time_to,$time_from,$payrate,$sleepshift,$surchargeassignment,$sleeptime,$registeras,$start_date=null,$end_date=null,$break=0)
    {


if ($start_date!=null)
 {
    $st=explode("-",$start_date);
$stt=$st[2]."-".$st[1];


$en=explode("-",$end_date);
$enn=$en[2]."-".$en[1];


$holyday=0;

if ($stt=='01-01' or $stt=='17-04' or $stt=='18-04' or  $stt=='27-04' or $stt=='26-05' or $stt=='05-06' or $stt=='06-06' or $stt=='25-12' or $stt=='26-12') 
{
    $holyday=1;
}

if ($enn=='01-01' or $enn=='17-04' or $enn=='18-04' or  $enn=='27-04' or $enn=='26-05' or $enn=='05-06' or $enn=='06-06' or $enn=='25-12' or $enn=='26-12') 
{
    $holyday=1;
}
}





        $os = array(
// "23:00",
"23:01",
"23:02",
"23:03",
"23:04",
"23:05",
"23:06",
"23:07",
"23:08",
"23:09",
"23:10",
"23:11",
"23:12",
"23:13",
"23:14",
"23:15",
"23:16",
"23:17",
"23:18",
"23:19",
"23:20",
"23:21",
"23:22",
"23:23",
"23:24",
"23:25",
"23:26",
"23:27",
"23:28",
"23:29",
"23:30",
"23:31",
"23:32",
"23:33",
"23:34",
"23:35",
"23:36",
"23:37",
"23:38",
"23:39",
"23:40",
"23:41",
"23:42",
"23:43",
"23:44",
"23:45",
"23:46",
"23:47",
"23:48",
"23:49",
"23:50",
"23:51",
"23:52",
"23:53",
"23:54",
"23:55",
"23:56",
"23:57",
"23:58",
"23:59",
"00:00",
"00:01",
"00:02",
"00:03",
"00:04",
"00:05",
"00:06",
"00:07",
"00:08",
"00:09",
"00:10",
"00:11",
"00:12",
"00:13",
"00:14",
"00:15",
"00:16",
"00:17",
"00:18",
"00:19",
"00:20",
"00:21",
"00:22",
"00:23",
"00:24",
"00:25",
"00:26",
"00:27",
"00:28",
"00:29",
"00:30",
"00:31",
"00:32",
"00:33",
"00:34",
"00:35",
"00:36",
"00:37",
"00:38",
"00:39",
"00:40",
"00:41",
"00:42",
"00:43",
"00:44",
"00:45",
"00:46",
"00:47",
"00:48",
"00:49",
"00:50",
"00:51",
"00:52",
"00:53",
"00:54",
"00:55",
"00:56",
"00:57",
"00:58",
"00:59",
"01:00",
"01:01",
"01:02",
"01:03",
"01:04",
"01:05",
"01:06",
"01:07",
"01:08",
"01:09",
"01:10",
"01:11",
"01:12",
"01:13",
"01:14",
"01:15",
"01:16",
"01:17",
"01:18",
"01:19",
"01:20",
"01:21",
"01:22",
"01:23",
"01:24",
"01:25",
"01:26",
"01:27",
"01:28",
"01:29",
"01:30",
"01:31",
"01:32",
"01:33",
"01:34",
"01:35",
"01:36",
"01:37",
"01:38",
"01:39",
"01:40",
"01:41",
"01:42",
"01:43",
"01:44",
"01:45",
"01:46",
"01:47",
"01:48",
"01:49",
"01:50",
"01:51",
"01:52",
"01:53",
"01:54",
"01:55",
"01:56",
"01:57",
"01:58",
"01:59",
"02:00",
"02:01",
"02:02",
"02:03",
"02:04",
"02:05",
"02:06",
"02:07",
"02:08",
"02:09",
"02:10",
"02:11",
"02:12",
"02:13",
"02:14",
"02:15",
"02:16",
"02:17",
"02:18",
"02:19",
"02:20",
"02:21",
"02:22",
"02:23",
"02:24",
"02:25",
"02:26",
"02:27",
"02:28",
"02:29",
"02:30",
"02:31",
"02:32",
"02:33",
"02:34",
"02:35",
"02:36",
"02:37",
"02:38",
"02:39",
"02:40",
"02:41",
"02:42",
"02:43",
"02:44",
"02:45",
"02:46",
"02:47",
"02:48",
"02:49",
"02:50",
"02:51",
"02:52",
"02:53",
"02:54",
"02:55",
"02:56",
"02:57",
"02:58",
"02:59",
"03:00",
"03:01",
"03:02",
"03:03",
"03:04",
"03:05",
"03:06",
"03:07",
"03:08",
"03:09",
"03:10",
"03:11",
"03:12",
"03:13",
"03:14",
"03:15",
"03:16",
"03:17",
"03:18",
"03:19",
"03:20",
"03:21",
"03:22",
"03:23",
"03:24",
"03:25",
"03:26",
"03:27",
"03:28",
"03:29",
"03:30",
"03:31",
"03:32",
"03:33",
"03:34",
"03:35",
"03:36",
"03:37",
"03:38",
"03:39",
"03:40",
"03:41",
"03:42",
"03:43",
"03:44",
"03:45",
"03:46",
"03:47",
"03:48",
"03:49",
"03:50",
"03:51",
"03:52",
"03:53",
"03:54",
"03:55",
"03:56",
"03:57",
"03:58",
"03:59",
"04:00",
"04:01",
"04:02",
"04:03",
"04:04",
"04:05",
"04:06",
"04:07",
"04:08",
"04:09",
"04:10",
"04:11",
"04:12",
"04:13",
"04:14",
"04:15",
"04:16",
"04:17",
"04:18",
"04:19",
"04:20",
"04:21",
"04:22",
"04:23",
"04:24",
"04:25",
"04:26",
"04:27",
"04:28",
"04:29",
"04:30",
"04:31",
"04:32",
"04:33",
"04:34",
"04:35",
"04:36",
"04:37",
"04:38",
"04:39",
"04:40",
"04:41",
"04:42",
"04:43",
"04:44",
"04:45",
"04:46",
"04:47",
"04:48",
"04:49",
"04:50",
"04:51",
"04:52",
"04:53",
"04:54",
"04:55",
"04:56",
"04:57",
"04:58",
"04:59",
"05:00",
"05:01",
"05:02",
"05:03",
"05:04",
"05:05",
"05:06",
"05:07",
"05:08",
"05:09",
"05:10",
"05:11",
"05:12",
"05:13",
"05:14",
"05:15",
"05:16",
"05:17",
"05:18",
"05:19",
"05:20",
"05:21",
"05:22",
"05:23",
"05:24",
"05:25",
"05:26",
"05:27",
"05:28",
"05:29",
"05:30",
"05:31",
"05:32",
"05:33",
"05:34",
"05:35",
"05:36",
"05:37",
"05:38",
"05:39",
"05:40",
"05:41",
"05:42",
"05:43",
"05:44",
"05:45",
"05:46",
"05:47",
"05:48",
"05:49",
"05:50",
"05:51",
"05:52",
"05:53",
"05:54",
"05:55",
"05:56",
"05:57",
"05:58",
"05:59",
"06:00",
"06:01",
"06:02",
"06:03",
"06:04",
"06:05",
"06:06",
"06:07",
"06:08",
"06:09",
"06:10",
"06:11",
"06:12",
"06:13",
"06:14",
"06:15",
"06:16",
"06:17",
"06:18",
"06:19",
"06:20",
"06:21",
"06:22",
"06:23",
"06:24",
"06:25",
"06:26",
"06:27",
"06:28",
"06:29",
"06:30",
"06:31",
"06:32",
"06:33",
"06:34",
"06:35",
"06:36",
"06:37",
"06:38",
"06:39",
"06:40",
"06:41",
"06:42",
"06:43",
"06:44",
"06:45",
"06:46",
"06:47",
"06:48",
"06:49",
"06:50",
"06:51",
"06:52",
"06:53",
"06:54",
"06:55",
"06:56",
"06:57",
"06:58",
"06:59",
"07:00",


        );


 

       $alltimestamp=  $time_to - $time_from ;
       $alltime= date('H:i', $alltimestamp);

              $Rh = new Rh;
       $alltime=$Rh::getduration($time_from,$time_to,$start_date,$end_date);

       
       $arr = explode(':', $alltime);
       if (count($arr) === 3) {
        return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
       }
       $alltimemin= $arr[0] * 60 + $arr[1];
 


       $starttime=date('H:i',$time_from);


    if ($break==1  and  $sleepshift==1)
     {
        $startbreakmin=0;
    }
    else
    {
        $startbreakmin=ceil($alltimemin/2);
    }



         
        $endbreakmin=$startbreakmin+29;







        $starterdate=@$stt;



        $mininhokkyday=0;
        $mininsleeptime=0;
        $normaltime=0;

        $payrate=$payrate / 60;


         $mainpricenormaltime=0;
         $mainpricesleeptime=0;
         $mininhokkydayprice=0;

         $alldefsurch=0;



        $starttimeforcheck=$starttime;
        for ($i=1; $i <= $alltimemin ; $i++)
         { 



       $unbreak=0;



    if ($break==1  and  $sleepshift==1)
     {

      


    }
    else
    {
         if ($break==1) 
        {

             if ( ($startbreakmin <= $i) && ($i <= $endbreakmin)) 
             {
            //      echo $i."---break";
            // echo "<br>";
                 $unbreak=1;
                   $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
             }
        }
    }

















 if ($unbreak==0) 
 {
       $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
          
                
                

            if ($starttimeforcheck=="00:00") 
            {
                $starterdate=@$enn;
            }


                if (in_array($starttimeforcheck, $os)) 
                {

                // echo $i."---sleep---".$starttimeforcheck;
                // echo "<br>";
                    $mininsleeptime++;
                    $mainpricesleeptimetmp=1*$payrate;

                    if ($sleepshift==1) 
                    {
                        $mainpricesleeptimetmp=$mainpricesleeptimetmp/2;
                    }
                    $extraforhalcare=0;

                    if ($registeras=='healthcare') 
                    {
                    $extraforhalcare=($mainpricesleeptimetmp*20)/100;
                    $alldefsurch=$alldefsurch+$extraforhalcare;
                    $mainpricesleeptimetmp=$mainpricesleeptimetmp+$extraforhalcare;
                    }
                    $mainpricesleeptime=$mainpricesleeptime+$mainpricesleeptimetmp;
                }
                else
                {

                  

                if ($starterdate=='01-01' or $starterdate=='17-04' or $starterdate=='18-04' or  $starterdate=='27-04' or $starterdate=='26-05' or $starterdate=='05-06' or $starterdate=='06-06' or $starterdate=='25-12' or $starterdate=='26-12') 
                {
                $mininhokkyday++;
                $mininhokkydaypricetmp=1 * $payrate;
                $extraforhalcaree=0;

                if ($registeras=='healthcare') 
                {
                $extraforhalcaree=($mininhokkydaypricetmp*20)/100;
                $alldefsurch=$alldefsurch+$extraforhalcaree;
                $mininhokkydaypricetmp=$mininhokkydaypricetmp+$extraforhalcaree;
                }
                $mininhokkydayprice=$mininhokkydayprice+$mininhokkydaypricetmp;
                }  
                else
                {
                //          echo $i."---normal---".$starttimeforcheck;
                // echo "<br>";
                  $normaltime++; 
                  $mainpricenormaltimetmp=1 * $payrate; 
                  $mainpricenormaltime=$mainpricenormaltime + $mainpricenormaltimetmp; 
                }

 
                }
 }

             



        }

 

        /// $normaltime=$alltimemin-$mininsleeptime;
        /// $normaltime=$normaltime-$mininhokkyday;



if ($sleepshift==1) 
{
     $sleeepmaintime=$mininsleeptime/2;
}
else
{
    $sleeepmaintime=$mininsleeptime; 
}

    
 
  if ($break==1  and  $sleepshift==1)
     {
   
      $normaltime=$normaltime-30;

     $temppayrateforbreake=30 * $payrate; 

     $mainpricenormaltime=$mainpricenormaltime-$temppayrateforbreake;

     }


$allmintimecal=$normaltime+$mininhokkyday+$sleeepmaintime;

//return $allmintimecal;


$min=($allmintimecal % 60);
$hour=intdiv($allmintimecal, 60);

if ($min<10) 
{
    $min="0".$min;
}




$hours = $hour.':'. $min;

return $hours;
 //return $converted_time = date('H:i', mktime(0,$allmintimecal));



 
        $mainprice=$mainpricesleeptime+$mainpricenormaltime+$mininhokkydayprice;









 




    if ($surchargeassignment>0) 
    {
        $extra=($mainprice*$surchargeassignment)/100;
        $mainprice=$mainprice+$extra;
    }
     return $mainprice;





    }








 
    public static function  getdefaultsurchargecredit($time_to,$time_from,$payrate,$sleepshift,$surchargeassignment,$sleeptime,$registeras,$start_date=null,$end_date=null,$break=0)
    {


if ($start_date!=null)
 {
    $st=explode("-",$start_date);
$stt=$st[2]."-".$st[1];


$en=explode("-",$end_date);
$enn=$en[2]."-".$en[1];


$holyday=0;

if ($stt=='01-01' or $stt=='17-04' or $stt=='18-04' or  $stt=='27-04' or $stt=='26-05' or $stt=='05-06' or $stt=='06-06' or $stt=='25-12' or $stt=='26-12') 
{
    $holyday=1;
}

if ($enn=='01-01' or $enn=='17-04' or $enn=='18-04' or  $enn=='27-04' or $enn=='26-05' or $enn=='05-06' or $enn=='06-06' or $enn=='25-12' or $enn=='26-12') 
{
    $holyday=1;
}
}





        $os = array(
// "23:00",
"23:01",
"23:02",
"23:03",
"23:04",
"23:05",
"23:06",
"23:07",
"23:08",
"23:09",
"23:10",
"23:11",
"23:12",
"23:13",
"23:14",
"23:15",
"23:16",
"23:17",
"23:18",
"23:19",
"23:20",
"23:21",
"23:22",
"23:23",
"23:24",
"23:25",
"23:26",
"23:27",
"23:28",
"23:29",
"23:30",
"23:31",
"23:32",
"23:33",
"23:34",
"23:35",
"23:36",
"23:37",
"23:38",
"23:39",
"23:40",
"23:41",
"23:42",
"23:43",
"23:44",
"23:45",
"23:46",
"23:47",
"23:48",
"23:49",
"23:50",
"23:51",
"23:52",
"23:53",
"23:54",
"23:55",
"23:56",
"23:57",
"23:58",
"23:59",
"00:00",
"00:01",
"00:02",
"00:03",
"00:04",
"00:05",
"00:06",
"00:07",
"00:08",
"00:09",
"00:10",
"00:11",
"00:12",
"00:13",
"00:14",
"00:15",
"00:16",
"00:17",
"00:18",
"00:19",
"00:20",
"00:21",
"00:22",
"00:23",
"00:24",
"00:25",
"00:26",
"00:27",
"00:28",
"00:29",
"00:30",
"00:31",
"00:32",
"00:33",
"00:34",
"00:35",
"00:36",
"00:37",
"00:38",
"00:39",
"00:40",
"00:41",
"00:42",
"00:43",
"00:44",
"00:45",
"00:46",
"00:47",
"00:48",
"00:49",
"00:50",
"00:51",
"00:52",
"00:53",
"00:54",
"00:55",
"00:56",
"00:57",
"00:58",
"00:59",
"01:00",
"01:01",
"01:02",
"01:03",
"01:04",
"01:05",
"01:06",
"01:07",
"01:08",
"01:09",
"01:10",
"01:11",
"01:12",
"01:13",
"01:14",
"01:15",
"01:16",
"01:17",
"01:18",
"01:19",
"01:20",
"01:21",
"01:22",
"01:23",
"01:24",
"01:25",
"01:26",
"01:27",
"01:28",
"01:29",
"01:30",
"01:31",
"01:32",
"01:33",
"01:34",
"01:35",
"01:36",
"01:37",
"01:38",
"01:39",
"01:40",
"01:41",
"01:42",
"01:43",
"01:44",
"01:45",
"01:46",
"01:47",
"01:48",
"01:49",
"01:50",
"01:51",
"01:52",
"01:53",
"01:54",
"01:55",
"01:56",
"01:57",
"01:58",
"01:59",
"02:00",
"02:01",
"02:02",
"02:03",
"02:04",
"02:05",
"02:06",
"02:07",
"02:08",
"02:09",
"02:10",
"02:11",
"02:12",
"02:13",
"02:14",
"02:15",
"02:16",
"02:17",
"02:18",
"02:19",
"02:20",
"02:21",
"02:22",
"02:23",
"02:24",
"02:25",
"02:26",
"02:27",
"02:28",
"02:29",
"02:30",
"02:31",
"02:32",
"02:33",
"02:34",
"02:35",
"02:36",
"02:37",
"02:38",
"02:39",
"02:40",
"02:41",
"02:42",
"02:43",
"02:44",
"02:45",
"02:46",
"02:47",
"02:48",
"02:49",
"02:50",
"02:51",
"02:52",
"02:53",
"02:54",
"02:55",
"02:56",
"02:57",
"02:58",
"02:59",
"03:00",
"03:01",
"03:02",
"03:03",
"03:04",
"03:05",
"03:06",
"03:07",
"03:08",
"03:09",
"03:10",
"03:11",
"03:12",
"03:13",
"03:14",
"03:15",
"03:16",
"03:17",
"03:18",
"03:19",
"03:20",
"03:21",
"03:22",
"03:23",
"03:24",
"03:25",
"03:26",
"03:27",
"03:28",
"03:29",
"03:30",
"03:31",
"03:32",
"03:33",
"03:34",
"03:35",
"03:36",
"03:37",
"03:38",
"03:39",
"03:40",
"03:41",
"03:42",
"03:43",
"03:44",
"03:45",
"03:46",
"03:47",
"03:48",
"03:49",
"03:50",
"03:51",
"03:52",
"03:53",
"03:54",
"03:55",
"03:56",
"03:57",
"03:58",
"03:59",
"04:00",
"04:01",
"04:02",
"04:03",
"04:04",
"04:05",
"04:06",
"04:07",
"04:08",
"04:09",
"04:10",
"04:11",
"04:12",
"04:13",
"04:14",
"04:15",
"04:16",
"04:17",
"04:18",
"04:19",
"04:20",
"04:21",
"04:22",
"04:23",
"04:24",
"04:25",
"04:26",
"04:27",
"04:28",
"04:29",
"04:30",
"04:31",
"04:32",
"04:33",
"04:34",
"04:35",
"04:36",
"04:37",
"04:38",
"04:39",
"04:40",
"04:41",
"04:42",
"04:43",
"04:44",
"04:45",
"04:46",
"04:47",
"04:48",
"04:49",
"04:50",
"04:51",
"04:52",
"04:53",
"04:54",
"04:55",
"04:56",
"04:57",
"04:58",
"04:59",
"05:00",
"05:01",
"05:02",
"05:03",
"05:04",
"05:05",
"05:06",
"05:07",
"05:08",
"05:09",
"05:10",
"05:11",
"05:12",
"05:13",
"05:14",
"05:15",
"05:16",
"05:17",
"05:18",
"05:19",
"05:20",
"05:21",
"05:22",
"05:23",
"05:24",
"05:25",
"05:26",
"05:27",
"05:28",
"05:29",
"05:30",
"05:31",
"05:32",
"05:33",
"05:34",
"05:35",
"05:36",
"05:37",
"05:38",
"05:39",
"05:40",
"05:41",
"05:42",
"05:43",
"05:44",
"05:45",
"05:46",
"05:47",
"05:48",
"05:49",
"05:50",
"05:51",
"05:52",
"05:53",
"05:54",
"05:55",
"05:56",
"05:57",
"05:58",
"05:59",
"06:00",
"06:01",
"06:02",
"06:03",
"06:04",
"06:05",
"06:06",
"06:07",
"06:08",
"06:09",
"06:10",
"06:11",
"06:12",
"06:13",
"06:14",
"06:15",
"06:16",
"06:17",
"06:18",
"06:19",
"06:20",
"06:21",
"06:22",
"06:23",
"06:24",
"06:25",
"06:26",
"06:27",
"06:28",
"06:29",
"06:30",
"06:31",
"06:32",
"06:33",
"06:34",
"06:35",
"06:36",
"06:37",
"06:38",
"06:39",
"06:40",
"06:41",
"06:42",
"06:43",
"06:44",
"06:45",
"06:46",
"06:47",
"06:48",
"06:49",
"06:50",
"06:51",
"06:52",
"06:53",
"06:54",
"06:55",
"06:56",
"06:57",
"06:58",
"06:59",
"07:00",


        );


 

       $alltimestamp=  $time_to - $time_from ;
       $alltime= date('H:i', $alltimestamp);

       $Rh = new Rh;
       $alltime=$Rh::getduration($time_from,$time_to,$start_date,$end_date);


       $arr = explode(':', $alltime);
       if (count($arr) === 3) {
        return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
       }
       $alltimemin= $arr[0] * 60 + $arr[1];
 


       $starttime=date('H:i',$time_from);

        //$startbreakmin=ceil($alltimemin/2);


    if ($break==1  and  $sleepshift==1)
     {
        $startbreakmin=0;
    }
    else
    {
        $startbreakmin=ceil($alltimemin/2);
    }






        $endbreakmin=$startbreakmin+29;







        $starterdate=@$stt;



        $mininhokkyday=0;
        $mininsleeptime=0;
        $normaltime=0;

        $payrate=$payrate / 60;


         $mainpricenormaltime=0;
         $mainpricesleeptime=0;
         $mininhokkydayprice=0;

         $alldefsurch=0;



        $starttimeforcheck=$starttime;
        for ($i=1; $i <= $alltimemin ; $i++)
         { 



       $unbreak=0;


    if ($break==1  and  $sleepshift==1)
     {

      


    }
    else
    {
         if ($break==1) 
        {

             if ( ($startbreakmin <= $i) && ($i <= $endbreakmin)) 
             {
            //      echo $i."---break";
            // echo "<br>";
                 $unbreak=1;
                   $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
             }
        }
    }







        // if ($break==1) 
        // {

        //      if ( ($startbreakmin <= $i) && ($i <= $endbreakmin)) 
        //      {
        //     //      echo $i."---break";
        //     // echo "<br>";
        //          $unbreak=1;
        //            $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
        //      }
        // }




 if ($unbreak==0) 
 {
       $starttimeforcheck = date('H:i', strtotime($starttimeforcheck. ' +1 minutes'));  
          
                
                

            if ($starttimeforcheck=="00:00") 
            {
                $starterdate=@$enn;
            }


                if (in_array($starttimeforcheck, $os)) 
                {

                // echo $i."---sleep---".$starttimeforcheck;
                // echo "<br>";
                    $mininsleeptime++;
                    $mainpricesleeptimetmp=1*$payrate;

                    if ($sleepshift==1) 
                    {
                        $mainpricesleeptimetmp=$mainpricesleeptimetmp/2;
                    }
                    $extraforhalcare=0;

                    if ($registeras=='healthcare') 
                    {
                    $extraforhalcare=($mainpricesleeptimetmp*20)/100;
                    $alldefsurch=$alldefsurch+$extraforhalcare;
                    $mainpricesleeptimetmp=$mainpricesleeptimetmp+$extraforhalcare;
                    }
                    $mainpricesleeptime=$mainpricesleeptime+$mainpricesleeptimetmp;
                }
                else
                {

                  

                if ($starterdate=='01-01' or $starterdate=='17-04' or $starterdate=='18-04' or  $starterdate=='27-04' or $starterdate=='26-05' or $starterdate=='05-06' or $starterdate=='06-06' or $starterdate=='25-12' or $starterdate=='26-12') 
                {
                $mininhokkyday++;
                $mininhokkydaypricetmp=1 * $payrate;
                $extraforhalcaree=0;

                if ($registeras=='healthcare') 
                {
                $extraforhalcaree=($mininhokkydaypricetmp*20)/100;
                $alldefsurch=$alldefsurch+@$extraforhalcaree;
                $mininhokkydaypricetmp=$mininhokkydaypricetmp+$extraforhalcaree;
                }
                $mininhokkydayprice=$mininhokkydayprice+$mininhokkydaypricetmp;
                }  
                else
                {
                //          echo $i."---normal---".$starttimeforcheck;
                // echo "<br>";
                  $normaltime++; 
                  $mainpricenormaltimetmp=1 * $payrate; 
                  $mainpricenormaltime=$mainpricenormaltime + $mainpricenormaltimetmp; 
                }

 
                }
 }

             



        }

 

        /// $normaltime=$alltimemin-$mininsleeptime;
        /// $normaltime=$normaltime-$mininhokkyday;
 
 return $alldefsurch;
        $mainprice=$mainpricesleeptime+$mainpricenormaltime+$mininhokkydayprice;









 




    if ($surchargeassignment>0) 
    {
        $extra=($mainprice*$surchargeassignment)/100;
        $mainprice=$mainprice+$extra;
    }
     return $mainprice;





    }













}
