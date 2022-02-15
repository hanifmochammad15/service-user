<?php
namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Vendor\econea\nusoap\src\nusoap;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Validator as Enter;

class AuthController extends Controller
{
    public function register(Request $request)
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
        //  $query = \DB::getQueryLog();
        //  dd(end($query));
        //$token = $user->createToken('auth_token')->plainTextToken;

        // return response()
        //     ->json(['data' => $user,'access_token' => $token, 'token_type' => 'Bearer', ]);
        $user = User::Where('username', '=',$request->username)->first(); //check username first //and get data

        return ResponseFormatter::success($user,'success to register');

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            //'email' => 'required|string|email|max:255|unique:users',
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:8'
        ]);

        if($validator->fails()){

            return ResponseFormatter::error($validator->errors(),"Validation Errors",400);

        }

        $non_bri = ($request['non_bri']) ? TRUE : FALSE;

        $user = User::where('email', $request['email'])->orWhere('username', '=', $request['email'])->first(); //check username first //and get data

        if (!isset($user)){
            return ResponseFormatter::error(null,'data not found',404);
        }
        if (!$non_bri){//if non bri false berarti bri
            $username = str_pad($request['email'], 8, '0', STR_PAD_LEFT);

            $client = new \nusoap_client("http://wsuser.bri.co.id/beranda/ldap/ws/ws_adUser.php?wsdl", true);
            $param 		= array('ldap_user'=>$username,'ldap_pass'=>$request['password']);
            $first  	= time();
            $result 	= $client->call('validate_aduser', $param);
            $second 	= time();
            if(!$result)
                return ResponseFormatter::error(null,'Unauthorized',401);

        }else{ //non bri masuk sini
            $username = $request['email'];
            $is_email = strpos($username, '@');
			if (!$is_email)
				$username = str_pad($username, 4, '0', STR_PAD_LEFT);
            $login = array();
            $login['email'] = $username;
            $login['password'] = $request['password'];
            if($user->password == md5($request['password'])){
                //klau mau convert ke bycrpt
                //$user->password = Hash::make($request['password']); // Convert to new format
                //$user->save();
                $result = true;

            }
            else if (!Auth::attempt($login)){
                return ResponseFormatter::error(null,'Unauthorized',401);
            }
            $result = true;
        }
        if ($result){
            //$token = $user->createToken('auth_token')->plainTextToken;
            if(!$non_bri)
                $username = $result;
            else
                $username = $user->username;

            //return response()
            //    ->json(['message' => 'Hi '.$username.', welcome to home','access_token' => $token, 'token_type' => 'Bearer', ]);
            $data['username'] = $user->username;
            // $data['id'] = $user->id;
            $data['choose_level'] = 0;
            // $data['access_token'] = $token;
            // $data['token_type'] = 'Bearer';
            $data['list_level'] = User::getLevelId($user->username);

            //dd($data);
            return ResponseFormatter::success($data,'Hi '.$username.', Pilih Level ID Anda');

        }
        return ResponseFormatter::error(null,'Unauthorized',401);
    }
    // method for user logout and delete token

    public function choose_level(Request $request){
        $validator = Validator::make($request->all(),[
            //'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255',
            'level_id' => 'required|integer|max:99',
        ]);

        if($validator->fails()){
            return ResponseFormatter::error($validator->errors(),"Validation Errors",400);
        }

        $user = User::Where('username', '=',$request->username)
        ->where('level_id', '=',$request->level_id)
        ->first(); //check username first //and get data
        $user['choose_level']=1;
        //where('email', $request['email'])->orWhere('username', '=', $request['email'])->first(); //check username first //and get data
        return ResponseFormatter::success($user,'Hi '.$user->username.', Welcome Home');

    }

    public function logout(Request $request)
    {
        $user_id=$request->user_id;
        $user=User::where("id",$user_id);
        if (!$user->exists()) {
            return ResponseFormatter::error(null,"your user not found",400);
        }
        $refreshToken=RefreshToken::where("user_id",$user_id)->first();
        $refreshToken->delete();

        return ResponseFormatter::success(null,'You have successfully logged out and the token was successfully deleted');
    }

    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'refresh_token' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        if($validator->fails()){
            return ResponseFormatter::error($validator->errors(),"Validation error",400);
        }
        $data["token"]=$request->refresh_token;
        $data["user_id"]=$request->user_id;
        $refreshToken=RefreshToken::updateOrCreate(["user_id" => $data["user_id"]],$data);
        return ResponseFormatter::success($refreshToken->id,'success create refresh token');
    }

    public function getRefreshToken(Request $request)
    {
        $refreshToken=$request->get('refresh_token');
        $token=RefreshToken::where("token",$refreshToken);
        if(!$token->exists()){
            return ResponseFormatter::error(null,"your token not found",400);
        }
        return ResponseFormatter::success($token->first(),'success create refresh token');

    }
}
