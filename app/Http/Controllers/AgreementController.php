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
class AgreementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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


public function createagreement($language)
{
    $agreementsetting = Agreementsetting::get();
    $registeras=DB::table('rehisterases')->where(["status"=> 1])->get();
    return view('dashboard.agreement.createagreement')->with(['registeras'=>$registeras,'agreementsetting'=>$agreementsetting]);
}



public function updateagreementstore(Request $request)
{

   


    $content = $request->box1;
    $content_length = mb_strlen($content, 'utf8');
    if ( $content_length > 60000 ) 
    {
        return redirect()->back()->with('message', "Text1 must be less than 60000 char");
    }


    $content = $request->box2;
    $content_length = mb_strlen($content, 'utf8');
    if ( $content_length > 60000 ) 
    {
        return redirect()->back()->with('message', "Text2 must be less than 60000 char");
    }

    $content = $request->box3;
    $content_length = mb_strlen($content, 'utf8');
    if ( $content_length > 60000 ) 
    {
        return redirect()->back()->with('message', "Text3 must be less than 60000 char");
    }





    $file = $request->file('pic');
    $scan = $request->file('scan');

    $requestData = $request->all();
    $olddata=Preaggrement::find($request->id);

// dd($olddata->pic);

    if($file) {
        $imagesUrl = $this->uploadImage($request->file('pic'));
        $imagesUrl=json_encode($imagesUrl);

        $requestData['pic'] = $imagesUrl;
    } else {
        $requestData['pic'] = $olddata->pic;
    }


    if($scan) {
        $imagesUrl = $this->uploadImage($request->file('scan'));
        $imagesUrl=json_encode($imagesUrl);

        $requestData['scan'] = $imagesUrl;
    } else {
        $requestData['scan'] = $olddata->scan;
    }



     


    Preaggrement::find($request->id)->update($requestData);


        //return redirect('/admin/cities');

   echo 1;exit;
}













 public function createagreementinsert(Request $request)
{



 

        // $imagesUrl = $this->uploadImage($request->file('pic'));
        // $imagesUrl=json_encode($imagesUrl);

    $requestData = $request->all();
        // $requestData['pic'] = $imagesUrl;


    $content = $request->box1;
    $content_length = mb_strlen($content, 'utf8');
    if ( $content_length > 60000 ) 
    {
        return redirect()->back()->with('message', "Text1 must be less than 60000 char");
    }


    $content = $request->box2;
    $content_length = mb_strlen($content, 'utf8');
    if ( $content_length > 60000 ) 
    {
        return redirect()->back()->with('message', "Text2 must be less than 60000 char");
    }

    $content = $request->box3;
    $content_length = mb_strlen($content, 'utf8');
    if ( $content_length > 60000 ) 
    {
        return redirect()->back()->with('message', "Text3 must be less than 60000 char");
    }


$imagesUrl ='';
 if (is_uploaded_file($request->pic)) 
{
     $imagesUrl = $this->uploadImage($request->file('pic'));
        $imagesUrl=json_encode($imagesUrl);
}



$imagesUrlscan ='';
 if (is_uploaded_file($request->scan)) 
{
     $imagesUrlscan = $this->uploadImage($request->file('scan'));
        $imagesUrlscan=json_encode($imagesUrl);
}




      $requestData['pic'] = $imagesUrl;
      $requestData['scan'] = $imagesUrlscan;

 
      Preaggrement::create($requestData);

        //return redirect('/admin/cities');

      echo 1;exit;
  }




