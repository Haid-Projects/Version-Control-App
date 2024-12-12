<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\File_User;
use App\Models\Group;
use App\Models\Member;
use App\Services\FileService;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as Files;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ZipArchive;


class FileController extends Controller
{
    use GeneralTrait;

    protected $fileService;
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {
        $files=File::all();
        return $this->returnSuccessData($files, 'all files', 200);
    }

    public function store(Request $request)
    {
        $file = $this->fileService->create($request->all());
        return $this->returnSuccessData($file, 'file created successfully', 200);
    }

    public function update(Request $request, $file_id)
    {
        return $this->fileService->update($request->all(), $file_id);
    }

    public function destroy($file_id)
    {
       return $this->fileService->delete($file_id);
    }

    public function check_in_single(Request $request)
    {
        return $this->fileService->checkIn($request->all());
    }

    public function check_in(Request $request)
    {
        return $this->fileService->checkInBulk($request->all());
    }

    public function check_out(Request $request)
    {
        return $this->fileService->checkOut($request->all());
    }

    public function content(Request $request){
        $file_id = $request->query('file_id');
        $content = $this->fileService->getContent($file_id);
        return response()->json(['data' => $content]);
    }


    public function upload_with_file(Request $request)
    {
        return $this->fileService->uploadFile($request->all());
    }

    public function upload_with_txt(Request $request)
    {
        return $this->fileService->uploadContent($request->all());
    }


    public function downloadFile(Request $request)
    {
        $file_id = $request->query('file_id');
        $file = File::find($file_id);

        $headers = [
            'Content-Type' => 'application/*',
        ];
        $path = public_path() . '/storage/file';
        return Response::download($path . $file->path);

    }




    public function show_own_file(Request $request)
    {
        $user_id=Auth::guard('user')->user()->id;
        $fileQuery=File::query();

        if($user_id)
        {$fileQuery->where('user_id',"=",$user_id)
            ->where('group_id',"=",null);}

        $files=$fileQuery->get();

        if(isset($files)) {
            return $this->returnSuccessData($files, "Success", 200);
        }
        return $this->returnError("Error not found",404);
    }


    public function show_files(Request $request)
    {

        $user_id=Auth::guard('user')->user()->id;
        $group_id=$request->query('group_id');
        $Member=Member::query()->where('group_id',"=",$group_id)
            ->where('user_id',"=",$user_id)->get()->first();
        if(!isset($Member)){
            return $this->returnError("not allowed",404);
        }
        $fileQuery=File::query()->with('user');
        if($group_id)
        {$fileQuery->where('group_id',"=",$group_id);}
        $files=$fileQuery->get();

        if(isset($files)) {
            return $this->returnSuccessData($files, "Success", 200);
        }
        return $this->returnError("Error not found",404);
    }


    public function add_to_group(Request $request,)
    {
        $validator = Validator::make($request->all(), [
            'label'=>"required",
            'path'=>"required|file",
            'group_id'=>"required"
        ]);

        if($validator->fails()) {
            return  $this->returnValidationError($validator->errors()->first(),400);
        }

        $user = Auth::guard('user')->user();
        $path=$request->file('path');
        $nameofpath=time().'.'.$path->getClientOriginalExtension();
        $destinationpath=public_path('storage/file');
        $path->move($destinationpath,$nameofpath);

        $group = Group::find($request->group_id);
        $file = File::create([
            'label' => $request->label,
            'user_id' => $user->id,
            'path' =>$nameofpath,
            'group_id'=>$group->id

        ]);
        return $this->returnSuccessData($file, 'file created successfully', 200);
    }



}
