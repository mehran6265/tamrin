<?php
namespace App\CustomClass;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AppRh
{

	public static function  checknullinpurs($array)
	{
		foreach ($array as $k => $v) 
		{
			$_POST[$k]=strip_tags($_POST[$k]);
			if (empty($v))
			{
				$result = json_encode(array('data'=>array('err'=>-1,"msg"=>$k." cant be null",'status'=>'fail'))); echo $result; exit;   
			}
		}
	}


	public static function  checkuser($token)
	{
     $User =  User::where("token",$token)->get();

     if ($User->isEmpty())
     {
      $result = json_encode(array('data'=>array('msg'=>"user does not exist"),'status'=>'fail')); echo $result; exit;
     }
     else
     {
     	return $User[0]; 
     }



	}





}
