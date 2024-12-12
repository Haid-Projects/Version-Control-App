<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Member;
use App\Models\User;
use App\Services\GroupService;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    use GeneralTrait;
    protected $groupService;

    public function __construct(GroupService $groupService){
        $this->groupService = $groupService;
    }

    public function index()
    {
        $groups = $this->groupService->index();
        return $this->returnSuccessData($groups, 'all group', 200);
    }

    public function store(Request $request)
    {
        $group = $this->groupService->create($request->all());
        return $this->returnSuccessData($group, 'group created successfully', 200);
    }

    public function update(Request $request, $group_id)
    {
        return $this->groupService->update($request->all(), $group_id);
    }

    public function destroy($group_id)
    {
        return $this->groupService->delete($group_id);
    }

    public function getInfo($group_id){
        return $this->groupService->find($group_id);
    }

    public function show_my_own_group()
    {
        return $this->groupService->getCreatedGroups();
    }

    public function show_my_join_group()
    {
        $groups = $this->groupService->getJoinedGroups();
        if(isset($groups))
            return $this->returnSuccessData($groups, "Success", 200);
        return $this->returnError("Error not found",404);
    }

}
