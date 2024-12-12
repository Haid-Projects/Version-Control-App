<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'path',
        'user_id',
        'isAvailable',
        'group_id',
        'version',
        'reservation_holder'
    ];
    public function user(){
        return $this->belongsTo(User::class,'user_id')->select("users.id","users.name");
    }
    public function group(){
        return $this->belongsTo(Group::class,'group_id');
    }

    public function files_user(){
        return $this->belongsToMany(User::class,'file_users')
            ->select('users.name','file_users.check_in','file_users.check_out','file_users.id')
            ->orderBy("file_users.check_in",'desc');
    }

}
