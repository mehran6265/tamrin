<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\User;
use App\Models\Profile;
use App\Models\Department;
use Carbon\Carbon;
use Exception;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\CustomClass\Rh;
use App\CustomClass\CustomAuth;
use App\Models\Documentsetting;
use App\Models\Image;
use App\Models\Languagejson;
use Lang;
class HomeController extends Controller
{


    public function __construct()
    {
      $this->middleware('auth');

    }




    public function index()
    {
 
 



//    $CustomAuth = new CustomAuth;

// echo $CustomAuth::userpermission('Total freelancer','Dashboard');
// exit;

   

      $Rh = new Rh;
if (isset($_GET['detass']))
 {
 $Profiles = Profile::select("first_name", "last_name","company_name","user_id")->get();

     $Assignment =  Assignment::where("id",(int)$_GET['detass'])->get();
        
// $Assignment = Assignment::find((int)$_GET['detass']);
 
foreach ($Assignment as $row) 
{
 
 

?>

<div><div><div><div><div class="modal-body"> 

  <?php echo __('Start date') ;?>  : <?php echo $Rh::eurodate($row->start_date);?>   

<br>

  <?php echo __('Time') ;?>  :  <?php echo date('H:i', $row->time_from) ;?> - <?php echo date('H:i', $row->time_to) ;?> 

<br>

  <?php echo __('Client') ;?>  :  <?php echo $Rh::getclientnameforcal($row->client_id,$Profiles);?>  


<br>

  <?php echo __('Department') ;?>  : <?php echo $Rh::getdepartmentname($row->department_id);?> 

<br>
  <?php echo __('Employee');?>  : <?php echo $Rh::getuserinfo($row->employee_id)[0]->first_name;?> <?php echo $Rh::getuserinfo($row->employee_id)[0]->last_name;?> 

<br>

  <?php echo __('Duration') ;?>  : <?php echo $Rh::getduration($row->time_from,$row->time_to,$row->start_date,$row->end_date);?>  


<br>
  <?php echo __('Break');?>  :      <?php 

if ($row->break==0) 
{
?>
<?php
}
else
{
?>
30 min<?php
}
?> 


<br>

<?php echo __('sleepshift') ;?>  : <?php if ($row->sleepshift==1)
{
?>
<?php 
if ($row->employee_id==1) 
{
$patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);
?>
 <?php $firstprice=$Rh::getsleeptime($row->time_to,$row->time_from,@$patratetmp[0]->payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);    echo $firstprice; ?> <?php
}
else
{
?>
 <?php $firstprice=$Rh::getsleeptime($row->time_to,$row->time_from,$row->payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);    echo $firstprice; ?>  <?php
}
?>
<?php
}
else
{
?>
<?php   
} 
?> 



<br>

<?php echo __('Calculated number hours') ;?>  : <?php 
if ($row->employee_id==1) 
{
$patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);
?>
<?php $firstprice=$Rh::calculatednumberhours($row->time_to,$row->time_from,@$patratetmp[0]->payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);    echo $firstprice; ?> <?php
}
else
{
?>
<?php $firstprice=$Rh::calculatednumberhours($row->time_to,$row->time_from,$row->payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);    echo $firstprice; ?> <?php
}

?> 

<br>

<?php 
if ($row->registeras=="healthcare")
{
?>
 <?php echo __('Default Surcharge') ;?>  :<?php 
if ($row->employee_id==1) 
{
$patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);
?>€ <?php $firstprice=$Rh::getdefaultsurcharge($row->time_to,$row->time_from,@$patratetmp[0]->payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);    echo number_format($firstprice,2,',','.'); ?> 
<br>
<?php
}
else
{
?>€ <?php $firstprice=$Rh::getdefaultsurcharge(@$row->time_to,@$row->time_from,@$row->payrate,@$row->sleepshift,@$row->surchargeassignment,@$row->sleeptime,@$row->registeras,@$row->start_date,@$row->end_date,@$row->break);    echo number_format($firstprice,2,',','.'); ?>
<br>
<?php
}
?> <?php
}
?>




<?php 
                    if ($row->registeras=="healthcare") 
                    {
                        ?>
                        <?php echo __('Payrate') ;?> : <?php 
                        if ($row->employee_id==1) 
                        {


                            $patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);

                            ?>
                            € <?php echo number_format(@$patratetmp[0]->payrate,2,",",".")   ;?>                            <?php
                        }
                        else
                        {
                            ?>
                            € <?php echo number_format($row->payrate,2,",",".") ;?>                            <?php
                        }

