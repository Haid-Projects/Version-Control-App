<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function find($id){
        return User::find($id);
    }

    public function create(array $data){
        return User::create([
            'name' => $data["name"],
            'email' => $data["email"],
            'phone_number' => $data["phone_number"],
            'address' => $data["address"],
            'birthdate' => $data["birthdate"],
            'password' => Hash::make($data["password"]),
        ]);
    }

}
