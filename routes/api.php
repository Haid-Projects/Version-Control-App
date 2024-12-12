<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuth;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MembersController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('admin/register', [UserAuth::class, 'register']);
Route::post('admin/login', [UserAuth::class, 'login']);
Route::get('admin/logout', [UserAuth::class, 'login'])->middleware('auth.guard:admin');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/show',[GroupController::class,'show']);

Route::post('user/register', [UserAuth::class, 'register']);
Route::post('user/login', [UserAuth::class, 'login'])->middleware('logger');
Route::get('user/logout', [UserAuth::class, 'logout'])->middleware('auth.guard:user');


//Managing Group Routes
Route::group(['prefix' => 'user','middleware' => ['auth.guard:user']],function () {
    Route::get('/index', [GroupController::class, 'index']);
    Route::post('/create_group', [GroupController::class, 'store']);
    Route::post('/update_group/{group_id}', [GroupController::class, 'update'])->middleware('check_group_ownership:');
    Route::delete('/delete_group/{group_id}', [GroupController::class, 'destroy'])->middleware('check_group_ownership:');

    Route::get('/show_group_users/{group_id}', [MembersController::class, 'show_group_users'])->middleware('check_group_membership');
    Route::get('/getInfo/{group_id}', [GroupController::class, 'getInfo'])->middleware('check_group_membership');
    Route::get('/show_my_own_group', [GroupController::class, 'show_my_own_group']);
    Route::get('/show_my_join_group', [GroupController::class, 'show_my_join_group']);
});


//Managing Group MemberShip Routes

Route::group(['prefix' => 'user','middleware' => ['auth.guard:user']],function () {
    Route::post('/add_to_group/{group_id}', [MembersController::class, 'store'])->middleware('check_group_ownership');
    Route::delete('/delete_group_member/{group_id}', [MembersController::class, 'delete_group_member'])->middleware('check_group_ownership');
    Route::post('/accept_join_request', [MembersController::class, 'accept_join_request']);
    Route::post('/reject_join_request', [MembersController::class, 'reject_join_request']);
    Route::get('/show_join_request', [MembersController::class, 'show']);

    Route::get('/search_user', [MembersController::class, 'search_user']);
});

//Managing File Routes
Route::group(['prefix' => 'user','middleware' => ['auth.guard:user']],function () {
    Route::get('fileContent', [FileController::class, 'content']);
    Route::post('/create_file', [FileController::class, 'store']);
    Route::post('/update_file/{id}', [FileController::class, 'update']);
    Route::get('/show_own_file',[FileController::class,'show_own_file']);
    Route::get('/show_files',[FileController::class,'show_files']);
    Route::delete('/delete_file/{id}', [FileController::class, 'destroy']);
    Route::post('/add_file', [FileController::class, 'add_to_group']);
    Route::post('/check_in_single', [FileController::class, 'check_in_single'])->middleware(['logger', 'transactional']);
    Route::post('/check_in', [FileController::class, 'check_in'])->middleware(['logger', 'transactional']);
    Route::post('/check_out', [FileController::class, 'check_out'])->middleware(['logger', 'transactional']);
    Route::post('/upload_with_file', [FileController::class, 'upload_with_file'])->middleware(['logger', 'transactional']);
    Route::post('/upload_with_txt', [FileController::class, 'upload_with_txt'])->middleware(['logger', 'transactional']);

    Route::get('/show_file_report', [\App\Http\Controllers\ReportController::class, 'fileReport']);
    Route::get('/show_user_report', [\App\Http\Controllers\ReportController::class, 'userReport']);
});











Route::get('file', function () {
    $myfile = fopen("D:file.txt", "r") or die("Unable to open file!");
    $content = fread($myfile,filesize("D:file.txt"));
    fclose($myfile);
    return response()->json(['data' => $content]);
});
Route::get('f', function (Request $request) {
    //if($request->query('label'))
    if(file_exists("D:" . $request->query('label')))
        return response()->file("D:".$request->query('label'));
    return response()->file('D:readme.txt');
});

Route::post('fileList', function (Request $request) {
    $user = \App\Models\User::create([
        'name' => 'user1',
        'email' => 'user1@gmail.com',
        'password' => '111'
    ]);
    return '0';
    $list = $request->list;
    foreach($list as $l){
        echo $l["label"];
    }
});

Route::post('t/{count}', function (Request $request,$count) {

    for($i=1;$i<=$count;$i++){
        if($request["checked_files".$i])
         echo $request["checked_files".$i];
    }
});
Route::post('test', function (Request $request) {
   // return $request->json["age"];
    return "test";
});

Route::get('download', [FileController::class ,'downloadFile']);


