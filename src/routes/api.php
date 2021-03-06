<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// API untuk cek data email dan password yang diterima apakah terdaftar dalam tabel user
// jika terdaftar maka kembalikan nilai is login true dan user id = id user terdaftar
// jike tidak terdafar maka kembalikan nilai is login false dan user id kosong
Route::post("login",function(Request $request){
    $is_login = false;
    $user_id = "";

    if(\Auth::attempt($request->only("email","password"))){
        $is_login = true;
        $user_id = \Auth::user()->id;
    }

    return response()->json([
        "is_login" => $is_login,
        "user_id" => $user_id
    ]);
});

// Ambil data kelas yang terdaftar atas user yang sedang login berdasarkan user id
Route::post("kelas",function(Request $request){
    $kelas = \App\Models\User::find($request->user_id)->mahasiswakelas()->get();

    // return response()->json([
    //     "kelas" => $kelas
    // ]);
    return \App\Http\Resources\KelasResource::collection($kelas);
});

// Ambil detail data absensi untuk setiap pertemuannya berdasarkan kelas id dan user id
Route::post("kelasdetail",function(Request $request){
    $kelasdetail = \App\Models\Pertemuan::with(["absensi" => function($query) use($request){
        $query->where("mahasiswa_id",$request->user_id);
    }])->where("kelas_id",$request->kelas_id)->get();

    // return response()->json([
    //     "kelasdetail" => $kelasdetail
    // ]);
    return \App\Http\Resources\KelasDetailResource::collection($kelasdetail);
});