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
class AssginmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function creditnote($language,$assignments_id)
    {


        if (isset($_GET['delete']))
         {
           
            $Credit_note=Credit_note::find((int)$_GET['credit_id']);

            if ($Credit_note->credits_id==0)
            {
                
             Credit_note::where(["id"=>$_GET['credit_id']])->delete();
             return redirect()->back()->with('message', "Credit has deleted");

            }
            else
            {
                return redirect()->back()->with('message', "You Cant Delete This Credit.");
            }

         }



      $Credit_note =  Credit_note::where("assignments_id",$assignments_id)->get();

                $Assignment =  Assignment::where("id",$assignments_id)->get();
      $Beforeinvoice =  Beforeinvoice::where("times_id",$Assignment[0]->times_id)->get();
      
      return view('dashboard.assignments.creditnotes')->with(['Credit_note'=>$Credit_note,'assignments_id'=>$assignments_id,'Beforeinvoice_id'=>$Beforeinvoice[0]->id]);
  }


  public function createcreditnote($language,$assignments_id)
  {
          $Assignment =  Assignment::where("id",$assignments_id)->get();
      $Beforeinvoice =  Beforeinvoice::where("times_id",$Assignment[0]->times_id)->get();

       return view('dashboard.assignments.cratecreditnote')->with(['assignments_id'=>$assignments_id,'Beforeinvoice_id'=>$Beforeinvoice[0]->id]);
  }


  public function creditnotestore(Request $request,$language)
  {


      $Assignment=Assignment::find($request->assignments_id);

      $time_from =  strtotime($Assignment->start_date.$request->time_from);
      $time_to =  strtotime($Assignment->end_date.$request->time_to);

 

      Credit_note::create([
        'time_from' =>$time_from,
        'time_to' =>$time_to,
        'assignments_id' =>$request->assignments_id,
        'break' =>$request->break,
        'sleepshift' =>$request->sleep_shift,
        'start_date' =>$Assignment->start_date,
        'end_date' =>$Assignment->end_date,
        'admin_id' =>Auth::user()->id,
        'times_id' =>$Assignment->times_id,
    ]);


 



      return redirect()->back()->with('message', "Credit Note Inserted.");

  }


  public function det($language,$id)
  {


    if (isset($_GET['path'])) 
    {

        $Times=Times::find($_GET['path']);


        $timesstatus="";

        if ($Times->status=="PENDING" or $Times->status=="EMPLOYEE_ACCEPTED") 
        {
            $timesstatus="pending";
        }

        if ($Times->status=="CLIENT_CANCELED") 
        {
            $timesstatus="CLIENT_CANCELED";
        }


        if ($Times->status=="CONFIRMED") 
        {
            $timesstatus="CONFIRMED";
        }

        if ($Times->status=="INVOICE_SENT") 
        {
            $timesstatus="CONFIRMED";
        }


        Assignment::where(["employee_id"=>$Times->employee_id,'year'=>$Times->year,'month'=>$Times->month,'registeras'=>$Times->registeras,'client_id'=>$Times->client_id,'department_id'=>$Times->department_id])->update([
           'times_id' =>$_GET['path'],
           'times_status' =>$timesstatus,
       ]);



        Beforeinvoice::where(["employee_id"=>$Times->employee_id,'year'=>$Times->year,'month'=>$Times->month,'registeras'=>$Times->registeras,'client_id'=>$Times->client_id,'department_id'=>$Times->department_id])->update([
         'times_id' =>$_GET['path'],
     ]);




     //    Assignment::where([
     //    "id"=>$times_id
     // ])->update([
     //       'times_id' =>$id,
     //       'times_status' =>"CONFIRMED",
     //    ]);
        echo "ok";
        exit;
    }


    $assignments=Assignment::find($id);

    $suggestionassignments = Suggestionassignments::where("user_id",Auth::user()->id)->get();

    return view('dashboard.assignments.det')->with(["assignment" => $assignments,'suggestionassignments'=>$suggestionassignments]);
}




