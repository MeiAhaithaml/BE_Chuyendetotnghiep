<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;
use App\Http\Controllers\Controller;
class AuthController extends Controller
{
    public function signInAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|min:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        // Tìm kiếm admin theo username
        $admin = AdminUser::where('username', $request->username)->first();
        
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'code' => 401,
                'msg' => 'Invalid credentials',
                'data' => null
            ], 401);
        }
    
        // Kiểm tra nếu admin đã có token
        if ($admin->token) {
            // Trả về token đã có sẵn và thông tin admin
            return response()->json([
                'code' => 200,
                'msg' => 'Admin logged in successfully',
                'data' => [
                    'access_token' => $admin->token,
                    'token' => $admin->token,
                    'name' => $admin->name,
                    'description' => $admin->description,
                    'avatar' => $admin->avatar,
                    // 'online' => $admin->online,  // Nếu có thông tin này trong DB
                    // 'type' => $admin->type,      // Nếu có thông tin này trong DB
                ],
            ], 200);
        } else {
            // Nếu không có token, tạo token mới
            $token = $admin->createToken('AdminApp')->plainTextToken;
            
            // Cập nhật token vào bảng admin_users
            $admin->token = $token;
            $admin->save();
            
            return response()->json([
                'code' => 200,
                'msg' => 'Admin logged in successfully',
                'data' => [
                    'access_token' => $token,
                    'token' => $token,
                    'name' => $admin->name,
                    'description' => $admin->description,
                    'avatar' => $admin->avatar,
                    // 'online' => $admin->online,  // Nếu có thông tin này trong DB
                    // 'type' => $admin->type,      // Nếu có thông tin này trong DB
                ],
            ], 200);
        }
    }
    
}    