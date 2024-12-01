<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
class ChatController extends Controller
{
    public function bindFcmToken(Request $request)
    {
        $userId = $request->input('user_id');
        $fcmToken = $request->input('fcm_token');
        DB::table('users')->where('id', $userId)->update(['fcm_token' => $fcmToken]);

        return $this->response(200, 'FCM token bound successfully', null);
    }

    public function sendNotification(Request $request)
{
    try {
        // Lấy dữ liệu từ request
        $callType = $request->input('call_type');
        $toToken = $request->input('to_token');
        $toAvatar = $request->input('to_avatar');
        $docId = $request->input('doc_id');
        $toName = $request->input('to_name');
        
        // Xử lý thông báo (Tạo nội dung thông báo theo callType hoặc các dữ liệu liên quan)
        $title = "New Call Notification";  // Có thể thay đổi tuỳ theo call_type hoặc dữ liệu khác
        $body = "You have a new call from $toName";

        // Gọi phương thức gửi thông báo
        $response = $this->sendFirebaseNotification($toToken, $title, $body);

        // Trả về kết quả
        return response()->json([
            'code' => 0, // Thực hiện thành công
            'message' => 'Notification sent successfully',
            'data' => $response
        ]);
    } catch (\Exception $e) {
        \Log::error('Send Notification Error:', ['error' => $e->getMessage()]);
        return response()->json([
            'code' => 1, // Thất bại
            'message' => 'Failed to send notification',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function sendFirebaseNotification($deviceToken, $title, $body)
{
    try {
        $firebaseCredentialsPath = storage_path('firebase/study-app-dab6f-firebase-adminsdk-pi8mm-53c23d4c38.json');
        $factory = (new Factory)
        ->withServiceAccount($firebaseCredentialsPath);
        $messaging = $factory->createMessaging();

        // Cấu hình thông báo
        $message = [
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'token' => $deviceToken, // Dùng token thiết bị để gửi thông báo
        ];

        // Gửi thông báo
        $response = $messaging->send($message);

        \Log::info('Firebase Notification Sent:', ['response' => $response]);

        return $response;
    } catch (\Kreait\Firebase\Exception\MessagingException $e) {
        \Log::error('Firebase Messaging Error:', ['error' => $e->getMessage()]);
        throw $e;
    } catch (\Exception $e) {
        \Log::error('General Error:', ['error' => $e->getMessage()]);
        throw $e;
    }
}

    public function getRtcToken(Request $request)
    {
        return $this->response(200, 'RTC token generated', 'generated_rtc_token');
    }

    public function sendMessage(Request $request)
    {
        $senderId = $request->input('sender_id');
        $receiverId = $request->input('receiver_id');
        $message = $request->input('message');

        DB::table('messages')->insert([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->response(200, 'Message sent', null);
    }

    public function uploadPhoto(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads/img_mess', 'public');
            $fullPath = asset('storage/' . $path);
            return $this->response(200, 'File uploaded successfully', $fullPath);
        }

        return $this->response(400, 'File upload failed', null);
    }

    public function syncMessage(Request $request)
    {
        $userId = $request->input('user_id');

        $messages = DB::table('messages')
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->get();

        return $this->response(200, 'Messages synced', $messages->toJson());
    }

   
    private function response($code, $msg, $data)
    {
        return response()->json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
}