                        ?>
                        <?php
                    }
                    else
                    {
                        ?>
                        <?php
                        if (Auth::user()->user_type=="ADMIN" or Auth::user()->user_type=="EMPLOYEE" )
                        {
                            ?>
                        <?php echo __('Payrate') ;?> :                            <?php 
                            if ($row->employee_id==1) 
                            {
                                $patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);

                                ?>
                                € <?php echo number_format(@$patratetmp[0]->payrate,2,",",".")    ;?>                                <?php
                            }
                            else
                            {
                                ?>
                             € <?php echo number_format($row->payrate,2,",",".") ;?>                                <?php
                            }

                            ?>
                            <?php
                        }
                        else
                        {
                           ?>
                           <!-- <li>-</li> -->                           <?php
                       }
                       ?>
                       <?php
                   }
                   ?>


<br>


                   <?php 
                   if (Auth::user()->user_type!="EMPLOYEE")
                   {
                    ?>
                 <?php echo __('Client Payrate') ;?> :<?php
                }
                ?>
                <?php 
                if ($row->registeras=="healthcare") 
                {
                    if (Auth::user()->user_type!="EMPLOYEE") 
                    {
                        ?>
                        <?php 
                        if ($row->employee_id==1) 
                        {
                         $patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);
                         ?>
                         €  <?php echo number_format(@$patratetmp[0]->client_payrate,2,",",".")    ;?>                         <?php
                     }
                     else
                     {
                        ?>
                         € <?php echo number_format($row->client_payrate,2,",",".") ;?>                         <?php
                    }

                    ?>
                    <?php
                }
                else
                {
                    ?>
                    <!-- <li>-</li> -->                    <?php
                }
            }
            else
            {
                ?>
                <?php 
                if (Auth::user()->user_type!="EMPLOYEE" )
                {
                    ?>
                    <?php 
                    if ($row->employee_id==1) 
                    {
                       $patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);
                       ?>
                       € <?php echo number_format(@$patratetmp[0]->client_payrate,2,",",".")   ;?>                       <?php
                   }
                   else
                   {
                    ?>
                    € <?php echo number_format($row->client_payrate,2,",",".") ;?>                     <?php
                }

                ?>
                <?php
            }
            else
            {
             ?>
             <!-- <li>-</li> -->             <?php
         }
         ?>
         <?php
     }
     ?>

     <br>

     <?php 

     if ($row->registeras=="healthcare") 
     {
        ?>
        <?php echo __('Total Payrate') ;?> :         <?php 
        if ($row->employee_id==1) 
        {
          $patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);
          ?>
         € <?php $firstprice=$Rh::calculationinvoicepayrate($row->time_to,$row->time_from,@$patratetmp[0]->payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);    echo number_format($firstprice,2,",","."); ?>           <?php
      }
      else
      {
        ?>
       € <?php $firstprice=$Rh::calculationinvoicepayrate($row->time_to,$row->time_from,$row->payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);    echo number_format($firstprice,2,",","."); ?>          <?php
    }

    ?>
    <?php
}
else
{
    ?>
    <?php
    if (Auth::user()->user_type=="ADMIN" or Auth::user()->user_type=="EMPLOYEE" )
    {
        ?>
      <?php echo __('Total Payrate') ;?> :       <?php 
        if ($row->employee_id==1) 
        {
          $patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);
          ?>
           € <?php $firstprice=$Rh::calculationinvoicepayrate($row->time_to,$row->time_from,@$patratetmp[0]->payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);    echo number_format($firstprice , 2,",","."); ?>           <?php
      }
      else
      {
        ?>
         € <?php $firstprice=$Rh::calculationinvoicepayrate($row->time_to,$row->time_from,$row->payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);    echo number_format($firstprice , 2,",","."); ?>         <?php
    }

    ?>
    <?php
}
else
{
   ?>
   <!-- <li>-</li> -->   <?php
}
?>
<?php
}
?>


<br>

<?php 
if (Auth::user()->user_type!="EMPLOYEE" )
{
    ?>
     <?php echo __('Total Client Payrate');?> :     <?php
}

