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
use App\Models\Rehisterases;
use App\Models\Times;
use App\Models\Agreementsetting;
use App\Models\Financial;
use App\Models\Credit_note;
use App\Models\Platformfunctions;
use App\Models\Address;
use App\Models\Profile;
use App\Models\Invoice;
use App\Models\Defaultinvoiceconfiguration;
use App\Models\Defaultjoinconfiguration;
use App\Models\Defaultpaymentconfiguration;
use App\Models\Defaultpayrates;
use App\Models\Defaultsurchargeconfiguration;
use App\Models\Documentsetting;
use App\Models\Document;
use App\Models\Role;
use App\Models\Joinclient;
use App\Models\Sitecontents;
use App\Models\Mehranfirstcrud;
use App\Models\Tamrin4;
use App\Models\Tamrin5;
use App\Models\Crud;
use App\Models\Crud1;
use App\Models\Tamrin1crud;
use App\Models\Firsttamrin;
use App\Models\Secendtamrin;
use App\Models\Formtamrin1;
use App\Models\Roles_permissions;
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
use App\Models\Languagejson;
class DeveloperController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

     protected function indexmehranfirstcrude()
    {

       
       $Mehranfirstcrud = Mehranfirstcrud::get();
       $User = User::get();
        return view('developer.mehrancrude')->with(["Mehranfirstcrud" => $Mehranfirstcrud,'User'=>$User]);  
    }

         protected function indexfirsttamrin()
    {

       $Firsttamrin = Firsttamrin::get();
        return view('developer.tamrin1')->with(["Firsttamrin" => $Firsttamrin]);  
    }

        protected function indextamrin5()
    {

       $tamrin5 = Tamrin5::get();
        return view('developer.tamrin5')->with(["tamrin5" => $tamrin5]);  
    }

             protected function indexsecendtamrin()
    {
       $Secendtamrin = Secendtamrin::get();
        return view('developer.tamrin3')->with(["mehran" => $Secendtamrin]);  
    }

             protected function indextamrin4()
     {
             
       $tamrin4 = Tamrin4::get();
        return view('developer.tamrin4')->with(["tamrin4" => $tamrin4]);  
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


    public function sitecontent($language)
    {

        $Sitecontent = Sitecontents::get();
        return view('developer.sitecontent')->with(["Sitecontent" => $Sitecontent]);
    }

    public function sitecontentcrude($language,Request $request)
    {


    $Sitecontent = Sitecontents::where(['id'=>$request->id])->get();

    $imagesUrl =$Sitecontent[0]->url;


if (is_uploaded_file($request->file)) 
{
     $imagesUrl = $this->uploadImage($request->file('image'));
        $imagesUrl=json_encode($imagesUrl);
}

 


      Sitecontents::where(["id"=>$request->id])->update([
            'url' =>$imagesUrl,
            'sign' =>$request->sign,
            'titr' =>$request->titr,
            'sub_title' =>$request->sub_title,
            'text' =>$request->text,
      ]);









      
    }



    public function changeaccesslevel()
    {
        
        if (isset($_GET['roleid'])) 
        {
         $Roles_permissions=Roles_permissions::where(["role_id"=>$_GET['roleid'],"permission_id"=>$_GET['perid']])->get();
         $s=0;
          if ($Roles_permissions->isEmpty())
          {
            Roles_permissions::create([
            'role_id' =>$_GET['roleid'],
            'permission_id' =>$_GET['perid'],
        ]);
          }
        else{
        Roles_permissions::where(["role_id"=>$_GET['roleid'],"permission_id"=>$_GET['perid']])->delete();
        }
        
        return 1;
    }
    return 1;
    }


    public function accesslevel($language)
    {
     return view('developer.accesslevel');
    }


    public function languages($language)
    {





if (isset($_GET['publish']))
 {



$Languagejson = Languagejson::where('lang','nl')->get();
$var =  __DIR__."/../../../resources/lang/nl.json";
$myfile = fopen($var, "w") or die("Unable to open file!");
$txt = "";
foreach ($Languagejson as $row)
 {
    $txt .=  '"'.$row->part1.'":"'.$row->part2.'",';
 }

$txt=rtrim($txt, ",");

$txt='{
'
.$txt.
'
}';
fwrite($myfile, $txt);
fclose($myfile);

 

$Languagejson = Languagejson::where('lang','en')->get();
$var =  __DIR__."/../../../resources/lang/en.json";
$myfile = fopen($var, "w") or die("Unable to open file!");
$txt = "";
foreach ($Languagejson as $row)
 {
    $txt .=  '"'.$row->part1.'":"'.$row->part2.'",';
 }
$txt=rtrim($txt, ",");
$txt='{
'
.$txt.
'
}';
fwrite($myfile, $txt);
fclose($myfile);






   exit;
}
          $Languagejson = Languagejson::get();
     return view('developer.lang')->with(["Languagejson" => $Languagejson]);


     return view('developer.lang');
    }

    public function languagescrude(Request $request)
    {
        if ($request->type=="create")
        {
         Languagejson::create([
            'lang' =>$request->lang,
            'part1' =>$request->part1,
            'part2' =>$request->part2,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
     }
     else
     {
      Languagejson::where(["id"=>$request->id])->update([
            'lang' =>$request->lang,
            'part1' =>$request->part1,
            'part2' =>$request->part2,
      ]);
    }
    return 1;
    }


    public function invoiceconfiguration($language)
    {
        $invoiceconfigurations = Defaultinvoiceconfiguration::get();
        return view('developer.invoiceconfiguration')->with(["invoiceconfigurations" => $invoiceconfigurations]);
    }

    public function createinvoiceconfiguration(Request $request)
    {
        if ($request->type=="create")
        {
         Defaultinvoiceconfiguration::create([
            'registeras' =>$request->registeras,
            'invoicingmodel' =>$request->invoicingmodel,
            'invoice1data' =>$request->invoice1data,
            'invoice1sender' =>$request->invoice1sender,
            'invoice1recirver' =>$request->invoice1recirver,
            'invoice1tax' =>$request->invoice1tax,
            'invoice2data' =>$request->invoice2data,
            'invoice2sender' =>$request->invoice2sender,
            'invoice2recirver' =>$request->invoice2recirver,
            'invoice2tax' =>$request->invoice2tax,
            'invoice3data' =>$request->invoice3data,
            'invoice3sender' =>$request->invoice3sender,
            'invoice3recirver' =>$request->invoice3recirver,
            'invoice3tax' =>$request->invoice3tax,
            'invoice4data' =>$request->invoice4data,
            'invoice4sender' =>$request->invoice4sender,
            'invoice4recirver' =>$request->invoice4recirver,
            'invoice4tax' =>$request->invoice4tax,
            'default' =>$request->default,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
     }
     else
     {
      Defaultinvoiceconfiguration::where(["id"=>$request->id])->update([
            'invoicingmodel' =>$request->invoicingmodel,
            'invoice1data' =>$request->invoice1data,
            'invoice1sender' =>$request->invoice1sender,
            'invoice1recirver' =>$request->invoice1recirver,
            'invoice1tax' =>$request->invoice1tax,
            'invoice2data' =>$request->invoice2data,
            'invoice2sender' =>$request->invoice2sender,
            'invoice2recirver' =>$request->invoice2recirver,
            'invoice2tax' =>$request->invoice2tax,
            'invoice3data' =>$request->invoice3data,
            'invoice3sender' =>$request->invoice3sender,
            'invoice3recirver' =>$request->invoice3recirver,
            'invoice3tax' =>$request->invoice3tax,
            'invoice4data' =>$request->invoice4data,
            'invoice4sender' =>$request->invoice4sender,
            'invoice4recirver' =>$request->invoice4recirver,
            'invoice4tax' =>$request->invoice4tax,
            'default' =>$request->default,
      ]);
    }
    return 1;
    }


    public function agreementsetting($language)
    {
      $agreementsetting = DB::table('agreementsetting')->get();
      return view('developer.agreementsetting')->with(["agreementsetting" => $agreementsetting]);
    }

    public function createagreementsetting(Request $request)
    {
        if ($request->type=="create")
        {
         Agreementsetting::create([
            'title' =>$request->title,
            'box1' =>$request->box1,
            'box2' =>$request->box2,
            'box3' =>$request->box3,
            'part1' =>$request->part1,
            'part2' =>$request->part2,
            'registeras' =>$request->registeras,
            'period' =>$request->period,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
     }
     else
     if ($request->type=="update")
     {
      Agreementsetting::where(["id"=>$request->id])->update([
            'title' =>$request->title,
            'box1' =>$request->box1,
            'box2' =>$request->box2,
            'box3' =>$request->box3,
            'part1' =>$request->part1,
            'part2' =>$request->part2,
            'period' =>$request->period,
      ]);
    }
    else
    {


   $tt=Agreementsetting::where(["id"=>$request->id])->select($request->field)->get();  


$field=$request->field;
  $ee=0;
if ($tt[0]->$field==0)
 {
   $ee=1;
}



         Agreementsetting::where(["id"=>$request->id])->update([
             $request->field =>$ee,
      ]);  


//echo $tt[0]->$field;exit;




      //       Agreementsetting::where(["id"=>$request->id])->update([
      //       'title' =>$request->title,
      // ]);  
    }
    return 1;
    }




    public function agreementconfihuration($language)
    {
     return view('developer.agreementconfihuration');
    }
    public function documentconfiguration($language)
    {
     $documentsetting = DB::table('documentsetting')->get();
     return view('developer.documentconfiguration')->with(["documentsetting" => $documentsetting]);
    }
    public function createdocumentsetting(Request $request)
    {

 
     if ($request->type=="newtitle")
        {

      Document::create([
            'title' =>$request->title,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);

           
        }
       else
        if ($request->type=="create")
        {
           



          $ch= DB::table('documentsetting')->where(['registeras'=>$request->registeras,'title'=>$request->title])->get(); 
         if (!$ch->isEmpty())
          {
            echo "this title exist";exit;
          }

         Documentsetting::create([
            'registeras' =>$request->registeras,
            'type' =>$request->typed,
            'title' =>$request->title,
            'Expirationdate' =>$request->Expirationdate,
            'checkbox' =>$request->checkbox,
            'Namecheckbox' =>$request->Namecheckbox,
            'status' =>$request->status,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
     }
     else
     {


          $ch= DB::table('documentsetting')->where(['registeras'=>$request->registeras,'title'=>$request->title])->get(); 
         if (!$ch->isEmpty())
          {
            echo "this title exist";exit;
          }


        
      Documentsetting::where(["id"=>$request->id])->update([
            'type' =>$request->typed,
            'Expirationdate' =>$request->Expirationdate,
            'checkbox' =>$request->checkbox,
            'Namecheckbox' =>$request->Namecheckbox,
            'status' =>$request->status,
      ]);
    }
    echo 1;exit;
    }






    public function onlinemediadocuments($language)
    {
     return view('developer.onlinemediadocuments');
    }


    public function jobtitleconfiguration($language)
    {
        $rehisterases = DB::table('rehisterases')->get();
        return view('developer.jobtitleconfiguration')->with(["rehisterases" => $rehisterases]);
    }



public function pagedashbord ($language)
    {
    
        return view('developer.dashbord');
    }

public function pageinsert ($language)
    {
    
        return view('developer.insertpage');
    }
public function pageindexinsert ($language,Request $request)
    {
    
    Crud1::create([
            'email' =>$request->email,
            'password' =>$request->password,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);


echo 1;exit;
        
    }

public function pageshow ($language,Request $request)
    {
    
        $crud1 = Crud1::get();
        return view('developer.pageshow')->with(["crud1" => $crud1]);
    }

    public function pageupdate ($language)
    {
    
     $crud1 = Crud1::where(['id'=>$_GET['id']])->get();
        return view('developer.pageupdate')->with(["crud1" => $crud1]);  

    }

    public function pagedelete ($language)
    {
    
      $crud1 = Crud1::where(['id'=>$_GET['id']])->delete();
       
        echo 1;exit; 

    }

    public function pageindexupdate ($language,Request $request)
    {
    
      Crud1::where(["id"=>$request->id])->update([
          'email' =>$request->email,
         'password' =>$request->password,
     ]);
echo 1;exit; 
    }







     public function indexform1 ($language)
    {
    
        return view('developer.form1');
    }


     public function deletetamrin1 ($language)
    {
     

        $Tamrin1crud = Tamrin1crud::where(['id'=>$_GET['id']])->delete();
       
        echo 1;exit; 
 
    }
         public function updatetamrin1 ($language)
    {
     
        $Tamrin1crud = Tamrin1crud::where(['id'=>$_GET['id']])->get();
        return view('developer.update_tamrin1')->with(["Tamrin1crud" => $Tamrin1crud]);
 
    }

     public function indexform1storm ($language)
    {
     
        $Tamrin1crud = Tamrin1crud::get();
        return view('developer.form1storm')->with(["Tamrin1crud" => $Tamrin1crud]);
 
    }

 

     public function indexform1stormstore ($language,Request $request)
    {
     

$email = $request->email;
if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email))
  {
  $emailErr = "فرمت فیلد ایمیل صحیح نیست"; 
  echo $emailErr;exit;
  }


        Tamrin1crud::create([
            'email' =>$request->email,
            'password' =>$request->password,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);

       


echo 1;exit;


         
       
    }

 public function indexformtamrin1 ($language)
    {
    
        return view('developer.formtamrin1');
    }

     public function indexformtamrin1storm ($language,Request $request)
    {
    
       Formtamrin1::create([
            'email' =>$request->email,
            'password' =>$request->password,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
 echo 1;exit;
    }

 



 public function formcrud ($language)
    {
    
        return view('developer.formcrud');
    }

public function insert ($language)
    {
    
        return view('developer.pageinsert');
    }

    public function indexinsert ($language,Request $request)
    {
    
    Crud::create([
            'email' =>$request->email,
            'password' =>$request->password,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
 echo 1;exit;
        
    }


public function pageform ($language,Request $request)
    {
     
        $crud = Crud::get();
        return view('developer.pageform')->with(["crud" => $crud]);
 
    }





     public function indexupdate ($language)
    {
    
        return view('developer.update');
    }

     public function indexupdatestorm ($language,Request $request)
    {
    
      Formtamrin1::where(["id"=>$request->id])->update([
          'email' =>$request->email,
         'password' =>$request->password,
     ]);
    }

     public function indexdelete ($language)
    {
    
        return view('developer.delete');
    }

     public function indexdeletestorm ($language)
    {

         Formtamrin1::where(["id"=>$_POST['id']])->delete();

         // Formtamrin1::where(["password"=>'123456'])->delete();

    }


     public function indexform ($language)
    {
    
        return view('developer.form');
    }

         public function indexformstore($language,Request $request)
    {
    
  // return $_POST['email'];
   // return $request->email;
   // echo "<br>";
   // var_dump($_GET);


 

 
     // Role::create([
            //'name' =>$request->name,
           // 'slug' =>$request->name,
           // 'created_at' =>Carbon::now(),
            //'updated_at' =>Carbon::now(),
        //]);

           // Roles_permissions::where(["role_id"=>$_GET['roleid'],"permission_id"=>$_GET['perid']])->delete();



         // Role::where(["id"=>$request->id])->update([
          //'name' =>$request->name,
         // 'slug' =>$request->name,
     // ]);
    }

    public function roleconfiguration($language)
    {
        $roles = DB::table('roles')->get();
        return view('developer.roleconfiguration')->with(["roles" => $roles]);
    }


    public function createrole(Request $request)
    {
        if ($request->type=="create")
        {
         Role::create([
            'name' =>$request->name,
            'slug' =>$request->name,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
     }
     else
     {
      Role::where(["id"=>$request->id])->update([
          'name' =>$request->name,
          'slug' =>$request->name,
      ]);
    }
    return 1;
    }


    public function createregisteras(Request $request)
    {
        if (isset($_GET['changestatus'])) 
        {
         $Rehisterases=Rehisterases::where(["id"=>$request->id])->get();
         $s=0;
         if ($Rehisterases[0]->status==0)
         {
            $s=1;
        }
        Rehisterases::where(["id"=>$request->id])->update([
          'status' =>$s,
      ]);
        return 1;
    }
    if ($request->type=="create")
    {
     Rehisterases::create([
        'title' =>$request->name,
        'status' =>1,
        'created_at' =>Carbon::now(),
        'updated_at' =>Carbon::now(),
    ]);


     Defaultpayrates::create([
        'registeras' =>$request->name,
        'payrate' =>5,
    ]);

     Defaultjoinconfiguration::create([
        'registeras' =>$request->name,
        'assingmenttype' =>"On",
        'jointype' =>"On",
        'offerpayrate' =>"Off",
    ]);


Defaultsurchargeconfiguration::create([
        'registeras' =>$request->name,
        'date' =>"-",
        'hourly' =>"-",
        'workingdays' =>"-",
        'weekend' =>"-",
        'datehourly' =>"-",
        'workingdayhourly' =>"-",
        'weekendhourly' =>"-",
        'percentagedate' =>"-",
        'percentagehourly' =>"-",
        'percentageworkingdays' =>"-",
        'percentageweekend' =>"-",
        'percentagedatehourly' =>"-",
        'percentageworkingdayhourly' =>"-",
        'percentageweekendhourly' =>"-",
        
 
    ]);



    }
    else
    {
      Rehisterases::where(["id"=>$request->id])->update([
          'title' =>$request->name,
      ]);
    }
    return 1;
    }


    public function platformfunctionsconfiguration($language)
    {
        $platformfunctions = DB::table('platformfunctions')->get();
        return view('developer.platformfunctionsconfiguration')->with(["platformfunctions" => $platformfunctions]);
    }

    public function changepelatform()
    {
        $s=0; 
        if (isset($_GET['changestatus'])) 
        {
         $Platformfunctions=Platformfunctions::where(["id"=>$_GET['changestatus']])->get();
         $s=0;
         if ($Platformfunctions[0]->status==0)
         {
            $s=1;
        }
        Platformfunctions::where(["id"=>$_GET['changestatus']])->update([
          'status' =>$s,
      ]);
        return 1;
    }
    return 1;
    }


    public function defaultsettings($language)
    {
     return view('developer.defaultsettings');
    }

    public function updateDefaultinvoiceconfiguration(Request $request)
    {
        Defaultinvoiceconfiguration::where(["id"=>$request->id])->update([
          'invoicingmodel' =>$request->invoicingmodel,
          'invoice1data' =>$request->invoice1data,
          'invoice1sender' =>$request->invoice1sender,
          'invoice1recirver' =>$request->invoice1recirver,
          'invoice1tax' =>$request->invoice1tax,
          'invoice2data' =>$request->invoice2data,
          'invoice2sender' =>$request->invoice2sender,
          'invoice2recirver' =>$request->invoice2recirver,
          'invoice2tax' =>$request->invoice2tax,
      ]);
        return 1;
    }

    public function updateDefaultsurchargeconfiguration(Request $request)
    {
        Defaultsurchargeconfiguration::where(["id"=>$request->id])->update([
          'date' =>$request->date,
          'hourly' =>$request->hourly,
              // 'workingdays' =>$request->workingdays,
          'weekend' =>$request->weekend,
          'datehourly' =>$request->datehourly,
          'workingdayhourly' =>$request->workingdayhourly,
          'weekendhourly' =>$request->weekendhourly,
          'percentagedate' =>$request->percentagedate,
          'percentagehourly' =>$request->percentagehourly,
          'percentageweekend' =>$request->percentageweekend,
          'percentageworkingdayhourly' =>$request->percentageworkingdayhourly,
          'percentageworkingdays' =>$request->percentageworkingdays,
          'percentageweekendhourly' =>$request->percentageweekendhourly,

      ]);
        return 1;
    }


    public function updateDefaultjoinconfiguration(Request $request)
    {
        Defaultjoinconfiguration::where(["id"=>$request->id])->update([
          'assingmenttype' =>$request->assingmenttype,
          'jointype' =>$request->jointype,
          'offerpayrate' =>$request->offerpayrate,
      ]);
        return 1;
    }

    public function updateDefaultpaymentconfiguration(Request $request)
    {
        Defaultpaymentconfiguration::where(["id"=>$request->id])->update([
          'invoicemodel' =>$request->invoicemodel,
          'sendinginvoice' =>$request->sendinginvoice,
      ]);
        return 1;
    }

    public function updateDefaultpayrates(Request $request)
    {
        Defaultpayrates::where(["id"=>$request->id])->update([
          'payrate' =>$request->payrate,
      ]);
        return 1;
    }

    public function configuration($language)
    {
     return view('developer.configuration');
    }


}