public function sendinvoice($language,$times_id)
{

    if (!Auth::user()->user_type=='EMPLOYEE')
    {
        die('2');
    }

    $Times=Times::where("id",$times_id)->get();

    $times_idforbe=$times_id;



    $image = Image::where(["imageable_id"=>$Times[0]->employee_id,'document_title'=>'Company Logo'])->get();
    $employeeprofiles = Profile::where(["user_id"=>$Times[0]->employee_id])->get();

    $employeeuser=User::where("id",$Times[0]->employee_id)->get();
    $employeeaddress=Address::where(["addressable_id"=>$Times[0]->employee_id,'addressable_type'=>'App\Models\User'])->get();
    $employeefinancial= Financial::where("user_id",$Times[0]->employee_id)->get();


    $employeedepartment= Department::where("id",$Times[0]->department_id)->get();


    if ($Times[0]->status!='CONFIRMED') 
    {
        abort(404);
    }




    if ($Times[0]->registeras=='healthcare')
    {


     $Beforeinvoice=Beforeinvoice::where(["times_id"=>$times_id,'type' =>"clientpaytoemploee"])->get();

     if ($Beforeinvoice->isEmpty())
     {

        Beforeinvoice::create([
            'client_id' =>$Times[0]->client_id,
            'department_id' =>$Times[0]->department_id,
            'employee_id' =>$Times[0]->employee_id,
            'registeras' =>$Times[0]->registeras,
            'year' =>$Times[0]->year,
            'month' =>$Times[0]->month,
            'type' =>"clientpaytoemploee",
            'status' =>'INVOICE_SENT',
            'times_id' =>$times_id,
            'logo' =>@$image[0]->url,
            'employeedeflogo' =>"http://immer-kauf.de/defin.jpeg",
            'company_name' =>@$employeeprofiles[0]->company_name,
            'employee_name' =>@$employeeprofiles[0]->first_name." ".$employeeprofiles[0]->last_name,
            'employee_email' =>@$employeeuser[0]->email,
            'employee_addr' =>@$employeeaddress[0]->address." ".@$employeeaddress[0]->home_number,
            'employee_exteraaddr' =>@$employeeaddress[0]->address_extra,
            'employee_city' =>@$employeeaddress[0]->city,
            'employee_postalcode' =>@$employeeaddress[0]->postcode,
            'employee_phone' =>@$employeeprofiles[0]->phone,
            'kvk_number' =>@$employeeprofiles[0]->kvk_number,
            'btw_number' =>@$employeeprofiles[0]->btw_number,
            'iban_number' =>@$employeefinancial[0]->iban_number,
            'iban_holder' =>@$employeefinancial[0]->iban_holder,
            'department_title' =>@$employeedepartment[0]->title,
            'department_costsection' =>@$employeedepartment[0]->cost,
            'invoicenumber' =>$Times[0]->invoicenumber,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);

    }

    $Beforeinvoice=Beforeinvoice::where(["times_id"=>$times_id,'type' =>"clientpaytozpc",])->get();

    if ($Beforeinvoice->isEmpty())
    {
        Beforeinvoice::create([
            'client_id' =>$Times[0]->client_id,
            'department_id' =>$Times[0]->department_id,
            'employee_id' =>$Times[0]->employee_id,
            'registeras' =>$Times[0]->registeras,
            'year' =>$Times[0]->year,
            'month' =>$Times[0]->month,
            'type' =>"clientpaytozpc",
            'status' =>'INVOICE_SENT',
            'logo' =>@$image[0]->url,
            'employeedeflogo' =>"http://immer-kauf.de/defin.jpeg",
            'company_name' =>@$employeeprofiles[0]->company_name,
            'employee_name' =>@$employeeprofiles[0]->first_name." ".$employeeprofiles[0]->last_name,
            'employee_email' =>@$employeeuser[0]->email,
            'employee_addr' =>@$employeeaddress[0]->address." ".@$employeeaddress[0]->home_number,
            'employee_exteraaddr' =>@$employeeaddress[0]->address_extra,
            'employee_city' =>@$employeeaddress[0]->city,
            'employee_postalcode' =>@$employeeaddress[0]->postcode,
            'employee_phone' =>@$employeeprofiles[0]->phone,
            'kvk_number' =>@$employeeprofiles[0]->kvk_number,
            'btw_number' =>@$employeeprofiles[0]->btw_number,
            'iban_number' =>@$employeefinancial[0]->iban_number,
            'iban_holder' =>@$employeefinancial[0]->iban_holder,
            'department_title' =>@$employeedepartment[0]->title,
            'department_costsection' =>@$employeedepartment[0]->cost,
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
            'times_id' =>$times_id,
        ]);
    }

}
else
{



 $Beforeinvoice=Beforeinvoice::where(["times_id"=>$times_id,'type' =>"zpcpaytoemploee",])->get();



 if ($Beforeinvoice->isEmpty())
 {
    Beforeinvoice::create([
        'client_id' =>$Times[0]->client_id,
        'department_id' =>$Times[0]->department_id,
        'employee_id' =>$Times[0]->employee_id,
        'registeras' =>$Times[0]->registeras,
        'year' =>$Times[0]->year,
        'month' =>$Times[0]->month,
        'type' =>"zpcpaytoemploee",
        'invoicenumber' =>$Times[0]->invoicenumber,
        'status' =>'INVOICE_SENT',
        'logo' =>@$image[0]->url,
        'employeedeflogo' =>"http://immer-kauf.de/defin.jpeg",
        'company_name' =>@$employeeprofiles[0]->company_name,
        'employee_name' =>@$employeeprofiles[0]->first_name." ".$employeeprofiles[0]->last_name,
        'employee_email' =>@$employeeuser[0]->email,
        'employee_addr' =>@$employeeaddress[0]->address." ".@$employeeaddress[0]->home_number,
        'employee_exteraaddr' =>@$employeeaddress[0]->address_extra,
        'employee_city' =>@$employeeaddress[0]->city,
        'employee_postalcode' =>@$employeeaddress[0]->postcode,
        'employee_phone' =>@$employeeprofiles[0]->phone,
        'kvk_number' =>@$employeeprofiles[0]->kvk_number,
        'btw_number' =>@$employeeprofiles[0]->btw_number,
        'iban_number' =>@$employeefinancial[0]->iban_number,
        'iban_holder' =>@$employeefinancial[0]->iban_holder,
        'department_title' =>@$employeedepartment[0]->title,
        'department_costsection' =>@$employeedepartment[0]->cost,
        'created_at' =>Carbon::now(),
        'updated_at' =>Carbon::now(),
        'times_id' =>$times_id,

    ]);
}


$Beforeinvoice=Beforeinvoice::where(["times_id"=>$times_id,'type' =>"clientpaytozpc",])->get();



if ($Beforeinvoice->isEmpty())
{

    Beforeinvoice::create([
        'client_id' =>$Times[0]->client_id,
        'department_id' =>$Times[0]->department_id,
        'employee_id' =>$Times[0]->employee_id,
        'registeras' =>$Times[0]->registeras,
        'year' =>$Times[0]->year,
        'month' =>$Times[0]->month,
        'type' =>"clientpaytozpc",
        'status' =>'INVOICE_SENT',
        'logo' =>@$image[0]->url,
        'employeedeflogo' =>"http://immer-kauf.de/defin.jpeg",
        'company_name' =>@$employeeprofiles[0]->company_name,
        'employee_name' =>@$employeeprofiles[0]->first_name." ".$employeeprofiles[0]->last_name,
        'employee_email' =>@$employeeuser[0]->email,
        'employee_addr' =>@$employeeaddress[0]->address." ".@$employeeaddress[0]->home_number,
        'employee_exteraaddr' =>@$employeeaddress[0]->address_extra,
        'employee_city' =>@$employeeaddress[0]->city,
        'employee_postalcode' =>@$employeeaddress[0]->postcode,
        'employee_phone' =>@$employeeprofiles[0]->phone,
        'kvk_number' =>@$employeeprofiles[0]->kvk_number,
        'btw_number' =>@$employeeprofiles[0]->btw_number,
        'iban_number' =>@$employeefinancial[0]->iban_number,
        'iban_holder' =>@$employeefinancial[0]->iban_holder,
        'department_title' =>@$employeedepartment[0]->title,
        'department_costsection' =>@$employeedepartment[0]->cost,
        'created_at' =>Carbon::now(),
        'updated_at' =>Carbon::now(),
        'times_id' =>$times_id,
    ]);
}


}

Times::where([
    "id"=>$times_id
])->update([
 'status' =>'INVOICE_SENT',
]);



if ($Times[0]->registeras=="healthcare") 
{
    $Beforeinvoice=$Times;

    $invoicename1="";
    $invoicename2="";

    $in1=Beforeinvoice::where(["times_id"=>$times_id,'type'=>"clientpaytoemploee"])->get();


    $assignments=Assignment::where(["times_id"=>$times_id,'status'=>'EMPLOYEE_ACCEPTED'])->get();


    $Beforeinvoice=$in1;

    $invoicename1="Factuur zzp-er";


    $companylogo=Image::where(["document_title"=>"Company Logo","imageable_id"=>$assignments[0]->employee_id])->get();



    $pdf_doc = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments','Beforeinvoice','companylogo'));


    Storage::put('public/pdf/'.$invoicename1.'.pdf', $pdf_doc->output());


    $in2=Beforeinvoice::where(["times_id"=>$times_id,'type'=>"clientpaytozpc"])->get();

    $Beforeinvoice=$in2;
    $assignments=Assignment::where(["times_id"=>$times_id,'status'=>'EMPLOYEE_ACCEPTED'])->get();


    $invoicename2="Fee factuur";


    $companylogo=Image::where(["document_title"=>"Company Logo","imageable_id"=>$assignments[0]->employee_id])->get();


    $pdf_doc1 = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments','Beforeinvoice','companylogo'));

    Storage::put('public/pdf/'.$invoicename2.'.pdf', $pdf_doc1->output());







    $scdep=Joindepartment::where(['client_id'=>$Beforeinvoice[0]->client_id,'registeras'=>$Beforeinvoice[0]->registeras,'department_id'=>$Beforeinvoice[0]->department_id])
    ->get();

    $tempdep = Department::where("id",$Beforeinvoice[0]->department_id)->get();



    if ($scdep->isEmpty())
    {


       

        if ($Beforeinvoice[0]->client_id==63) 
        {
            $clientemail="crediteurenadministratie@mondriaan.eu";
        }
        else
        if ($Beforeinvoice[0]->client_id==170) 
        {
        $clientemail="eadcrediteurenadm@koraal.nl";
        }
        else
        if ($Beforeinvoice[0]->client_id==156) 
        {
            $clientemail="facturen@pergamijn.org";
        }
        else
        if ($Beforeinvoice[0]->client_id==162) 
        {
        $clientemail="facturen@daelzicht.nl";
        }
        else
        if ($Beforeinvoice[0]->client_id==58) 
        {
            $clientemail="zorgtag001@gmail.com";
        }
        else
        {
          $clientemaill= User::where(["id"=>$Beforeinvoice[0]->client_id])->get();
          $clientemail=$clientemaill[0]->email;
        }




            $details = [
                'title' => "Beste financiële administratie,",
                'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "Ook is de factuur voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
            \Mail::send((new \App\Mail\WelcomeEmail($details))
                ->to($clientemail)->subject("Factuur ZZP-er + factuur Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


               $Rh = new Rh;

               $function="Factuur ZZP-er + factuur Fee ZPC Afdeling:";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_ids=1;
               $agreement_id=1;


               $Rh::emaillog($clientemail,$function,$description,$assignments_id,$invoice_id,$times_ids,$agreement_id);





        // $allfinancials = User::where(["client_id"=>$Beforeinvoice[0]->client_id,'user_type'=>'FINANCIAL'])->get();




        // foreach ($allfinancials as $row)
        // {

        //     $details = [
        //         'title' => "Beste financiële administratie,",
        //         'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
        //         'body2' => "Ook is de factuur voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
        //         'body3' => "Met vriendelijke groet,",
        //         'body4' => "",
        //     ];
        //     \Mail::send((new \App\Mail\WelcomeEmail($details))
        //         ->to($row->email)->subject("Factuur ZZP-er + factuur Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


        //        $Rh = new Rh;

        //        $function="Factuur ZZP-er + factuur Fee ZPC Afdeling:";
        //        $description=json_encode(@$details);
        //        $assignments_id=1;
        //        $invoice_id=@$Beforeinvoice[0]->id;
        //        $times_ids=1;
        //        $agreement_id=1;


        //        $Rh::emaillog($row->email,$function,$description,$assignments_id,$invoice_id,$times_ids,$agreement_id);




        // }

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
                        'body2' => "Ook is de factuur voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
                        'body3' => "Met vriendelijke groet,",
                        'body4' => "",
                    ];
                    \Mail::send((new \App\Mail\WelcomeEmail($details))
                        ->to("crediteurenadministratie@mondriaan.eu")->subject("Factuur ZZP-er + factuur Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


               $Rh = new Rh;

               $function="Factuur ZZP-er + factuur Fee ZPC Afdeling:";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog("crediteurenadministratie@mondriaan.eu",$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);





                }


 
                $details = [
                    'title' => "Beste financiële administratie,",
                    'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
                    'body2' => "Ook is de factuur voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
                    'body3' => "Met vriendelijke groet,",
                    'body4' => "",
                ];
                \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to($user[0]->email)->subject("Factuur ZZP-er + factuur Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));

               $Rh = new Rh;

               $function="Factuur ZZP-er + factuur Fee ZPC Afdeling: ";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog($user[0]->email,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);




            }


        }


if ($nistke==0) 
{

        if ($Beforeinvoice[0]->client_id==63) 
        {
            $clientemail="crediteurenadministratie@mondriaan.eu";
        }
        else
        if ($Beforeinvoice[0]->client_id==170) 
        {
        $clientemail="eadcrediteurenadm@koraal.nl";
        }
        else
        if ($Beforeinvoice[0]->client_id==156) 
        {
            $clientemail="facturen@pergamijn.org";
        }
        else
        if ($Beforeinvoice[0]->client_id==162) 
        {
        $clientemail="facturen@daelzicht.nl";
        }
        else
        if ($Beforeinvoice[0]->client_id==58) 
        {
            $clientemail="zorgtag001@gmail.com";
        }
        else
        {
          $clientemaill= User::where(["id"=>$Beforeinvoice[0]->client_id])->get();
          $clientemail=$clientemaill[0]->email;
        }


            $details = [
                'title' => "Beste financiële administratie,",
                'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "Ook is de factuur voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
            \Mail::send((new \App\Mail\WelcomeEmail($details))
                ->to($clientemail)->subject("Factuur ZZP-er + factuur Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


               $Rh = new Rh;

               $function="Factuur ZZP-er + factuur Fee ZPC Afdeling:";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_ids=1;
               $agreement_id=1;


               $Rh::emaillog($clientemail,$function,$description,$assignments_id,$invoice_id,$times_ids,$agreement_id);



    
        //     $allfinancials = User::where(["client_id"=>$Beforeinvoice[0]->client_id,'user_type'=>'FINANCIAL'])->get();




        // foreach ($allfinancials as $row)
        // {

        //     $details = [
        //         'title' => "Beste financiële administratie,",
        //         'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
        //         'body2' => "Ook is de factuur voor de Fee van ZPC toegevoegd. Deze fee is gekoppeld aan de gewerkte uren van de ZZP-er.",
        //         'body3' => "Met vriendelijke groet,",
        //         'body4' => "",
        //     ];
        //     \Mail::send((new \App\Mail\WelcomeEmail($details))
        //         ->to($row->email)->subject("Factuur ZZP-er + factuur Fee ZPC Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf'))->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


        //        $Rh = new Rh;

        //        $function="Factuur ZZP-er + factuur Fee ZPC Afdeling: ";
        //        $description=json_encode(@$details);
        //        $assignments_id=1;
        //        $invoice_id=@$Beforeinvoice[0]->id;
        //        $times_idss=1;
        //        $agreement_id=1;


        //        $Rh::emaillog($row->email,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);




        // }
}





    }


    File::Delete(storage_path('app/public/pdf/'.$invoicename1.'.pdf'));
    File::Delete(storage_path('app/public/pdf/'.$invoicename2.'.pdf'));

          ///  return $pdf_doc->stream('pdf.pdf');



    $invoicenumberrrr=Beforeinvoice::where(["times_id"=>$times_idforbe])->get();
 

 $Rh=new Rh;
//send invoice

$user_id=Auth::user()->id;
$page="Times";
$function="sending invoice";
$description="wehn the invoice made and sent";
$assignments_id=1;
$invoice_id=@$invoicenumberrrr[0]->id;
$times_idss=@$times_id;
$agreement_id=1;


$Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);




$Rh::updatetotalhoursass($times_id,$invoice_id);












    return redirect()->back()->with('message', "Invoice Sent");


}
else
{
//if its not healthcare

    $Beforeinvoice=$Times;

    $invoicename1="";
    $invoicename2="";

    $in1=Beforeinvoice::where(["times_id"=>$times_id,'type'=>"clientpaytozpc"])->get();


    $assignments=Assignment::where(["times_id"=>$times_id,'status'=>'EMPLOYEE_ACCEPTED'])->get();


    $Beforeinvoice=$in1;

    $invoicename1="Factuur Zorgbeveiliging zzp'er";


    $companylogo=Image::where(["document_title"=>"Company Logo","imageable_id"=>$assignments[0]->employee_id])->get();



    $pdf_doc = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments','Beforeinvoice','companylogo'));


    Storage::put('public/pdf/'.$invoicename1.'.pdf', $pdf_doc->output());


    $in2=Beforeinvoice::where(["times_id"=>$times_id,'type'=>"zpcpaytoemploee"])->get();

    $Beforeinvoice=$in2;
    $assignments=Assignment::where(["times_id"=>$times_id,'status'=>'EMPLOYEE_ACCEPTED'])->get();


    $invoicename2="Factuur Zorgbeveiliging";


    $companylogo=Image::where(["document_title"=>"Company Logo","imageable_id"=>$assignments[0]->employee_id])->get();


    $pdf_doc1 = PDF::loadView('dashboard.invoices.exportPdf', compact('assignments','Beforeinvoice','companylogo'));

    Storage::put('public/pdf/'.$invoicename2.'.pdf', $pdf_doc1->output());





 $tempdep = Department::where("id",$Beforeinvoice[0]->department_id)->get();

    $scdep=Joindepartment::where(['client_id'=>$Beforeinvoice[0]->client_id,'registeras'=>$Beforeinvoice[0]->registeras,'department_id'=>$Beforeinvoice[0]->department_id])
    ->get();

   



    if ($scdep->isEmpty())
    {




        if ($Beforeinvoice[0]->client_id==63) 
        {
            $clientemail="crediteurenadministratie@mondriaan.eu";
        }
        else
        if ($Beforeinvoice[0]->client_id==170) 
        {
        $clientemail="eadcrediteurenadm@koraal.nl";
        }
        else
        if ($Beforeinvoice[0]->client_id==156) 
        {
            $clientemail="facturen@pergamijn.org";
        }
        else
        if ($Beforeinvoice[0]->client_id==162) 
        {
        $clientemail="facturen@daelzicht.nl";
        }
        else
        if ($Beforeinvoice[0]->client_id==58) 
        {
            $clientemail="zorgtag001@gmail.com";
        }
        else
        {
          $clientemaill= User::where(["id"=>$Beforeinvoice[0]->client_id])->get();
          $clientemail=$clientemaill[0]->email;
        }




            $details = [
                'title' => "Beste financiële administratie,",
                'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
         


    \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to($clientemail)->subject("Factuur Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf')));


               $Rh = new Rh;

               $function="Factuur Zorgbeveiliging zzp'er Afdeling: ";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog($clientemail,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);




    //     $allfinancials = User::where(["client_id"=>$Beforeinvoice[0]->client_id,'user_type'=>'FINANCIAL'])->get();




    //     foreach ($allfinancials as $row)
    //     {

    //         $details = [
    //             'title' => "Beste financiële administratie,",
    //             'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
    //             'body2' => "",
    //             'body3' => "Met vriendelijke groet,",
    //             'body4' => "",
    //         ];
         


    // \Mail::send((new \App\Mail\WelcomeEmail($details))
    //                 ->to($row->email)->subject("Factuur Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf')));


    //            $Rh = new Rh;

    //            $function="Factuur Zorgbeveiliging zzp'er Afdeling: ";
    //            $description=json_encode(@$details);
    //            $assignments_id=1;
    //            $invoice_id=@$Beforeinvoice[0]->id;
    //            $times_idss=1;
    //            $agreement_id=1;


    //            $Rh::emaillog($row->email,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);






    //     }







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
                    ->to('crediteurenadministratie@mondriaan.eu')->subject("Factuur Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf')));



               $Rh = new Rh;

               $function="Factuur Zorgbeveiliging zzp'er Afdeling:";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog('crediteurenadministratie@mondriaan.eu',$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);





                }


 
                $details = [
                    'title' => "Beste financiële administratie,",
                    'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
                    'body2' => "",
                    'body3' => "Met vriendelijke groet,",
                    'body4' => "",
                ];
            

    \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to($user[0]->email)->subject("Factuur Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf')));


               $Rh = new Rh;

               $function="Factuur Zorgbeveiliging zzp'er Afdeling: ";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog($user[0]->email,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);



            }


        }


if ($nistke==0) 
{
   

        if ($Beforeinvoice[0]->client_id==63) 
        {
            $clientemail="crediteurenadministratie@mondriaan.eu";
        }
        else
        if ($Beforeinvoice[0]->client_id==170) 
        {
        $clientemail="eadcrediteurenadm@koraal.nl";
        }
        else
        if ($Beforeinvoice[0]->client_id==156) 
        {
            $clientemail="facturen@pergamijn.org";
        }
        else
        if ($Beforeinvoice[0]->client_id==162) 
        {
        $clientemail="facturen@daelzicht.nl";
        }
        else
        if ($Beforeinvoice[0]->client_id==58) 
        {
            $clientemail="zorgtag001@gmail.com";
        }
        else
        {
          $clientemaill= User::where(["id"=>$Beforeinvoice[0]->client_id])->get();
          $clientemail=$clientemaill[0]->email;
        }


            $details = [
                'title' => "Beste financiële administratie,",
                'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
         


    \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to($clientemail)->subject("Factuur Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf')));


                $Rh = new Rh;

               $function="Factuur Zorgbeveiliging zzp'er Afdeling: ";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog($clientemail,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);






    //    $allfinancials = User::where(["client_id"=>$Beforeinvoice[0]->client_id,'user_type'=>'FINANCIAL'])->get();




    //     foreach ($allfinancials as $row)
    //     {

    //         $details = [
    //             'title' => "Beste financiële administratie,",
    //             'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
    //             'body2' => "",
    //             'body3' => "Met vriendelijke groet,",
    //             'body4' => "",
    //         ];
         


    // \Mail::send((new \App\Mail\WelcomeEmail($details))
    //                 ->to($row->email)->subject("Factuur Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename1.'.pdf')));


    //             $Rh = new Rh;

    //            $function="Factuur Zorgbeveiliging zzp'er Afdeling: ";
    //            $description=json_encode(@$details);
    //            $assignments_id=1;
    //            $invoice_id=@$Beforeinvoice[0]->id;
    //            $times_idss=1;
    //            $agreement_id=1;


    //            $Rh::emaillog($row->email,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);




    //     }




   
}









    }





    $invoicenumberrrr=Beforeinvoice::where(["times_id"=>$times_idforbe])->get();
 

 $Rh=new Rh;
//send invoice

$user_id=Auth::user()->id;
$page="Times";
$function="sending invoice";
$description="wehn the invoice made and sent";
$assignments_id=1;
$invoice_id=$invoicenumberrrr[0]->id;
$times_idss=$times_id;
$agreement_id=1;


$Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);




            $details = [
                'title' => "Beste,",
                'body1' => "In de bijlage is een factuur toegevoegd voor de gewerkte uren van de ZZP-er voor de afgelopen maand.",
                'body2' => "",
                'body3' => "Met vriendelijke groet,",
                'body4' => "",
            ];
          

    \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to('info@zorgpuntconnect.nl')->subject("Factuur Zorgbeveiliging zzp'er Afdeling: ".$tempdep[0]->title)->attach(storage_path('app/public/pdf/'.$invoicename2.'.pdf')));


                    $Rh = new Rh;

               $function="Factuur Zorgbeveiliging zzp'er Afdeling: ";
               $description=json_encode(@$details);
               $assignments_id=1;
               $invoice_id=@$Beforeinvoice[0]->id;
               $times_idss=1;
               $agreement_id=1;


               $Rh::emaillog('info@zorgpuntconnect.nl',$function,$description,$assignments_id,$invoice_id,$times_idss,$agreement_id);



$Rh::updatetotalhoursass($times_id,$invoice_id);



    File::Delete(storage_path('app/public/pdf/'.$invoicename1.'.pdf'));
    File::Delete(storage_path('app/public/pdf/'.$invoicename2.'.pdf'));

          ///  return $pdf_doc->stream('pdf.pdf');

    return redirect()->back()->with('message', "Invoice Sent");




//if its not healthcare
}













return redirect()->back()->with('message', "Invoice Sent");



}






 









 




public function setfreetoass($language,$id)
{

  $suggestionassignments = Suggestionassignments::where("id",$id)->get();
  $freelancer_id=$suggestionassignments[0]->user_id;
  $assignments_id=$suggestionassignments[0]->assignments_id;


  $assignmerntdata = Assignment::where("id",$assignments_id)->get();





        $Rh=new Rh;


        $rrrr= $Rh::duplicatetime($assignmerntdata[0]->time_from,$assignmerntdata[0]->time_to,$assignmerntdata[0]->start_date,$freelancer_id,$assignmerntdata[0]->department_id); 



 

        if ($rrrr==1) 
        {

            return redirect("/" . $language . '/assignments.employee.suggestions/'.$assignments_id)->with('message', "This time is already reserved for the freelancer");
            return redirect()->back()->with('message', "This time is already reserved for the freelancer");
        }

 





  $agreement_id=$assignmerntdata[0]->agreement_id;
  $client_id=$assignmerntdata[0]->client_id;
  $registeras=$assignmerntdata[0]->registeras;



  $joinclient = Joinclient::where(["user_id"=>$freelancer_id,"client_id"=>$client_id,'registeras'=>$registeras])->get();

  if ($joinclient->isEmpty())
  {
    // $validated['payrate'] ='';
    // $validated['client_payrate'] ='';
  }
  else
  {
    $payrate =$joinclient[0]->payrate;
    $client_payrate =$joinclient[0]->client_payrate;
}


// echo $agreement_id;exit;

$preaggrement = Preaggrement::where("id",$agreement_id)->get();

$emploee = User::where("id",$freelancer_id)->get();
$profile = Profile::where("user_id",$freelancer_id)->get();
$addresses = Address::where(["addressable_id"=>$freelancer_id,"addressable_type"=>"App\Models\User"])->get();
$financials = Financial::where("user_id",$freelancer_id)->get();


$clientprofile = Profile::where("user_id",$client_id)->get();  
$clientaddresses = Address::where("addressable_id",$client_id)->get();

$top="";
if ($preaggrement->isEmpty() or $freelancer_id==1 )
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


    $text3=$profile[0]->company_name.", ".$addresses[0]->address." ".$addresses[0]->postcode." ".$addresses[0]->city.", met KVK-nummer  ".$profile[0]->kvk_number."  en BTW-nummer
    ".$profile[0]->btw_number." hierbij rechtsgeldig vertegenwoordigd door haar directeur ".$profile[0]->first_name." ".$profile[0]->last_name.", hierna te noemen Opdrachtnemer
    ";




    $date = $assignmerntdata[0]->start_date;
    $startdate=date("d-m-Y",strtotime($date));

    $date = $assignmerntdata[0]->end_date;
    $enddate=date("d-m-Y",strtotime($date));


    if ($assignmerntdata[0]->registeras=='healthcare') 
    {
     $payratee='   <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Uurtarief: '.number_format($assignmerntdata[0]->payrate,2).' € </span></span></span></span></p>';
 }
 else
 {
    $payratee="";
}

$centertext='
<br>
<p><span style=\"font-size:8pt;margin-top:30px;"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Organisatie: '.$clientprofile[0]->company_name.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Locatie: '.$clientaddresses[0]->address." ".$clientaddresses[0]->address_extra." ".$clientaddresses[0]->postcode." ".$clientaddresses[0]->city.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Soort opdracht: '.$type.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Begin: '.$startdate." ".date('H:i',$assignmerntdata[0]->time_from).'</span></span></span></span></p>


<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Einde: '.$enddate." ".date('H:i', $assignmerntdata[0]->time_to).'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Aantal uren: '.Rh::getduration($assignmerntdata[0]->time_from,$assignmerntdata[0]->time_to,$assignmerntdata[0]->start_date,$assignmerntdata[0]->end_date).'</span></span></span></span></p>

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


$clientprofile = Profile::where("user_id",$assignmerntdata[0]->client_id)->get();




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

<div style="width:50%;float: right;">
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">Opdrachtnemer</div><br>
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.$profile[0]->company_name.'</div><br>
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.$profile[0]->first_name." ".$profile[0]->last_name.'</div><br>
</div>

</div>
';

$agreementtemp=$clientsignbox;
} 



Assignment::where(['id'=>$assignments_id])
->update([
 'agreementtext' =>$top,
 'employee_id' =>$freelancer_id,
 'status' =>'EMPLOYEE_ACCEPTED',
 'payrate' =>$payrate,
 'client_payrate' =>$client_payrate,
 'employee_id1'=>1,
 'employee_id2'=>1,
 'employee_id3'=>1,
 'employee_id4'=>1,
 'employee_id5'=>1,
]);




$oldassignment = Assignment::findOrFail($assignments_id);

$assignmenttr =$oldassignment;



 


    $timmmmmm=0;

    $checkhas=0;
    $times = Times::where(["registeras"=>$oldassignment->registeras,'client_id'=>$oldassignment->client_id,'department_id'=>$oldassignment->department_id,'year'=>$oldassignment->year,'month'=>$oldassignment->month,'employee_id'=>$oldassignment->employee_id,['status', '!=', 'INVOICE_SENT'],])->get();

    foreach ($times as $row) 
    {

        $checkhas=1;

        Times::where(['id'=>$row->id])
        ->update([
            'status' =>'PENDING',
        ]);

        $assignmenttr->times_id=$row->id;
        $assignmenttr->save();

        $timmmmmm=$row->id;
    }



    if ($checkhas==0)
    {


        Times::create([
            'registeras' =>$assignmenttr->registeras,
            'client_id' =>$assignmenttr->client_id,
            'department_id' =>$assignmenttr->department_id,
            'employee_id' =>$assignmenttr->employee_id,
            'year'=>$assignmenttr->year,
            'month'=>$assignmenttr->month,
            'status' =>'PENDING',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);



        $lasttimes= DB::getPdo()->lastInsertId();

        $timmmmmm=$lasttimes;

        Times::where(['id'=>$lasttimes])
        ->update([
            'registeras' =>$assignmenttr->registeras,
            'employee_id' =>$assignmenttr->employee_id,
            'year'=>$assignmenttr->year,
            'month'=>$assignmenttr->month,
            'status' =>'PENDING',
            'type' =>'ASSIGNMENT',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);


        $assignmenttr->times_id=$lasttimes;
        $assignmenttr->save();




    }



    $Rh=new Rh;

//accept assignment 
    $user_id=Auth::user()->id;
    $page="assignments";
    $function="accept";
    $description="When the accept-button is clicked";
    $assignments_id=$oldassignment->id;
    $invoice_id=1;
    $times_id=$timmmmmm;
    $agreement_id=1;


    $Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);







 







$getemailsca =  User::where(["id"=>$freelancer_id])->get();

$details = [
    'title' => "Beste ZPC-er,",
    'body1' => "Aanmelding geaccepteerd!",
    'body2' => "Log in om dit bekijken",
    'body3' => "Met vriendelijke groet,",
    'body4' => "Team ZPC",
];
\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to($getemailsca[0]->email)->subject("Aanmelding geaccepteerd! Dienstnr: ".$assignments_id));



                $Rh = new Rh;

               $function="Aanmelding geaccepteerd! Dienstnr: - accept assignment";
               $description=json_encode(@$details);
               $assignments_id=@$oldassignment->id;
               $invoice_id=1;
               $times_id=@$timmmmmm;
               $agreement_id=1;


               $Rh::emaillog($getemailsca[0]->email,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);




return redirect("/" . $language . '/assignments/asc/-1/-1/-1/-1/-1/-1/-1/-1?open=-1');

return back();
}



public function suggestions($language,$id)
{
 if (Auth::user()->user_type!="EMPLOYEE"  ) 
 {

       // $suggestionassignments = Suggestionassignments::where("id",$id)->get();
    if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL" )
    {
        $suggestionassignments = DB::table('suggestionassignments')
        ->join('profiles', 'profiles.user_id', '=', 'suggestionassignments.user_id')
        ->select('suggestionassignments.*', 'profiles.first_name', 'profiles.last_name')
        ->where(['assignments_id'=>$id])
        ->paginate(1000);
    }
    else
        if (Auth::user()->user_type=="CLIENT")
        {
            $suggestionassignments = DB::table('suggestionassignments')
            ->join('profiles', 'profiles.user_id', '=', 'suggestionassignments.user_id')
            ->select('suggestionassignments.*', 'profiles.first_name', 'profiles.last_name')
            ->where(['assignments_id'=>$id])
            ->paginate(1000);
        }
        else
            if (Auth::user()->user_type=="ADMIN")
            {
              $suggestionassignments = DB::table('suggestionassignments')
              ->join('profiles', 'profiles.user_id', '=', 'suggestionassignments.user_id')
              ->select('suggestionassignments.*', 'profiles.first_name', 'profiles.last_name')
              ->where(['assignments_id'=>$id])
              ->paginate(1000);   
          }


          return view('dashboard.assignments.suggestionassignmentslist')
          ->with([
            "suggestionassignments"=>$suggestionassignments,
        ]);



      }
      else
      {
        abort(404);
    }
}



public function sendsuggestion($language,$id)
{


  $assignment = Assignment::where("id",$id)->get();


  $assignmenttr = Assignment::findOrFail($id);
  $wantaccepttime_from= $assignmenttr->time_from+60;
  $wantaccepttime_to= $assignmenttr->time_to;


  $checkass =  Assignment::where(["start_date"=>$assignmenttr->start_date,'status'=>'EMPLOYEE_ACCEPTED','employee_id'=>Auth::user()->id])->get();

  $rrrr=0;



  $nex_date = date('Y-m-d', strtotime($assignmenttr->start_date . ' +1 day'));
// $pre_date = date('Y-m-d', strtotime($assignmenttr->date_from . ' -1 day'));
  $pre_date = date('Y-m-d', strtotime($assignmenttr->start_date . ' -0 day'));

  $pre_date1 = date('Y-m-d', strtotime($assignmenttr->start_date . ' -1 day'));

  $checkass3 =  Assignment::where(["start_date"=>$nex_date,'status'=>'EMPLOYEE_ACCEPTED','employee_id'=>Auth::user()->id])->get();



  $checkass2 =  Assignment::where(["end_date"=>$pre_date,'status'=>'EMPLOYEE_ACCEPTED','employee_id'=>Auth::user()->id])->get();


 // dd($checkass3);

// echo $pre_date1;exit;
  if (isset($checkass4[0]->id))
  {

    $acceptedtime_from=$checkass4[0]->time_from+60;
    $acceptedtime_to=$checkass4[0]->time_to;


    $time_from= $checkass4[0]->time_from;
    $time_to=  $checkass3[0]->time_to;
    $alltimestamp=  $time_to - $time_from ;


    if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
    {
       $rrrr=1;
   }

   if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
   {
       $rrrr=1;
   }




   if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }

   if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }



}


if (isset($checkass3[0]->id))
{

    $acceptedtime_from=$checkass3[0]->time_from+60;
    $acceptedtime_to=$checkass3[0]->time_to;


    $time_from= $checkass3[0]->time_from;
    $time_to=  $checkass3[0]->time_to;
    $alltimestamp=  $time_to - $time_from ;


    if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
    {
       $rrrr=1;
   }

   if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
   {
       $rrrr=1;
   }




   if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }

   if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }



}





if (isset($checkass2[0]->id))
{

    $acceptedtime_from=$checkass2[0]->time_from+60;
    $acceptedtime_to=$checkass2[0]->time_to;


    $time_from= $checkass2[0]->time_from;
    $time_to=  $checkass2[0]->time_to;
    $alltimestamp=  $time_to - $time_from ;


    if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
    {
       $rrrr=1;
   }

   if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
   {
       $rrrr=1;
   }




   if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }

   if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }



}





