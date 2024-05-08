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
use App\Models\Agreementsetting;
class FiltersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }



  public function party2()
    {
$records=null;
        $agreementsetting = Agreementsetting::where(["id"=> $_GET['agreementtypeid']])->get();

        if (!$agreementsetting->isEmpty())
        { 
            
          
                if ($agreementsetting[0]->part2=="EMPLOYEE")
                 {
                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.user_type'=>'EMPLOYEE'])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();
                }
                else
                    if ($agreementsetting[0]->part2=="ADMIN")
                {
                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.user_type'=>'ADMIN'])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();
                }
                else
                    if ($agreementsetting[0]->part2=="MEDIATOR")
                {
                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.user_type'=>'MEDIATOR'])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();
                }
                else
                    if ($agreementsetting[0]->part2=="CLIENT")
                {
                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.user_type'=>'CLIENT'])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();
                }
                else
                if ($agreementsetting[0]->part2=="ACCOUNTANT")
                {
                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.user_type'=>'ACCOUNTANT'])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();
                }

        }



      ?>
            <select  class="select2 form-select w-100 ifpartiesselected2">
                <option value="0"><?php echo __('Select One'); ?></option>
                <?php foreach (@$records as $row) { ?>
                <option value="<?php echo @$row->id; ?>"><?php echo  $row->first_name." ".$row->last_name; ?></option>
                <?php } ?>
            </select>


            <script type="text/javascript">
    $('.ifpartiesselected2').on('change', function() {

   var ifpartiesselected2 = $('.ifpartiesselected2').find(":selected").val(); 

   if(ifpartiesselected2==0)
   {
    CKEDITOR.instances.Party2details.setData('');
   }
   else
   {

$.ajax({url: "/en/getusersdetailsinagreement?id="+ifpartiesselected2, success: function(result){
   
                CKEDITOR.instances.Party2details.setData(result);
                }});
   }
 

               
               
 

});
</script>


      <?php
    }



  public function getusersdetailsinagreement()
    {

                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.id'=>$_GET['id']])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();

$text="freelancer ".$records[0]->first_name;

     return $text;
    }







  public function party1()
    {


        $records=null;
        $agreementsetting = Agreementsetting::where(["id"=> $_GET['agreementtypeid']])->get();

        if (isset($_GET['justid'])) 
        {
            $ressss="";
            if ($_GET['justid']=="box1") 
            {
                if ($agreementsetting[0]->box1=="Input") 
                {
                    return 1;
                }
                else
                if ($agreementsetting[0]->box1=="Assignment details") 
                {
                   ?>
                       
                    <?php 
                    if ($agreementsetting[0]->box1turn1==1) 
                    {
                        ?>
                    <div class="form-check">
                      <label class="form-check-label" for="check2">Client Comapny name</label>
                    </div>
                        <?php

                    }
                    ?>

                                           <?php 
                    if ($agreementsetting[0]->box1turn2==1) 
                    {
                        ?>
                          <div class="form-check">
                              <label class="form-check-label">Department name</label>
                            </div>
                        <?php
                    }
                    ?>

                                              <?php 
                    if ($agreementsetting[0]->box1turn3==1) 
                    {
                        ?>
                        <div class="form-check">
                              <label class="form-check-label">Department adress</label>
                            </div>
                        <?php
                    }
                    ?>


                                               <?php 
                    if ($agreementsetting[0]->box1turn4==1) 
                    {
                        ?>
                       <div class="form-check">
                              <label class="form-check-label">Jobtitle</label>
                            </div>
                        <?php
                    }
                    ?>

                    <?php 
                    if ($agreementsetting[0]->box1turn5==1) 
                    {
                        ?>
                       <div class="form-check">
                              <label class="form-check-label">Start date + time</label>
                            </div>
                        <?php
                    }
                    ?>
                           
                                               <?php 
                    if ($agreementsetting[0]->box1turn6==1) 
                    {
                        ?>
                        <div class="form-check">
                              <label class="form-check-label">End date + time</label>
                            </div>
                        <?php
                    }
                    ?>


                                                <?php 
                    if ($agreementsetting[0]->box1turn7==1) 
                    {
                        ?>
                      <div class="form-check">
                              <label class="form-check-label">Total number hours</label>
                            </div>
                        <?php
                    }
                    ?>

                    <?php 
                    if ($agreementsetting[0]->box1turn8==1) 
                    {
                        ?>
                    <div class="form-check">
                              <label class="form-check-label">Payrate</label>
                            </div>
                        <?php
                    }
                    ?>
                          
                          
                                           <?php 
                    if ($agreementsetting[0]->box1turn9==1) 
                    {
                        ?>
                     <div class="form-check">
                              <label class="form-check-label">Fee</label>
                            </div>
                        <?php
                    }
                    ?>


                        
                   <?php
                   exit; 
                }
                return 0;
            }
            if ($_GET['justid']=="box2") 
            {
                if ($agreementsetting[0]->box2=="Input") 
                {
                    return 1;
                }
                else
                if ($agreementsetting[0]->box2=="Assignment details") 
                {
                    

  ?>
                       
                    <?php 
                    if ($agreementsetting[0]->box2turn1==1) 
                    {
                        ?>
                    <div class="form-check">
                      <label class="form-check-label" for="check2">Client Comapny name</label>
                    </div>
                        <?php
                    }
                    ?>

                                           <?php 
                    if ($agreementsetting[0]->box2turn2==1) 
                    {
                        ?>
                          <div class="form-check">
                              <label class="form-check-label">Department name</label>
                            </div>
                        <?php
                    }
                    ?>

                                              <?php 
                    if ($agreementsetting[0]->box2turn3==1) 
                    {
                        ?>
                        <div class="form-check">
                              <label class="form-check-label">Department adress</label>
                            </div>
                        <?php
                    }
                    ?>


                                               <?php 
                    if ($agreementsetting[0]->box2turn4==1) 
                    {
                        ?>
                       <div class="form-check">
                              <label class="form-check-label">Jobtitle</label>
                            </div>
                        <?php
                    }
                    ?>

                    <?php 
                    if ($agreementsetting[0]->box2turn5==1) 
                    {
                        ?>
                       <div class="form-check">
                              <label class="form-check-label">Start date + time</label>
                            </div>
                        <?php
                    }
                    ?>
                           
                                               <?php 
                    if ($agreementsetting[0]->box2turn6==1) 
                    {
                        ?>
                        <div class="form-check">
                              <label class="form-check-label">End date + time</label>
                            </div>
                        <?php
                    }
                    ?>


                                                <?php 
                    if ($agreementsetting[0]->box2turn7==1) 
                    {
                        ?>
                      <div class="form-check">
                              <label class="form-check-label">Total number hours</label>
                            </div>
                        <?php
                    }
                    ?>

                    <?php 
                    if ($agreementsetting[0]->box2turn8==1) 
                    {
                        ?>
                    <div class="form-check">
                              <label class="form-check-label">Payrate</label>
                            </div>
                        <?php
                    }
                    ?>
                          
                          
                                           <?php 
                    if ($agreementsetting[0]->box2turn9==1) 
                    {
                        ?>
                     <div class="form-check">
                              <label class="form-check-label">Fee</label>
                            </div>
                        <?php
                    }
                    ?>

                      
                        
                   <?php

             exit; 

                }
                return 0;
            }
            if ($_GET['justid']=="box3") 
            {
                if ($agreementsetting[0]->box3=="Input") 
                {
                    return 1;
                }
                else
                if ($agreementsetting[0]->box3=="Assignment details") 
                {
                   

  ?>
                       
                    <?php 
                    if ($agreementsetting[0]->box3turn1==1) 
                    {
                        ?>
                    <div class="form-check">
                      <label class="form-check-label" for="check2">Client Comapny name</label>
                    </div>
                        <?php
                    }
                    ?>

                                           <?php 
                    if ($agreementsetting[0]->box3turn2==1) 
                    {
                        ?>
                          <div class="form-check">
                              <label class="form-check-label">Department name</label>
                            </div>
                        <?php
                    }
                    ?>

                                              <?php 
                    if ($agreementsetting[0]->box3turn3==1) 
                    {
                        ?>
                        <div class="form-check">
                              <label class="form-check-label">Department adress</label>
                            </div>
                        <?php
                    }
                    ?>


                                               <?php 
                    if ($agreementsetting[0]->box3turn4==1) 
                    {
                        ?>
                       <div class="form-check">
                              <label class="form-check-label">Jobtitle</label>
                            </div>
                        <?php
                    }
                    ?>

                    <?php 
                    if ($agreementsetting[0]->box3turn5==1) 
                    {
                        ?>
                       <div class="form-check">
                              <label class="form-check-label">Start date + time</label>
                            </div>
                        <?php
                    }
                    ?>
                           
                                               <?php 
                    if ($agreementsetting[0]->box3turn6==1) 
                    {
                        ?>
                        <div class="form-check">
                              <label class="form-check-label">End date + time</label>
                            </div>
                        <?php
                    }
                    ?>


                                                <?php 
                    if ($agreementsetting[0]->box3turn7==1) 
                    {
                        ?>
                      <div class="form-check">
                              <label class="form-check-label">Total number hours</label>
                            </div>
                        <?php
                    }
                    ?>

                    <?php 
                    if ($agreementsetting[0]->box3turn8==1) 
                    {
                        ?>
                    <div class="form-check">
                              <label class="form-check-label">Payrate</label>
                            </div>
                        <?php
                    }
                    ?>
                          
                          
                                           <?php 
                    if ($agreementsetting[0]->box3turn9==1) 
                    {
                        ?>
                     <div class="form-check">
                              <label class="form-check-label">Fee</label>
                            </div>
                        <?php
                    }
                    ?>

                      
                        
                   <?php

exit; 

                }
                return 0;
            }
            exit; 
        }




        if (!$agreementsetting->isEmpty())
        { 
          
          
                if ($agreementsetting[0]->part1=="EMPLOYEE")
                 {
                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.user_type'=>'EMPLOYEE'])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();
                }
                else
                    if ($agreementsetting[0]->part1=="ADMIN")
                {
                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.user_type'=>'ADMIN'])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();
                }
                else
                    if ($agreementsetting[0]->part1=="MEDIATOR")
                {
                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.user_type'=>'MEDIATOR'])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();
                }
                else
                    if ($agreementsetting[0]->part1=="CLIENT")
                {
                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.user_type'=>'CLIENT'])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();
                }
                else
                if ($agreementsetting[0]->part1=="ACCOUNTANT")
                {
                  $records = DB::table('users')
                  ->join('profiles', 'profiles.user_id', '=', 'users.id')
                  ->where(['users.user_type'=>'ACCOUNTANT'])
                  ->select('profiles.first_name', 'profiles.last_name', 'profiles.company_name', 'users.id')
                  ->get();
                }


        }



      ?>
            <select  class="select2 form-select w-100 ifpartiesselected1">
                <option value="0"><?php echo __('Select One'); ?></option>
                <?php foreach (@$records as $row) { ?>
                <option value="<?php echo @$row->id; ?>"><?php echo  $row->first_name." ".$row->last_name; ?></option>
                <?php } ?>
            </select>

<script type="text/javascript">
    $('.ifpartiesselected1').on('change', function() {

   var ifpartiesselected1 = $('.ifpartiesselected1').find(":selected").val(); 

   if(ifpartiesselected1==0)
   {
    CKEDITOR.instances.Party1details.setData('');
   }
   else
   {

$.ajax({url: "/en/getusersdetailsinagreement?id="+ifpartiesselected1, success: function(result){
                CKEDITOR.instances.Party1details.setData(result);
                }});
   }
 

               
               
 

});
</script>















      <?php
    }













  public function getagreementstypes()
    {

        $agreementsetting = Agreementsetting::where(["registeras"=> $_GET['registeras']])->get();
      ?>
            <select   class="select2 form-select w-100 sct_createagreement_agreementtype">
                <option value="0"><?php echo __('Select One'); ?></option>
                <?php foreach ($agreementsetting as $row) { ?>
                <option value="<?php echo $row->id; ?>"><?php echo __($row->title); ?></option>
                <?php } ?>
            </select>

<script type="text/javascript">
    $('.sct_createagreement_agreementtype').on('change', function() {
   var sct_createagreement_agreementtype = $('.sct_createagreement_agreementtype').find(":selected").val();  
 
$('.mustbehideinchangess').hide();





$.ajax({url: "/en/party1?justid=box1&agreementtypeid="+sct_createagreement_agreementtype, success: function(result){
                 if(result==1){
                    $('.createagreement_box1').show();
                 }
                 else {
                    $('.createagreement_box1ifisass').show();
                    $('.createagreement_box1ifisassdata').html(result);
                 } 


                

                }});

$.ajax({url: "/en/party1?justid=box2&agreementtypeid="+sct_createagreement_agreementtype, success: function(result){
                    if(result==1){
                    $('.createagreement_box2').show();
                 }
                   else {
                    $('.createagreement_box2ifisass').show();
                     $('.createagreement_box2ifisassdata').html(result);
                 } 
                }});


$.ajax({url: "/en/party1?justid=box3&agreementtypeid="+sct_createagreement_agreementtype, success: function(result){
                    if(result==1){
                    $('.createagreement_box3').show();
                 }
                   else{
                    $('.createagreement_box3ifisass').show();
                      $('.createagreement_box3ifisassdata').html(result);
                 } 
                }});




 $.ajax({url: "/en/party1?agreementtypeid="+sct_createagreement_agreementtype, success: function(result){
                $('.party1select').html(result);
               
                }});

 $.ajax({url: "/en/party2?agreementtypeid="+sct_createagreement_agreementtype, success: function(result){
                $('.party2select').html(result);

                }});



 
 
 




});



</script>


      <?php
    }





    public function assignmentfilters()
    {


    ?>

    <?php 
    if (Auth::user()->user_type!="EMPLOYEE") 
    {
    ?>
    <div style="width:19%;float: left;margin-right: 5px;">
    <select style="width:100%;" class="select2class2 btn btn-outline-secondaryf dropdown-toggle shadow jobtitleselectoption" >
    <option value="-1"><?php echo __('Job Title') ?> : <?php echo __('All') ?></option>
    <option  value="healthcare security"><?php echo __('Job Title') ?> : <?php echo __('Healthcare Security') ?></option>
    <option value="healthcare" ><?php echo __('Job Title') ?> : <?php echo __('Healthcare') ?> </option>
    </select>
    </div>
    <script src='/newface/select2/select2/dist/js/select2.min.js' type='text/javascript'></script>
    <link href='/newface/select2/select2/dist/css/select2.min.css' rel='stylesheet' type='text/css'>
    <script>
    $(document).ready(function() 
    {
        $('.select2class2').select2();
    });
    </script>
    <?php
    }
    ?>



    <?php
    }










}
