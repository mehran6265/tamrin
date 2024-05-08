<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\User;
use App\Traits\ApiTrait;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    use ApiTrait, NotificationTrait;


    function getDepartment($language, $id)
    {
        try {
            $department = Department::with("address")->findOrFail($id);
            return response()->json($department, 200);
        } catch (Exception $e) {
            return response()->json(null, 500);
        }
    }

    function getEmployees($language)
    {
        if (Auth::user()->hasRole('employee')) {
            return response()->json(null, 403);
        }

        try {
            $users = User::where("user_type", "EMPLOYEE")->with("profile")->get();
            return response()->json($users, 200);
        } catch (Exception $e) {
            return response()->json(null, 500);
        }
    }

    function getAssignments($language, $start_date, $last_date)
    {
        $user_role = Auth::user()->user_type;

        $query = Assignment::query();
        if ($start_date == $last_date) {
            $query = $query->whereDate("start_date", $start_date)
                ->orderBy("time_from", "asc")
                ->where("type", "ASSIGNMENT");
        } else {
            $query = $query->whereDate("start_date", ">", $start_date)
                ->where("type", "ASSIGNMENT")
                ->whereDate("start_date", "<", $last_date);
        }

        try {

            switch ($user_role) {
                case 'EMPLOYEE':
                    $assignments = $query
                        ->where("employee_id", Auth::user()->id)
                        ->where("status", "EMPLOYEE_ACCEPTED")
                        ->with(["department", "client", "employee", "employee.profile", "client.profile"])
                        ->get();
                    break;
                case 'CLIENT':
                    $assignments = $query
                        ->where("client_id", Auth::user()->id)
                        ->where("employee_id", ">", 3)
                        ->where("status", "EMPLOYEE_ACCEPTED")
                        ->with(["department", "client", "employee", "employee.profile", "client.profile"])
                        ->get();
                    break;
                case 'SCHEDULE':
                case 'FINANCIAL':
                    $assignments = $query
                        ->where("client_id", Auth::user()->client_id)
                        ->where("employee_id", ">", 3)
                        ->where("status", "EMPLOYEE_ACCEPTED")
                        ->with(["department", "client", "employee", "employee.profile", "client.profile"])
                        ->get();
                    break;
                case 'ADMIN':
                    $assignments = $query
                        ->where("status", "EMPLOYEE_ACCEPTED")
                        ->where("employee_id", ">", 3)
                        ->with(["department", "client", "employee", "employee.profile", "client.profile"])
                        ->get();
                    break;

                default:
                    $assignments = [];
                    break;
            }

            return response()->json($assignments, 200);
        } catch (Exception $e) {
            return response()->json(null, 500);
        }
    }


    function getUser($language, $id)
    {
        // john doe
        if ($user_id == 2) {
            return response()->json(null, 500);
        }

        try {
            $user = User::with(["profile", "address"])->findOrFail($id);
            return response()->json($user, 200);
        } catch (Exception $e) {
            return response()->json(null, 500);
        }
    }

    function getUserFilter(Request $request)
    {
        $user_validator = Validator::make($request->all(), [
            'user_type' => ['required', 'in:EMPLOYEE,CLIENT'],
            'query' => ['nullable', 'string', 'max:400'],
        ]);
        if ($user_validator->fails()) {
            return response()->json($request, 400);
        }
        // Retrieve the validated input...
        $user_validated = $user_validator->validated();

        $user_type = $user_validated["user_type"];
        $query_str = $user_validated["query"];

        try {
            $users = User::orWhere(function ($query) use ($user_type, $query_str) {
                $query->where("user_type", $user_type)
                    ->where('email', 'LIKE', '%' . $query_str . '%');
            })->orWhere(function ($query) use ($user_type, $query_str) {
                $query->where("user_type", $user_type)
                    ->whereHas('profile', function (Builder $query) use ($query_str) {
                        $query->where('company_name', 'like', '%' . $query_str . '%');
                    });
            })->where("employee_id", ">", 3)->with(["profile"])->take(10)->get();

            return response()->json($users, 200);
        } catch (Exception $e) {
            return response()->json(null, 500);
        }
    }


    function applyDepartment($language, $department_id, $user_id)
    {
        // john doe
        if ($user_id == 2) {
            return response()->json(null, 500);
        }

        try {
            DB::beginTransaction();
            if (Auth::user()->hasRole('employee')) {
                $user_id = Auth::user()->id;
            } else {
                $user = User::where("user_type", "EMPLOYEE")->findOrFail($user_id);
                $user_id = $user->id;
            }
            $department = Department::findOrFail($department_id);

            if (Assignment::where('employee_id', $user_id)->where('department_id', $department->id)->exists()) {
                return response()->json(null, 409);
            }

            $assignment = new Assignment();
            $assignment->client_id = $department->user_id;
            $assignment->department_id = $department->id;
            $assignment->employee_id = $user_id;
            $assignment->save();

            DB::commit();
            return response()->json(null, 204);
        } catch (Exception $e) {

            DB::rollBack();
            return response()->json(null, 500);
        }
    }

    function employeeUpdateAssignment($language, $assignment_id, $action)
    {
        try {
            DB::beginTransaction();
            $assignment = Assignment::findOrFail($assignment_id);
            // 'PENDING', 'EMPLOYEE_ACCEPTED', 'EMPLOYEE_REJECTED', 'CLIENT_CANCELED', 
            // 'EMPLOYEE_CANCELED', 'CLIENT_ACCEPTED'
            $assignment->status = $action;
            $assignment->save();

            DB::commit();
            return response()->json(null, 204);
        } catch (Exception $e) {

            DB::rollBack();
            return response()->json(null, 500);
        }
    }


    function employeeSendApplicationForAssignment($language, $assignment_id)
    {
        try {
            DB::beginTransaction();

            $assignment_exists = Assignment::whereId($assignment_id)->exists();

            if (!$assignment_exists) {
                return response()->json(null, 404);
            }

            $application_exists = Application::where("assignment_id", $assignment_id)->where("employee_id", Auth::user()->id)->exists();
            if ($application_exists) {
                return response()->json(null, 409);
            }

            $application = new Application();
            $application->employee_id = Auth::user()->id;
            $application->assignment_id = $assignment_id;
            $application->save();

            DB::commit();
            return response()->json(null, 204);
        } catch (Exception $e) {

            DB::rollBack();
            return response()->json(null, 500);
        }
    }
}