if (isset($checkass[0]->id))
{

    $acceptedtime_from=$checkass[0]->time_from+60;
    $acceptedtime_to=$checkass[0]->time_to;


    $time_from= $checkass[0]->time_from;
    $time_to=  $checkass[0]->time_to;
    $alltimestamp=  $time_to - $time_from ;


    if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
    {
       $rrrr=1;
   }

   if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
   {
       $rrrr=1;
   }




   if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }

   if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }



}






// echo $rrrr;exit;

if ($rrrr==1) 
{
    return redirect()->back()->with('message', "You are already booked on this time period.");
}















$speedasignment=$assignment[0]->speedasignment;
$employee_id=$assignment[0]->employee_id;
$client_id=$assignment[0]->client_id;
$registeras=$assignment[0]->registeras;

if ($speedasignment==1 and $employee_id==1)
{


   $joinclient = Joinclient::where(["user_id"=>Auth::user()->id,"client_id"=>$client_id,'registeras'=>$registeras])->get();

   if ($joinclient->isEmpty())
   {
    // $validated['payrate'] ='';
    // $validated['client_payrate'] ='';
   }
   else
   {
    $payrate =$joinclient[0]->payrate;
    $client_payrate =$joinclient[0]->client_payrate;

    Assignment::where(['id'=>$id])
    ->update([
     'employee_id' =>Auth::user()->id,
     'payrate' =>$payrate,
     'client_payrate' =>$client_payrate,
 ]);


//added new
    $freelancer_id=Auth::user()->id;
    $assignments_id=$id;


    $assignmerntdata = Assignment::where("id",$assignments_id)->get();



    $agreement_id=$assignmerntdata[0]->agreement_id;
    $client_id=$assignmerntdata[0]->client_id;
    $registeras=$assignmerntdata[0]->registeras;



    $joinclient = Joinclient::where(["user_id"=>$freelancer_id,"client_id"=>$client_id,'registeras'=>$registeras])->get();

    if ($joinclient->isEmpty())
    {
    // $validated['payrate'] ='';
    // $validated['client_payrate'] ='';
    }
    else
    {
        $payrate =$joinclient[0]->payrate;
        $client_payrate =$joinclient[0]->client_payrate;
    }


// echo $agreement_id;exit;

    $preaggrement = Preaggrement::where("id",$agreement_id)->get();

    $emploee = User::where("id",$freelancer_id)->get();
    $profile = Profile::where("user_id",$freelancer_id)->get();
    $addresses = Address::where(["addressable_id"=>$freelancer_id,"addressable_type"=>"App\Models\User"])->get();
    $financials = Financial::where("user_id",$freelancer_id)->get();


    $clientprofile = Profile::where("user_id",$client_id)->get();  
    $clientaddresses = Address::where("addressable_id",$client_id)->get();

    $top="";
    if ($preaggrement->isEmpty() or $freelancer_id==1 )
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


        $text3=$profile[0]->company_name.", ".$addresses[0]->address." ".$addresses[0]->postcode." ".$addresses[0]->city.", met KVK-nummer  ".$profile[0]->kvk_number."  en BTW-nummer
        ".$profile[0]->btw_number."  hierbij rechtsgeldig vertegenwoordigd door haar directeur ".$profile[0]->first_name." ".$profile[0]->last_name.", hierna te noemen 'Opdrachtnemer'
        ";




        $date = $assignmerntdata[0]->start_date;
        $startdate=date("d-m-Y",strtotime($date));

        $date = $assignmerntdata[0]->end_date;
        $enddate=date("d-m-Y",strtotime($date));


        if ($assignmerntdata[0]->registeras=='healthcare') 
        {
         $payratee='   <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Uurtarief: '.number_format($assignmerntdata[0]->payrate,2).' € </span></span></span></span></p>';
     }
     else
     {
        $payratee="";
    }

    $centertext='
    <br>
    <p><span style=\"font-size:8pt;margin-top:30px;"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Organisatie: '.$clientprofile[0]->company_name.'</span></span></span></span></p>

    <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Locatie: '.$clientaddresses[0]->address." ".$clientaddresses[0]->address_extra." ".$clientaddresses[0]->postcode." ".$clientaddresses[0]->city.'</span></span></span></span></p>

    <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Soort opdracht: '.$type.'</span></span></span></span></p>

    <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Begin: '.$startdate." ".date('H:i',$assignmerntdata[0]->time_from).'</span></span></span></span></p>


    <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Einde: '.$enddate." ".date('H:i', $assignmerntdata[0]->time_to).'</span></span></span></span></p>

    <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Aantal uren: '.Rh::getduration($assignmerntdata[0]->time_from,$assignmerntdata[0]->time_to,$assignmerntdata[0]->start_date,$assignmerntdata[0]->end_date).'</span></span></span></span></p>

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


    $clientprofile = Profile::where("user_id",$assignmerntdata[0]->client_id)->get();




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

    <div style="width:50%;float: right;">
    <div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">Opdrachtnemer</div><br>
    <div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.$profile[0]->company_name.'</div><br>
    <div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.$profile[0]->first_name." ".$profile[0]->last_name.'</div><br>
    </div>
    
    </div>
    ';

    $agreementtemp=$clientsignbox;
} 



Assignment::where(['id'=>$assignments_id])
->update([
 'agreementtext' =>$top,
 'employee_id' =>$freelancer_id,
 'status' =>'EMPLOYEE_ACCEPTED',
 'payrate' =>$payrate,
 'client_payrate' =>$client_payrate,
]);



$assignmenttr = Assignment::findOrFail($assignments_id);

$oldassignment=$assignmenttr;

 


    $timmmmmm=0;

    $checkhas=0;
    $times = Times::where(["registeras"=>$oldassignment->registeras,'client_id'=>$oldassignment->client_id,'department_id'=>$oldassignment->department_id,'year'=>$oldassignment->year,'month'=>$oldassignment->month,'employee_id'=>$oldassignment->employee_id,['status', '!=', 'INVOICE_SENT'],])->get();

    foreach ($times as $row) 
    {

        $checkhas=1;

        Times::where(['id'=>$row->id])
        ->update([
            'status' =>'PENDING',
        ]);

        $assignmenttr->times_id=$row->id;
        $assignmenttr->save();

        $timmmmmm=$row->id;
    }



    if ($checkhas==0)
    {


        Times::create([
            'registeras' =>$assignmenttr->registeras,
            'client_id' =>$assignmenttr->client_id,
            'department_id' =>$assignmenttr->department_id,
            'employee_id' =>$assignmenttr->employee_id,
            'year'=>$assignmenttr->year,
            'month'=>$assignmenttr->month,
            'status' =>'PENDING',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);



        $lasttimes= DB::getPdo()->lastInsertId();

        $timmmmmm=$lasttimes;

        Times::where(['id'=>$lasttimes])
        ->update([
            'registeras' =>$assignmenttr->registeras,
            'employee_id' =>$assignmenttr->employee_id,
            'year'=>$assignmenttr->year,
            'month'=>$assignmenttr->month,
            'status' =>'PENDING',
            'type' =>'ASSIGNMENT',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);


        $assignmenttr->times_id=$lasttimes;
        $assignmenttr->save();




    }



    $Rh=new Rh;

//accept assignment 
    $user_id=Auth::user()->id;
    $page="assignments";
    $function="accept";
    $description="When the accept-button is clicked";
    $assignments_id=$oldassignment->id;
    $invoice_id=1;
    $times_id=$timmmmmm;
    $agreement_id=1;


    $Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);






$Joindepartment =  Joindepartment::where(["client_id"=>$assignmerntdata[0]->client_id,'department_id'=>$assignmerntdata[0]->department_id,'registeras'=>$assignmerntdata[0]->registeras])->get();

foreach ($Joindepartment as $row)
{

    $getemailsca =  User::where(["id"=>$row->user_id])->get();

    if ($getemailsca[0]->user_type=="SCHEDULE") 
    {


// $details = [
//             'title' => "Beste planner,",
//             'body1' => "Een speddienst is geaccepteerd.",
//             'body2' => "Log in op het portaal voor meer informatie.",
//             'body3' => "Met vriendelijke groet,",
//             'body4' => "",
// ];
// \Mail::send((new \App\Mail\WelcomeEmail($details))
//     ->to($getemailsca[0]->email)->subject("Nieuwe aanmelding. Dienstnummer: ".$assignments_id));

    }

}

return back();
}

}
else
{
    Suggestionassignments::create([
        'user_id' =>Auth::user()->id,
        'assignments_id' =>$id,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);


    $Joindepartment =  Joindepartment::where(["client_id"=>$assignment[0]->client_id,'department_id'=>$assignment[0]->department_id,'registeras'=>$assignment[0]->registeras])->get();

    foreach ($Joindepartment as $row)
    {

        $getemailsca =  User::where(["id"=>$row->user_id])->get();

        if ($getemailsca[0]->user_type=="SCHEDULE") 
        {

// $details = [
//             'title' => "Beste planner,",
//             'body1' => "Er is een nieuwe aanmelding voor de dienst.",
//             'body2' => "Log in op het portaal om deze te bekijken.",
//             'body3' => "Met vriendelijke groet,",
//             'body4' => "",
//         ];

// \Mail::send((new \App\Mail\WelcomeEmail($details))
//     ->to($getemailsca[0]->email)->subject("Nieuwe aanmelding. Dienstnummer: ".$assignment[0]->id));

        }

    }

}

return back();
}


