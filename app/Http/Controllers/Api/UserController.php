<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;  

class UserController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User
     */
    public function login(Request $request)
    {

    
try {
            $validateUser = Validator::make($request->all(),
            [
                'avatar'=>'required',
                'type'=>'required',
                'name' => 'required',
                'email' => 'required',
                'open_id' => 'required',
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $validated = $validateUser->validated();
            $map = [];
            $map['type']=$validated['type'];
            $map['open_id'] = $validated['open_id'];
            $user = User::where($map)->first();

            if(empty($user->id)){
                $validated['token'] = md5(uniqid().rand(10000, 99999));
                $validated['created_at']=Carbon::now();
                $userID = User::insertGetId($validated);
                $userInfo = User::where('id', '=', $userID)->first();
                $accessToken = $userInfo->createToken(uniqid())->plainTextToken;
                $userInfo->access_token = $accessToken;
                User::where('id', '=', $userID)->update(['access_token'=>$accessToken]);

                return response()->json([
                    'code' => 200,
                    'msg' => 'User Created Successfully',
                    'data' => $userInfo
                ], 200);

            }
            $accessToken = $user->createToken(uniqid())->plainTextToken;
            $user->access_token = $accessToken;
            User::where('open_id', '=', $validated['open_id'])->update(['access_token'=>$accessToken]);
            return response()->json([
                'code' => 200,
                'msg' => 'User logged in Successfully',
                'data' => $user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function updateAvatar(Request $request)
    {
        try {
            
            $validateAvatar = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
    
            
            if ($validateAvatar->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateAvatar->errors()
                ], 400);
            }
    
            
            $user = Auth::user(); 
    
            
            if ($request->hasFile('avatar')) {
                
                $avatarDir = 'images'; 
    
                
                $avatarPath = $request->file('avatar')->storeAs($avatarDir, uniqid() . '.' . $request->file('avatar')->getClientOriginalExtension(), 'admin');
                
                
                $user->avatar = $avatarPath; 
                $user->save();
    
                
                return response()->json([
                    'status' => true,
                    'message' => 'Avatar updated successfully',
                    'data' => ['avatar' => $user->avatar]
                ], 200);
            }
    
        
            return response()->json([
                'status' => false,
                'message' => 'Avatar file not found'
            ], 400);
    
        } catch (\Throwable $th) {
           
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function updatePassword(Request $request)
    {
        try {
            // Validate đầu vào
            $validateUpdate = Validator::make($request->all(), [
                'open_id' => 'required|string', // Firebase UID của người dùng
                'password' => 'required|string|min:6|confirmed',
            ]);
    
            if ($validateUpdate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUpdate->errors(),
                ], 400);
            }
    
            $firebaseCredentialsPath = storage_path('firebase/study-app-dab6f-firebase-adminsdk-pi8mm-53c23d4c38.json');
    
            
            $firebase = (new Factory)
                ->withServiceAccount($firebaseCredentialsPath);
    
            
            $auth = $firebase->createAuth();
    
           
            $openId = $request->input('open_id');
    
            try {
                
                $userRecord = $auth->getUser($openId);
            } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found in Firebase',
                ], 404);
            }
    
            $auth->changeUserPassword($openId, $request->input('password'));
    
            return response()->json([
                'code' => 200,
                'msg' => 'Password updated successfully',
                'data' => [
                    'uid' => $userRecord->uid,
                    'email' => $userRecord->email,
                ],
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    
}
