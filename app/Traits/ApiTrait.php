<?php


namespace App\Traits;


trait ApiTrait
{
    public function restErrorCodes($code)
    {
        /*400 Bad Request — Client sent an invalid request — such as lacking required request body or parameter
        401 Unauthorized — Client failed to authenticate with the server
        403 Forbidden — Client authenticated but does not have permission to access the requested resource
        404 Not Found — The requested resource does not exist
        412 Precondition Failed — One or more conditions in the request header fields evaluated to false
        500 Internal Server Error — A generic error occurred on the server
        503 Service Unavailable — The requested service is not available*/
        switch ($code) {

            case 400:
                $message = "Bad Request — Client sent an invalid request — such as lacking required request body or parameter";
                break;
            case 401:
                $message = "Unauthorized — Client failed to authenticate with the server";
                break;
            case 403:
                $message = "Forbidden — Client authenticated but does not have permission to access the requested resource";
                break;
            case 404:
                $message = "Not Found — The requested resource does not exist";
                break;
            case 409:
                $message = "Conflict — The request could not be completed due to a conflict with the current state";
                break;
            case 412:
                $message = "Precondition Failed — One or more conditions in the request header fields evaluated to false";
                break;
            case 500:
                $message = "Internal Server Error — A generic error occurred on the server";
                break;
            case 503:
                $message = "Service Unavailable — The requested service is not available";
                break;
            default:
                $message = "";
        }

        return $message;
    }
}