public function assignmentAgreementIndex()
{
    return view('legals.assignmentAgreement');
}


public function employeeaccept()
{
   





if (isset($_GET['acc']))
{

    $assignmenttr = Assignment::findOrFail($_GET['acc']);

    $Rh=new Rh;

 

    $wantaccepttime_from= $assignmenttr->time_from+60;
    $wantaccepttime_to= $assignmenttr->time_to;






    $checkass =  Assignment::where(["start_date"=>$assignmenttr->start_date,'status'=>'EMPLOYEE_ACCEPTED','employee_id'=>Auth::user()->id,['status','!=',"EMPLOYEE_CANCELED"]])->get();

    $rrrr=0;



    $nex_date = date('Y-m-d', strtotime($assignmenttr->start_date . ' +1 day'));
    // $pre_date = date('Y-m-d', strtotime($assignmenttr->date_from . ' -1 day'));
    $pre_date = date('Y-m-d', strtotime($assignmenttr->start_date . ' -0 day'));

    $pre_date1 = date('Y-m-d', strtotime($assignmenttr->start_date . ' -1 day'));

    $checkass3 =  Assignment::where(["start_date"=>$nex_date,'status'=>'EMPLOYEE_ACCEPTED','employee_id'=>Auth::user()->id])->get();



    $checkass2 =  Assignment::where(["end_date"=>$pre_date,'status'=>'EMPLOYEE_ACCEPTED','employee_id'=>Auth::user()->id])->get();


 // dd($checkass3);

 
    if (isset($checkass4[0]->id))
    {

        $acceptedtime_from=$checkass4[0]->time_from+60;
        $acceptedtime_to=$checkass4[0]->time_to;


        $time_from= $checkass4[0]->time_from;
        $time_to=  $checkass3[0]->time_to;
        $alltimestamp=  $time_to - $time_from ;


        if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
        {
           $rrrr=1;
       }

       if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
       {
           $rrrr=1;
       }




       if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
       {
           $rrrr=1;
       }

       if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
       {
           $rrrr=1;
       }



   }


   if (isset($checkass3[0]->id))
   {

    $acceptedtime_from=$checkass3[0]->time_from+60;
    $acceptedtime_to=$checkass3[0]->time_to;


    $time_from= $checkass3[0]->time_from;
    $time_to=  $checkass3[0]->time_to;
    $alltimestamp=  $time_to - $time_from ;


    if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
    {
       $rrrr=1;
   }

   if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
   {
       $rrrr=1;
   }




   if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }

   if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }



}





if (isset($checkass2[0]->id))
{

    $acceptedtime_from=$checkass2[0]->time_from+60;
    $acceptedtime_to=$checkass2[0]->time_to;


    $time_from= $checkass2[0]->time_from;
    $time_to=  $checkass2[0]->time_to;
    $alltimestamp=  $time_to - $time_from ;


    if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
    {
       $rrrr=1;
   }

   if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
   {
       $rrrr=1;
   }




   if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }

   if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }



}





if (isset($checkass[0]->id))
{

    $acceptedtime_from=$checkass[0]->time_from+60;
    $acceptedtime_to=$checkass[0]->time_to;


    $time_from= $checkass[0]->time_from;
    $time_to=  $checkass[0]->time_to;
    $alltimestamp=  $time_to - $time_from ;


    if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
    {
       $rrrr=1;
   }

   if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
   {
       $rrrr=1;
   }




   if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }

   if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }



}
 










if ( $assignmenttr->duobleplaning==1)
 {
    $rrrr=0;
}

// echo $rrrr;exit;

if ($rrrr==1) 
{
   echo "This time is already reserved for the freelancer";exit;
}








$oldassignment=$assignmenttr;



if ($assignmenttr->employee_id==Auth::user()->id) 
{
    $assignmenttr->status="EMPLOYEE_ACCEPTED";
    $assignmenttr->times_status="pending";
    $assignmenttr->save();






    $timmmmmm=0;

    $checkhas=0;
    $times = Times::where(["registeras"=>$oldassignment->registeras,'client_id'=>$oldassignment->client_id,'department_id'=>$oldassignment->department_id,'year'=>$oldassignment->year,'month'=>$oldassignment->month,'employee_id'=>$oldassignment->employee_id,['status', '!=', 'INVOICE_SENT'],])->get();

    foreach ($times as $row) 
    {

        $checkhas=1;

        Times::where(['id'=>$row->id])
        ->update([
            'status' =>'PENDING',
            'checkdate' =>$oldassignment->year."-".$oldassignment->month."-1",
        ]);

        $assignmenttr->times_id=$row->id;
        $assignmenttr->save();

        $timmmmmm=$row->id;
    }



    if ($checkhas==0)
    {


        Times::create([
            'registeras' =>$assignmenttr->registeras,
            'client_id' =>$assignmenttr->client_id,
            'department_id' =>$assignmenttr->department_id,
            'employee_id' =>$assignmenttr->employee_id,
            'year'=>$assignmenttr->year,
            'month'=>$assignmenttr->month,
            'status' =>'PENDING',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);



        $lasttimes= DB::getPdo()->lastInsertId();

        $timmmmmm=$lasttimes;

        Times::where(['id'=>$lasttimes])
        ->update([
            'registeras' =>$assignmenttr->registeras,
            'employee_id' =>$assignmenttr->employee_id,
            'year'=>$assignmenttr->year,
            'month'=>$assignmenttr->month,
            'status' =>'PENDING',
            'type' =>'ASSIGNMENT',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
            'checkdate' =>$oldassignment->year."-".$oldassignment->month."-1",
        ]);


        $assignmenttr->times_id=$lasttimes;
        $assignmenttr->save();




    }



    $Rh=new Rh;

    //accept assignment 
    $user_id=Auth::user()->id;
    $page="assignments";
    $function="accept";
    $description="When the accept-button is clicked";
    $assignments_id=$oldassignment->id;
    $invoice_id=1;
    $times_id=$timmmmmm;
    $agreement_id=1;

 $Rh::timetotapayrate($timmmmmm);



$Rh::totalhoursass($oldassignment->id,$times_id);



$Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);







}



 





echo 1;exit;
}












}

public function employeereject()
{
    if (isset($_GET['rej']) and Auth::user()->user_type=="EMPLOYEE")
    {
        $assignmenttr = Assignment::findOrFail($_GET['rej']);
        if ($assignmenttr->employee_id==Auth::user()->id) 
        {
            $assignmenttr->status="EMPLOYEE_CANCELED";
            $assignmenttr->save();
        }

        $Rh = new Rh;
        //reject assignment 
        $user_id=Auth::user()->id;
        $page="assignments";
        $function="reject";
        $description="When the reject-button is clicked";
        $assignments_id=$_GET['rej'];
        $invoice_id=1;
        $times_id=1;
        $agreement_id=1;
        $Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);
       echo 1;exit;
    }   
}

public function indexopenassignment($language, $sort_upcoming = "asc",$jobtitle, $client_id = -1, $department_id = -1, $employee_id = -1, $status = -1,$year=-1,$month=-1,$start_date=-1)
{
$suggestionassignments = Suggestionassignments::where("user_id",Auth::user()->id)->get();
$query = Assignment::query();
    if (Auth::user()->user_type=='SCHEDULE' or Auth::user()->user_type=='FINANCIAL' )
    {
       $query = $query->where("client_id",Auth::user()->client_id);
    }


    if ($sort_upcoming == 'desc' || $sort_upcoming == 'asc') 
    {
        $query = $query->orderBy("start_date", $sort_upcoming);
    } 
    else 
    if ($sort_upcoming == 'iddesc') 
    {
        $query = $query->orderBy("id", "desc");
    }
    else
    {
        $query = $query->orderBy("start_date", $sort_upcoming);
    }

    if ($client_id > 0) 
    {
        $query = $query->where("client_id", $client_id);
    }

    if ($department_id > 0) 
    {
        $query = $query->where("department_id", $department_id);
    }

    if ($status == "EMPLOYEE_ACCEPTED") 
    {
        $query = $query->where("status", "EMPLOYEE_ACCEPTED");
    } 
    else if ($status == "PENDING")
    {
        $query = $query->where("status", "PENDING");
    }
    else if ($status == "EMPLOYEE_CANCELED")
    {
        $query = $query->whereIn("status", ["EMPLOYEE_CANCELED"]);
    } 
    

    if (Auth::user()->user_type=="EMPLOYEE") 
    {
       $query = $query->where("employee_id",Auth::user()->id);
    }

    if ($jobtitle!="-1") 
    {
       $query = $query->where("registeras",$jobtitle);
    }
 
    if ($year> 0) 
    {
       $query = $query->where("year",$year);
    }

    if ($month> 0) 
    {
       $query = $query->where("month",$month);
    }
    if ($start_date!="-1") 
    {
        if ($start_date=="justlast") 
        {
            //$query = $query->where("start_date", '>=' , Carbon::now()->subDays(60));
        }
        else
        {
          $query = $query->where("start_date",$start_date);  
      }
    }
    if ($client_id > 0) 
    {
      $query = $query->where("client_id", $client_id);
    }
    if (Auth::user()->user_type != "EMPLOYEE")
    {
        if ($employee_id > 0) 
        {
            $query = $query->where("employee_id", $employee_id);
        }
    }
    if (isset($_GET['id']) and $_GET['id']!=0)
    {
        $query = $query->where("id",(int)$_GET['id']);
    }

$query = $query->where("employee_id",1);

$assignments = $query->with(["client", "client.profile", "employee", "employee.profile", "department"])->paginate(Auth::user()->paginationnum);
$profiles = Profile::get();
 


return view('dashboard.assignments.index')
->with([
    "profiles" => $profiles,
    "assignments" => $assignments,
    "suggestionassignments"=>$suggestionassignments,
]);


}














public function index($language, $sort_upcoming = "asc",$jobtitle, $client_id = -1, $department_id = -1, $employee_id = -1, $status = -1,$year=-1,$month=-1,$start_date=-1)
{
$suggestionassignments = Suggestionassignments::where("user_id",Auth::user()->id)->get();
$query = Assignment::query();
    if (Auth::user()->user_type=='SCHEDULE' or Auth::user()->user_type=='FINANCIAL' )
    {
       $query = $query->where("client_id",Auth::user()->client_id);
    }


    if ($sort_upcoming == 'desc' || $sort_upcoming == 'asc') 
    {
        $query = $query->orderBy("start_date", $sort_upcoming);
    } 
    else 
    if ($sort_upcoming == 'iddesc') 
    {
        $query = $query->orderBy("id", "desc");
    }
    else
    {
        $query = $query->orderBy("start_date", $sort_upcoming);
    }

    if ($client_id > 0) 
    {
        $query = $query->where("client_id", $client_id);
    }

    if ($department_id > 0) 
    {
        $query = $query->where("department_id", $department_id);
    }

    if ($status == "EMPLOYEE_ACCEPTED") 
    {
        $query = $query->where("status", "EMPLOYEE_ACCEPTED");
    } 
    else if ($status == "PENDING")
    {
        $query = $query->where("status", "PENDING");
    }
    else if ($status == "EMPLOYEE_CANCELED")
    {
        $query = $query->whereIn("status", ["EMPLOYEE_CANCELED"]);
    } 
    

    if (Auth::user()->user_type=="EMPLOYEE") 
    {
       $query = $query->where("employee_id",Auth::user()->id);
    }

    if ($jobtitle!="-1") 
    {
       $query = $query->where("registeras",$jobtitle);
    }
 
    if ($year> 0) 
    {
       $query = $query->where("year",$year);
    }

    if ($month> 0) 
    {
       $query = $query->where("month",$month);
    }
    if ($start_date!="-1") 
    {
        if ($start_date=="justlast") 
        {
            //$query = $query->where("start_date", '>=' , Carbon::now()->subDays(60));
        }
        else
        {
          $query = $query->where("start_date",$start_date);  
      }
    }
    if ($client_id > 0) 
    {
      $query = $query->where("client_id", $client_id);
    }
    if (Auth::user()->user_type != "EMPLOYEE")
    {
        if ($employee_id > 0) 
        {
            $query = $query->where("employee_id", $employee_id);
        }
    }
    if (isset($_GET['id']) and $_GET['id']!=0)
    {
        $query = $query->where("id",(int)$_GET['id']);
    }



$assignments = $query->with(["client", "client.profile", "employee", "employee.profile", "department"])->paginate(Auth::user()->paginationnum);
$profiles = Profile::get();
 


return view('dashboard.assignments.index')
->with([
    "profiles" => $profiles,
    "assignments" => $assignments,
    "suggestionassignments"=>$suggestionassignments,
]);


}




