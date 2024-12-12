<?php

namespace App\Services;

use App\Events\GenerateBackupEvent;
use App\Models\File;
use App\Models\File_User;
use App\Models\Member;
use App\Repositories\fileRepository;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as Files;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ZipArchive;


class FileService
{
    use GeneralTrait;

    protected $fileRepository;

    public function __construct(fileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'label' => "required",
            'path' => "required|file"
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError($validator->errors()->first(), 400);
        }

        $path = $data["path"];
        $fileName = time() . '.' . $path->getClientOriginalExtension();
        $destinationPath = public_path('storage/file');
        $path->move($destinationPath, $fileName);

        return $this->fileRepository->create($data, $fileName);
    }

    public function update(array $data, $id)
    {
        $file = $this->fileRepository->find($id);
        if (isset($file)) {
            if ($file->user_id === auth('user')->user()->id) {
                $file->update([
                    'label' => $data["label"] ?? $file->label,
                ]);
                return $this->returnSuccessData($file, 'file updated successfully', 200);
            }
            return $this->returnError('you can not update this group', 400);
        }
        return $this->returnError("Error not found", 404);
    }

    public function delete($id)
    {
        $file = $this->fileRepository->find($id);
        if (isset($file)) {
            if ($file->user_id == auth('user')->user()->id) {
                if ($file->isAvailable == 1) {
                    $file_paths = config('constants.constant.file_url');
                    $file_path = $file_paths . 'storage/file/' . $file->path;  // Value is not URL but directory file path
                    if (Files::exists($file_path))
                        Files::delete($file_path);
                    $this->fileRepository->delete($id);
                    return $this->returnSuccessData($file, 'file deleted successfully', 200);
                }
                return $this->returnError('file not available', 400);
            }
            return $this->returnError('you can not delete this group', 400);
        }
        return $this->returnError("Error not found", 404);
    }

    public function index()
    {
        return $this->fileRepository->index();
    }

    public function find($id)
    {
        $file = $this->fileRepository->find($id);
        if ($file->user_id === auth('user')->user()->id)
            return $this->returnSuccessData($file, "INfo", 200);
        return $this->returnError("Unauthorized", 403);
    }

    public function checkIn(array $data)
    {
        $user = auth('user')->user();
        $file_id = $data["file_id"];
        $version = $data["version"];
        $file = $this->fileRepository->find($file_id);

        if (isset($file)) {
            if ($file->isAvailable == 1 &&$file->version==$version) {
                $file->update([
                    'isAvailable' => false,
                    'version' => $file->version + 1,
                    'reservation_holder' => $user->id
                ]);

                $report = File_User::create([
                    'check_in' => Carbon::now(),
                    'check_out' => null,
                    'user_id' => $user->id,
                    'file_id' => $file->id,

                ]);
            } else {
                throw new \Exception("The file has been requested ");
            }
            return $this->returnSuccessData($report, 'successfully', 200);
        }
        return $this->returnError("Error not found", 404);
    }

    public function checkInBulk(array $data)
    {
        try {
            $user = auth('user')->user();
            $files = $data["files"];
            $zipFileName = Str::random(10) . '.zip';
            $zipFilePath = '/storage/zip/' . $zipFileName;
            $zip = new ZipArchive();
            $zip->open(public_path($zipFilePath), ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if (isset($files)) {
                foreach ($files as $reserved_file) {
                    $file = File::find($reserved_file["id"]);
                    if (isset($file)) {
                        if ($file->isAvailable == 1 && $file->version == $reserved_file["version"]) {
                            $file_path = 'storage/file/' . $file->path;
                            $extension = pathinfo($file_path, PATHINFO_EXTENSION);
                            $zip->addFile($file_path, $file->label . '.' . $extension);
                            $file->update([
                                'isAvailable' => false,
                                'version' => $file->version + 1,
                                'reservation_holder' => $user->id
                            ]);

                            $report = File_User::create([
                                'check_in' => Carbon::now(),
                                'check_out' => null,
                                'user_id' => $user->id,
                                'file_id' => $file->id,

                            ]);
                        } else {
                            throw new \Exception("The file has been requested ");

                        }
                    } else {
                        throw new \Exception("The file not found ");
                    }
                }

                $zip->close();
                $zipFilePath2 = public_path("storage/zip/" . $zipFileName);

                if (file_exists($zipFilePath2)) {
                    return Response::download($zipFilePath2, Auth::guard('user')->user()->name . '.zip', array('Content-Type: application/zip', 'Content-Length: ' . filesize($zipFilePath2)));
                } else {
                    return $this->returnError("zip file does not exist", 404);
                }
            }
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 400);
        }
        return $this->returnError("Error not found", 404);
    }

    public function checkOut(array $data)
    {
        try {
            $user = auth('user')->user();
            $files = $data["files"];
            if (isset($files)) {
                foreach ($files as $check_out_files) {
                    $file = File::find($check_out_files);
                    if (isset($file)) {
                        if ($file->isAvailable === 0) {
                            if ($file->reservation_holder == $user->id) {
                                $file->update([
                                    'isAvailable' => 1,
                                    'reservation_holder' => null,
                                ]);
                                $file_user = File_User::query()->where('file_id', '=', $file->id)
                                    ->where('user_id', '=', $user->id)
                                    ->whereNull('check_out')->get()->first();
                                $file_user->update([
                                    'check_out' => Carbon::now()
                                ]);
                            } else {
                                throw new \Exception("error in the process check out");
                            }
                        } else {
                            throw new \Exception("error in the process check out");
                        }
                    } else {
                        throw new \Exception("error file not found");
                    }
                }
                return $this->returnSuccessData("", 'check out successfully', 200);
            }
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 400);
        }
        return $this->returnError("Error not found", 404);
    }

    public function getContent($id)
    {
        $file = $this->fileRepository->find($id);
        $myfile = fopen("storage/file/$file->path", "r") or die("Unable to open file!");
        $content = fread($myfile, filesize("storage/file/$file->path"));
        fclose($myfile);
        return $content;
    }

    public function uploadFile(array $data)
    {
        $file = $this->fileRepository->find($data["file_id"]);
        $user = auth('user')->user();
        if(isset($file))
        {
            if($file->isAvailable == 0 )
            {
                $file_path = 'storage/file/'.$file->path;
                if(Files::exists($file_path)) {
                    //make backup
                    event(new GenerateBackupEvent($file));

                    $old_type=explode('/', Files::mimeType($file_path))[0];
                    $new_type=explode('/',Files::mimeType($data["file"]))[0];
                    if($old_type!=$new_type)
                        return $this->returnError("type doesnt match",404);

                    if($new_type=="text")
                    {
                        $content_old = Files::get($file_path);
                        $content_new = Files::get($data["file"]);
                        Files::put($file_path, ' ');
                        Files::put($file_path, (string)$content_new);
                    }
                    else
                    {
                        $path = $data["file"];
                        $nameofpath = time().'.'.$path->getClientOriginalExtension();
                        $destinationpath = public_path('storage/file');
                        $path->move($destinationpath,$nameofpath);
                        Files::delete($file_path);
                        $file->update([
                            'path'=> $nameofpath
                        ]);

                    }
                    $file->update([
                        'isAvailable' => 1,
                        'reservation_holder' => null,
                    ]);
//                    $file_user = File_User::query()->where('file_id', '=', $file->id)
//                        ->where('user_id', '=', $user->id)
//                        ->whereNull('check_out')->get()->first();
//                    $file_user->update([
//                        'check_out' => Carbon::now()
//                    ]);
                    return $this->returnSuccessData("", 'updated successfully', 200);
                }
                else
                    return $this->returnError("the sever can not find this file",404);
            }
            else
                return $this->returnError("your are not allowed to update this file right now",404);
        }
        return $this->returnError("Error not found",404);
    }

    public function uploadContent(array $data)
    {
        $file = $this->fileRepository->find($data["file_id"]);
        $user = auth('user')->user();
        if(isset($file))
        {
            if($file->isAvailable == 0 && $file->reservation_holder==$user->id)
            {
                $file_path = 'storage/file/'.$file->path;
                if(Files::exists($file_path))
                {
                    $content_old =Files::get($file_path);
                    $content_new = $data["txt"];
                    Files::put($file_path, ' ');
                    Files::put($file_path,(string) $content_new);
                    $file->update([
                        'isAvailable' => 1,
                        'reservation_holder' => null,
                    ]);
                    $file_user = File_User::query()->where('file_id', '=', $file->id)
                        ->where('user_id', '=', $user->id)
                        ->whereNull('check_out')->get()->first();
                    $file_user->update([
                        'check_out' => Carbon::now()
                    ]);
                    return $this->returnSuccessData("", 'updated successfully', 200);
                }
                else
                    return $this->returnError("the sever can not find this file",404);
            }
            else
                return $this->returnError("your are not allowed to update this file right now",404);
        }
        return $this->returnError("Error not found",404);
    }


}
