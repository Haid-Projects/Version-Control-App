<?php

namespace App\Services;

use App\Models\File;
use App\Models\File_User;
use App\Models\Group;
use App\Models\JoinRequest;
use App\Models\Member;
use App\Models\User;
use App\Repositories\MemberRepository;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MemberService
{
    use GeneralTrait;
    protected $memberRepository;

    public function __construct(MemberRepository $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    public function sendJoinRequest(array $data, $group_id)
    {
        $validator = Validator::make($data, [
            'email' => "required",
        ]);

        if ($validator->fails())
            return $this->returnValidationError($validator->errors()->first(), 400);

        $user = auth('user')->user();
        $group = Group::find($group_id);

        $receiver = User::query()->where('email','=',$data["email"])->get()->first();
        if(!isset($receiver))
            return $this->returnError("email not found", 404);

        $member = Member::query()->where('user_id','=',$receiver->id)->get()->first();
        if(isset($member))
            return $this->returnError("user already joined", 404);

        $check = JoinRequest::query()->where('receiver_id','=',$receiver->id)->get()->first();
        if(isset($check))
            return $this->returnError("Join request already send", 404);

        if (isset($group))
        {
            if ($group->user_id === $user->id)
            {
                $join_request= JoinRequest::create([
                    'sender_id'=>$user->id,
                    'receiver_id'=>$receiver->id,
                    'group_id'=>$group_id,
                ]);
                return $this->returnSuccessData($join_request, 'join requested successfully', 200);
            }
            return $this->returnError('you can not add to this group', 400);
        }
        return $this->returnError("Error not found", 404);
    }

    public function acceptJoinRequest(array $data)
    {
        $user = auth('user')->user();
        $join_request = JoinRequest::find($data["join_request"]);
        if(isset($join_request))
        {
            $group=Member::create([
                'user_id'=>$user->id,
                'group_id'=>$join_request->group_id
            ]);
            $join_request->delete();
            return $this->returnSuccessData($group, 'accepted successfully', 200);
        }
        return $this->returnError("some thing went wrong", 404);
    }

    public function rejectJoinRequest(array $data)
    {
//        $user = auth('user')->user();
        $join_request=JoinRequest::find($data["join_request"]);
        if(isset($join_request))
        {
            $join_request->delete();
            return $this->returnSuccessData(null, 'rejected successfully', 200);
        }
        return $this->returnError("some thing went wrong", 404);
    }

    public function getJoinRequests()
    {
        $id = auth('user')->user()->id;
        $user = User::find($id);
        return $this->returnSuccessData($user->join_requests()->get(), "Success", 200);
    }

    public function getGroupUsers($group_id)
    {
        $group = Group::where("id", "=", $group_id)->first();
        $users = $group->users;
        if(isset($group))
            return $this->returnSuccessData($users, "Success", 200);
        return $this->returnError("Error not found",404);
    }

    public function deleteGroupMember(array $data, $group_id)
    {
        $group = Group::find($group_id);
        $user = auth('user')->user();
        if (isset($group))
        {
            if ($group->user_id == $user->id)
            {
                $files=File::query()->where('group_id','=',$group->id)->get();
                foreach ($files as $file){
                    if($file->reservation_holder == $data["deleted_user"]){
                        $file->update([
                            'isAvailable' => 1,
                            'reservation_holder' => null,
                        ]);
                        $file_user = File_User::query()->where('file_id', '=', $file->id)
                            ->where('user_id', '=', $data["deleted_user"])
                            ->whereNull('check_out')->get();
                        $file_user->update([
                            'check_out' => Carbon::now()
                        ]);
                    }
                }
                Member::query()->where('user_id','=',$data["deleted_user"])
                    ->where('group_id','=',$group->id)->delete();
                return $this->returnSuccessData('', 'user deleted successfully', 200);
            }
            return $this->returnError('you dont have authorization on this group', 400);
        }
        return $this->returnError("Error not found",404);
    }

    public function searchUser(array $data)
    {
        return $this->memberRepository->search($data["email"]);
    }

}