public function canceledIndex2($language, $sort_upcoming = "asc", $client_id = -1, $department_id = -1, $employee_id = -1, $status = -1)
{
    try {
        $query = Assignment::query();
        if (Auth::user()->user_type == "CLIENT" || Auth::user()->user_type == "SCHEDULE") {
            $client_id = Auth::user()->id;
            $clients = null;
            $departments = Department::where("client_id", $client_id)->select(["id", "title"])->get();
            $employees = User::where("client_id", $client_id)->where("id", ">", 3)->where("user_type", "EMPLOYEE")->where("is_activated", true)->with(['profile:user_id,first_name,last_name'])->select(["id", "email"])->get();
            if ($employee_id > 0) {
                $employee = User::where("client_id", $client_id)->findOrFail($employee_id);
                $employee_id_array = [$employee_id];
            }
        } else {
            $departments = Department::select(["id", "title"])->get();
            $users = User::select(["id", "email", "user_type"])->whereIn("user_type", ["CLIENT", "EMPLOYEE"])->where("is_activated", true)->with(['profile:user_id,first_name,last_name'])->get();

            $clients = $users->where("user_type", "CLIENT")->all();
            $employees = $users->where("id", ">", 3)->where("user_type", "EMPLOYEE")->all();

            if ($employee_id > 0) {
                $employee_id_array = [$employee_id];
            }
        }

        if ($sort_upcoming == 'desc' || $sort_upcoming == 'asc') {
            $query = $query->orderBy("start_date", $sort_upcoming);
        } else {
            $sort_upcoming = "asc";
            $query = $query->orderBy("start_date", $sort_upcoming);
        }

        if ($client_id > 0) {
            $query = $query->where("client_id", $client_id);
        }
        if ($employee_id > 0) {
            $query = $query->whereIn("employee_id", $employee_id_array);
        }
        if ($department_id > 0) {
            $query = $query->where("department_id", $department_id);
        }


        $query = $query->where("status", "EMPLOYEE_CANCELED");

        $query = $query->where("type", "ASSIGNMENT");

        $assignments = $query->with(["client", "client.profile", "employee", "employee.profile", "department", "cancellations"])->paginate(Auth::user()->paginationnum);

        $configuration = Configuration::where("slug", "CREATE_ASSIGNMENT_AS_EMPLOYEE_ACCEPTED")->first();

        return view('dashboard.assignments.index')
        ->with([
            "user_is_blocked" => $configuration->is_enabled,
            "departments" => $departments,
            "employees" => $employees,
            "clients" => $clients,
            "assignments" => $assignments,
            "sort_upcoming" => $sort_upcoming,
            "client_id" => $client_id,
            "employee_id" => $employee_id,
            "department_id" => $department_id,
            "status" => $status
        ]);
    } catch (\Exception $e) {
        dd($e);
        abort(404);
    }
}


public function canceledIndex($language)
{
        // TODO: add cancellation configuration
    abort(404);

    try {
        $cancellations = Cancellation::with(["assignment", "employee"])->paginate(Auth::user()->paginationnum);

        return view('dashboard.assignments.cancellationsIndex')
        ->with([
            "cancellations" => $cancellations,
        ]);
    } catch (\Exception $e) {
        dd($e);
        abort(404);
    }
}

public function createAssignmentIndex($language)
{






 
  if (isset($_GET['getdepartmentsbyregisteras']))
    {
       $Joinclient = Joinclient::where(["user_id"=>Auth::user()->id,'client_id'=>$_GET['client_id'],'registeras'=>$_GET['getdepartmentsbyregisteras']])->select(["*"])->get()->unique('department_id'); 

      $departmentinupdate=0;
       if (isset($_GET['assidinupdate'])) 
       {
        $assignmentupdateid = Assignment::findOrFail($_GET['assidinupdate']);
      $departmentinupdate=$assignmentupdateid->department_id;
          
        } 
       
?>

            
                <select style="width:100%;" name="department_id" required
                    class="select2class3 btn btn-outline-secondaryf dropdown-toggle shadow getdepartment" id="department_id">
                  <?php        

                  foreach ($Joinclient as $row) 
                   {



    $departments = Department::where("id", $row->department_id)->get();
if ($departments[0]->is_available==1)
 {
     


                    ?>
                        <option <?php if ($departmentinupdate==$departments[0]->id ) 
                        {
                            echo "selected";
                        } ?> 
                            data-id="<?php echo $departments[0]->id ?>" data-cost="<?php  echo $departments[0]->cost ?>"
                            data-description="<?php  echo $departments[0]->description ?>"
                            data-requirements="<?php  echo $departments[0]->requirements ?>"
                            data-conditions="<?php  echo $departments[0]->conditions ?>"
                            value="<?php  echo $departments[0]->id ?>">
                            <?php  echo $departments[0]->title ?> 
                        </option>
                    <?php
                }
                }
                ?>
            
                </select>


        
<?php
       exit;
    }


    if (isset($_GET['getinfo']))
    {




        $joinclient = Joinclient::where("id",$_GET['joinclient'])->get();
        $client_payrate='';
        $payrate='';
        foreach ($joinclient as $row)
        {
            $registeras=$row['registeras'];
            $client_payrate=$row['client_payrate'];
            $payrate=$row['payrate'];
        }

        if ($_GET['type']=='clientpayrate') 
        {
            echo $client_payrate;
        }
        else
        {
            echo $payrate;
        }


        exit;
    }



    $client_id=0;
    $clients=array();


    $departments = Department::orderBy("title", "asc")->get();


   if (Auth::user()->user_type=="EMPLOYEE") 
    {
        $clients = DB::table('joinclient')
        ->join('profiles', 'profiles.user_id', '=', 'joinclient.client_id')
        ->where(["joinclient.user_id"=> Auth::user()->id])
        ->select('profiles.first_name','profiles.last_name','joinclient.client_id','joinclient.company_name')
        ->orderBy("profiles.first_name","asc")
        ->get()->unique('client_id');

    }
    else
    {

        $clients = DB::table('users')
        ->join('profiles', 'profiles.user_id', '=', 'users.id')
        ->where(["users.user_type"=> "CLIENT"])
        ->select('profiles.first_name','profiles.last_name','users.id as client_id','profiles.company_name','users.is_activated')
        ->orderBy("profiles.first_name","asc")
        ->get()->unique('client_id');
           
    }




$registeras=DB::table('rehisterases')->where(["status"=> 1])->get();



    return view('dashboard.assignments.create_form')->with(["client_id" => $client_id, "departments" => $departments, "clients" => $clients,'registeras'=>$registeras]);
 



 
}

public function updateAssignmentIndex($language, $client_id, $assignment_id)
{
    return view('dashboard.assignments.update')->with(["client_id" => $client_id, "assignment_id" => $assignment_id]);
}


public function createAssignmentForm($language, $client_id)
{


 

    if (Auth::user()->hasRole('client')) {
        $client_id = Auth::user()->id;
    } else if (Auth::user()->hasRole('financial')) {
        abort(403);
    } else if (Auth::user()->hasRole('schedule')) {
        $client_id = Auth::user()->client_id;
    }


    if ($client_id < 0) {
        $client = User::where("user_type", "CLIENT")->select("id")->first();
        $client_id = $client->id;
    } else {
        $client_exists = User::where("user_type", "CLIENT")->whereId($client_id)->exists();

        if (!$client_exists) {
            abort(404);
        }
    }

    $users = User::select(["id", "user_type", "client_id"])->whereIn("user_type", ["CLIENT", "EMPLOYEE"])->where("is_activated", true)->with(['profile'])->get();

    $clients = $users->where("user_type", "CLIENT")->all();
    $employees = $users->where("id", ">", 3)->where("client_id", $client_id)->where("user_type", "EMPLOYEE")->all();



    $departments = Department::where("is_available", true)->where("client_id", $client_id)->orderBy("title", "asc")->get();





    if (count($departments) > 0) {
        return view('dashboard.assignments.create_form')->with(["employees" => $employees, "client_id" => $client_id, "departments" => $departments, "clients" => $clients]);
    } else {
        abort(405);
    }
}