?>
<?php 
if ($row->registeras=="healthcare") 
{
    if (Auth::user()->user_type!="EMPLOYEE") 
    {
        ?>
        <?php 
        if ($row->employee_id==1) 
        {
         $patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);
         ?>
           € <?php $seccondprice=$Rh::calculationinvoiceclientpayrate($row->time_to,$row->time_from,@$patratetmp[0]->client_payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);  echo number_format($seccondprice,2,",","."); ?>           <?php
     }
     else
     {
        ?>
         € <?php $seccondprice=$Rh::calculationinvoiceclientpayrate($row->time_to,$row->time_from,$row->client_payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);  echo number_format($seccondprice,2,",","."); ?>          <?php
    }

    ?>
    <?php
}
else
{
   ?>
   <!-- <li>-</li> -->   <?php  
}
}
else
{
    ?>
    <?php 
    if (Auth::user()->user_type!="EMPLOYEE" )
    {
        ?>
        <?php 
        if ($row->employee_id==1) 
        {
         $patratetmp=$Rh::getjoinclientpayrates(Auth::user()->id,$row->client_id,$row->registeras);
         ?>
           € <?php $seccondprice=$Rh::calculationinvoiceclientpayrate($row->time_to,$row->time_from,@$patratetmp[0]->client_payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);  echo number_format($seccondprice , 2,",","."); ?>           <?php
     }
     else
     {
        ?>
          € <?php $seccondprice=$Rh::calculationinvoiceclientpayrate($row->time_to,$row->time_from,$row->client_payrate,$row->sleepshift,$row->surchargeassignment,$row->sleeptime,$row->registeras,$row->start_date,$row->end_date,$row->break);  echo number_format($seccondprice , 2,",","."); ?>          <?php
    }

    ?>
    <?php
}
else
{
 ?>
 <!-- <li>-</li> --> <?php
}
?>
<?php
}
?>
  </div></div></div></div></div>

