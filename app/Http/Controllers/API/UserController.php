<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\User;
use App\Helpers\ResponseFormatter;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$data = User::latest()->get();
        $data = User::get();

        return ResponseFormatter::success($data,'User fetched.');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'username' => 'required|string|max:255', //pn
            //'email' => 'required|string|email|max:255|unique:users',
            'email' => 'required|string|max:100',
            'password' => 'required|string|min:8',
            'nama_lengkap' => 'required|string|max:50',
            'jabatan' => 'required|string|max:50',
            'telepon' => 'nullable|string|max:15',
            'uker_main' => 'required|string|max:50',
            'uker_branch' => 'string|max:30',
            'uker' => 'string|max:40',
            'level_id' => 'required|integer|max:99',
            'ip' => 'nullable|string|max:100',

        ]);

        if($validator->fails()){

            return ResponseFormatter::error(null,$validator->errors(),400);

        }

        //\DB::enableQueryLog();
        $user = User::create([
            'username' => $request->username, //atau pn
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan' => $request->jabatan,
            'telepon' => $request->telepon,
            'uker_main' => $request->uker_main,
            'uker_branch' => $request->uker_branch,
            'uker' => $request->uker,
            'level_id' => $request->level_id,
            'active_status' => 1,
            'ip' => $request->ip,

         ]);

        $user = User::Where('username', '=',$request->username)->first(); //check username first //and get data

        return ResponseFormatter::success($user,'user saved successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return ResponseFormatter::error(null,'data not found',404);
        }
        return ResponseFormatter::success($user,null);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(),[
            'username' => 'required|string|max:255', //pn
            //'email' => 'required|string|email|max:255|unique:users',
            'email' => 'required|string|max:100',
            'password' => 'required|string|min:8',
            'nama_lengkap' => 'required|string|max:50',
            'jabatan' => 'required|string|max:50',
            'telepon' => 'nullable|string|max:15',
            'uker_main' => 'required|string|max:50',
            'uker_branch' => 'string|max:30',
            'uker' => 'string|max:40',
            'level_id' => 'required|integer|max:99',
            'ip' => 'nullable|string|max:100',

        ]);

        if($validator->fails()){
            return ResponseFormatter::error(null,$validator->errors(),400);
        }

        $user->username = $request->username; //atau pn
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->nama_lengkap = $request->nama_lengkap;
        $user->jabatan = $request->jabatan;
        $user->telepon =$request->telepon;
        $user->uker_main = $request->uker_main;
        $user->uker_branch = $request->uker_branch;
        $user->uker =$request->uker;
        $user->level_id = $request->level_id;
        $user->active_status = 1;
        $user->ip = $request->ip;
        $user->save();

        return ResponseFormatter::success($user,'user updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return ResponseFormatter::success(null,$user->username.' deleted successfully');

    }
}
