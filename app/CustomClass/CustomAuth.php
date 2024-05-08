<?php
namespace App\CustomClass;

use App\Models\Permission;
use App\Models\Roles_permissions;
use App\Models\Users_roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\t_log;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DateTime;

use Illuminate\Support\Facades\Http;
class CustomAuth
{


public static function userpermission($permission,$page)
{
    $Users_roles = Users_roles::where("user_id",Auth::user()->id)->get();
    $role_id =$Users_roles[0]->role_id;
    $can=0;
    $Permission = Permission::where(["name"=>$permission,'page'=>$page])->get();
    $Permission_id=@$Permission[0]->id;
    $Roles_permissions = Roles_permissions::where(["role_id"=>$role_id,"permission_id"=>$Permission_id])->get();

   if (!$Roles_permissions->isEmpty()){
       $can=1;
   }
return $can;

}


 


}
