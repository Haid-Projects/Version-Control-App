<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use GeneralTrait;

    public function fileReport(Request $request)
    {
        $file_user=File::find($request->file_id);
        return $this->returnSuccessData($file_user->files_user()->get(), "Success", 200);
    }

    public function userReport(Request $request)
    {
        $user = $request->user_id;
        $report = DB::table('files')
            ->join('groups', 'files.group_id', '=', 'groups.id')
            ->join('file_users', 'file_users.file_id', '=', 'files.id')
            ->where('file_users.user_id', '=', $user)
            ->select('groups.label as group', 'files.id', 'files.label as file', 'file_users.check_in', 'file_users.check_out')
            ->get();
        return $this->returnSuccessData($report, "Success", 200);
    }

    public function groupReport(Request $request)
    {

    }
}
