<?php

namespace App\Repositories;

use App\Models\File;

class FileRepository
{
    public function find($id){
        return File::find($id);
    }

    public function create($data, $fileName){
        return File::create([
            'label' => $data["label"],
            'user_id' => auth('user')->user()->id(),
            'path' =>$fileName,
        ]);
    }

    public function update($data){
        return File::update($data);
    }

    public function delete($id){
        return File::destroy($id);
    }

    public function index(){
        return File::all();
    }

    public function createdFiles($user_id){
        return File::where('user_id', '=', $user_id)->get();
    }


}
