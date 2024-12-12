<?php

namespace App\Http\Controllers\Auth;

use App\Aspects\Logger;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Validator;

#[Logger]
class AdminAuth extends Controller
{
    use GeneralTrait;

    public function register(Request $request){
        $admin = Admin::create([
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        return $admin;
    }
    /**
     * Login
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()) {
            return  $this->returnValidationError($validator->errors()->first(),400);
        }

        //$credentials = $request->only(['email', 'password']);

        $admin = Admin::where('email', $request->email)->first();

        if(isset($admin)){
            if(Hash::check($request->password, $admin->password)){
                //create token
                $token = $admin->createToken('admin_token')->plainTextToken;

                //response
                return response()->json([
                    'status' => 1,
                    'message' => 'admin logged in successfully',
                    'data' => $admin,
                    'token' => $token
                ]);
            }else{
                return response()->json([
                    'status' => 0,
                    'message' => 'password did not match',
                ]);
            }
        }
        return response()->json([
            'status' => 0,
            'message' => 'admin not found',
        ]);
    }

    /**
     * Logout
     */
    public function logout(){
        Auth::guard('admin')->user()->tokens()->delete();

        return response()->json([
            'status' => 1,
            'message' => 'admin logged out successfully',
        ]);
    }

    //test
    public function test(){
        $admin = Auth::guard('admin')->user();
        return $this->returnSuccessData($admin,'message', 200);
    }
}
