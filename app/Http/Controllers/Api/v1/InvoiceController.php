<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Configuration;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Image;
use App\Models\Profile;
use App\Models\Beforeinvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Credits;
use App\Models\Times;
use App\CustomClass\Rh;
use App\Models\Joindepartment;
use Illuminate\Support\Facades\Storage;
use File;
use App\Models\Credit_note;
use App\Models\Address;
use App\Models\Financial;
class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


  public function gotolastinvoice($language, Request $request)
    {


return redirect('/'.$language.'/departmentforclientpdf/'.$request->client_id.'?year='.$request->year.'&month='.$request->month.'&registeras='.$request->registeras.'&type='.$request->type);




return $request;
     }



  public function allcredits($language,$beforinvoice)
    {








if (isset($_GET['confirm'])) 
{

        $Beforeinvoice=Beforeinvoice::where(["id"=>$beforinvoice])->get();

        $Credit_note=Credit_note::where(["times_id"=>$Beforeinvoice[0]->times_id,'credits_id'=>0])->get();


        if ($Credit_note->isEmpty())
        {
            return redirect()->back()->with('message', "Nothing to Confirm");

        }
        else
        {

        Credits::create([
            'times_id' =>$Beforeinvoice[0]->times_id,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
 
        ]);

          $lastinsertedid= DB::getPdo()->lastInsertId();
          foreach ($Credit_note as $row)
           {
              

        Credit_note::where([
        "id"=>$row->id
           ])->update([
           'archive' =>1,
           'credits_id' =>$lastinsertedid,
        ]);


          }

return redirect()->back()->with('message', "Credit Notes Confirmed");

        }
}

       $Beforeinvoice=Beforeinvoice::where(["id"=>$beforinvoice])->get();

       $Credits=Credits::where(["times_id"=>$Beforeinvoice[0]->times_id])->paginate(Auth::user()->paginationnum);




        return view('dashboard.invoices.Credits')
            ->with([
                "Credits" => $Credits,
                "Beforeinvoice" => $Beforeinvoice,
            ]);



    }


 
    public function sendcredit($language,$beforinvoice,$credits_id)
    {

$in1=Beforeinvoice::where(["id"=>$beforinvoice])->get();
$times_id=$in1[0]->times_id;
$in2=Beforeinvoice::where(["times_id"=>$times_id,['id','!=',$in1[0]->id]])->get();
$assignments=Assignment::where(["times_id"=>$in1[0]->times_id])->get();
$Credit_note=Credit_note::where(["times_id"=>$in1[0]->times_id,'archive'=>1,'credits_id'=>$credits_id])->get();

 $companylogo=Image::where(["document_title"=>"Company Logo","imageable_id"=>$assignments[0]->employee_id])->get();


$invoicename1="";
$invoicename2="";

$dd=(explode(" ",$Credit_note[0]->created_at));
$ddd=(explode("-",$dd[0]));


  if($in1[0]->invoicenumber!=null)
  { 

$invoicename1=$in1[0]->invoicenumber."-".$credits_id;

    
} 
else
 { 

    $invoicename1=$in1[0]->id."-".$credits_id;

   
  }


    





$Beforeinvoice=$in1;
$pdf_doc = PDF::loadView('dashboard.invoices.exportcreditPdf', compact('assignments','companylogo','Beforeinvoice','Credit_note','credits_id'));

Storage::put('public/pdf/'.$invoicename1.'.pdf', $pdf_doc->output());


  if($in2[0]->invoicenumber!=null)
  { 

$invoicename2=$in2[0]->invoicenumber."-".$credits_id;

    
} 
else
 { 

    $invoicename2=$in2[0]->id."-".$credits_id;

   
  }



 
$Beforeinvoice=$in2;
  $pdf_doc = PDF::loadView('dashboard.invoices.exportcreditPdf', compact('assignments','companylogo','Beforeinvoice','Credit_note','credits_id'));
Storage::put('public/pdf/'.$invoicename2.'.pdf', $pdf_doc->output());


//if its healthcare
if ($in1[0]->registeras=="healthcare") 
{


$scdep=Joindepartment::where(['client_id'=>$in1[0]->client_id,'registeras'=>$in1[0]->registeras,'department_id'=>$in1[0]->department_id])
->get();

$tempdep = Department::where("id",$in1[0]->department_id)->get();

    if ($scdep->isEmpty())
    {

        $allfinancials = User::where(["client_id"=>$Beforeinvoice[0]->client_id,'user_type'=>'FINANCIAL'])->get();
        foreach ($allfinancials as $row)
        {

            $details = [
                'title' => "Beste financiële administratie,",
                'body1' => "In de bijlage is een creditnota toegevoegd voor de juiste uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "Ook is de creditnota voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
            \Mail::send((new \App\Mail\WelcomeEmail($details))
                ->to($row->email)->subject("Creditnota ZZP-er + Creditnota Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));

               $Rh = new Rh;

               $function="Creditnota ZZP-er + Creditnota Fee ZPC Afdeling";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_ids=1;
               $agreement_id=1;
               $Rh::emaillog($row->email,$function,$description,$assignments_id,$invoice_id,$times_ids,$agreement_id);

        }

    }


     else
    {

        $nistke=0;

        foreach ($scdep as $row)
        {

            $user=User::where(['id'=>$row->user_id])
            ->get();





            if ($user[0]->user_type=="FINANCIAL") 
            {

                $nistke++;

                if ($user[0]->email=="inkoopfacturen@mondriaan.eu") 
                {



                               $details = [
                'title' => "Beste financiële administratie,",
                'body1' => "In de bijlage is een creditnota toegevoegd voor de juiste uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "Ook is de creditnota voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
            \Mail::send((new \App\Mail\WelcomeEmail($details))
                ->to("crediteurenadministratie@mondriaan.eu")->subject("Creditnota ZZP-er + Creditnota Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));

               $Rh = new Rh;

               $function="Creditnota ZZP-er + Creditnota Fee ZPC Afdeling";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_ids=1;
               $agreement_id=1;
               $Rh::emaillog("crediteurenadministratie@mondriaan.eu",$function,$description,$assignments_id,$invoice_id,$times_ids,$agreement_id);

 

                }


 


             $details = [
                'title' => "Beste financiële administratie,",
                'body1' => "In de bijlage is een creditnota toegevoegd voor de juiste uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "Ook is de creditnota voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
            \Mail::send((new \App\Mail\WelcomeEmail($details))
                ->to($user[0]->email)->subject("Creditnota ZZP-er + Creditnota Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));

               $Rh = new Rh;

               $function="Creditnota ZZP-er + Creditnota Fee ZPC Afdeling";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_ids=1;
               $agreement_id=1;
               $Rh::emaillog($user[0]->email,$function,$description,$assignments_id,$invoice_id,$times_ids,$agreement_id);


 


            }


        }


if ($nistke==0) 
{
            $allfinancials = User::where(["client_id"=>$Beforeinvoice[0]->client_id,'user_type'=>'FINANCIAL'])->get();




        foreach ($allfinancials as $row)
        {





             $details = [
                'title' => "Beste financiële administratie,",
                'body1' => "In de bijlage is een creditnota toegevoegd voor de juiste uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "Ook is de creditnota voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
            \Mail::send((new \App\Mail\WelcomeEmail($details))
                ->to($row->email)->subject("Creditnota ZZP-er + Creditnota Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));

               $Rh = new Rh;

               $function="Creditnota ZZP-er + Creditnota Fee ZPC Afdeling";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_ids=1;
               $agreement_id=1;
               $Rh::emaillog($row->email,$function,$description,$assignments_id,$invoice_id,$times_ids,$agreement_id);



 


        }
}





    }

 
 

File::Delete(storage_path('app/public/pdf/'.$invoicename1.'.pdf'));
File::Delete(storage_path('app/public/pdf/'.$invoicename2.'.pdf'));



}
else //if its healthcare security
if ($in1[0]->registeras=="healthcare security")
{




 $tempdep = Department::where("id",$in2[0]->department_id)->get();

    $scdep=Joindepartment::where(['client_id'=>$in2[0]->client_id,'registeras'=>$in2[0]->registeras,'department_id'=>$in2[0]->department_id])
    ->get();

   



    if ($scdep->isEmpty())
    {

        $allfinancials = User::where(["client_id"=>$in2[0]->client_id,'user_type'=>'FINANCIAL'])->get();




        foreach ($allfinancials as $row)
        {

            $details = [
                'title' => "Beste financiële administratie,",
                'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
         


    \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to($row->email)->subject("Creditnota Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


               $Rh = new Rh;

               $function="Factuur Zorgbeveiliging zzp'er Afdeling: ";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$in2[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog($row->email,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);






        }







    }
    else
    {

        $nistke=0;

        foreach ($scdep as $row)
        {

            $user=User::where(['id'=>$row->user_id])
            ->get();





            if ($user[0]->user_type=="FINANCIAL") 
            {


$nistke++;
                if ($user[0]->email=="inkoopfacturen@mondriaan.eu") 
                {
 


        $details = [
                    'title' => "Beste financiële administratie,",
                    'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
                    'body2' => "",
                    'body3' => "Met vriendelijke groet,",
                    'body4' => "",
                ];
            

    \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to('crediteurenadministratie@mondriaan.eu')->subject("Creditnota Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));



               $Rh = new Rh;

               $function="Factuur Zorgbeveiliging zzp'er Afdeling:";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$in2[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog('crediteurenadministratie@mondriaan.eu',$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);





                }


 
                $details = [
                'title' => "Beste financiële administratie,",
                'body1' => "In de bijlage is een creditnota toegevoegd voor de juiste uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "Met vriendelijke groet,",
                'body3' => "",
                 

            ];
            

    \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to($user[0]->email)->subject("Creditnota Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


               $Rh = new Rh;

               $function="Creditnota Zorgbeveiliging zzp'er Afdeling: ";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$in2[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog($user[0]->email,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);



            }


        }


if ($nistke==0) 
{
   
       $allfinancials = User::where(["client_id"=>$in2[0]->client_id,'user_type'=>'FINANCIAL'])->get();




        foreach ($allfinancials as $row)
        {

            $details = [
                'title' => "Beste Beheerder",
                'body1' => "In de bijlage is een creditnota toegevoegd voor de juiste uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "Met vriendelijke groet,",
                'body3' => "",
                
            ];
         


    \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to($row->email)->subject("Creditnota Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


                $Rh = new Rh;

               $function="Factuur Zorgbeveiliging zzp'er Afdeling: ";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$in2[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog($row->email,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);




        }




   
}









    }




            $details = [
                'title' => "Beste,",
                'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
          

    \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to('info@zorgpuntconnect.nl')->subject("Creditnota Zorgbeveiliging zzp'er Afdeling: ".@$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf')));


                    $Rh = new Rh;

               $function="Factuur Zorgbeveiliging zzp'er Afdeling: ";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog('info@zorgpuntconnect.nl',$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);





   File::Delete(storage_path('app/public/pdf/'.$invoicename1.'.pdf'));
   File::Delete(storage_path('app/public/pdf/'.$invoicename2.'.pdf'));













}



 $Credits=Credits::where(["id"=>$credits_id])->get();

$lastcount=$Credits[0]->sendcout+1;

   Credits::where([
        "id"=>$credits_id
     ])->update([
           'sendcout' =>$lastcount,
        ]);

     
  return redirect()->back()->with('message', "Invoice Sent");


    }







    public function exportcreditnotePDF($language,$beforinvoice,$credits_id)
    {
 
 

 

       $Beforeinvoice=Beforeinvoice::where(["id"=>$beforinvoice])->get();



       $assignments=Assignment::where(["times_id"=>$Beforeinvoice[0]->times_id])->get();
 

       $Credit_note=Credit_note::where(["times_id"=>$Beforeinvoice[0]->times_id,'archive'=>1,'credits_id'=>$credits_id])->get();


 

 
            if (!$assignments || $assignments == null || count($assignments) == 0) {
                abort(404);
            }

            // view()->share('assignments', $assignments);

 
 $companylogo=Image::where(["document_title"=>"Company Logo","imageable_id"=>$assignments[0]->employee_id])->get();

 
 

          //return view('dashboard.invoices.exportcreditPdf' ,compact('assignments','companylogo','Beforeinvoice','Credit_note','credits_id'));


 

            $pdf_doc = PDF::loadView('dashboard.invoices.exportcreditPdf', compact('assignments','companylogo','Beforeinvoice','Credit_note','credits_id'));


// return PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('reports.invoiceSell')->stream();




    if (isset($_GET['sendcredit'])) 
    {













           
return redirect()->back()->with('message', "Credit Note Sent");


    }
//send creditnote

else
{
   return $pdf_doc->stream('pdf.pdf'); 
}


 
    }


 



    public function accindex($language, $client_id = -1, $department_id = -1, $employee_id = -1,$jobtitle=0,$year=-1,$month=-1,$type=-1,$status=-1)
    {
      

if (isset($_GET['checkkkk']))
 {




$Times=Times::where(["id"=>$_GET['checkkkk']])->get();




$can=0;

   $in1=Beforeinvoice::where(["employee_id"=>$Times[0]->employee_id,'year'=>$Times[0]->year,'month'=>$Times[0]->month,'registeras'=>$Times[0]->registeras,'client_id'=>$Times[0]->client_id,'department_id'=>$Times[0]->department_id])->get();


foreach ($in1 as $row) 
{
    $can++;
}


if ($can>0)
 {
   
        Times::where([
        "id"=>$_GET['checkkkk']
     ])->update([
           'status' =>'INVOICE_SENT',
        ]);

}



echo $can;


    die();
      $in1=Beforeinvoice::where(['type'=>"clientpaytoemploee"])->get();


foreach ($in1 as $row)
 {
    ?>
<div class="container">
  <h2>Basic Table</h2>
  <p>The .table class adds basic styling (light padding and only horizontal dividers) to a table:</p>            
  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Sent time</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?php echo $row->id; ?></td>
        <td><?php echo $row->created_at; ?></td>
      </tr>
    </tbody>
  </table>
</div>
    <?php
}

      exit;
}




if (isset($_GET['deleteinvoice'])) 
{
    if (Auth::user()->email=='ilixitdesign@gmail.com') 
    {
       $basket= Beforeinvoice::where(['id'=>$_GET['deleteinvoice'],'client_id'=>58])->delete();
       return redirect()->back()->with('message', "Invoice Deleted");
    }
}
 



// die('2');
//  $t=Rh::checkIBAN('NL69RABO0364603674');

// dd($t);

//  exit;

     if (isset($_GET['sendinvoice']))
     {


        if (!Auth::user()->user_type=='EMPLOYEE')
        {
            die('2');
        }

        $Beforeinvoice=Invoice::where(['id'=>(int)$_GET['sendinvoice'],'employee_id'=>Auth::user()->id])
         ->get();


        Invoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id])->update([
           'status' =>'INVOICE_SENT',
        ]);

        Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id])->update([
           'status' =>'INVOICE_SENT',
        ]);




        if ($Beforeinvoice[0]->registeras=="healthcare") 
        {

            $invoicename1="";
            $invoicename2="";
            
     $in1=Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>"clientpaytoemploee"])->get();

 
       $assignments=Invoice::where(["employee_id"=>$in1[0]->employee_id,'year'=>$in1[0]->year,'month'=>$in1[0]->month,'registeras'=>$in1[0]->registeras,'client_id'=>$in1[0]->client_id,'department_id'=>$in1[0]->department_id,'type'=>$in1[0]->type])->get();

       $invoicename1="Factuur zzp-er";

    $pdf_doc = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments'));


   Storage::put('public/pdf/'.$invoicename1.'.pdf', $pdf_doc->output());


 $in2=Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>"clientpaytozpc"])->get();

$Beforeinvoice=$in2;
$assignments=Invoice::where(["employee_id"=>$in2[0]->employee_id,'year'=>$in2[0]->year,'month'=>$in2[0]->month,'registeras'=>$in2[0]->registeras,'client_id'=>$in2[0]->client_id,'department_id'=>$in2[0]->department_id,'type'=>"clientpaytozpc"])->get();

       
            $invoicename2="Fee factuur";
 

    $pdf_doc1 = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments','Beforeinvoice'));

   Storage::put('public/pdf/'.$invoicename2.'.pdf', $pdf_doc1->output());
// return $pdf_doc1->stream('pdf.pdf');





$scdep=Joindepartment::where(['client_id'=>$Beforeinvoice[0]->client_id,'registeras'=>$Beforeinvoice[0]->registeras,'department_id'=>$Beforeinvoice[0]->department_id])
         ->get();


 $tempdep = Department::where("id",$Beforeinvoice[0]->department_id)->get();



foreach ($scdep as $row)
 {
  
$user=User::where(['id'=>$row->user_id])
         ->get();

if ($user[0]->user_type=="FINANCIAL") 
{
     //email for fin
$details = [
            'title' => "Beste financiële administratie,",
            'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
            'body2' => "Ook is de factuur voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
        'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user[0]->email)->subject("Factuur ZZP-er + factuur Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


}

 
}



 

File::Delete(storage_path('app/public/pdf/'.$invoicename1.'.pdf'));
File::Delete(storage_path('app/public/pdf/'.$invoicename2.'.pdf'));

          ///  return $pdf_doc->stream('pdf.pdf');

  return redirect()->back()->with('message', "Invoice Sent");


        }






        if ($Beforeinvoice[0]->registeras!="healthcare") 
        {



            $invoicename1="";
            $invoicename2="";
            
     $in1=Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>"zpcpaytoemploee"])->get();


       $assignments=Invoice::where(["employee_id"=>$in1[0]->employee_id,'year'=>$in1[0]->year,'month'=>$in1[0]->month,'registeras'=>$in1[0]->registeras,'client_id'=>$in1[0]->client_id,'department_id'=>$in1[0]->department_id,'type'=>$in1[0]->type])->get();

       $invoicename1="Factuur ZPC";

    $pdf_doc = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments'));


   Storage::put('public/pdf/'.$invoicename1.'.pdf', $pdf_doc->output());


 $in2=Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>"clientpaytozpc"])->get();

$Beforeinvoice=$in2;
$assignments=Invoice::where(["employee_id"=>$in2[0]->employee_id,'year'=>$in2[0]->year,'month'=>$in2[0]->month,'registeras'=>$in2[0]->registeras,'client_id'=>$in2[0]->client_id,'department_id'=>$in2[0]->department_id,'type'=>"clientpaytozpc"])->get();

       
            $invoicename2="Factuur zzp-er";
 

    $pdf_doc1 = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments','Beforeinvoice'));

   Storage::put('public/pdf/'.$invoicename2.'.pdf', $pdf_doc1->output());
 


//Urenverificatie to schedule department

$scdep=Joindepartment::where(['client_id'=>$Beforeinvoice[0]->client_id,'registeras'=>$Beforeinvoice[0]->registeras,'department_id'=>$Beforeinvoice[0]->department_id])
         ->get();

 $tempdep = Department::where("id",$Beforeinvoice[0]->department_id)->get();

foreach ($scdep as $row)
 {
  
$user=User::where(['id'=>$row->user_id])
         ->get();

if ($user[0]->user_type=="FINANCIAL") 
{
     //email for fin
$details = [
            'title' => "Beste financiële administratie,",
            'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de Zzp-er voor de afgelopen maand.",
            'body2' => "Met vriendelijke groet,",
            'body3' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user[0]->email)->subject("Factuur ZPC. Afdeling: ".$tempdep[0]->title )->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));
}

 
}









 //email for admin
$details = [
            'title' => "Beste Beheerder,",
            'body1' => "In de bijlage is een factuur toegevoegd van de gewerkte uren van de Zzp-er van de afgelopen maand.",
            'body2' => "Met vriendelijke groet,",
            'body3' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to('beheerderzpc@gmail.com')->subject("Factuur ZPC. Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf')));



 

File::Delete(storage_path('app/public/pdf/'.$invoicename1.'.pdf'));
File::Delete(storage_path('app/public/pdf/'.$invoicename2.'.pdf'));

          ///  return $pdf_doc->stream('pdf.pdf');

  return redirect()->back()->with('message', "Invoice Sent");


        }





     }


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

       $Beforeinvoice=Invoice::where(['id'=>(int)$_GET['clientconfirm'],'client_id'=>$client_id])
       ->get();




        Invoice::where([
        "employee_id"=>$Beforeinvoice[0]->employee_id,
        'year'=>$Beforeinvoice[0]->year,
        'month'=>$Beforeinvoice[0]->month,
        'registeras'=>$Beforeinvoice[0]->registeras,
        'client_id'=>$Beforeinvoice[0]->client_id,
        'department_id'=>$Beforeinvoice[0]->department_id,
        'type'=>$Beforeinvoice[0]->type])
        ->update([
           'status' =>'CONFIRMED',
        ]);

        Beforeinvoice::where([
        "employee_id"=>$Beforeinvoice[0]->employee_id,
        'year'=>$Beforeinvoice[0]->year,
        'month'=>$Beforeinvoice[0]->month,
        'registeras'=>$Beforeinvoice[0]->registeras,
        'client_id'=>$Beforeinvoice[0]->client_id,
        'department_id'=>$Beforeinvoice[0]->department_id,
        'type'=>$Beforeinvoice[0]->type])
        ->update([
           'status' =>'CONFIRMED',
        ]);

        if ($Beforeinvoice[0]->type=="clientpaytoemploee") 
        {

        Invoice::where([
        "employee_id"=>$Beforeinvoice[0]->employee_id,
        'year'=>$Beforeinvoice[0]->year,
        'month'=>$Beforeinvoice[0]->month,
        'registeras'=>$Beforeinvoice[0]->registeras,
        'client_id'=>$Beforeinvoice[0]->client_id,
        'department_id'=>$Beforeinvoice[0]->department_id,
        'type'=>"clientpaytozpc"])
        ->update([
           'status' =>'CONFIRMED',
        ]);

        Beforeinvoice::where([
        "employee_id"=>$Beforeinvoice[0]->employee_id,
        'year'=>$Beforeinvoice[0]->year,
        'month'=>$Beforeinvoice[0]->month,
        'registeras'=>$Beforeinvoice[0]->registeras,
        'client_id'=>$Beforeinvoice[0]->client_id,
        'department_id'=>$Beforeinvoice[0]->department_id,
        'type'=>"clientpaytozpc"])
        ->update([
           'status' =>'CONFIRMED',
        ]);

        }


        if ($Beforeinvoice[0]->type=="clientpaytozpc") 
        {

        Invoice::where([
        "employee_id"=>$Beforeinvoice[0]->employee_id,
        'year'=>$Beforeinvoice[0]->year,
        'month'=>$Beforeinvoice[0]->month,
        'registeras'=>$Beforeinvoice[0]->registeras,
        'client_id'=>$Beforeinvoice[0]->client_id,
        'department_id'=>$Beforeinvoice[0]->department_id,
        'type'=>"zpcpaytoemploee"])
        ->update([
           'status' =>'CONFIRMED',
        ]);

        Beforeinvoice::where([
        "employee_id"=>$Beforeinvoice[0]->employee_id,
        'year'=>$Beforeinvoice[0]->year,
        'month'=>$Beforeinvoice[0]->month,
        'registeras'=>$Beforeinvoice[0]->registeras,
        'client_id'=>$Beforeinvoice[0]->client_id,
        'department_id'=>$Beforeinvoice[0]->department_id,
        'type'=>"zpcpaytoemploee"])
        ->update([
           'status' =>'CONFIRMED',
        ]);
        
        }

// Uren goedgekeurd door planning to freelancer
$useremail=Rh::getuseremail($Beforeinvoice[0]->employee_id);

$details = [
            'title' => "Beste ZPC-er,",
            'body1' => "Jouw gewerkte uren van de afgelopen maand zijn goedgekeurd.",
            'body2' => "Log in op het portaal om een factuur aan te maken en op te sturen.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "Team ZPC",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($useremail['0']->email)->subject("Uren goedgekeurd door planning"));




       return redirect()->back()->with('message', "Successfuly confirmed!");


     }



    if (isset($_GET['clientreject']))
     {
        die('2');
    if (Auth::user()->user_type=='CLIENT')
         {
        Invoice::where(['id'=>(int)$_GET['clientreject'],'client_id'=>Auth::user()->id])
       ->update([
           'status' =>'CLIENT_CANCELED',
        ]);
         }
         else
        if (Auth::user()->user_type=='SCHEDULE')
         {
        Invoice::where(['id'=>(int)$_GET['clientreject'],'client_id'=>Auth::user()->client_id])
       ->update([
           'status' =>'CLIENT_CANCELED',
        ]);
         }


       return redirect()->back()->with('message', "Successfuly rejected!");


     }








    if (isset($_GET['emploeeconfirm']))
     {
        // die('2');
        if (Auth::user()->user_type=='EMPLOYEE')
         {

         $Beforeinvoice=Invoice::where(['id'=>(int)$_GET['emploeeconfirm'],'employee_id'=>Auth::user()->id])
         ->get();


Invoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>$Beforeinvoice[0]->type])->update([
           'status' =>'EMPLOYEE_ACCEPTED',
        ]);


 Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>$Beforeinvoice[0]->type])->update([
           'status' =>'EMPLOYEE_ACCEPTED',
        ]);


         if ($Beforeinvoice[0]->type=="zpcpaytoemploee") 
         {
             
Invoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>"clientpaytozpc"])->update([
           'status' =>'EMPLOYEE_ACCEPTED',
        ]);


 Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>"clientpaytozpc"])->update([
           'status' =>'EMPLOYEE_ACCEPTED',
        ]);


         }


 
//Urenverificatie to schedule department

$scdep=Joindepartment::where(['client_id'=>$Beforeinvoice[0]->client_id,'registeras'=>$Beforeinvoice[0]->registeras,'department_id'=>$Beforeinvoice[0]->department_id])
         ->get();


foreach ($scdep as $row)
 {
  
$user=User::where(['id'=>$row->user_id])
         ->get();

if ($user[0]->user_type=="SCHEDULE") 
{

$details = [
            'title' => "Beste Planner,",
            'body1' => "De uren van de afgelopen maand kunnen gecontroleerd en goedgekeurd worden.",
            'body2' => "Log in op het portaal om de uren te bekijken en goed te keuren.",
            'body3' => "Met vriendelijke groet,",
            'body4' => "Team ZPC",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user[0]->email)->subject("Urenverificatie"));

}


}










       return redirect()->back()->with('message', "Successfuly confirmed!");
         }
     }


    if (isset($_GET['emploeereject']))
     {
        die('2');
    if (Auth::user()->user_type=='EMPLOYEE')
         {
        Invoice::where(['id'=>(int)$_GET['emploeereject'],'employee_id'=>Auth::user()->id])
       ->update([
           'status' =>'EMPLOYEE_REJECTED',
        ]);
       return redirect()->back()->with('message', "Successfuly rejected!");
         }
     }


 if (isset($_GET['payinvoce']))
     {
 
 $Beforeinvoice=Beforeinvoice::where(["id"=>$_GET['payinvoce'],'client_id'=>Auth::user()->client_id])->get();

if (@$Beforeinvoice[0]->client_id==Auth::user()->client_id) 
{
     Beforeinvoice::where(["id"=>$_GET['payinvoce'],'client_id'=>Auth::user()->client_id])->update([
           'status' =>'PAID',
        ]);

Invoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>$Beforeinvoice[0]->type])->update([
           'status' =>'PAID',
        ]);

return redirect('/'.$language.'/accinvoices/all/all/all/all/all/all/all');


 


}
else
{
    return redirect()->back()->with('message', "Opss!"); 
}





     }



     $query = Beforeinvoice::query();

        if ($jobtitle!="all") 
        {
             $query = $query->where("registeras",$jobtitle);
        }

        if ($type!="all") 
        {
             $query = $query->where("type",$type);
        }



        if ($department_id!="all") 
        {
             $query = $query->where("department_id",$department_id);
        }

        if ($year!="all") 
        {
             $query = $query->where("year",$year);
        }

        if ($month!="all") 
        {
             $query = $query->where("month",$month);
        }


if (Auth::user()->user_type=="ADMIN") 
{ 
        if ($client_id!="all") 
        {
             $query = $query->where("client_id",$client_id);
        }

        if ($employee_id!="all") 
        {
             $query = $query->where("employee_id",$employee_id);
        }


}
else
if (Auth::user()->user_type=="CLIENT")
{
 $query = $query->where("client_id",Auth::user()->id);
 $query = $query->whereIn("type", ["clientpaytoemploee", "clientpaytozpc"]);
}
else
if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
{
 $query = $query->where("client_id",Auth::user()->client_id);
 $query = $query->whereIn("type", ["clientpaytoemploee", "clientpaytozpc"]);
 }
else
if (Auth::user()->user_type=="EMPLOYEE")
{

        if ($client_id!="all") 
        {
             $query = $query->where("client_id",$client_id);
        }


 $query = $query->where("employee_id",Auth::user()->id);
 $query = $query->whereIn("type", ["clientpaytoemploee", "zpcpaytoemploee"]);
}


 $query = $query->whereIn("status", ["PAID", "INVOICE_SENT","CONFIRMED"]);

              if (Auth::user()->user_type != "EMPLOYEE")
             {

            if ($employee_id > 0 and $employee_id!='all') 
            {
 
                $query = $query->where("employee_id", $employee_id);
            }

            }


        if ($status!="all") 
        {
             $query = $query->where("status",$status);
        }



  if (isset($_GET['id']) and $_GET['id']!=0) //for show open assighnment
        {
 $query = $query->where("id",(int)$_GET['id']);
        }


                     $beforinvoices = $query->paginate(Auth::user()->paginationnum);




if (Auth::user()->user_type=="EMPLOYEE") 
{

 $departments =  array();
 // $departments = Department::select(["id", "title"])->get();
     $employees =null;        
}


if (Auth::user()->user_type=="CLIENT") 
{
        $departments = Department::select(["id", "title"])->where("client_id", Auth::user()->id)->get();

                              $employees = DB::table('joinclient')
                      ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
                      ->where(["joinclient.client_id"=> Auth::user()->id])
                      ->select('profiles.first_name','profiles.last_name','joinclient.*')
                      ->orderBy("profiles.first_name","asc")
                      ->get()->unique('user_id');
             
}

if (Auth::user()->user_type=="ADMIN") 
{
       $departments = Department::select(["id", "title"])->get();
       $employees = User::where("user_type", "EMPLOYEE")->get();    
}

if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL") 
{
        $departments = Department::select(["id", "title"])->where("client_id", Auth::user()->client_id)->get();

                              $employees = DB::table('joinclient')
                      ->join('profiles', 'profiles.user_id', '=', 'joinclient.user_id')
                      ->where(["joinclient.client_id"=> Auth::user()->client_id])
                      ->select('profiles.first_name','profiles.last_name','joinclient.*')
                      ->orderBy("profiles.first_name","asc")
                      ->get()->unique('user_id');

             
}

                






                    $clients = User::where("user_type", "CLIENT")->get();
                  
                    $profiles = Profile::get();
   


 
            return view('dashboard.invoices.accemployeeIndex')
                ->with([
                    "beforinvoices" => $beforinvoices,
                    "client_id" => $client_id,
                    "department_id" => $department_id,
                    "employee_id" => $employee_id,
                    "year" => $year,
                    "type" => $type,
                    "jobtitle" => $jobtitle,
                    "month" => $month,
                    "employees" => $employees,
                    "clients" => $clients,
                    "departments" => $departments,
                    "profiles" => $profiles,
                    "status" => $status,
                ]);
       
    }


























   public function addinvoicenumber($en,$id)
    {
 

if (isset($_POST['com'])) 
{
   


       $_GET['emploeeconfirm']=$id;
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







//  $department=Department::where(["id"=>$Times[0]->department_id])->get();



// // Afkeur urenregistratie (department) to freelancer
// $useremail=Rh::getuseremail($Times[0]->employee_id);

// $details = [
//             'title' => "Beste ZPC-er,",
//             'body1' => "Jouw gewerkte uren van de afgelopen maand zijn niet goedgekeurd door de roostermakers.",
//             'body2' => "Log in op het portaal om de notitie van de roostermaker te bekijken.",
//             'body3' => "Met vriendelijke groet,",
//             'body4' => "Team ZPC",
//         ];
// \Mail::send((new \App\Mail\WelcomeEmail($details))
//     ->to($useremail[0]->email)->subject("Afkeur urenregistratie ".$department[0]->title.""));







    
       return redirect()->back()->with('message', "Successfuly inserted!");
         }

}
else
{

$canupdaethisinvoicenum=1;
$silab = "-";
$invoicenumbertemp = @$_POST['invoicenumber'];



if (is_numeric($invoicenumbertemp))
 {

     if (strpos($invoicenumbertemp, ".") !== false) 
     {
       $canupdaethisinvoicenum=0;
     }

     if (is_float($invoicenumbertemp)) 
     {
       $canupdaethisinvoicenum=0;
     }
     
 }
 else
 {

  // Test if string contains the word 
if(strpos($invoicenumbertemp, $silab) !== false)
{
  
       if (strpos($invoicenumbertemp, ".") !== false) 
     {
       $canupdaethisinvoicenum=0;
     }

     if (is_float($invoicenumbertemp)) 
     {
       $canupdaethisinvoicenum=0;
     }

$exploded=explode("-",$invoicenumbertemp);

if (is_numeric($exploded[0]))
 {
     
 }
 else
 {
 $canupdaethisinvoicenum=0;
 }

 if (is_numeric($exploded[1]))
 {
      
 }
 else
 {
    $canupdaethisinvoicenum=0;
 }
 


} 
else
{
    $canupdaethisinvoicenum=0;
}




 }




if ($canupdaethisinvoicenum==1) 
{
     
}
else
{
  return redirect()->back()->with('message', "Factuurnummers mogen alleen uit cijfers bestaan zonder spatie. Streepjes(-) zijn ook toegestaan. 
Bijv.: 082022 of 082022-1");
}

 

    $checkhasinvoicenumber=Times::where(["employee_id"=>Auth::user()->id,'invoicenumber'=>@$_POST['invoicenumber']])->get();


       if ($checkhasinvoicenumber->isEmpty())
        {
        }
        else
        {
        return redirect()->back()->with('message', "This Invoice Number Exist!");
        }
 


         $_GET['emploeeconfirm']=$id;
        if (Auth::user()->user_type=='EMPLOYEE')
         {

  $Times=Times::where(['id'=>(int)$_GET['emploeeconfirm'],'employee_id'=>Auth::user()->id])
       ->get();


 
Times::where(["id"=>$Times[0]->id])->update([
           'invoicenumber' =>@$_POST['invoicenumber'],
        ]);



 $Rh=new Rh;
//send invoice

$user_id=Auth::user()->id;
$page="Times";
$function="send invoice-number";
$description="wehn the send invoice-number-button is clicked";
$assignments_id=1;
$invoice_id=1;
$times_id=$Times[0]->id;
$agreement_id=1;


$Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);


 

    return redirect(app()->getLocale().'/sendinvoice/'.$Times[0]->id);
    
       ///return redirect()->back()->with('message', "Successfuly inserted!");




         }  
}




 
    




    
    }

    public function index($language, $client_id = -1, $department_id = -1, $employee_id = -1,$jobtitle=0,$year=-1,$month=-1,$type=-1)
    {
      


     if (isset($_GET['sendinvoice']))
     {


        if (!Auth::user()->user_type=='EMPLOYEE')
        {
            die('2');
        }

        $Beforeinvoice=Invoice::where(['id'=>(int)$_GET['sendinvoice'],'employee_id'=>Auth::user()->id])
         ->get();


        Invoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id])->update([
           'status' =>'INVOICE_SENT',
        ]);

        Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id])->update([
           'status' =>'INVOICE_SENT',
        ]);




        if ($Beforeinvoice[0]->registeras=="healthcare") 
        {

            $invoicename1="";
            $invoicename2="";
            
     $in1=Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>"clientpaytoemploee"])->get();

 
       $assignments=Invoice::where(["employee_id"=>$in1[0]->employee_id,'year'=>$in1[0]->year,'month'=>$in1[0]->month,'registeras'=>$in1[0]->registeras,'client_id'=>$in1[0]->client_id,'department_id'=>$in1[0]->department_id,'type'=>$in1[0]->type])->get();

       $invoicename1="Factuur zzp-er";

    $pdf_doc = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments'));


   Storage::put('public/pdf/'.$invoicename1.'.pdf', $pdf_doc->output());


 $in2=Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>"clientpaytozpc"])->get();