public function createAssignment(Request $request)
{

 
 
  

    if (Auth::user()->user_type=="EMPLOYEE" )
    {
      if (Auth::user()->caninsertassignment==0) 
      {
        die('Oops');echo "ops";exit;
      }
    }


    if (Auth::user()->user_type!="EMPLOYEE") 
    {
        
    $a=$request->employee_id;
    $b=$request->openemployee_id1;
    $c=$request->openemployee_id2;
    $d=$request->openemployee_id3;
    $e=$request->openemployee_id4;

    if ($b==1)
    {
        $b=2;
    }

    if ($c==1)
    {
        $c=3;
    }

    if ($d==1)
    {
        $d=4;
    }

    if ($e==1)
    {
        $e=5;
    }

    $values = array($a, $b, $c, $d, $e);


    if(count(array_unique($values))<count($values))
    {


Session::flash('message', "some employees selected duplicate.");
return view('dashboard.assignments.create')->with(["client_id" => $request->client_id]);
      //  return redirect()->back()->with('message', "some employees selected duplicate.");
exit;
    }

    }
    else
    {

    }










//nullable != required


    $validator = Validator::make($request->all(), [
        'client_id' => ['nullable', 'integer', "exists:users,id"],
        'employee_id' => ['nullable', 'integer', "exists:users,id"],
        'department_id' => ['required', 'integer', "exists:departments,id"],
            // 'time_from' => ['required', 'string'],
            // 'time_to' => ['nullable', 'string'],
            // 'end_date' => ['required', 'date'],
            // 'start_date' => ['required', 'date', 'before_or_equal:end_date'],
        'department_id' => ['required', 'integer'],
        'payrate' => ['nullable'],
        'description' => ['nullable', 'string'],
        'requirements' => ['nullable', 'string'],
        'conditions' => ['nullable', 'string'],
        'extra_description' => ['nullable', 'string'],
    ]);
    if ($validator->fails()) {

        Session::flash('message', "some of your data is wrong;");
return view('dashboard.assignments.create')->with(["client_id" => $request->client_id]);
      //  return redirect()->back()->with('message', "some employees selected duplicate.");
exit;


        return back()->withErrors($validator)->withInput();
    }

        // Retrieve the validated input...
    $validated = $validator->validated();



        //$validated['client_id']=$request->client_id;


    if (!array_key_exists("client_id", $validated)) {
        if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
        {
            $validated['client_id'] = Auth::user()->client_id;
            $request->client_id = Auth::user()->client_id;
        }
        else
        {
         $validated['client_id'] = Auth::user()->id;
         $request->client_id = Auth::user()->id;

     }
 }
 else
 {
    $validated['client_id'] = $request->client_id;
}







if (!array_key_exists("employee_id", $validated) ||  $validated['employee_id'] == null || $validated['employee_id'] == "") {


  if (Auth::user()->user_type=="EMPLOYEE") 
    {
         $validated['employee_id'] = Auth::user()->id;
    }
    else
    {
        $validated['employee_id'] = 1;
    }


   
}

 

        // $configuration = Configuration::where("slug", "CREATE_ASSIGNMENT_AS_EMPLOYEE_ACCEPTED")->first();
        // if ($configuration->is_enabled) {
$validated['status'] =  "PENDING";
        // }
// return $request->assignment;
try {


    $joinclient = Joinclient::where(["user_id"=>$validated['employee_id'],"client_id"=>$validated['client_id'],'registeras'=>$request->registeras,'department_id'=>$request->department_id])->get();
 



    if ($joinclient->isEmpty())
    {


if (Auth::user()->user_type=="EMPLOYEE")
 {
         Session::flash('message', "You Cant Insert This Assignment");
return view('dashboard.assignments.create')->with(["client_id" => $request->client_id]);
      //  return redirect()->back()->with('message', "some employees selected duplicate.");
exit;
}



    // $validated['payrate'] =0;
    // $validated['client_payrate'] =0;
    }
    else
    {
        $validated['payrate'] =$joinclient[0]->payrate;
        $validated['client_payrate'] =$joinclient[0]->client_payrate;
    }

// return  $validated['client_payrate'];

    $insertedids="";
    $emailss=array();

    $ids="";


   $countalert="Aantal diensten : ".$request->countkala." <br>";
   $insertedalert='<br> Toegevoegde diensten : <br>';


    $textforreservedtime="De zzp'er is al ingeroosterd op volgende tijdstippen : <br>";
    $counttextforreservedtime=0;

    for ($i=1; $i <= $request->countkala ; $i++) 
    { 

        $timeforchekindup=$request->assignment['time_from'][$i];
        $timeforchekindupp=$request->assignment['time_to'][$i];


        $starttime = str_replace(':', '',  $request->assignment['time_from'][$i]);
        $endtime = str_replace(':', '',  $request->assignment['time_to'][$i]);
        if ($starttime < $endtime) 
        {
            $end_dateee=$request->assignment['start_date'][$i];
        }
        else
        {
            $end_dateee=date('Y-m-d', strtotime($request->assignment['start_date'][$i]. ' + 1 days'));
        }


// $assignmenttr = Assignment::findOrFail($_GET['acc']);




        $validated['time_from'] =  strtotime($request->assignment['start_date'][$i].$request->assignment['time_from'][$i]);
        $validated['time_to'] =  strtotime($end_dateee.$request->assignment['time_to'][$i]);


        $myTime = $request->assignment['time_from'][$i];
        $time = preg_match('#^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$#', $myTime);
        if ( $time == 1 )
        {
  //echo 1;
        }
        else
        {
            abort(403);
        }


        $myTime = $request->assignment['time_to'][$i];
        $time = preg_match('#^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$#', $myTime);
        if ( $time == 1 )
        {
  //echo 1;
        }
        else
        {
            abort(403);
        }







// return $request;




        $Rh=new Rh;


        $rrrr= $Rh::duplicatetime($validated['time_from'],$validated['time_to'],$request->assignment['start_date'][$i],$validated['employee_id'],$request->department_id); 







        if ($rrrr==1) 
        {

            $textforreservedtime.=Rh::eurodate($request->assignment['start_date'][$i])." ".$timeforchekindup."-".$timeforchekindupp."<br>"; 

    //return redirect()->back()->with('message', "This time is already reserved for the freelancer");
            $counttextforreservedtime++;

        }


 ///////////////////////////////////////////////////////////////////////////////////////////after check















        $validated['start_date'] =   $request->assignment['start_date'][$i];
        $validated['end_date'] =   $end_dateee;


        $validated['break']=0; 
        if (@$request->break=="1") 
        {
            $validated['break']=1;
        }


        $st=explode("-",$request->assignment['start_date'][$i]);


        $curentyear=$st[0];
        $curentmonth=$st[1];
        $curentday=$st[2];












        if ($rrrr!=1) 
        {


            DB::beginTransaction();



//geting department title 
            if (!isset($request->client_id)) 
            {



                if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
                {
                    $department = Department::where("is_available", true)->where("client_id",Auth::user()->client_id)->findOrFail($validated['department_id']);
                }
                else
                {
                   $department = Department::where("is_available", true)->where("client_id",Auth::user()->id)->findOrFail($validated['department_id']);  
               }
           }
           else
           { 

             

            $department = Department::where("is_available", true)->where("client_id", $validated['client_id'])->findOrFail($validated['department_id']);


        }
//geting department title 

        



        $validated['education_title'] = $department->education_title;

        $assignment = new Assignment();
        $assignment->fill($validated);
        $assignment->save();


        $lastinsertedid= DB::getPdo()->lastInsertId();


        $insertedalert.=$request->assignment['start_date'][$i]."   ".$request->assignment['time_from'][$i]."   ".$request->assignment['time_to'][$i]."   - Id :  ".$lastinsertedid." <br>";



        $insertedids.=$lastinsertedid.",";




        $preaggrement = Preaggrement::where("id",$request->agreement_id)->get();

        $emploee = User::where("id",$validated['employee_id'])->get();
        $profile = Profile::where("user_id",$validated['employee_id'])->get();
        $addresses = Address::where(["addressable_id"=>$validated['employee_id'],"addressable_type"=>"App\Models\User"])->get();
        $financials = Financial::where("user_id",$validated['employee_id'])->get();


        if (!isset($request->client_id)) 
        {

            if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
            {
                $clientprofile = Profile::where("user_id",Auth::user()->client_id)->get();
                $clientaddresses = Address::where("addressable_id",Auth::user()->client_id)->get();
            }
            else
            {
                $clientprofile = Profile::where("user_id",Auth::user()->id)->get();
                $clientaddresses = Address::where("addressable_id",Auth::user()->id)->get();   
            }




        }
        else
        {
          $clientprofile = Profile::where("user_id",$request->client_id)->get();  
          $clientaddresses = Address::where("addressable_id",$request->client_id)->get();

      }








      $top="";
      if ($preaggrement->isEmpty() or $validated['employee_id']==1 )
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

        $text3=$profile[0]->company_name.", ".$addresses[0]->address." ".$addresses[0]->postcode." ".$addresses[0]->city.", met KVK-nummer  ".$profile[0]->kvk_number."  en BTW-nummer
        ".$profile[0]->btw_number."  hierbij rechtsgeldig vertegenwoordigd door haar directeur ".$profile[0]->first_name." ".$profile[0]->last_name.", hierna te noemen 'Opdrachtnemer'
        ";



        $date = $request->assignment['start_date'][$i];
        $startdate=date("d-m-Y",strtotime($date));

        $date = $end_dateee;
        $enddate=date("d-m-Y",strtotime($date));



        if ($request->registeras=='healthcare') 
        {
         $payratee='   <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Uurtarief: '.number_format($validated['payrate'],2).' €</span></span></span></span></p>';
     }
     else
     {
        $payratee="";
    }

    $centertext='
    <br>
    <p><span style=\"font-size:8pt;margin-top:30px;"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Organisatie: '.$clientprofile[0]->company_name.'</span></span></span></span></p>

    <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Locatie: '.$clientaddresses[0]->address." ".$clientaddresses[0]->address_extra." ".$clientaddresses[0]->postcode." ".$clientaddresses[0]->city.'</span></span></span></span></p>

    <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Soort opdracht: '.$type.'</span></span></span></span></p>

    <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Begin: '.$startdate." ".date('H:i',$validated['time_from']).'</span></span></span></span></p>


    <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Einde: '.$enddate." ".date('H:i', $validated['time_to']).'</span></span></span></span></p>

    <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Aantal uren: '.Rh::getduration($validated['time_from'],$validated['time_to'],$validated['start_date'],$validated['end_date']).'</span></span></span></span></p>

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

    if (!array_key_exists("client_id", $validated)) 
    {
        if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
        {
           $clientprofile = Profile::where("user_id",Auth::user()->client_id)->get();
       }
       else
       {
          $clientprofile = Profile::where("user_id",Auth::user()->id)->get();
      }
  }
  else
  {
    $clientprofile = Profile::where("user_id",$request->client_id)->get();
}



$path = $picurl;
$path = substr($path, 1);


       // $type = pathinfo($path, PATHINFO_EXTENSION);
        //$data = file_get_contents($path);
        //$logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
  //in View

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

<div style="width:50%;float: right;">
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">Opdrachtnemer</div><br>
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.$profile[0]->company_name.'</div><br>
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.$profile[0]->first_name." ".$profile[0]->last_name.'</div><br>
</div>

</div>
';

$agreementtemp=$clientsignbox;
} 



// return $validated['break'];






$st=explode("-",$request->assignment['start_date'][$i]);


$curentyear=$st[0];
$curentmonth=$st[1];
$curentday=$st[2];




if (Auth::user()->user_type!="EMPLOYEE") 
{
   
if ($request->openemployee_id1==1)
{

  Assignment::where(['id'=>$lastinsertedid])
  ->update([
     'sleepshift' =>@$request->assignment['sleep_shift'][$i],
     'sleeptime' =>$request["sleeptime"],
     'surchargeassignment' =>$request["Surcharge_assignment"],
     'speedasignment' =>@$request->assignment['speed_assignment'][$i],
     'client_payrate' =>@$validated['client_payrate'],
     'registeras' =>$request["registeras"],
     'agreementtext' =>$top,
     'agreement_id'=>$request->agreement_id,
     'break'=>@$request->assignment['break'][$i],
     'year'=>$curentyear,
     'month'=>$curentmonth,
     'day'=>$curentday,
     'employee_id1'=>@$request->openemployee_id1,
     'employee_id2'=>@$request->openemployee_id2,
     'employee_id3'=>@$request->openemployee_id3,
     'employee_id4'=>@$request->openemployee_id4,
     'employee_id5'=>@$request->openemployee_id5,
     'employee_id6'=>@$request->openemployee_id6,
     'employee_id7'=>@$request->openemployee_id7,
     'employee_id8'=>@$request->openemployee_id8,
     'employee_id9'=>@$request->openemployee_id9,
     'employee_id10'=>@$request->openemployee_id10,
     'admin_id'=>Auth::user()->id,

 ]);
  

  if ($request->countkala>1) 
  {
    $userlisttt = User::where(["id"=>$validated['employee_id']])->get();
    $emailss=$userlisttt[0]->email;


    $ids.=$lastinsertedid.",";
}
}
else
{

    Assignment::where(['id'=>$lastinsertedid])
    ->update([
     'sleepshift' =>@$request->assignment['sleep_shift'][$i],
     'sleeptime' =>$request["sleeptime"],
     'surchargeassignment' =>$request["Surcharge_assignment"],
     'speedasignment' =>@$request->assignment['speed_assignment'][$i],
     'client_payrate' =>null,
     'payrate' =>null,
     'employee_id' =>1,
     'registeras' =>$request["registeras"],
     'agreementtext' =>$top,
     'agreement_id'=>$request->agreement_id,
     'break'=>@$request->assignment['break'][$i],
     'year'=>$curentyear,
     'month'=>$curentmonth,
     'day'=>$curentday,
     'employee_id1'=>@$validated['employee_id'],
     'employee_id2'=>@$request->openemployee_id1,
     'employee_id3'=>@$request->openemployee_id2,
     'employee_id4'=>@$request->openemployee_id3,
     'employee_id5'=>@$request->openemployee_id4,
     'employee_id6'=>@$request->openemployee_id5,
     'employee_id7'=>@$request->openemployee_id6,
     'employee_id8'=>@$request->openemployee_id7,
     'employee_id9'=>@$request->openemployee_id8,
     'employee_id10'=>@$request->openemployee_id9,
     'admin_id'=>Auth::user()->id,

 ]);
}




}
else
{
 // return $request;
   Assignment::where(['id'=>$lastinsertedid])
  ->update([
     'sleepshift' =>@$request->assignment['sleep_shift'][$i],
     'sleeptime' =>$request["sleeptime"],
     'surchargeassignment' =>$request["Surcharge_assignment"],
     'speedasignment' =>@$request->assignment['speed_assignment'][$i],
     'client_payrate' =>@$validated['client_payrate'],
     'registeras' =>$request["registeras"],
     'agreementtext' =>$top,
     'agreement_id'=>$request->agreement_id,
     'break'=>@$request->assignment['break'][$i],
     'year'=>$curentyear,
     'month'=>$curentmonth,
     'day'=>$curentday,
     'employee_id1'=>@$request->openemployee_id1,
     'employee_id2'=>@$request->openemployee_id2,
     'employee_id3'=>@$request->openemployee_id3,
     'employee_id4'=>@$request->openemployee_id4,
     'employee_id5'=>@$request->openemployee_id5,
     'employee_id6'=>@$request->openemployee_id6,
     'employee_id7'=>@$request->openemployee_id7,
     'employee_id8'=>@$request->openemployee_id8,
     'employee_id9'=>@$request->openemployee_id9,
     'employee_id10'=>@$request->openemployee_id10,
     'admin_id'=>Auth::user()->id,

 ]);
  

  if ($request->countkala>1) 
  {
    $userlisttt = User::where(["id"=>$validated['employee_id']])->get();
    $emailss=$userlisttt[0]->email;


    $ids.=$lastinsertedid.",";
}  




}


















DB::commit();


$Rh = new Rh;

//creating assignment 
$user_id=Auth::user()->id;
$page="create new assignment";
$function="submit";

if (!isset($request->client_id)) 
{
    if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
    {
        $cl = Auth::user()->client_id;
    }
    else
    {
        $cl = Auth::user()->id;
    }
}
else
{
    $cl = $request->client_id;
}

$description="OG-lD:".$cl.", A-ID:".$validated['department_id'].", Z-ID:".@$validated['employee_id'].", D:".$Rh::eurodate($validated['start_date']).", T:".$timeforchekindup."-".$timeforchekindupp.", P:(".@$request->assignment['break'][$i]."), S:(".@$request->assignment['sleep_shift'][$i].")";
$assignments_id=$lastinsertedid;
$invoice_id=1;
$times_id=1;
$agreement_id=1;





$Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);




}






}







if ($rrrr!=1) 
{

if (Auth::user()->user_type!="EMPLOYEE") 
{
    // code...


//for emails
    if ($validated['employee_id']!=1 )
    {


        if ($request->openemployee_id1==1) 
        {



            if ($request->countkala>1) 
            {

                $details = [
                    'title' => "Beste ZPC-er,",
                    'body1' => "Er zijn nieuwe diensten voor jou aangemaakt.",
                    'body2' => "Klik <a href='https://mijnzpc.com/nl/assignments/asc/-1/-1/-1/-1/-1/-1/-1/justlast?open=-1'>hier</a> om naar dienstenpagina te gaan.",
                    'body3' => "Met vriendelijke groet,",
                    'body4' => "",
                ];

                \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to("mail@mijnzpc.com")->bcc($emailss)->subject("Nieuwe diensten! Dienstnummers: #".@$ids.""));

               $Rh = new Rh;

               $function="New assignment--Email to freelancer about new assignment";
               $description=json_encode($details);
               $assignments_id=@$lastinsertedid;
               $invoice_id=1;
               $times_id=1;
               $agreement_id=1;


               $Rh::emaillog(json_encode($emailss),$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);





            }




            if ($request->countkala==1) 
            {
        //New assignment to freelancer

                $details = [
                    'title' => "Beste ZPC-er,",
                    'body1' => "Er is een dienst voor jou aangemaakt.",
                    'body2' => "Klik <a href='https://mijnzpc.com/en/assignmentspage/".$lastinsertedid."'>hier</a> om naar dienstenpagina te gaan.",
                    'body3' => "Met vriendelijke groet,",
                    'body4' => "",
                ];
                \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to($emploee[0]->email)->subject("Nieuwe dienst! Dienstnummer: ".$lastinsertedid ));


               $Rh = new Rh;

               $function="New assignment--Email to freelancer about new assignment";
               $description=json_encode($details);
               $assignments_id=@$lastinsertedid;
               $invoice_id=1;
               $times_id=1;
               $agreement_id=1;


               $Rh::emaillog($emploee[0]->email,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);




            }



        }
        else
        {

   //limit assignment

            $emails=array();








            if ($validated['employee_id']!=1) 
            {
               $userlist1 = User::where(["id"=>$validated['employee_id']])->get();
               array_push($emails,$userlist1[0]->email);
           }

           if ($request->openemployee_id1!=1) 
           {
            $userlist2 = User::where(["id"=>$request->openemployee_id1])->get();
            array_push($emails,$userlist2[0]->email);
        }


        if ($request->openemployee_id2!=1) 
        {
         $userlist3 = User::where(["id"=>$request->openemployee_id2])->get();
         array_push($emails,$userlist3[0]->email);
     }


     if ($request->openemployee_id3!=1) 
     {
       $userlist4 = User::where(["id"=>$request->openemployee_id3])->get();
       array_push($emails,$userlist4[0]->email);

   }

   if ($request->openemployee_id4!=1) 
   {
       $userlist5 = User::where(["id"=>$request->openemployee_id4])->get();
       array_push($emails,$userlist5[0]->email);
   }








//New open assignment to freelancer

   $details = [
    'title' => "Beste ZPC-er,",
    'body1' => "Er zijn beschikbare diensten aangemeld.",
    'body2' => "Log in op het portaal om je aan te melden voor deze diensten.",
    'body3' => "Met vriendelijke groet,",
    'body4' => "",
];

\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to("mail@mijnzpc.com")->bcc($emails)->subject("Nieuwe beschikbare diensten! Dienstnummer: #".@$insertedids.""));

               $Rh = new Rh;

               $function="New open assignment--Email to freelancer about new open assignment";
               $description=json_encode($details);
               $assignments_id=@$lastinsertedid;
               $invoice_id=1;
               $times_id=1;
               $agreement_id=1;


               $Rh::emaillog(json_encode($emails),$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);



}










}
//for emails
}


 



if ($validated['employee_id']==1 )
{

 
  $emails=array();






  $Joinclientlist = Joinclient::where(["client_id"=>$request->client_id,'department_id'=>$request->department_id,'registeras'=>$request->registeras])->get();

  if ($Joinclientlist->isEmpty())
  {
      $Joinclientlist = Joinclient::where(["client_id"=>$request->client_id,'department_id'=>1,'registeras'=>$request->registeras])->get();
  }



  foreach ($Joinclientlist as $row) 
  {

      $userlist = User::where(["id"=>$row->user_id])->get();

      array_push($emails,$userlist[0]->email);

  }


          if ($request->countkala==1) 
            {


                //New open assignment to freelancer

  $details = [
    'title' => "Beste ZPC-er,",
    'body1' => "Er is een nieuwe beschikbare dienst aangemeld.",
    'body2' => "Log in op het portaal om jezelf aan te melden voor deze dienst.",
    'body3' => "Met vriendelijke groet,",
    'body4' => "",
];

\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to("mail@mijnzpc.com")->bcc($emails)->subject("Nieuwe beschikbare dienst! Dienstnummer: #".@$lastinsertedid.""));


               $Rh = new Rh;

               $function="New open assignment--Email to freelancer about new open assignment";
               $description=json_encode($details);
               $assignments_id=@$lastinsertedid;
               $invoice_id=1;
               $times_id=1;
               $agreement_id=1;


               $Rh::emaillog(json_encode($emails),$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);




            }
            else
            {


//New open assignment to freelancer

  $details = [
    'title' => "Beste ZPC-er,",
    'body1' => "Er zijn nieuwe beschikbare diensten aangemeld.",
    'body2' => "Log in op het portaal om jezelf aan te melden voor deze diensten.",
    'body3' => "Met vriendelijke groet,",
    'body4' => "",
];

\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to("mail@mijnzpc.com")->bcc($emails)->subject("Nieuwe beschikbare diensten! Dienstnummers: #".@$ids.""));


               $Rh = new Rh;

               $function="New open assignment--Email to freelancer about new open assignment";
               $description=json_encode($details);
               $assignments_id=@$lastinsertedid;
               $invoice_id=1;
               $times_id=1;
               $agreement_id=1;


               $Rh::emaillog(json_encode($emails),$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);



            }






}



}


// return $request;


if ($counttextforreservedtime>0)
{


Session::flash('message', $countalert."<br>".$textforreservedtime.$insertedalert);
//return Redirect::back();

   //return redirect()->back()->with('message',$textforreservedtime);
}
else
{

    Session::flash('message',$countalert."<br>".$insertedalert);
//return Redirect::back();
  //return redirect()->back()->with('message', "Assignment created successfully.");
}
return view('dashboard.assignments.create')->with(["client_id" => $request->client_id]);






} catch (\PDOException $e) {
    DB::rollBack();

     Session::flash('message',$e->getMessage());
return view('dashboard.assignments.create')->with(["client_id" => $request->client_id]);
      //  return redirect()->back()->with('message', "some employees selected duplicate.");
exit;



    //return redirect()->back()->withInput()->with('error', $e->getMessage());
}
}


