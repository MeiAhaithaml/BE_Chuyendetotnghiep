<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Customer;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Course;
use App\Models\Order;

class PaymentController extends Controller
{
    //
    public function checkout(Request $request){
        try{
            
        $courseId = $request->id;
        $user = $request->user();
        $token = $user->token;
        
        Stripe::setApiKey(
            "sk_test_51QDxlj2eXtQ0HOmWD7B9ERqBsRSMCzw2W6IuaAaEaWdLHrGMvczzFBtZ28YhhKW5RCa1xkmSzloDcpOzyKfL1I0k00jn7s4uhQ"
        );
        $searchCourse = Course::where('id',"=", $courseId)->first();

        if(empty($searchCourse)){
            return response()->json(
                [
                    'code'=>409,
                    'msg'=>'No course found',
                    'data'=>""
                ], 200
                );
        }

        $orderMap = [];
        $orderMap["course_id"]=$courseId;
        $orderMap["token"]=$token;
        $orderMap["status"] = 1;
        $orderRes = Order::where($orderMap)->first();
        if(!empty($orderRes)){
            return response()->json(
                [
                    'code'=>200,
                    'msg'=>'The order already exist',
                    'data'=>""
                ], 200
                );
        }
        
        if ($searchCourse->price == 0) {
            Order::create([
                'course_id' => $courseId,
                'token' => $token,
                'total_amount' => 0,
                'status' => 1, 
                'created_at' => Carbon::now()
            ]);

            return response()->json([
                'code' => 200,
                'msg' => 'The course has been added successfully for free',
                'data' => ""
            ], 200);
        }

        $your_domain = env('APP_URL');

        $map = [];
        $map['token'] = $token;
        $map['course_id'] = $courseId;
        $map['total_amount'] = $searchCourse->price;
        $map['status'] = 0;
        $map['created_at'] = Carbon::now();

        $orderNum = Order::insertGetId($map);

        $checkOutSession = Session::create([
            'line_items' =>[[
                'price_data'=>[
                    'currency'=>'USD',
                    'product_data'=>[
                        'name'=>$searchCourse->name,
                        'description'=>$searchCourse->description,
                    ],

                    'unit_amount'=>intval(($searchCourse->price)*100)
                ],
                'quantity'=>1,
            ]],
            'payment_intent_data'=>[
                'metadata'=>['order_num'=>$orderNum, 'token'=>$token],
            ],
            'metadata'=>['order_num'=>$orderNum, 'token'=>$token],
            'mode'=>'payment',
            'success_url' => 'https://0da9-2405-4802-1bf1-3d70-f9c4-8136-97d5-11d0.ngrok-free.app/success',
            'cancel_url' => 'https://0da9-2405-4802-1bf1-3d70-f9c4-8136-97d5-11d0.ngrok-free.app/cancel',
        ]);

        return response()->json(
            [
                'code'=>200,
                'msg'=>'Order has been placed',
                'data'=>$checkOutSession->url
            ], 200
            );

        }catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }
// sk_test_51QDxlj2eXtQ0HOmWD7B9ERqBsRSMCzw2W6IuaAaEaWdLHrGMvczzFBtZ28YhhKW5RCa1xkmSzloDcpOzyKfL1I0k00jn7s4uhQ
    public function webGoHooks() {
        Log::info('Webhook handler started.');
    
        \Stripe\Stripe::setApiKey('sk_test_51QDxlj2eXtQ0HOmWD7B9ERqBsRSMCzw2W6IuaAaEaWdLHrGMvczzFBtZ28YhhKW5RCa1xkmSzloDcpOzyKfL1I0k00jn7s4uhQ');
    
        $endPointSecret = 'whsec_lLdoATjWMovXUB9AW9HbCNxJ6kuDn3wl';
    //whsec_lLdoATjWMovXUB9AW9HbCNxJ6kuDn3wl
        $payload = @file_get_contents('php://input');
    
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;
    
        if (!$sigHeader) {
            Log::error('Stripe signature header missing.');
            http_response_code(400); 
            exit();
        }
    
       
        $event = null;
    
        try {
        
            $event = \Stripe\Webhook::constructEvent(
                $payload,       
                $sigHeader,     
                $endPointSecret 
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload: ' . $e->getMessage());
            http_response_code(400); 
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature: ' . $e->getMessage());
            http_response_code(400); 
            exit();
        }
    
        
        Log::info('Received valid webhook event: ' . $event->type);
    
        if ($event->type === "charge.succeeded") {
            $session = $event->data->object;
    
          
            Log::info('Session data: ' . json_encode($session));
    
           
            $metadata = $session->metadata;
            if (!$metadata || !isset($metadata['order_num']) || !isset($metadata['token'])) {
                Log::error('Missing metadata fields.');
                http_response_code(400);
                exit();
            }
    
            $orderNum = $metadata['order_num'];
            $userToken = $metadata['token'];
    
            Log::info('Order ID: ' . $orderNum);
            Log::info('User Token: ' . $userToken);
    
            
            $map = [
                'status' => 1, 
                'updated_at' => \Carbon\Carbon::now() 
            ];
    
            
            $whereMap = [
                'token' => $userToken,
                'id' => $orderNum
            ];
    
            if (Order::where($whereMap)->update($map)) {
    Log::info('Order updated successfully for order ID: ' . $orderNum);
            } else {
                Log::error('Order update failed for order ID: ' . $orderNum);
            }
        } else {
            Log::info('Unhandled event type: ' . $event->type);
        }
    
        http_response_code(200); 
    }
    
}
