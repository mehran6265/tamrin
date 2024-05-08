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
class AllneedController extends Controller
{


public function userinfo()
{
 header('Content-Type: application/json');
 $user=AppRh::checkuser($_GET['token']);
 $profile=Profile::where("user_id",(int)$_GET['id'])->get();
 $result = json_encode(array('data'=>array('profile'=>$profile),'status'=>'success')); echo $result; exit;
}







}
