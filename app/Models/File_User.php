<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File_User extends Model
{
    use HasFactory;

    protected $table = "file_users";

    protected $fillable = [
        'check_in',
        'check_out',
        'user_id',
        'file_id',
    ];

}