$Beforeinvoice=$in2;
$assignments=Invoice::where(["employee_id"=>$in2[0]->employee_id,'year'=>$in2[0]->year,'month'=>$in2[0]->month,'registeras'=>$in2[0]->registeras,'client_id'=>$in2[0]->client_id,'department_id'=>$in2[0]->department_id,'type'=>"clientpaytozpc"])->get();

       
            $invoicename2="Fee factuur";
 

    $pdf_doc1 = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments','Beforeinvoice'));

   Storage::put('public/pdf/'.$invoicename2.'.pdf', $pdf_doc1->output());
// return $pdf_doc1->stream('pdf.pdf');





$scdep=Joindepartment::where(['client_id'=>$Beforeinvoice[0]->client_id,'registeras'=>$Beforeinvoice[0]->registeras,'department_id'=>$Beforeinvoice[0]->department_id])
         ->get();


 $tempdep = Department::where("id",$Beforeinvoice[0]->department_id)->get();



foreach ($scdep as $row)
 {
  
$user=User::where(['id'=>$row->user_id])
         ->get();

if ($user[0]->user_type=="FINANCIAL") 
{
     //email for fin
$details = [
            'title' => "Beste financiële administratie,",
            'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
            'body2' => "Ook is de factuur voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
        'body3' => "Met vriendelijke groet,",
            'body4' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user[0]->email)->subject("Factuur ZZP-er + factuur Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


}

 
}



 

File::Delete(storage_path('app/public/pdf/'.$invoicename1.'.pdf'));
File::Delete(storage_path('app/public/pdf/'.$invoicename2.'.pdf'));

          ///  return $pdf_doc->stream('pdf.pdf');

  return redirect()->back()->with('message', "Invoice Sent");


        }






        if ($Beforeinvoice[0]->registeras!="healthcare") 
        {



            $invoicename1="";
            $invoicename2="";
            
     $in1=Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>"zpcpaytoemploee"])->get();


       $assignments=Invoice::where(["employee_id"=>$in1[0]->employee_id,'year'=>$in1[0]->year,'month'=>$in1[0]->month,'registeras'=>$in1[0]->registeras,'client_id'=>$in1[0]->client_id,'department_id'=>$in1[0]->department_id,'type'=>$in1[0]->type])->get();

       $invoicename1="Factuur ZPC";

    $pdf_doc = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments'));


   Storage::put('public/pdf/'.$invoicename1.'.pdf', $pdf_doc->output());


 $in2=Beforeinvoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>"clientpaytozpc"])->get();

