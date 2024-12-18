<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'user_id',
        'description',
    ];

    public function users(){
        return $this->belongsToMany(User::class,'members','group_id');
    }
}