public function updateAssignmentForm($language, $client_id, $assignment_id)
{

    try {
        $assignment = Assignment::with(["client", "department", "employee"])->findOrFail($assignment_id);

        if (!$assignment->department->is_available) {
            abort(403);
            exit();
        }

        $users = User::select(["id", "user_type", "client_id"])->whereIn("user_type", ["CLIENT", "EMPLOYEE"])->where("is_activated", true)->with(['profile'])->get();

        if (Auth::user()->hasRole('admin')) {
            $clients = $users->where("user_type", "CLIENT")->all();
        } else {
            $clients = null;
        }

        $employees = $users->where("id", ">", 3)->where("client_id", $client_id)->where("user_type", "EMPLOYEE")->all();


        $departments = Department::where("client_id", $client_id)->orderByDesc('updated_at')->get();

        return view('dashboard.assignments.update_form')->with(["employees" => $employees, "client_id" => $client_id, "departments" => $departments, "clients" => $clients, "assignment" => $assignment]);
    } catch (\Exception $e) {
        abort(404);
    }
}

public function updateAssignment(Request $request)
{



$oldassignment = Assignment::findOrFail($request->assignment_id);


 

if (Auth::user()->user_type=="EMPLOYEE")
 {

if (Auth::user()->caninsertassignment==0) 
{
    die('Oops');
}

 }

    $assignmentsforupdate=Assignment::where(['id'=>$request->assignment_id])->get();


    if ($assignmentsforupdate[0]->status!='EMPLOYEE_CANCELED') 
    {
            $Beforeinvoice=Beforeinvoice::where(["times_id"=>$assignmentsforupdate[0]->times_id])->get();

        if ($Beforeinvoice->isEmpty())
        {

        }
        else
        {
            return redirect()->back()->with('message', "Some Of Invoces Of This Assignment Confirmed! You Cant Update!");
        }
    }







       // $can=1;

       //  $checkinvoice=Invoice::where(["assignments_id"=>$request->assignment_id])->get();

       //  foreach ($checkinvoice as $row)
       //   {
       //      if ($row['status']=='CONFIRMED') 
       //      {
       //          $can=0;
       //      }
       //  }


       //      if ($can==0)
       //       {
       //          return redirect()->back()->with('message', "Some Of Invoces Of This Assignment Confirmed! You Cant Update!");
       //      }

 // dd($checkinvoice);


    $validator = Validator::make($request->all(), [
        'client_id' => ['nullable', 'integer', "exists:users,id"],
        'employee_id' => ['nullable', 'integer', "exists:users,id"],
        'department_id' => ['required', 'integer', "exists:departments,id"],
            // 'time_from' => ['required', 'string'],
            // 'time_to' => ['nullable', 'string'],
            // 'end_date' => ['required', 'date'],
            // 'start_date' => ['required', 'date', 'before_or_equal:end_date'],
        'department_id' => ['required', 'integer'],
        'payrate' => ['nullable'],
        'description' => ['nullable', 'string'],
        'requirements' => ['nullable', 'string'],
        'conditions' => ['nullable', 'string'],
        'extra_description' => ['nullable', 'string'],
    ]);
    if ($validator->fails()) {
        return back()
        ->withErrors($validator)
        ->withInput();
    }

        // Retrieve the validated input...
    $validated = $validator->validated();



        //$validated['client_id']=$request->client_id;


    if (!array_key_exists("client_id", $validated)) 
    {
        if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
        {
            $validated['client_id'] = Auth::user()->client_id;
            $request->client_id = Auth::user()->client_id;
        }
        else
        {
         $validated['client_id'] = Auth::user()->id;
         $request->client_id = Auth::user()->id;

     }
 }
 else
 {
    $validated['client_id'] = $request->client_id;
}



if (Auth::user()->user_type=="EMPLOYEE")
 {
    $validated['employee_id']=Auth::user()->id;
}
else
{
  if (!array_key_exists("employee_id", $validated) ||  $validated['employee_id'] == null || $validated['employee_id'] == "") {
    $validated['employee_id'] = 1;
}
  
}



        // $configuration = Configuration::where("slug", "CREATE_ASSIGNMENT_AS_EMPLOYEE_ACCEPTED")->first();
        // if ($configuration->is_enabled) {
$validated['status'] =  "PENDING";
        // }
// return $request->assignment;
try {






if (Auth::user()->user_type=="EMPLOYEE")
 {
        $joinclient = Joinclient::where(["user_id"=>Auth::user()->id,"client_id"=>$validated['client_id'],'registeras'=>$request->registeras,'department_id'=>$request->department_id])->get();
}
else
{
       $joinclient = Joinclient::where(["user_id"=>$request->employee_id,"client_id"=>$validated['client_id'],'registeras'=>$request->registeras,'department_id'=>$request->department_id])->get(); 
}



    if ($joinclient->isEmpty())
    {
    // $validated['payrate'] ='';
    // $validated['client_payrate'] ='';
    }
    else
    {
        $validated['payrate'] =$joinclient[0]->payrate;
        $validated['client_payrate'] =$joinclient[0]->client_payrate;
    }

// return  $validated['client_payrate'];



    for ($i=1; $i <= $request->countkala ; $i++) 
    { 


        $starttime = str_replace(':', '',  $request->assignment['time_from'][$i]);
        $endtime = str_replace(':', '',  $request->assignment['time_to'][$i]);
        if ($starttime < $endtime) 
        {
            $end_dateee=$request->assignment['start_date'][$i];
        }
        else
        {
            $end_dateee=date('Y-m-d', strtotime($request->assignment['start_date'][$i]. ' + 1 days'));
        }








        $validated['time_from'] =  strtotime($request->assignment['start_date'][$i].$request->assignment['time_from'][$i]);
        $validated['time_to'] =  strtotime($end_dateee.$request->assignment['time_to'][$i]);

        $validated['start_date'] =   $request->assignment['start_date'][$i];
        $validated['end_date'] =   $end_dateee;


        $st=explode("-",$request->assignment['start_date'][$i]);


        $curentyear=$st[0];
        $curentmonth=$st[1];
        $curentday=$st[2];










// return  $request->assignment['time_from'][$i];

        $wantaccepttime_from= $validated['time_from']+60;
        $wantaccepttime_to= $validated['time_to'];


 // return $request->assignment_id;



        $checkass =  Assignment::where(["start_date"=>$request->assignment['start_date'][$i],'employee_id'=>$request->employee_id,['id','!=',$request->assignment_id]])->get();

        $rrrr=0;



        $nex_date = date('Y-m-d', strtotime($request->assignment['start_date'][$i] . ' +1 day'));
// $pre_date = date('Y-m-d', strtotime($assignmenttr->date_from . ' -1 day'));
        $pre_date = date('Y-m-d', strtotime($request->assignment['start_date'][$i] . ' -0 day'));

        $pre_date1 = date('Y-m-d', strtotime($request->assignment['start_date'][$i] . ' -1 day'));

        $checkass3 =  Assignment::where(["start_date"=>$nex_date,'employee_id'=>$request->employee_id,['id','!=',$request->assignment_id]])->get();



        $checkass2 =  Assignment::where(["end_date"=>$pre_date,'employee_id'=>$request->employee_id,['id','!=',$request->assignment_id]])->get();


 // dd($checkass3);

// echo $pre_date1;exit;
        if (isset($checkass4[0]->id))
        {

            $acceptedtime_from=$checkass4[0]->time_from+60;
            $acceptedtime_to=$checkass4[0]->time_to;


            $time_from= $checkass4[0]->time_from;
            $time_to=  $checkass3[0]->time_to;
            $alltimestamp=  $time_to - $time_from ;


            if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
            {
               $rrrr=1;
           }

           if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
           {
               $rrrr=1;
           }




           if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
           {
               $rrrr=1;
           }

           if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
           {
               $rrrr=1;
           }



       }


       if (isset($checkass3[0]->id))
       {

        $acceptedtime_from=$checkass3[0]->time_from+60;
        $acceptedtime_to=$checkass3[0]->time_to;


        $time_from= $checkass3[0]->time_from;
        $time_to=  $checkass3[0]->time_to;
        $alltimestamp=  $time_to - $time_from ;


        if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
        {
           $rrrr=1;
       }

       if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
       {
           $rrrr=1;
       }




       if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
       {
           $rrrr=1;
       }

       if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
       {
           $rrrr=1;
       }



   }





   if (isset($checkass2[0]->id))
   {

    $acceptedtime_from=$checkass2[0]->time_from+60;
    $acceptedtime_to=$checkass2[0]->time_to;


    $time_from= $checkass2[0]->time_from;
    $time_to=  $checkass2[0]->time_to;
    $alltimestamp=  $time_to - $time_from ;


    if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
    {
       $rrrr=1;
   }

   if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
   {
       $rrrr=1;
   }




   if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }

   if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }



}





if (isset($checkass[0]->id))
{

    $acceptedtime_from=$checkass[0]->time_from+60;
    $acceptedtime_to=$checkass[0]->time_to;


    $time_from= $checkass[0]->time_from;
    $time_to=  $checkass[0]->time_to;
    $alltimestamp=  $time_to - $time_from ;


    if (($wantaccepttime_from <= $acceptedtime_from) && ($acceptedtime_from <= $wantaccepttime_to)) 
    {
       $rrrr=1;
   }

   if (($wantaccepttime_from <= $acceptedtime_to) && ($acceptedtime_to <= $wantaccepttime_to)) 
   {
       $rrrr=1;
   }




   if (($acceptedtime_from <= $wantaccepttime_from) && ($wantaccepttime_from <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }

   if (($acceptedtime_from <= $wantaccepttime_to) && ($wantaccepttime_to <= $acceptedtime_to)) 
   {
       $rrrr=1;
   }



}



if (Auth::user()->user_type=="EMPLOYEE")
 {
$Rh=new Rh;
$rrrr=$Rh::duplicatetimeupdate($validated['time_from'],$validated['time_to'],$request->assignment['start_date'][$i],Auth::user()->id,$request->department_id,$request->assignment_id);
}




if ($rrrr==1) 
{

//$textforreservedtime.=Rh::eurodate($request->assignment['start_date'][$i])." ".$timeforchekindup."  "; 

   return redirect()->back()->with('message', "This time is already reserved for the freelancer");
//$counttextforreservedtime++;

}
























DB::beginTransaction();


if (!isset($request->client_id)) 
{

    if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
    {
        $department = Department::where("is_available", true)->where("client_id",Auth::user()->client_id)->findOrFail($validated['department_id']);

    }
    else
    {

       $department = Department::where("is_available", true)->where("client_id",Auth::user()->id)->findOrFail($validated['department_id']);  
   }
}
else
{
    $department = Department::where("is_available", true)->where("client_id", $validated['client_id'])->findOrFail($validated['department_id']);

}






$validated['education_title'] = $department->education_title;

        //     $assignment = new Assignment();
        //     $assignment->fill($validated);
        //     $assignment->save();


        // $lastinsertedid= DB::getPdo()->lastInsertId();


$preaggrement = Preaggrement::where("id",$request->agreement_id)->get();



if (Auth::user()->user_type=="EMPLOYEE") 
{
    $emploee = User::where("id",Auth::user()->id)->get();
$profile = Profile::where("user_id",Auth::user()->id)->get();

    $addresses = Address::where(["addressable_id"=>Auth::user()->id,"addressable_type"=>"App\Models\User"])->get();
    $financials = Financial::where("user_id",Auth::user()->id)->get();
}
else
{
    $emploee = User::where("id",$request->employee_id)->get();
$profile = Profile::where("user_id",$request->employee_id)->get();
 $addresses = Address::where(["addressable_id"=>$request->employee_id,"addressable_type"=>"App\Models\User"])->get();  
 $financials = Financial::where("user_id",$request->employee_id)->get(); 
}








if (!isset($request->client_id)) 
{

    if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
    {
        $clientprofile = Profile::where("user_id",Auth::user()->client_id)->get();
        $clientaddresses = Address::where("addressable_id",Auth::user()->client_id)->get();
    }
    else
    {
        $clientprofile = Profile::where("user_id",Auth::user()->id)->get();
        $clientaddresses = Address::where("addressable_id",Auth::user()->id)->get();   
    }




}
else
{
  $clientprofile = Profile::where("user_id",$request->client_id)->get();  
  $clientaddresses = Address::where("addressable_id",$request->client_id)->get();

}








$top="";
if ($preaggrement->isEmpty() or $request->employee_id==1 )
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



    $text3=$profile[0]->company_name.", ".$addresses[0]->address." ".$addresses[0]->postcode." ".$addresses[0]->city.", met KVK-nummer  ".$profile[0]->kvk_number."  en BTW-nummer
    ".$profile[0]->btw_number."  hierbij rechtsgeldig vertegenwoordigd door haar directeur ".$profile[0]->first_name." ".$profile[0]->last_name.", hierna te noemen 'Opdrachtnemer'
    ";



    $date = $request->assignment['start_date'][$i];
    $startdate=date("d-m-Y",strtotime($date));

    $date = $end_dateee;
    $enddate=date("d-m-Y",strtotime($date));


    if ($request->registeras=='healthcare') 
    {
     $payratee='   <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Uurtarief: '.number_format($validated['payrate'],2).' €</span></span></span></span></p>';
 }
 else
 {
    $payratee="";
}

$centertext='
<br>
<p><span style=\"font-size:8pt;margin-top:30px;"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Organisatie: '.$clientprofile[0]->company_name.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Locatie: '.$clientaddresses[0]->address." ".$clientaddresses[0]->address_extra." ".$clientaddresses[0]->postcode." ".$clientaddresses[0]->city.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Soort opdracht: '.$type.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Begin: '.$startdate." ".date('H:i',$validated['time_from']).'</span></span></span></span></p>


<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Einde: '.$enddate." ".date('H:i', $validated['time_to']).'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Aantal uren: '.Rh::getduration($validated['time_from'],$validated['time_to'],$validated['start_date'],$validated['end_date']).'</span></span></span></span></p>

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


if (!array_key_exists("client_id", $validated)) 
{
    if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
    {
       $clientprofile = Profile::where("user_id",Auth::user()->client_id)->get();
   }
   else
   {
      $clientprofile = Profile::where("user_id",Auth::user()->id)->get();
  }
}
else
{
    $clientprofile = Profile::where("user_id",$request->client_id)->get();
}



$path = $picurl;
$path = substr($path, 1);


       // $type = pathinfo($path, PATHINFO_EXTENSION);
        //$data = file_get_contents($path);
        //$logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
  //in View


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

<div style="width:50%;float: right;">
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">Opdrachtnemer</div><br>
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.$profile[0]->company_name.'</div><br>
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.$profile[0]->first_name." ".$profile[0]->last_name.'</div><br>
</div>

</div>
';

$agreementtemp=$clientsignbox;
} 




$assignmentsforupdate=Assignment::where(['id'=>$request->assignment_id])->get();


$Times=Times::where(["id"=>$assignmentsforupdate[0]->times_id])->get();



if ($Times->isEmpty())
{

}
else
{

    Times::where(['id'=>$Times[0]->id])
    ->update([
        'status' =>'PENDING',
    ]); 
}





if ($request->openemployee_id1==1 or $request->openemployee_id1==null)
{



    $emp=@$validated['employee_id'];

    Assignment::where(['id'=>$request->assignment_id])
    ->update([
     'education_title' =>$validated['education_title'],
     'registeras' =>$request["registeras"],
     'department_id' =>$request["department_id"],
     'sleepshift' =>$request["sleep_shift"],
     'sleeptime' =>$request["sleeptime"],
     'surchargeassignment' =>$request["Surcharge_assignment"],
     'speedasignment' =>$request["speed_assignment"],
     'client_payrate' =>@$validated['client_payrate'],
     'registeras' =>$request["registeras"],
     'agreementtext' =>$top,
     'agreement_id'=>$request->agreement_id,
     'time_from'=>$validated['time_from'],
     'time_to'=>$validated['time_to'],
     'start_date'=>$validated['start_date'],
     'end_date'=>$validated['end_date'],
     'payrate'=>@$validated['payrate'],
     'client_payrate'=>@$validated['client_payrate'],
     'extra_description'=>$validated['extra_description'],
     'employee_id'=>$validated['employee_id'],
     'break'=>$request["break"],
     'status'=>"PENDING",
     'year'=>$curentyear,
     'month'=>$curentmonth,
     'day'=>$curentday,
     'employee_id1'=>1,
     'employee_id2'=>1,
     'employee_id3'=>1,
     'employee_id4'=>1,
     'employee_id5'=>1,
     'employee_id6'=>1,
     'employee_id7'=>1,
     'employee_id8'=>1,
     'employee_id9'=>1,
     'employee_id10'=>1,

 ]);


if (Auth::user()->id=="EMPLOYEE") 
{
    Assignment::where(['id'=>$request->assignment_id])
    ->update([
     'employee_id'=>Auth::user()->id,
     ]);
}




}
else
{






    $emp=@$request->employee_id;
 Assignment::where(['id'=>$request->assignment_id])
 ->update([
     'education_title' =>$validated['education_title'],
     'registeras' =>$request["registeras"],
     'department_id' =>$request["department_id"],
     'sleepshift' =>$request["sleep_shift"],
     'sleeptime' =>$request["sleeptime"],
     'surchargeassignment' =>$request["Surcharge_assignment"],
     'speedasignment' =>$request["speed_assignment"],
     'client_payrate' =>null,
     'payrate' =>null,
     'employee_id' =>1,
     'registeras' =>$request["registeras"],
     'agreementtext' =>$top,
     'agreement_id'=>$request->agreement_id,
     'time_from'=>$validated['time_from'],
     'time_to'=>$validated['time_to'],
     'start_date'=>$validated['start_date'],
     'end_date'=>$validated['end_date'],
     'extra_description'=>$validated['extra_description'],
     'break'=>$request["break"],
     'status'=>"PENDING",
     'year'=>$curentyear,
     'month'=>$curentmonth,
     'day'=>$curentday,
     'employee_id1'=>$request->employee_id,
     'employee_id2'=>$request->openemployee_id1,
     'employee_id3'=>$request->openemployee_id2,
     'employee_id4'=>$request->openemployee_id3,
     'employee_id5'=>$request->openemployee_id4,
     'employee_id6'=>@$request->openemployee_id5,
     'employee_id7'=>@$request->openemployee_id6,
     'employee_id8'=>@$request->openemployee_id7,
     'employee_id9'=>@$request->openemployee_id8,
     'employee_id10'=>@$request->openemployee_id9,

 ]); 
}

           //Rh::updateinvoce($request->assignment_id);


$Rh = new Rh;
//updating assignment 
$user_id=Auth::user()->id;
$page="Update Assignment";
$function="submit";

if (!isset($request->client_id)) 
{
    if (Auth::user()->user_type=="SCHEDULE" or Auth::user()->user_type=="FINANCIAL")
    {
        $cl = Auth::user()->client_id;
    }
    else
    {
        $cl = Auth::user()->id;
    }
}
else
{
    $cl = $request->client_id;
}




$description="OG-lD:".$cl.", A-ID:".$validated['department_id'].", Z-ID:".@$emp.", D:".$Rh::eurodate(@$request->assignment['start_date'][$i]).", T:".@$request->assignment['time_from'][$i]."-".@$request->assignment['time_to'][$i].", P:(".@$request["break"]."), S:(".@$request["sleep_shift"].")";
$assignments_id=$request->assignment_id;
$invoice_id=1;
$times_id=1;
$agreement_id=1;


$Rh::steplog($user_id,$page,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);


$Rh::totalhoursassdelete($request->assignment_id);




$Suggestionassignments=Suggestionassignments::where(["assignments_id"=>$request->assignment_id])->delete(); 



$newassignment = Assignment::findOrFail($request->assignment_id);



if ($newassignment->employee_id1!=1)
 {

    if ($oldassignment->employee_id1!=$newassignment->employee_id1) 
{

 $emails=array();


            if ($validated['employee_id']!=1) 
            {
               $userlist1 = User::where(["id"=>$validated['employee_id']])->get();
               array_push($emails,$userlist1[0]->email);
           }

           if ($request->openemployee_id1!=1) 
           {
            $userlist2 = User::where(["id"=>$request->openemployee_id1])->get();
            array_push($emails,$userlist2[0]->email);
        }


        if ($request->openemployee_id2!=1) 
        {
         $userlist3 = User::where(["id"=>$request->openemployee_id2])->get();
         array_push($emails,$userlist3[0]->email);
     }


     if ($request->openemployee_id3!=1) 
     {
       $userlist4 = User::where(["id"=>$request->openemployee_id3])->get();
       array_push($emails,$userlist4[0]->email);

   }

   if ($request->openemployee_id4!=1) 
   {
       $userlist5 = User::where(["id"=>$request->openemployee_id4])->get();
       array_push($emails,$userlist5[0]->email);
   }


//New open assignment to freelancer

   $details = [
    'title' => "Beste ZPC-er,",
    'body1' => "Er zijn beschikbare diensten aangemeld.",
    'body2' => "Log in op het portaal om je aan te melden voor deze diensten.",
    'body3' => "Met vriendelijke groet,",
    'body4' => "",
];

\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to("mail@mijnzpc.com")->bcc($emails)->subject("Nieuwe beschikbare diensten! Dienstnummer: #".@$request->assignment_id.""));

               $Rh = new Rh;

               $function="New open assignment--Email to freelancer about new open assignment";
               $description=json_encode($details);
               $assignments_id=@$lastinsertedid;
               $invoice_id=1;
               $times_id=1;
               $agreement_id=1;


               $Rh::emaillog(json_encode($emails),$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);

   
}


 }