$Beforeinvoice=$in2;
$assignments=Invoice::where(["employee_id"=>$in2[0]->employee_id,'year'=>$in2[0]->year,'month'=>$in2[0]->month,'registeras'=>$in2[0]->registeras,'client_id'=>$in2[0]->client_id,'department_id'=>$in2[0]->department_id,'type'=>"clientpaytozpc"])->get();

       
            $invoicename2="Factuur zzp-er";
 

    $pdf_doc1 = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments','Beforeinvoice'));

   Storage::put('public/pdf/'.$invoicename2.'.pdf', $pdf_doc1->output());
 


//Urenverificatie to schedule department

$scdep=Joindepartment::where(['client_id'=>$Beforeinvoice[0]->client_id,'registeras'=>$Beforeinvoice[0]->registeras,'department_id'=>$Beforeinvoice[0]->department_id])
         ->get();

 $tempdep = Department::where("id",$Beforeinvoice[0]->department_id)->get();

foreach ($scdep as $row)
 {
  
$user=User::where(['id'=>$row->user_id])
         ->get();

if ($user[0]->user_type=="FINANCIAL") 
{
     //email for fin
$details = [
            'title' => "Beste financiële administratie,",
            'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de Zzp-er voor de afgelopen maand.",
            'body2' => "Met vriendelijke groet,",
            'body3' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($user[0]->email)->subject("Factuur ZPC. Afdeling: ".$tempdep[0]->title )->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));
}

 
}









 //email for admin
