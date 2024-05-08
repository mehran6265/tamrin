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

     if($Beforeinvoice[0]->invoicenumber!=null){ $tttttt= $Beforeinvoice[0]->invoicenumber."-".$credits_id;} else { $tttttt= $Beforeinvoice[0]->id."-".$credits_id; }  


   return $pdf_doc->stream($tttttt.'.pdf'); 
}


 
    }


 



    public function accindex($language, $client_id = -1, $department_id = -1, $employee_id = -1,$jobtitle=0,$year=-1,$month=-1,$type=-1,$status=-1)
    {
      

 
   

 



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
                    "status" => $status,
                ]);
       
    }


























   public function addinvoicenumber($en,$id)
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

























 
 




    public function indexdetailsGroupby($language, $beforeinvoice_id)
    {

 

    $Beforeinvoice=Beforeinvoice::where(["id"=>$beforeinvoice_id])->get();

        $assignments=Assignment::where(["times_id"=>$Beforeinvoice[0]->times_id,'status'=>'EMPLOYEE_ACCEPTED'])->orderBy("start_date","asc")->get();

                $year =  $Beforeinvoice[0]->year;
                $month=$Beforeinvoice[0]->month;


if ($assignments->isEmpty())
{
      $Beforeinvoice=Beforeinvoice::where(["id"=>$beforeinvoice_id])->delete();  
       return redirect()->back()->with('message', "Invoice Is Invalid");
}
        
        return view('dashboard.invoices.detinvoices')
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





    public function pendingInvoicesIndex($language, $client_id = -1, $department_id = -1, $employee_id = -1)
    {
        if (Auth::user()->hasRole('financial') || Auth::user()->hasRole('client') || Auth::user()->hasRole('employee')) {
            abort(403);
            exit();
        }


        $query = Assignment::query();
        switch (Auth::user()->user_type) {
            case 'SCHEDULE':
                $client_id = Auth::user()->client_id;
                $clients = null;
                $departments = Department::where("client_id", $client_id)->select(["id", "title"])->get();
                $employees = User::where("id", ">", 3)->where("client_id", $client_id)->where("user_type", "EMPLOYEE")->where("is_activated", true)->with(['profile:user_id,first_name,last_name'])->select(["id", "email"])->get();
                if ($employee_id > 0) {
                    $employee = User::where("client_id", $client_id)->findOrFail($employee_id);
                    $employee_id_array = [$employee_id];
                }

                $query = $query->where("status", "EMPLOYEE_CONFIRMED");

                DB::statement("SET SQL_MODE=''");
                $query = $query->groupBy(DB::raw("DATE_FORMAT(start_date, '%m-%Y')"));
                $query = $query->groupBy("department_id");
                break;
            default:
                $departments = Department::select(["id", "title"])->get();
                $users = User::select(["id", "email", "user_type"])->whereIn("user_type", ["CLIENT", "EMPLOYEE"])->where("is_activated", true)->with(['profile:user_id,first_name,last_name'])->get();

                $clients = $users->where("user_type", "CLIENT")->where("is_activated", true);
                $employees = $users->where("id", ">", 3)->where("user_type", "EMPLOYEE")->where("is_activated", true);

                $query = $query->where("status", "EMPLOYEE_CONFIRMED");

                DB::statement("SET SQL_MODE=''");
                $query = $query->groupBy(DB::raw("DATE_FORMAT(start_date, '%m-%Y')"));
                $query = $query->groupBy("department_id");

                if ($employee_id > 0) {
                    $employee_id_array = [$employee_id];
                }

                break;
        }

        $query = $query->orderBy("start_date", "desc");

        if ($client_id > 0) {
            $query = $query->where("client_id", $client_id);
        }

        if ($employee_id > 0) {
            $query = $query->whereIn("employee_id", $employee_id_array);
        }
        if ($department_id > 0) {
            $query = $query->where("department_id", $department_id);
        }

        $query = $query->where("type", "INVOICE");


        $assignments = $query->with(["client", "client.profile", "employee", "employee.profile", "department"])->paginate(Auth::user()->paginationnum);

        return view('dashboard.invoices.pendingIndex')
            ->with([
                "departments" => $departments,
                "employees" => $employees,
                "clients" => $clients,
                "assignments" => $assignments,
                "client_id" => $client_id,
                "employee_id" => $employee_id,
                "department_id" => $department_id
            ]);
    }




    public function epartmentsConfirm($language, $department_id, $year, $month)
    {

        if (Auth::user()->hasRole('employee') || Auth::user()->hasRole('client') || Auth::user()->hasRole('financial')) {
            abort(403);
            exit();
        }

        $query = Assignment::query();

        if (Auth::user()->hasRole('client')) {
            $query = $query->where("client_id", Auth::user()->id);
        } else if (Auth::user()->hasRole('schedule') || Auth::user()->hasRole('financial')) {
            $query = $query->where("client_id", Auth::user()->client_id);
        }

        try {
            DB::beginTransaction();

            $query = $query->whereYear("start_date", "=", $year);
            $query = $query->whereMonth("start_date", "=", $month);

            $query = $query->where("department_id", $department_id);
            $query = $query->where("type", "INVOICE");

            $query = $query->where("status", "EMPLOYEE_CONFIRMED");
            $query->update(['status' => 'SCHEDULE_ACCEPTED']);

            DB::commit();

            return redirect()->back()->with('message', "Successfuly confirmed!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function employeesConfirm($language, $employee_id, $year, $month)
    {

        if (Auth::user()->hasRole('client') || Auth::user()->hasRole('financial') || Auth::user()->hasRole('schedule')) {
            abort(403);
            exit();
        }

        $query = Assignment::query();

        if (Auth::user()->hasRole('employee')) {
            $query = $query->where("client_id", Auth::user()->client_id);
        }

        try {
            DB::beginTransaction();

            $query = $query->where("employee_id", $employee_id);

            $query = $query->whereYear("start_date", "=", $year);
            $query = $query->whereMonth("start_date", "=", $month);

            $query = $query->where("type", "INVOICE");

            $query = $query->where("status", "EMPLOYEE_ACCEPTED");

            $query->update(['status' => 'EMPLOYEE_CONFIRMED']);

            DB::commit();

            return redirect()->back()->with('message', "Successfuly confirmed!");
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function indexEmployeesPendingInvoices($language, $employee_id, $year, $month, $group_by = false)
    {
   
        if (Auth::user()->hasRole('client') || Auth::user()->hasRole('financial') || Auth::user()->hasRole('schedule')) {
            abort(403);
            exit();
        }

        $query = Assignment::query();
        $client_id = -1;
        switch (Auth::user()->user_type) {
            case 'EMPLOYEE':
                // $client_id = Auth::user()->client_id;
                $employee_id = Auth::user()->id;
                // $departments = Department::select(["id", "title"])->get();
                $clients = null;
                $employees = null;


                // TODO: not pending
                // $query = $query->where("status", "EMOLYEE_ACCEPTED");

                // update the requests till now
                $today = Carbon::now()->tz(config('app.app_timezone'))->format('Y-m-d');
                DB::table('assignments')->where("employee_id", Auth::user()->id)->where("start_date", "<", $today)->where("status", "EMPLOYEE_ACCEPTED")->where("type", "ASSIGNMENT")->update(['type' => 'INVOICE']);


                break;
            case 'CLIENT':
                abort(403);
                break;
            case 'SCHEDULE':
            case 'FINANCIAL':
                abort(403);
                break;
            default:
                // $departments = Department::select(["id", "title"])->get();
                // $users = User::select(["id", "email", "user_type"])->whereIn("user_type", ["CLIENT", "EMPLOYEE"])->where("is_activated", true)->with(['profile:user_id,first_name,last_name'])->get();

                //  $clients = $users->where("user_type", "CLIENT");
                // $employees = $users->where("user_type", "EMPLOYEE")->where("is_activated",true);

                // TODO: remove extra
                $today = Carbon::now()->tz(config('app.app_timezone'))->format('Y-m-d');
                DB::table('assignments')->where("employee_id", $employee_id)->where("start_date", "<", $today)->where("status", "EMPLOYEE_ACCEPTED")->where("type", "ASSIGNMENT")->update(['type' => 'INVOICE']);

                break;
        }

        $query = $query->orderBy("start_date", "desc");

        if ($year > 0) {
            $query = $query->whereYear("start_date", "=", $year);
        }

        if ($month > 0) {
            $query = $query->whereMonth("start_date", "=", $month);
        }

        if ($client_id > 0) {
            $query = $query->where("client_id", $client_id);
        }

        $query = $query->where("employee_id", $employee_id);
        $query = $query->where("type", "INVOICE");
        $query = $query->whereIn("status", ["EMPLOYEE_ACCEPTED", "EMPLOYEE_CONFIRMED"]);

        if ($group_by) {
            DB::statement("SET SQL_MODE=''");
            $query = $query->groupBy(DB::raw("DATE_FORMAT(start_date, '%m-%Y')"));
            // $query = $query->groupBy("department_id");

            $assignments = $query->with(["client", "client.profile", "employee", "employee.profile", "department"])->paginate(100);

            return view('dashboard.invoices.employeesPendingIndex')
                ->with([
                    "departments" => null,
                    "employees" => null,
                    "clients" => null,
                    "assignments" => $assignments,
                    "year" => $year,
                    "month" => $month,
                    "client_id" => -1,
                    "employee_id" => $employee_id,
                    "department_id" => -1
                ]);
        } else {
            $assignments = $query->with(["client", "client.profile", "employee", "employee.profile", "department"])->paginate(100);

            return view('dashboard.invoices.employeesPendingDetailIndex')
                ->with([
                    "departments" => null,
                    "employees" => null,
                    "clients" => null,
                    "assignments" => $assignments,
                    "year" => $year,
                    "month" => $month,
                    "client_id" => -1,
                    "employee_id" => $employee_id,
                    "department_id" => -1
                ]);
        }
    }


    public function viewInvoicePDF($language, $employee_id)
    {

        try {
            $assignments = Assignment::where("type", "INVOICE")
                ->where("employee_id", $employee_id)
                ->with(["department", "employee.profile", "employee.financial", "employee.address", "client.profile", "client.address"])
                ->get();

            if (!$assignments || $assignments == null || empty($assignments)) {
                abort(404);
            }

            return view('dashboard.invoices.exportPdf')->with(["assignments" => $assignments]);
        } catch (\Exception $e) {

            dd($e);

            abort(404);
        }
    }

    public function exportInvoicePDF($language, $employee_id)
    {
        

        try {
            $assignments = Assignment::where("type", "INVOICE")
                ->where("employee_id", $employee_id)
                ->with(["department", "employee.profile", "employee.financial", "employee.address", "client.profile", "client.address"])
                ->get();

            if (!$assignments || $assignments == null || count($assignments) == 0) {
                abort(404);
            }


            // view()->share('assignments', $assignments);

            $pdf_doc = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments'));

            return $pdf_doc->stream('pdf.pdf');
        } catch (\Exception $e) {

            dd($e);

            abort(404);
        }
    }




    public function exportDepartmentforclientPDF($language,$client_id)
    {


 

    // $Beforeinvoice=Beforeinvoice::where(["id"=>$beforeinvoice_id])->get();

    //     $assignments=Invoice::where(["employee_id"=>$Beforeinvoice[0]->employee_id,'year'=>$Beforeinvoice[0]->year,'month'=>$Beforeinvoice[0]->month,'registeras'=>$Beforeinvoice[0]->registeras,'client_id'=>$Beforeinvoice[0]->client_id,'department_id'=>$Beforeinvoice[0]->department_id,'type'=>$Beforeinvoice[0]->type])->get();


 
           

      
 
 // $companylogo=Image::where(["document_title"=>"Company Logo","imageable_id"=>$assignments[0]->employee_id])->get();

 


        // return view('dashboard.invoices.exportPdfforclient', compact('client_id'));




            $pdf_doc = PDF::loadView('dashboard.invoices.exportPdfforclient', compact('client_id'));
           // $pdf_doc = PDF::loadView('dashboard.invoices.exportPdfforclient');


 

            return $pdf_doc->stream('pdf.pdf');
       
    }












    public function exportAllInvoicePDF($language, $beforeinvoice_id)
    {


 

    $Beforeinvoice=Beforeinvoice::where(["id"=>$beforeinvoice_id])->get();
 
  $assignments=Assignment::where(["times_id"=>$Beforeinvoice[0]->times_id,'status'=>'EMPLOYEE_ACCEPTED'])->get();   


       


 
            if (!$assignments || $assignments == null || count($assignments) == 0) {
                abort(404);
            }

            // view()->share('assignments', $assignments);

 
 $companylogo=Image::where(["document_title"=>"Company Logo","imageable_id"=>$assignments[0]->employee_id])->get();

 


       // return view('dashboard.invoices.exportPdf' ,compact('assignments','companylogo','Beforeinvoice'));




            $pdf_doc = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments','Beforeinvoice','companylogo'));


// return PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('reports.invoiceSell')->stream();

  $invn=0;
  if($Beforeinvoice[0]->invoicenumber!=null){ $invn=$Beforeinvoice[0]->invoicenumber;} else { $invn=$Beforeinvoice[0]->id; }  

            return $pdf_doc->stream($invn.'.pdf');
       
    }

 


 
}