public function updateagreement($language,$id)
{
 $preaggrement = Preaggrement::where(['id'=>$id])
 ->get();
     $agreementsetting = Agreementsetting::get();
    $registeras=DB::table('rehisterases')->where(["status"=> 1])->get();
    return view('dashboard.agreement.updateagreement')->with(['registeras'=>$registeras,'agreementsetting'=>$agreementsetting,'preaggrement' => $preaggrement]);
  
}



  public function index($language)
  {



    if (isset($_GET['getcost']))
    {

       $department = Department::findOrFail($_GET['getcost']);

       return $department->cost;
   }


   if (isset($_GET['emploeebyregisterasforopen']))
   {


    if (isset($_GET['justget'])) 
    {


        $joinclient = DB::table('joinclient')
        ->join('profiles', 'joinclient.user_id', '=', 'profiles.user_id')
        ->where([

            "joinclient.client_id"=>$_GET['client_id'],
            'joinclient.registeras'=>$_GET['emploeebyregisterasforopen'],
            'joinclient.department_id'=>$_GET['department_id'],
            'joinclient.user_id'=>$_GET['employee_id'],
        ])
        ->select('profiles.first_name','joinclient.id','joinclient.user_id','joinclient.client_payrate','profiles.last_name','joinclient.payrate')
        ->orderBy("profiles.first_name","asc")
        ->get();


       // echo $_GET['client_id'];
        echo $joinclient[0]->payrate."@".$joinclient[0]->client_payrate;

        exit;
    }




    $has=0;
// $joinclient = Joinclient::where(["client_id"=>$_GET['client_id'],'registeras'=>$_GET['emploeebyregisteras'],'department_id'=>1])->get();


    $joinclient = DB::table('joinclient')
    ->join('profiles', 'joinclient.user_id', '=', 'profiles.user_id')
    ->where(["joinclient.client_id"=>$_GET['client_id'],'joinclient.registeras'=>$_GET['emploeebyregisterasforopen'],'joinclient.department_id'=>1])
    ->select('profiles.first_name','joinclient.id','joinclient.user_id','joinclient.client_payrate','profiles.last_name')
    ->orderBy("profiles.first_name","asc")
    ->get();



    foreach ($joinclient as $row) 
    {
        $has++;
    }

    if ($has>0)
    {

        $joinclient = DB::table('joinclient')
        ->join('profiles', 'joinclient.user_id', '=', 'profiles.user_id')
        ->where(["joinclient.client_id"=>$_GET['client_id'],'joinclient.registeras'=>$_GET['emploeebyregisterasforopen']])
        ->select('profiles.first_name','joinclient.id','joinclient.user_id','joinclient.client_payrate','profiles.last_name')
        ->orderBy("profiles.first_name","asc")
        ->get();

    }
    else
    {
        $joinclient = DB::table('joinclient')
        ->join('profiles', 'joinclient.user_id', '=', 'profiles.user_id')
        ->where(["joinclient.client_id"=>$_GET['client_id'],'joinclient.registeras'=>$_GET['emploeebyregisterasforopen'],'joinclient.department_id'=>@$_GET['department_id']])
        ->select('profiles.first_name','joinclient.id','joinclient.user_id','joinclient.client_payrate','profiles.last_name')
        ->orderBy("profiles.first_name","asc")
        ->get();
    }
 // $query = $query->orderBy("start_date", $sort_upcoming);
    ?>
    <select style="width:100%;" name="openemployee_id<?php echo $_GET['radif']; ?>" class="js-example-basic-single1 openemployeeselectoption<?php echo $_GET['radif']; ?>"
        id="openemployeeselect<?php echo $_GET['radif']; ?>">
        <option selected value="1" id="1">Alle</option>
        <?php foreach($joinclient as $i => $employee) { ?>
            <option id="<?php echo $employee->id; ?>" onclick="getjoininfo('<?php echo $employee->id; ?>')"
               data-clientpayrate="<?php echo $employee->client_payrate;  ?>"
               value="<?php echo $employee->user_id;  ?>"   <?php if ($employee->user_id == @$_GET['employee_id']){ echo "selected";}  ?> >
               <?php echo $employee->first_name." ".$employee->last_name; ?>
           </option>
       <?php }  ?>
   </select>
   
   <?php 

   if ($_GET['radif']==1)
   {
    ?>
    <script src='/newface/select2/select2/dist/js/select2.min.js' type='text/javascript'></script>
    <link href='/newface/select2/select2/dist/css/select2.min.css' rel='stylesheet' type='text/css'>
    <?php
}

?>


<?php 

if (1==1)
// if ($_GET['radif']==$_GET['radif'])
{
    ?>
    <script type="text/javascript">
        $(document).ready(function()
        {
          $(".openemployeeselectoption"+<?php echo $_GET['radif']; ?>).select2();
      });

        $('.openemployeeselectoption'+<?php echo $_GET['radif']; ?>).on('change', function() 
        {
          var value = $(this).children(":selected").attr("id");
          var selectid = $(this).attr("id");

          var ret = selectid.replace('openemployeeselect','');


          if (value==1) 
          {



            $('.openemployeepayrate'+ret).val('');
            $('.openemployeeclientpayrate'+ret).val('');
            $('.dontshowthis').hide();
            
        }
        else
        {


            $.ajax({url: "/en/assignments/create/38?type=payrate&getinfo=4&joinclient="+value, success: function(result){
                var getregisterasdatas = $('.getregisterasdatas').find(":selected").val();

                if (getregisterasdatas=='healthcare')
                {
                    $('.dontshowthis').show();
                    $('.openemployeepayrate'+ret).val(result);
                }
                else
                {
                    $('.dontshowthis').hide();
                    <?php 
                    if (Auth::user()->user_type=="ADMIN" or Auth::user()->user_type=="EMPLOYEE" )
                    {
                        ?>
                        $('.openemployeepayrate'+ret).val(result);
                        <?php
                    }
                    ?>
                }
            }});


            $.ajax({url: "/en/assignments/create/38?type=clientpayrate&getinfo=4&joinclient="+value, success: function(result){
             $('.openemployeeclientpayrate'+ret).val(result); 
         }}); 




     }





 });



</script>
<?php
}

?>


<?php

exit;
}




if (isset($_GET['emploeebyregisteras']))
{







   $has=0;
// $joinclient = Joinclient::where(["client_id"=>$_GET['client_id'],'registeras'=>$_GET['emploeebyregisteras'],'department_id'=>1])->get();


//    $joinclient = DB::table('joinclient')
//    ->join('profiles', 'joinclient.user_id', '=', 'profiles.user_id')
//    ->where(["joinclient.client_id"=>$_GET['client_id'],'joinclient.registeras'=>$_GET['emploeebyregisteras'],'joinclient.department_id'=>1])
//    ->select('profiles.first_name','joinclient.id','joinclient.user_id','joinclient.client_payrate','profiles.last_name')
//    ->orderBy("profiles.first_name","asc")
//    ->get();



//    foreach ($joinclient as $row) 
//    {
//     $has++;
// }

// if ($has>0)
// {

//     $joinclient = DB::table('joinclient')
//     ->join('profiles', 'joinclient.user_id', '=', 'profiles.user_id')
//     ->where(["joinclient.client_id"=>$_GET['client_id'],'joinclient.registeras'=>$_GET['emploeebyregisteras']])
//     ->select('profiles.first_name','joinclient.id','joinclient.user_id','joinclient.client_payrate','profiles.last_name')
//     ->orderBy("profiles.first_name","asc")
//     ->get()->unique('user_id');

// }
// else
// {
    $joinclient = DB::table('joinclient')
    ->join('profiles', 'joinclient.user_id', '=', 'profiles.user_id')
    ->where(["joinclient.client_id"=>$_GET['client_id'],'joinclient.registeras'=>$_GET['emploeebyregisteras'],'joinclient.department_id'=>@$_GET['department_id']])
    ->select('profiles.first_name','joinclient.id','joinclient.user_id','joinclient.client_payrate','profiles.last_name')
    ->orderBy("profiles.first_name","asc")
    ->get();
// }
 // $query = $query->orderBy("start_date", $sort_upcoming);


?>
<select style="width:100%;" name="employee_id" class="js-example-basic-single1 checkselectedfreeelancer"
id="employee_id">
<option selected value="1" id="1">Alle</option>
<?php foreach($joinclient as $i => $employee) { ?>
    <option id="<?php echo $employee->id; ?>" onclick="getjoininfo('<?php echo $employee->id; ?>')"
       data-clientpayrate="<?php echo $employee->client_payrate;  ?>"
       value="<?php echo $employee->user_id;  ?>"   <?php if ($employee->user_id == @$_GET['employee_id']){ echo "selected";}  ?> >
       <?php echo $employee->first_name." ".$employee->last_name; ?>
   </option>
<?php }  ?>
</select>


<!-- <script src='/newface/select2/jquery-3.2.1.min.js' type='text/javascript'></script> -->
<script src='/newface/select2/select2/dist/js/select2.min.js' type='text/javascript'></script>

<link href='/newface/select2/select2/dist/css/select2.min.css' rel='stylesheet' type='text/css'>


<script type="text/javascript">
    $(document).ready(function() {


      $("#employee_id").select2();
  });
</script>

<?php

exit;
}




if (isset($_GET['aggrementtype']))
{


    if ($_GET['aggrementtype']=="healthcare")
    {

        $preaggrement = Preaggrement::where(["client_id"=>$_GET['client_id'],'registeras'=>$_GET['aggrementtype']])->get();
        ?>
        <select name="agreement_id" required class="form-select " id="agreement_id">
            <?php foreach ($preaggrement as $row){  ?>
                <option value="<?php echo  $row->id ?>" <?php if ($row->id ==@$_GET['aggrement_id']){ echo "selected";}  ?>>
                 <?php echo $row->title ?>
             </option>
         <?php } ?>
     </select>
     <?php


 }

 if ($_GET['aggrementtype']=="healthcare security")
 {

    $preaggrement = Preaggrement::where(["client_id"=>$_GET['client_id'],'registeras'=>$_GET['aggrementtype']])->get();
    ?>
    <select name="agreement_id"  class="form-select " id="agreement_id">
        <?php foreach ($preaggrement as $row){  ?>
            <option value="<?php echo  $row->id ?>" <?php if ($row->id ==@$_GET['aggrement_id']){ echo "selected";}  ?>>
             <?php echo $row->title ?>
         </option>
     <?php } ?>
 </select>
 <?php


}






exit;
}



if (!isset($_GET['client_id'])) 
{
    $_GET['client_id']="all";
}
if (!isset($_GET['title'])) 
{
    $_GET['title']="all";
}
if (!isset($_GET['jobtitle'])) 
{
    $_GET['jobtitle']="all";
}


$query = Preaggrement::query();
$clients=array();

if (Auth::user()->user_type=='CLIENT')
{
    $profiles = Profile::get();
    $query = $query->where("client_id",Auth::user()->id);
    $query = $query->where("registeras",'healthcare');

    $agreements = Preaggrement::where(['client_id'=>Auth::user()->id,'registeras'=>'healthcare'])->orderBy("title","asc")->get();

    $clients=array();


}
else
    if (Auth::user()->user_type=='SCHEDULE' or Auth::user()->user_type=='FINANCIAL')
    {
        $profiles = Profile::get();
        $query = $query->where("client_id",Auth::user()->client_id);
        $query = $query->where("registeras",'healthcare');

        $clients=array();

        $agreements = Preaggrement::where(['client_id'=>Auth::user()->client_id,'registeras'=>'healthcare'])->orderBy("title","asc")->get();
    }
    else
        if (Auth::user()->user_type=='ADMIN')
        {
         $clients = User::where("user_type", "CLIENT")->get();
         $profiles = Profile::get();
         $agreements = Preaggrement::orderBy("title","asc")->get();

         if ($_GET['client_id']!='all') 
         {

           $query = $query->where("client_id",$_GET['client_id']);
       }


   }






   if ($_GET['title']!='all') 
   {

       $query = $query->where("id",$_GET['title']);
   }

   if ($_GET['jobtitle']!='all') 
   {
       $query = $query->where("registeras",$_GET['jobtitle']);
   }



   $query = $query->orderBy("title", "asc");
   $preaggrement = $query->paginate(Auth::user()->paginationnum);

// dd($preaggrement);
   return view('dashboard.agreement.index')
   ->with([
    'preaggrement' => $preaggrement,
    'clients' => $clients,
    'client_id' => @$_GET['client_id'],
    'title' => @$_GET['title'],
    'profiles' => $profiles,
    'jobtitle' => @$_GET['jobtitle'],
    'agreements' => $agreements,
]);
}