$details = [
            'title' => "Beste Beheerder,",
            'body1' => "In de bijlage is een factuur toegevoegd van de gewerkte uren van de Zzp-er van de afgelopen maand.",
            'body2' => "Met vriendelijke groet,",
            'body3' => "",
        ];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to('beheerderzpc@gmail.com')->subject("Factuur ZPC. Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf')));



 

File::Delete(storage_path('app/public/pdf/'.$invoicename1.'.pdf'));
File::Delete(storage_path('app/public/pdf/'.$invoicename2.'.pdf'));

          ///  return $pdf_doc->stream('pdf.pdf');

  return redirect()->back()->with('message', "Invoice Sent");


        }





     }


 
     $query = Beforeinvoice::query();

        if ($jobtitle!="all") 
        {
             $query = $query->where("registeras",$jobtitle);
        }

        if ($department_id!="all") 
        {
             $query = $query->where("department_id",$department_id);
        }

        if ($year!="all") 
        {
             $query = $query->where("year",$year);
        }

        if ($month!="all") 
        {
             $query = $query->where("month",$month);
        }

        if ($type!="all") 
        {
             $query = $query->where("type",$type);
        }

        
if (Auth::user()->user_type=="ADMIN") 
{ 
        if ($client_id!="all") 
        {
             $query = $query->where("client_id",$client_id);
        }

        if ($employee_id!="all") 
        {
             $query = $query->where("employee_id",$employee_id);
        }


}
else
if (Auth::user()->user_type=="CLIENT")
{
 $query = $query->where("client_id",Auth::user()->id);
 $query = $query->whereIn("type", ["clientpaytoemploee", "clientpaytozpc"]);
}
else
if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
{
 $query = $query->where("client_id",Auth::user()->client_id);
 $query = $query->whereIn("type", ["clientpaytoemploee", "clientpaytozpc"]);
 }
