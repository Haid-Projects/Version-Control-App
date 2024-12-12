<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Member;
use App\Repositories\GroupRepository;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupService
{
    use GeneralTrait;
    protected $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function create(array $data){
        $validator = Validator::make($data, [
            'label'=>'required',
        ]);

        if($validator->fails()) {
            return  $this->returnValidationError($validator->errors()->first(),400);
        }

        $group = $this->groupRepository->create($data, auth('user')->user()->id);

        Member::create([
            'user_id'=>auth('user')->user()->id,
            'group_id'=>$group->id
        ]);
        return $group;
    }

    public function update(array $data, $id){
        $group = $this->groupRepository->find($id);
        $user = auth('user')->user();
        $group->update($data);
        return $this->returnSuccessData($group, 'group updated successfully', 200);

    }

    public function delete($id){
        $group = $this->groupRepository->find($id);
        $group->delete();
        return $this->returnSuccessData($group, 'group deleted successfully', 200);
    }

    public function index()
    {
        return $this->groupRepository->index();
    }

    public function find($id)
    {
        $group = $this->groupRepository->find($id);
        return $this->returnSuccessData($group, "INfo", 200);
    }

    public function getCreatedGroups()
    {
        $groups = $this->groupRepository->createdGroups(auth('user')->user()->id);
        return $this->returnSuccessData($groups, "Success", 200);
    }

    public function getJoinedGroups()
    {
        $user=Auth::guard('user')->user();
        return $groups = Member::where('user_id', '=', $user->id)->get();
    }
}