<?php
}
exit;
}





      if (isset($_GET['dashboard']))
      {
        $month=date("m");
        $year=date("Y");

        if (isset($_GET['month']))
        {
         $month=$_GET['month'];
       }

       if (isset($_GET['year']))
       {
         $year=$_GET['year'];
       }


      if ($month<10) 
      {
        $month=$month+1;
        $month=$month-1;

        $month='0'.$month;
      }




       $assignmentsdates = DB::table('assignments')
       ->select('start_date')
       ->where(["month"=>$month,"year"=>$year])
       ->orderBy("day","asc")
       ->get()->unique('start_date');


$Rh = new Rh;
  $Profiles = Profile::select("first_name", "last_name","company_name","user_id")->get();



if(Auth::user()->user_type=="EMPLOYEE")
{

  $assignments = DB::table('assignments')
  ->join('departments', 'assignments.department_id', '=', 'departments.id')
  ->join('profiles', 'assignments.employee_id', '=', 'profiles.user_id')
           ->where("assignments.start_date", '>=' ,date('Y-m-d', strtotime(date('Y-m-d'). ' -1 day')))
  ->where(['assignments.employee_id'=>Auth::user()->id,'assignments.status'=>"EMPLOYEE_ACCEPTED","month"=>$month,"year"=>$year])
  ->select('assignments.time_from','assignments.time_to','assignments.start_date','assignments.end_date', 'profiles.first_name as first_name', 'profiles.last_name as last_name','departments.title','assignments.employee_id','assignments.department_id','assignments.registeras','assignments.id','assignments.surchargeassignment','assignments.sleepshift','assignments.payrate','assignments.client_payrate','assignments.sleeptime','assignments.break','profiles.company_name','assignments.client_id')
  ->get();


// dd($assignments);
            // $assignments = Assignment::where(['employee_id'=>Auth::user()->id,'status'=>"EMPLOYEE_ACCEPTED"])->whereDate("start_date", '>=' ,Carbon::now())->paginate(50);
}
else
  if(Auth::user()->user_type=="ADMIN")
  {

    $assignments = DB::table('assignments')
    ->join('departments', 'assignments.department_id', '=', 'departments.id')
    ->join('profiles', 'assignments.employee_id', '=', 'profiles.user_id')
             ->where("assignments.start_date", '>=' ,date('Y-m-d', strtotime(date('Y-m-d'). ' -1 day')))
    ->where(['assignments.status'=>"EMPLOYEE_ACCEPTED","month"=>$month,"year"=>$year])
    ->select('assignments.time_from','assignments.time_to','assignments.start_date','assignments.end_date', 'profiles.first_name as first_name', 'profiles.last_name as last_name','departments.title','assignments.employee_id','assignments.department_id','assignments.registeras','assignments.id','assignments.surchargeassignment','assignments.sleepshift','assignments.payrate','assignments.client_payrate','assignments.sleeptime','assignments.break','profiles.company_name','assignments.client_id')
    ->get();


  }
  else
    if(Auth::user()->user_type=="CLIENT")
    {
      $assignments = DB::table('assignments')
      ->join('departments', 'assignments.department_id', '=', 'departments.id')
      ->join('profiles', 'assignments.employee_id', '=', 'profiles.user_id')
             ->where("assignments.start_date", '>=' ,date('Y-m-d', strtotime(date('Y-m-d'). ' -1 day')))
      ->where(['assignments.client_id'=>Auth::user()->id,'assignments.status'=>"EMPLOYEE_ACCEPTED","month"=>$month,"year"=>$year])
      ->select('assignments.time_from','assignments.time_to','assignments.start_date','assignments.end_date', 'profiles.first_name as first_name', 'profiles.last_name as last_name','departments.title','assignments.employee_id','assignments.department_id','assignments.registeras','assignments.id','assignments.surchargeassignment','assignments.sleepshift','assignments.payrate','assignments.client_payrate','assignments.sleeptime','assignments.break','profiles.company_name','assignments.client_id')
      ->get();



    }
    else
      if(Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
      {
        $assignments = DB::table('assignments')
        ->join('departments', 'assignments.department_id', '=', 'departments.id')
        ->join('profiles', 'assignments.employee_id', '=', 'profiles.user_id')
             ->where("assignments.start_date", '>=' ,date('Y-m-d', strtotime(date('Y-m-d'). ' -1 day')))
        ->where(['assignments.client_id'=>Auth::user()->client_id,'assignments.status'=>"EMPLOYEE_ACCEPTED","month"=>$month,"year"=>$year])
        ->select('assignments.time_from','assignments.time_to','assignments.start_date','assignments.end_date', 'profiles.first_name as first_name', 'profiles.last_name as last_name','departments.title','assignments.employee_id','assignments.department_id','assignments.registeras','assignments.id','assignments.surchargeassignment','assignments.sleepshift','assignments.payrate','assignments.client_payrate','assignments.sleeptime','assignments.break','profiles.company_name','assignments.client_id')
        ->get();


      }




       foreach ($assignmentsdates as $roww) 
       {
        $has=0;
            foreach ($assignments as $assignment) 
            {
              if ($roww->start_date==$assignment->start_date) 
              {
                 $has++;
              }
            }
        ?>
        <table class="table">
          <thead>
            <?php 
            if ($has>0)
             {
              ?>
            <tr>
              <th><?php echo $roww->start_date; ?></th>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
            </tr>
              <?php
            }
             ?>

          </thead>
          <tbody>
            <?php
            foreach ($assignments as $assignment) 
            {
              if ($roww->start_date==$assignment->start_date) 
              {
               ?>
               <tr>
                <td style="text-align:left;width:120px;"><?php echo date('H:i', $assignment->time_from)." - ".date('H:i', $assignment->time_to); ?></td>
                <td style="width: 120px;"></td>
                <td style="width:120px;"></td>
                <td style="text-align:left;">
              <?php 
                $freelancerzz="";
                $titr="";
                 if (Auth::user()->user_type!="EMPLOYEE") 
                 {
                  $freelancerzz=$assignment->first_name.' '.$assignment->last_name;
                 }
                 if (Auth::user()->user_type=="EMPLOYEE") 
                 {
                  $titr=$assignment->title." - ".date('H:i', $assignment->time_from)." - ".date('H:i', $assignment->time_to);
                 }
                 if (Auth::user()->user_type=="ADMIN") 
                 {
                  $titr="<div style='width: 250px;float: left;'>".$Rh::getclientnameforcal($assignment->client_id,$Profiles)."</div><div style='width: 20px;float: left;'>   </div> <div style='width: 250px;float: left;'>".$assignment->title."</div> <div style='width: 20px;float: left;'>   </div> <div style='width: 250px;float: left;'>".$assignment->first_name." ".$assignment->last_name."</div>";
                 }
                 if (Auth::user()->user_type=="CLIENT" or Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL") 
                 {
                  $titr=$assignment->title." - ".$assignment->first_name." ".$assignment->last_name;
                 }
                ?>
                  <div class="tooltipee" id="ass<?php echo $assignment->id; ?>" style="z-index:99999;text-align: left;" >
                   <span   class="tooltiptextee" id="assdets<?php echo $assignment->id; ?>"></span>
                    <a style="text-align:left;"  href="/<?php echo app()->getLocale(); ?>/assignmentspage/<?php echo $assignment->id; ?>"><?php echo $titr; ?>
                    </a>
                  </div>
                </td>
                <td></td>



              </tr>











              <?php
            }
          }
          ?>
        </tbody>
      </table>



      <?php
    }


    ?>
<script>
$( ".tooltipee" ).hover(
  function() {

    idd=$(this).attr('id');
    let id = idd.substring(3);
 
   $.ajax({url: "/nl/home?detass="+id, success: function(result){
    // alert(result);
         
                $('#assdets'+id).html(result);
                }});
  },
   function() {

    id=$(this).attr('id');

    // alert('out');
  }
);
</script>
    <?php
    exit;
  }





  if (isset($_GET['sidebarcolorlight']))
  {
   $user = User::findOrFail(Auth::user()->id);
   if($_GET['sidebarcolorlight']=='sidebarcolorlight1'){
    $_GET['sidebarcolorlight']="light";
  }
  else
    if($_GET['sidebarcolorlight']=='sidebarcolorlight2'){
      $_GET['sidebarcolorlight']="dark";
    }

    $user->datasidebar=$_GET['sidebarcolorlight'];
    $user->save();
  }



  if (isset($_GET['datatopbar']))
  {
   $user = User::findOrFail(Auth::user()->id);
   if($_GET['datatopbar']=='datatopbar1'){
    $_GET['datatopbar']="light";
  }
  else
    if($_GET['datatopbar']=='datatopbar2'){
      $_GET['datatopbar']="dark";
    }

    $user->datatopbar=$_GET['datatopbar'];
    $user->save();
  }



  if (isset($_GET['datasidebarsize']))
  {
   $user = User::findOrFail(Auth::user()->id);
   if($_GET['datasidebarsize']=='datasidebarsize1'){
    $_GET['datasidebarsize']="lg";
  }
  else
    if($_GET['datasidebarsize']=='datasidebarsize2'){
      $_GET['datasidebarsize']="sm";
    }
    else
    {
     $_GET['datasidebarsize']="sm-hover";
   }
   $user->datasidebarsize=$_GET['datasidebarsize'];
   $user->save();
 }



 if (isset($_GET['datalayoutstyle']))
 {
   $user = User::findOrFail(Auth::user()->id);
   if($_GET['datalayoutstyle']=='datalayoutstyle1'){
    $_GET['datalayoutstyle']="default";
  }
  else
  {
   $_GET['datalayoutstyle']="detached";
 }
 $user->datalayoutstyle=$_GET['datalayoutstyle'];
 $user->save();
 exit;
}




if (isset($_GET['datalayoutmode']))
{
 $user = User::findOrFail(Auth::user()->id);
 if($_GET['datalayoutmode']=='datalayoutmode1'){
  $_GET['datalayoutmode']="light";
}
else
{
 $_GET['datalayoutmode']="dark";
}
$user->datalayoutmode=$_GET['datalayoutmode'];
$user->save();
exit;
}


if (isset($_GET['datalayout']))
{
 $user = User::findOrFail(Auth::user()->id);
 if($_GET['datalayout']=='datalayout1'){
  $_GET['datalayout']="vertical";
}
else
{
 $_GET['datalayout']="horizontal";
}
$user->datalayout=$_GET['datalayout'];
$user->save();
exit;
}



if(Auth::user()->user_type=="EMPLOYEE")
{

  $assignments = DB::table('assignments')
  ->join('departments', 'assignments.department_id', '=', 'departments.id')
  ->join('profiles', 'assignments.employee_id', '=', 'profiles.user_id')
          // ->where("assignments.start_date", '>=' ,Carbon::now())
  ->where(['assignments.employee_id'=>Auth::user()->id,'assignments.status'=>"EMPLOYEE_ACCEPTED"])->whereDate("assignments.start_date", '>=' , Carbon::now()->subDays(1))
  ->select('assignments.time_from','assignments.time_to','assignments.start_date','assignments.end_date', 'profiles.first_name as first_name', 'profiles.last_name as last_name','departments.title','assignments.employee_id','assignments.department_id','assignments.registeras','assignments.id','assignments.surchargeassignment','assignments.sleepshift','assignments.payrate','assignments.client_payrate','assignments.sleeptime','assignments.break','profiles.company_name','assignments.client_id')
  ->paginate(50);


// dd($assignments);
            // $assignments = Assignment::where(['employee_id'=>Auth::user()->id,'status'=>"EMPLOYEE_ACCEPTED"])->whereDate("start_date", '>=' ,Carbon::now())->paginate(50);
}
else
  if(Auth::user()->user_type=="ADMIN")
  {

    $assignments = DB::table('assignments')
    ->join('departments', 'assignments.department_id', '=', 'departments.id')
    ->join('profiles', 'assignments.employee_id', '=', 'profiles.user_id')
          // ->where("assignments.start_date", '>=' ,Carbon::now())
    ->where(['assignments.status'=>"EMPLOYEE_ACCEPTED"])->whereDate("assignments.start_date", '>=' , Carbon::now()->subDays(1))
    ->select('assignments.time_from','assignments.time_to','assignments.start_date','assignments.end_date', 'profiles.first_name as first_name', 'profiles.last_name as last_name','departments.title','assignments.employee_id','assignments.department_id','assignments.registeras','assignments.id','assignments.surchargeassignment','assignments.sleepshift','assignments.payrate','assignments.client_payrate','assignments.sleeptime','assignments.break','profiles.company_name','assignments.client_id')
    ->paginate(50);


  }
  else
    if(Auth::user()->user_type=="CLIENT")
    {
      $assignments = DB::table('assignments')
      ->join('departments', 'assignments.department_id', '=', 'departments.id')
      ->join('profiles', 'assignments.employee_id', '=', 'profiles.user_id')
          // ->where("assignments.start_date", '>=' ,Carbon::now())
      ->where(['assignments.client_id'=>Auth::user()->id,'assignments.status'=>"EMPLOYEE_ACCEPTED"])->whereDate("assignments.start_date", '>=' , Carbon::now()->subDays(1))
      ->select('assignments.time_from','assignments.time_to','assignments.start_date','assignments.end_date', 'profiles.first_name as first_name', 'profiles.last_name as last_name','departments.title','assignments.employee_id','assignments.department_id','assignments.registeras','assignments.id','assignments.surchargeassignment','assignments.sleepshift','assignments.payrate','assignments.client_payrate','assignments.sleeptime','assignments.break','profiles.company_name','assignments.client_id')
      ->paginate(50);



    }
    else
      if(Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
      {
        $assignments = DB::table('assignments')
        ->join('departments', 'assignments.department_id', '=', 'departments.id')
        ->join('profiles', 'assignments.employee_id', '=', 'profiles.user_id')
          // ->where("assignments.start_date", '>=' ,Carbon::now())
        ->where(['assignments.client_id'=>Auth::user()->client_id,'assignments.status'=>"EMPLOYEE_ACCEPTED"])->whereDate("assignments.start_date", '>=' , Carbon::now()->subDays(1))
        ->select('assignments.time_from','assignments.time_to','assignments.start_date','assignments.end_date', 'profiles.first_name as first_name', 'profiles.last_name as last_name','departments.title','assignments.employee_id','assignments.department_id','assignments.registeras','assignments.id','assignments.surchargeassignment','assignments.sleepshift','assignments.payrate','assignments.client_payrate','assignments.sleeptime','assignments.break','profiles.company_name','assignments.client_id')
        ->paginate(50);


      }



 // dd($assignments);

      $Profiles = Profile::select("first_name", "last_name","company_name","user_id")->get();

 $User = User::findOrFail(Auth::user()->id);
  $User->alertseen=0;
$User->save();
      return view('home')->with(["assignments" => $assignments,'Profiles'=>$Profiles]);


    }





    public function indexx()
    {


      if (isset($_GET['push']))
      {
        $user = User::findOrFail(Auth::user()->id);

        if ($user->leftside==0)
        {
         $user->leftside=1;
       }
       else
       {
        $user->leftside=0;  
      }

      $user->save();

      exit;
    }



// $url=$_SERVER['HTTP_HOST'];
// if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
//     $link = "https";
// else
// $link = "http";


// if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
//     if ($url!='localhost:8000') 
//     {
//     $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//     header('HTTP/1.1 301 Moved Permanently');
//     header('Location: ' . $location);
//     exit;
//     }
// }





    $not_allowed_statuses = ["CLIENT_ACCEPTED"];
    $assignment_department_ids = Assignment::where('employee_id', Auth::user()->id)->whereIn("status", $not_allowed_statuses)->whereDate("start_date", '>=' ,Carbon::now())->pluck('department_id')->toArray();



    $available_departments = Department::whereIn("id", $assignment_department_ids)->where("is_available", true)->get();


        // $available_departments = Department::where("is_public", true)->where("is_available", true)->get();
        // $available_departments = Department::where("is_public", true)->where("is_available", true)->paginate(10);


// dd($available_departments);


    return view('home')->with([
      'available_departments' => $available_departments
    ]);
  }
}
