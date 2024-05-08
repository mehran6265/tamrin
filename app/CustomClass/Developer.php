<?php
namespace App\CustomClass;
use App\Models\Defaultinvoiceconfiguration;
use App\Models\Defaultjoinconfiguration;
use App\Models\Defaultpaymentconfiguration;
use App\Models\Defaultpayrates;
use App\Models\Permission;
use App\Models\Document;
use App\Models\Rehisterases;
use App\Models\Roles_permissions;
use App\Models\Defaultsurchargeconfiguration;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\t_log;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DateTime;

use Illuminate\Support\Facades\Http;
class Developer
{


 public static function  getalldoc()
    {
    $items=Document::get();
    return $items;
    }



public static function getregiseras()
{
    $Rehisterases = Rehisterases::where(['status'=>1])->get();
    return $Rehisterases;
}



public static function getrolepermissions($role_id)
{
    $Roles_permissions = Roles_permissions::where("role_id",$role_id)->get();
    return $Roles_permissions;
}



public static function getpermissions()
{
     $data =  DB::table('permissions')->get();
     return $data;
}


public static function getpermissionsditinc()
{
     $data =  DB::table('permissions')->select('slug')->groupBy('slug')->get();
     return $data;
}




public static function getdefaultinvoiceconfiguration()
{
     $data=Defaultinvoiceconfiguration::get();
     return $data;
}
public static function getdefaultjoinconfiguration()
{
     $data= Defaultjoinconfiguration::get();
     return $data;
}
public static function getdefaultpaymentconfiguration()
{
     $data= Defaultpaymentconfiguration::get();
     return $data;
}
public static function getdefaultpayrates()
{
     $data= Defaultpayrates::get();
     return $data;
}
public static function getdefaultsurchargeconfiguration()
{
     $data= Defaultsurchargeconfiguration::get();
     return $data;
}


}
