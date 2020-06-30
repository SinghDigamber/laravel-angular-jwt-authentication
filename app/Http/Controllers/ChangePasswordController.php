<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdatePasswordRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\User;

class ChangePasswordController extends Controller {

    public function passwordResetProcess(UpdatePasswordRequest $request){
      return $this->updatePasswordRow($request)->count() > 0 ? $this->resetPassword($request) : $this->tokenNotFoundError();
    }

    // Verify if token is valid
    private function updatePasswordRow($request){
       return DB::table('recover_password')->where([
           'email' => $request->email,
           'token' => $request->passwordToken
       ]);
    }

    // Token not found response
    private function tokenNotFoundError() {
        return response()->json([
          'error' => 'Either your email or token is wrong.'
        ],Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // Reset password
    private function resetPassword($request) {
        // find email
        $userData = User::whereEmail($request->email)->first();
        // update password
        $userData->update([
          'password'=>bcrypt($request->password)
        ]);
        // remove verification data from db
        $this->updatePasswordRow($request)->delete();

        // reset password response
        return response()->json([
          'data'=>'Password has been updated.'
        ],Response::HTTP_CREATED);
    }    

}