if ($newassignment->employee_id1==1 or $newassignment->employee_id1==null)
 {
   






if ($newassignment->employee_id==1) 
{

if ($oldassignment->employee_id1!=$newassignment->employee_id1) 
{

  $emails=array();

  $Joinclientlist = Joinclient::where(["client_id"=>$newassignment->client_id,'department_id'=>$newassignment->department_id,'registeras'=>$newassignment->registeras])->get();

  if ($Joinclientlist->isEmpty())
  {
      $Joinclientlist = Joinclient::where(["client_id"=>$newassignment->client_id,'department_id'=>1,'registeras'=>$newassignment->registeras])->get();
  }



  foreach ($Joinclientlist as $row) 
  {

      $userlist = User::where(["id"=>$row->user_id])->get();

      array_push($emails,$userlist[0]->email);

  }




     $details = [
    'title' => "Beste ZPC-er,",
    'body1' => "Er zijn beschikbare diensten aangemeld.",
    'body2' => "Log in op het portaal om je aan te melden voor deze diensten.",
    'body3' => "Met vriendelijke groet,",
    'body4' => "",
];

\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to("mail@mijnzpc.com")->bcc($emails)->subject("Nieuwe beschikbare diensten! Dienstnummer: #".@$request->assignment_id.""));

               $Rh = new Rh;

               $function="New open assignment--Email to freelancer about new open assignment after update";
               $description=json_encode($details);
               $assignments_id=@$request->assignment_id;
               $invoice_id=1;
               $times_id=1;
               $agreement_id=1;


               $Rh::emaillog(json_encode($emails),$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id); 



}




if ($oldassignment->employee_id!=$newassignment->employee_id) 
{

  $emails=array();

  $Joinclientlist = Joinclient::where(["client_id"=>$newassignment->client_id,'department_id'=>$newassignment->department_id,'registeras'=>$newassignment->registeras])->get();

  if ($Joinclientlist->isEmpty())
  {
      $Joinclientlist = Joinclient::where(["client_id"=>$newassignment->client_id,'department_id'=>1,'registeras'=>$newassignment->registeras])->get();
  }



  foreach ($Joinclientlist as $row) 
  {

      $userlist = User::where(["id"=>$row->user_id])->get();

      array_push($emails,$userlist[0]->email);

  }




     $details = [
    'title' => "Beste ZPC-er,",
    'body1' => "Er zijn beschikbare diensten aangemeld.",
    'body2' => "Log in op het portaal om je aan te melden voor deze diensten.",
    'body3' => "Met vriendelijke groet,",
    'body4' => "",
];

\Mail::send((new \App\Mail\WelcomeEmail($details))
    ->to("mail@mijnzpc.com")->bcc($emails)->subject("Nieuwe beschikbare diensten! Dienstnummer: #".@$request->assignment_id.""));

               $Rh = new Rh;

               $function="New open assignment--Email to freelancer about new open assignment after update";
               $description=json_encode($details);
               $assignments_id=@$request->assignment_id;
               $invoice_id=1;
               $times_id=1;
               $agreement_id=1;


               $Rh::emaillog(json_encode($emails),$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id); 




}

}




if ($newassignment->employee_id!=1) 
{
   
// if employee changed 
if ($oldassignment->employee_id!=$newassignment->employee_id) 
{
    
$userlist1 = User::where(["id"=>$newassignment->employee_id])->get();
                  $details = [
                    'title' => "Beste ZPC-er,",
                    'body1' => "Er zijn nieuwe diensten voor jou aangemaakt.",
                    'body2' => "Klik <a href='https://mijnzpc.com/nl/assignments/asc/-1/-1/-1/-1/-1/-1/-1/justlast?open=-1'>hier</a> om naar dienstenpagina te gaan.",
                    'body3' => "Met vriendelijke groet,",
                    'body4' => "",
                ];

                \Mail::send((new \App\Mail\WelcomeEmail($details))
                    ->to($userlist1[0]->email)->subject("Nieuwe diensten! Dienstnummers: #".@$request->assignment_id.""));

               $Rh = new Rh;

               $function="update assignment--Email to freelancer about new assignment";
               $description=$userlist1[0]->email;
               $assignments_id=@$request->assignment_id;
               $invoice_id=1;
               $times_id=1;
               $agreement_id=1;


               $Rh::emaillog($userlist1[0]->email,$function,$description,$assignments_id,$invoice_id,$times_id,$agreement_id);



}
// if employee changed 

}


 }




DB::commit();

}






return redirect()->back()->with('message', "Assignment updated successfully.");
} catch (\PDOException $e) {
    DB::rollBack();
    return redirect()->back()->withInput()->with('error', $e->getMessage());
}

}



 
 


 

 

 
 

 

public function viewAgreementPDF($language, $assignment_id)
{

    try {
        $assignment = Assignment::findOrFail($assignment_id);

        return view('dashboard.assignments.assignmentAgreementPdf')->with(["assignment" => $assignment]);
    } catch (\Exception $e) {

        dd($e);

        abort(404);
    }
}


public function exportpreAgreementPDF($language, $assignment_id)
{




  $assignments_id=$assignment_id;


  $assignmerntdata = Assignment::where("id",$assignments_id)->get();



  $agreement_id=$assignmerntdata[0]->agreement_id;
  $client_id=$assignmerntdata[0]->client_id;
  $registeras=$assignmerntdata[0]->registeras;
  $freelancer_id=$assignmerntdata[0]->employee_id;


  $joinclient = Joinclient::where(["user_id"=>$freelancer_id,"client_id"=>$client_id,'registeras'=>$registeras])->get();

  if ($joinclient->isEmpty())
  {
    // $validated['payrate'] ='';
    // $validated['client_payrate'] ='';
  }
  else
  {
    $payrate =$joinclient[0]->payrate;
    $client_payrate =$joinclient[0]->client_payrate;
}


 // echo $agreement_id;exit;

$preaggrement = Preaggrement::where("id",$agreement_id)->get();

$emploee = User::where("id",$freelancer_id)->get();
$profile = Profile::where("user_id",$freelancer_id)->get();
$addresses = Address::where(["addressable_id"=>$freelancer_id,"addressable_type"=>"App\Models\User"])->get();
$financials = Financial::where("user_id",$freelancer_id)->get();


$clientprofile = Profile::where("user_id",$client_id)->get();  
$clientaddresses = Address::where("addressable_id",$client_id)->get();

$top="";
if ($preaggrement->isEmpty() or $freelancer_id==1 )
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


    $text3=$profile[0]->company_name.", ".$addresses[0]->address." ".$addresses[0]->postcode." ".$addresses[0]->city.", met KVK-nummer  ".$profile[0]->kvk_number."  en BTW-nummer
    ".$profile[0]->btw_number."  hierbij rechtsgeldig vertegenwoordigd door haar directeur ".$profile[0]->first_name." ".$profile[0]->last_name.", hierna te noemen 'Opdrachtnemer'
    ";




    $date = $assignmerntdata[0]->start_date;
    $startdate=date("d-m-Y",strtotime($date));

    $date = $assignmerntdata[0]->end_date;
    $enddate=date("d-m-Y",strtotime($date));


    if ($assignmerntdata[0]->registeras=='healthcare') 
    {
     $payratee='   <p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Uurtarief: '.number_format($assignmerntdata[0]->payrate,2).' € </span></span></span></span></p>';
 }
 else
 {
    $payratee="";
}

$centertext='
<br>
<p><span style=\"font-size:8pt;margin-top:30px;"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Organisatie: '.$clientprofile[0]->company_name.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Locatie: '.$clientaddresses[0]->address." ".$clientaddresses[0]->address_extra." ".$clientaddresses[0]->postcode." ".$clientaddresses[0]->city.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Soort opdracht: '.$type.'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Begin: '.$startdate." ".date('H:i',$assignmerntdata[0]->time_from).'</span></span></span></span></p>


<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Einde: '.$enddate." ".date('H:i', $assignmerntdata[0]->time_to).'</span></span></span></span></p>

<p style=\"margin-left:22.7pt; margin-right:0mm\"><span style=\"font-size:8pt\"><span style=\"font-family:&quot;Arial&quot;,sans-serif\"><span style=\"color:black\"><span style=\"font-size:10.0pt\">Aantal uren: '.Rh::getduration($assignmerntdata[0]->time_from,$assignmerntdata[0]->time_to,$assignmerntdata[0]->start_date,$assignmerntdata[0]->end_date).'</span></span></span></span></p>

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


$clientprofile = Profile::where("user_id",$assignmerntdata[0]->client_id)->get();




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

<div style="width:50%;float: right;">
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">Opdrachtnemer</div><br>
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.$profile[0]->company_name.'</div><br>
<div style="width:100%;float: left;font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.$profile[0]->first_name." ".$profile[0]->last_name.'</div><br>
</div>

</div>
';

$agreementtemp=$clientsignbox;
} 


Assignment::where(['id'=>$assignments_id])
->update([
 'preagreementtext' =>$top,
]);



$assignment = Assignment::findOrFail($assignments_id);

view()->share('assignment', $assignment);

  // return view('dashboard.assignments.assignmentpreAgreementPdf')
  //                ->with([
  //                    "assignment"=>$assignment,
  //                ]);


$pdf_doc = PDF::loadView('dashboard.agreement.assignmentpreAgreementPdf', $assignment);
return $pdf_doc->stream('pdf.pdf');








}




    // Export to PDF
public function exportAgreementPDF($language, $assignment_id)
{

    try {
        $assignment = Assignment::findOrFail($assignment_id);

        view()->share('assignment', $assignment);


   //return view('dashboard.assignments.assignmentAgreementPdf')
              //   ->with([
                   //  "assignment"=>$assignment,
                // ]);



        $pdf_doc = PDF::loadView('dashboard.assignments.assignmentAgreementPdf', $assignment);
        return $pdf_doc->stream('pdf.pdf');
    } catch (\Exception $e) {

        dd($e);

        abort(404);
    }
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

private function resize($path , $sizes , $imagePath , $filename)
{
    $images['original'] = $imagePath . $filename;

    foreach($sizes as $size)
    {

        $images[$size] = $imagePath . "{$size}_" . $filename;
        Image::make($path)->resize($size, null, function ($constraint) {
            $constraint->aspectRatio();
        })->save(public_path($images[$size]));
    }
    return $images;
}













}
