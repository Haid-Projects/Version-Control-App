<?php

namespace App\Repositories;

use App\Models\User;

class MemberRepository
{
    public function search($email)
    {
        return User::query()->where('email', 'LIKE', '%' . $email . '%')->get();
    }

}
