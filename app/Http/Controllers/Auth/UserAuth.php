<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserAuth extends Controller
{
    use GeneralTrait;
    protected $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }
    /**
     * Register
     */
    public function register(Request $request){
        $user = $this->userService->register($request->all());
        return $this->login($request);
    }

    /**
     * Login
     */
    public function login(Request $request){
        return $this->userService->login($request->all());
    }

    /**
     * Get User Profile
     */
    public function profile()
    {
        $user = Auth::guard('user')->user();
        return $this->returnSuccessData($user, '', 200);
    }
    /**
     * Logout
     */
    public function logout(){
        $this->userService->logout();
        return $this->returnSuccessData(null, 'user logged out successfully', 200);
    }

    public function test(){
        $user = Auth::guard('user')->user();
        return $this->returnSuccessData($user,'message', 200);
    }

}
