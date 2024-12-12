<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\File_User;
use App\Models\Group;
use App\Models\Member;
use App\Models\JoinRequest;
use App\Models\User;
use App\Services\MemberService;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MembersController extends Controller
{
    use GeneralTrait;

    public function __construct(protected MemberService $memberService)
    {
    }

    public function store(Request $request, $group_id)
    {
        return $this->memberService->sendJoinRequest($request->all(), $group_id);
    }

    public function accept_join_request(Request $request)
    {
        return $this->memberService->acceptJoinRequest($request->all());
    }

    public function reject_join_request(Request $request)
    {
        return $this->memberService->rejectJoinRequest($request->all());
    }

    public function show()
    {
        return $this->memberService->getJoinRequests();
    }


    public function show_group_users(Request $request, $group_id)
    {
       return $this->memberService->getGroupUsers($group_id);
    }

    public function delete_group_member(Request $request, $group_id)
    {
        return $this->memberService->deleteGroupMember($request->all(), $group_id);
    }

    public function search_user(Request $request)
    {
        return $this->memberService->searchUser($request->all());
    }


}
