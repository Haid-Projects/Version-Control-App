<?php

namespace App\Repositories;

use App\Models\Group;

class GroupRepository
{
    public function find($id){
        return Group::find($id);
    }

    public function create($data, $user_id){
        return Group::create([
            'label' => $data["label"],
            'description' => $data["label"] ?? null,
            'user_id' => $user_id,
        ]);
    }

    public function update($data){
        return Group::update($data);
    }

    public function delete($id){
        return Group::destroy($id);
    }

    public function index(){
        return Group::all();
    }

    public function createdGroups($user_id){
        return Group::where('user_id', '=', $user_id)->get();
    }


}
