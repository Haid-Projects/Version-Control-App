<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'address',
        'birthdate',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function files(){
        return $this->hasMany(File::class,'user_id');
    }
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id');
    }

    public function groups(){
        return $this->hasMany(Group::class,'user_id');
    }
    public function user_groups(){
        return $this->belongsToMany(Group::class,'group_users','user_id');
    }
    public function user_groups_order_owner($user_id){
        return $this->belongsToMany(Group::class,'group_users','user_id')
            ->where("groups.user_id","=",$user_id)
            ->orderByRaw("groups.created_at DESC");
    }
    public function user_groups_order_joined($user_id){
        return $this->belongsToMany(Group::class,'members','user_id')
            ->where("groups.user_id","=",$user_id)
            ->orderByRaw("groups.created_at DESC");
    }

    public function receivers(){
        return $this->belongsToMany(Group::class,'join_requests','receiver_id');
    }
    public function senders(){
        return $this->belongsToMany(Group::class,'join_requests','sender_id');
    }

    public function user_files(){
        return $this->belongsToMany(File::class,'file_users');
    }


    public function join_requests(){
        return JoinRequest::query()->select( 'groups.label', 'users.name','join_requests.created_at','join_requests.id')
            ->join('groups', 'join_requests.group_id', '=', 'groups.id')
            ->join('users', 'join_requests.sender_id', '=', 'users.id')
            ->where("receiver_id",'=',$this->id)->orderBy("join_requests.created_at",'desc');


    }
}
