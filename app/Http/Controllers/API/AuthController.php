<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use App\Models\User;
use Vendor\econea\nusoap\src\nusoap;
use App\Helpers\ResponseFormatter;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            //'email' => 'required|string|email|max:255|unique:users',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if($validator->fails()){

            return ResponseFormatter::error(null,$validator->errors(),400);

        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
         ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json(['data' => $user,'access_token' => $token, 'token_type' => 'Bearer', ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            //'email' => 'required|string|email|max:255|unique:users',
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:8'
        ]);

        if($validator->fails()){

            return ResponseFormatter::error(null,$validator->errors(),400);

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
            $token = $user->createToken('auth_token')->plainTextToken;
            if(!$non_bri)
                $username = $result;
            else
                $username = $user->name;

            //return response()
            //    ->json(['message' => 'Hi '.$username.', welcome to home','access_token' => $token, 'token_type' => 'Bearer', ]);
            $user['access_token'] = $token;
            $user['token_type'] = 'Bearer';

            return ResponseFormatter::success($user,'Hi '.$username.', welcome to home');

        }
        return ResponseFormatter::error(null,'Unauthorized',401);
    }
    // method for user logout and delete token
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'You have successfully logged out and the token was successfully deleted'
        ];
    }
}