public function exporttmppreAgreementPDF($language, $agreement_id)
{





  $preaggrement = Preaggrement::where("id",$agreement_id)->get();




  $registeras=$preaggrement[0]->registeras;




  $payrate ="0";
  $client_payrate ="0";








  $clientprofile = Profile::where("user_id",$preaggrement[0]->client_id)->get();  
  $clientaddresses = Address::where("addressable_id",Auth::user()->id)->get();

  $top="";
  if ($preaggrement->isEmpty())
  {

  }
  else
  {



    $clientbox=$preaggrement[0]->clientbox;

    $text4=$preaggrement[0]->text1;
    $type=$preaggrement[0]->type;
    $btwplicht=$preaggrement[0]->btwplicht;
    $ort=$preaggrement[0]->ort;
    $extratitle1=$preaggrement[0]->extratitle1;
    $extratext1=$preaggrement[0]->extratext1;
    $extratitle2=$preaggrement[0]->extratitle2;
    $extratext2=$preaggrement[0]->extratext2;
    $extratitle3=$preaggrement[0]->extratitle3;
    $extratext3=$preaggrement[0]->extratext3;
    $extratitle4=$preaggrement[0]->extratitle4;
    $extratext4=$preaggrement[0]->extratext4;
    $extratitle5=$preaggrement[0]->extratitle5;
    $extratext5=$preaggrement[0]->extratext5;
    $extratitle6=$preaggrement[0]->extratitle6;
    $extratext6=$preaggrement[0]->extratext6;
    $text2=$preaggrement[0]->text2;
    $pic=$preaggrement[0]->pic;
    $clientsignbox=$preaggrement[0]->clientsignbox;
    $client_id=$preaggrement[0]->client_id;

    $images=json_decode($pic);
    $picurl= $images->images;


    $text3="Bedrijfsnaam zzp-er , Adres zzp-er - Postcode - stad, KVK-nummer  1234 en BTW-nummer
    1234 hierbij rechtsgeldig vertegenwoordigd door haar directeur Naam zzp-er, hierna te noemen 'Opdrachtnemer'
    ";




    $date = "Temp Date";
    $startdate="Temp Date";

    $date = "Temp Date";
    $enddate="Temp Date";


    if ($preaggrement[0]->registeras=='healthcare') 
    {
     $payratee='   <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Uurtarief: '.number_format(0,2).' € </span></span></span></span></p>';
 }
 else
 {
    $payratee="";
}

$centertext='
<br>
<p><span style=\"font-size:8pt;margin-top:30px;"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Organisatie: '.@$clientprofile[0]->company_name.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Locatie: '.@$clientaddresses[0]->address." ".@$clientaddresses[0]->address_extra." ".@$clientaddresses[0]->postcode." ".@$clientaddresses[0]->city.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Soort opdracht: '.$type.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Begin: '.$startdate.' 22:00   </span></span></span></span></p>


<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Einde: '.$enddate.' 23:00  </span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Aantal uren: 1</span></span></span></span></p>

'.$payratee.'


<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">ORT: '.$ort.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Btw-plicht: '.$btwplicht.'</span></span></span></span></p>


<p><span style=\"font-size:10.0pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\">'.$extratitle1.' '.$extratext1.' </span></span></span></p>

<p><span style=\"font-size:10.0pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\">'.$extratitle2.' '.$extratext2.' </span></span></span></p>

<p><span style=\"font-size:10.0pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\">'.$extratitle3.' '.$extratext3.' </span></span></span></p>

<p><span style=\"font-size:10.0pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\">'.$extratitle4.' '.$extratext4.' </span></span></span></p>

<p><span style=\"font-size:10.0pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\">'.$extratitle5.' '.$extratext5.' </span></span></span></p>

<p><span style=\"font-size:10.0pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\">'.$extratitle6.' '.$extratext6.' </span></span></span></p>
<br>
';


$clientprofile = Profile::where("user_id",$preaggrement[0]->client_id)->get();




$path = $picurl;
$path = substr($path, 1);



$top='<div class="agreement-content">
<div class="page">
<div class="container">
<div class="row">
<div class="col-md-4">
<img style="float:right;" src="'.$path.'" alt="" width="150" height="100"></div>
<div class="col-md-8">
<h1 class="h5 f-bold"> Opdrachtovereenkomst </h1>
</div>
<div class="clearfix"></div>
<div class="col-md-12">
<ol>
<li><p>'.$clientbox.'</p></li>
<li><p>'.$text3.'</p></li>
</ol>
</div>
<div class="col-md-12">
'.$text4.'
</div>
<div class="col-md-12">
<p> Komen hierbij als volgt overeen:</p>
'.$centertext.'
</div>
<div class="col-md-12 mb-5">
'.$text2.'
</div>
</div>
</div>
</div>
</div>
<div style="width: 100%;">

<div style="width:50%;float: left;">
'.$clientsignbox.'
</div>

<div style="width:50%;float: right;font-family:Arial,Helvetica,sans-serif;font-size:14px;">
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">Opdrachtnemer</div><br>
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">Bedrijfsnaam zzp-er</div><br>
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">Naam zzp-er</div><br>
</div>

</div>
';

$agreementtemp=$clientsignbox;
} 





view()->share('assignment', $top);




$pdf_doc = PDF::loadView('dashboard.agreement.tmpassignmentAgreementPdf', array($top));

return $pdf_doc->stream('pdf.pdf');








}





















}
