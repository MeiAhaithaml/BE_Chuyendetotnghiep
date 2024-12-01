<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;
use App\Models\Favorite;
use App\Models\AdminUser;
class CourseController extends Controller
{
    public function courseList(){
        $result = Course::select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();

        return response()->json([
            'code' => 200,
            'msg' => 'My course list is here',
            'data' => $result
        ], 200);
    }

        public function courseDetail(Request $request){
            $id = $request->id;
            try{

            $result =  Course::where('id', '=', $id)->select(
                'id',
                'name',
                'token',
                'description',
                'thumbnail',
                'lesson_num',
                'follow',
                'score',
                'video_length',
                'price')->first();

                return response()->json(
                    [
                        'code'=>200,
                        'msg'=>'My course detail is here',
                        'data'=>$result
                    ], 200
                    );

            }catch(\Throwable $e){
                return response()->json(
                    [
                        'code'=>200,
                        'msg'=>'Server internal error',
                        'data'=>$e->getMessage()
                    ], 500
                    );
            }

        }
        public function coursesBought(Request $request) {
            $user = $request->user();
        
            $result = Course::join('orders', 'courses.id', '=', 'orders.course_id')
                ->where('orders.token', '=', $user->token)
                ->where('orders.status', '=', 1) 
                ->select('courses.name', 'courses.thumbnail', 'courses.lesson_num', 'courses.price', 'courses.id')
                ->get();
        
            return response()->json([
                'code' => 200,
                'msg' => 'The courses you bought',
                'data' => $result
            ], 200);
        }
        
        public function coursesSearchDefault(Request $request){
            $user = $request->user();
    
            $result = Course::where('recommended', '=', 1)
            ->select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();
    
            return response()->json([
                'code' => 200,
                'msg' => 'The courses recommened for you',
                'data' => $result
            ], 200);
        }
    
        public function coursesSearch(Request $request){
            $user = $request->user();
            $search = $request->search;
    
            $result = Course::where("name", "like", "%" .$search. "%")
            ->select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();
    
            return response()->json([
                'code' => 200,
                'msg' => 'The courses you searched',
                'data' => $result
            ], 200);
        }
    
        public function authorCourseList(Request $request){
            $token = $request->query('token');
            \Log::info("Token: " . $token); 
        
            $result = Course::where('token', '=', $token)
                            ->select('token', 'name', 'thumbnail', 'lesson_num', 'price', 'id', 'score')
                            ->get();
        
            return response()->json([
                'code' => 200,
                'msg' => 'Courses of Author',
                'data' => $result
            ], 200);
        }
        
    
        public function courseAuthor(Request $request){
            $token = $request->token;
        
            $result = AdminUser::where('token', '=', $token)
                ->select('token', 'name', 'avatar', 'description')
                ->first();
        
            return response()->json([
                'code' => 200,
                'msg' => 'Author Info',
                'data' => $result
            ], 200);
        }
    
        public function coursesFavorite(Request $request) {
            $user = $request->user(); 
        
            if (!$user) {
                return response()->json([
                    'code' => 404,
                    'msg' => 'User not found',
                ], 404);
            }
        
            $result = Course::join('favorite_table', 'courses.id', '=', 'favorite_table.course_id')
                ->where('favorite_table.user_id', '=', $user->id)
                ->where('favorite_table.status', '=', 1)
                ->select('courses.name', 'courses.thumbnail', 'courses.lesson_num', 'courses.price', 'courses.id')
                ->get();
        
            return response()->json([
                'code' => 200,
                'msg' => 'The courses you like',
                'data' => $result
            ], 200);
        }
        public function mostFavoritedCourses() {
            $result = Course::select('id', 'name', 'thumbnail', 'lesson_num', 'price', 'follow')
                ->where('follow', '>=', 10) // Điều kiện lọc theo cột follow
                ->orderByDesc('follow') // Sắp xếp theo số lượng follow giảm dần
                ->get();
        
            return response()->json([
                'code' => 200,
                'msg' => 'Most favorited courses',
                'data' => $result
            ], 200);
        }
        
        
        public function newestCourses() {
            $result = Course::select('id', 'name', 'thumbnail', 'lesson_num', 'price', 'created_at')
                ->orderByDesc('created_at')
                ->get();
        
            return response()->json([
                'code' => 200,
                'msg' => 'Newest courses',
                'data' => $result
            ], 200);
        }
        
        
}
