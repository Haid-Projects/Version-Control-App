<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class UserService
{
    use GeneralTrait;
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data){
        $validator = Validator::make($data, [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()) {
            return  $this->returnValidationError($validator->errors()->first(),400);
        }

        $user = $this->userRepository->create($data);
        return $user;
    }

    public function login(array $data){
        $validator = Validator::make($data, [
            'email' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()) {
            return  $this->returnValidationError($validator->errors()->first(),400);
        }

        $user = User::where('email', $data["email"])->first();
        if(isset($user)){
            if(Hash::check($data["password"], $user->password)){
                //create token
                $token = $user->createToken('user_token')->plainTextToken;

                //response
                return $this->returnSuccessData($token, 'user logged in successfully', 200);
            }else{
                return $this->returnError('password did not match', 400);
            }
        }
        return $this->returnError('user not found', 404);
    }

    public function logout(){
        Auth::guard('user')->user()->tokens()->delete();
    }

}