else
if (Auth::user()->user_type=="EMPLOYEE")
{

        if ($client_id!="all") 
        {
             $query = $query->where("client_id",$client_id);
        }


 $query = $query->where("employee_id",Auth::user()->id);
 $query = $query->whereIn("type", ["clientpaytoemploee", "zpcpaytoemploee"]);
}



 $query = $query->whereIn("status", ["DENIED", "PENDING", "CONFIRMED", "EMPLOYEE_ACCEPTED", "EMPLOYEE_REJECTED", "CLIENT_CANCELED", "EMPLOYEE_CANCELED","EMPLOYEE_CONFIRMED","CLIENT_ACCEPTED","SCHEDULE_ACCEPTED","FINANCIAL_ACCEPTED","FINANCIAL_ACCEPTED"]);

 


                     $beforinvoices = $query->paginate(Auth::user()->paginationnum);



                    $departments = Department::select(["id", "title"])->get();
             

                    $clients = User::where("user_type", "CLIENT")->get();
                    $employees = User::where("user_type", "EMPLOYEE")->get();
                    $profiles = Profile::get();
   
 
            return view('dashboard.invoices.employeeIndex')
                ->with([
                    "beforinvoices" => $beforinvoices,
                    "client_id" => $client_id,
                    "department_id" => $department_id,
                    "employee_id" => $employee_id,
                    "year" => $year,
                    "type" => $type,
                    "jobtitle" => $jobtitle,
                    "month" => $month,
                    "employees" => $employees,
                    "clients" => $clients,
                    "departments" => $departments,
                    "profiles" => $profiles,
                ]);
       
    }





 

 
 

 


 
 



    public function indexDepartmentsGroupby($language, $beforeinvoice_id, $year, $month)
    {

 

    $Beforeinvoice=Beforeinvoice::where(["id"=>$beforeinvoice_id])->get();

        $assignments=Assignment::where(["times_id"=>$Beforeinvoice[0]->times_id,'status'=>'EMPLOYEE_ACCEPTED'])->orderBy("start_date","asc")->get();


if ($assignments->isEmpty())
{
      $Beforeinvoice=Beforeinvoice::where(["id"=>$beforeinvoice_id])->delete();  
       return redirect()->back()->with('message', "Invoice Is Invalid");
}
        
        return view('dashboard.invoices.departments')
            ->with([
                "departments" => null,
                "employees" => null,
                "clients" => null,
                "assignments" => $assignments,
                "year" => $year,
                "month" => $month,
                "client_id" => -1,
                "employee_id" => -1,
                "department_id" => $Beforeinvoice[0]->department_id
            ]);
    }



 

 
  



}
