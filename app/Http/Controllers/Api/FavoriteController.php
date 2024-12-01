<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
class FavoriteController extends Controller
{
    
    public function addFavorite(Request $request)
    {
        $user = Auth::user();
        $course_id = $request->input('course_id');
    
        $favorite = Favorite::where('user_id', $user->id)
                            ->where('course_id', $course_id)
                            ->where('status', 1)
                            ->first();
    
        if ($favorite) {
            return response()->json([
                'message' => 'Khóa học đã có trong danh sách yêu thích',
                'status' => $favorite->status
            ], 200);
        }
        $course = Course::find($course_id);
        \Log::info('Giá trị follow trước khi tăng: ' . ($course->follow ?? 'không có'));
        \Log::info('Giá trị id trước khi tăng: ' . ($course->course_id ?? 'không có'));
        $newFavorite = Favorite::create([
            'user_id' => $user->id,
            'course_id' => $course_id,
            'status' => 1
        ]);
        if ($course->follow === null) {
            $course->follow = 0;
        }
        Course::where('id', $course_id)->increment('follow');
    
        return response()->json([
            'message' => 'Khóa học đã được thêm vào danh sách yêu thích',
            'favorite' => $newFavorite
        ], 201);
    }
    

    
    public function removeFavorite(Request $request)
{
    $user = Auth::user();
    $course_id = $request->input('course_id');

    $favorite = Favorite::where('user_id', $user->id)
                        ->where('course_id', $course_id)
                        ->first();

    if ($favorite) {
        $favorite->delete();
        
        $course = Course::find($course_id);
        if ($course->follow === null) {
            $course->follow = 0;
        }
        
        Course::where('id', $course_id)->decrement('follow');

        return response()->json([
            'message' => 'Khóa học đã được bỏ yêu thích',
            'status' => 0
        ], 200);
    }

    return response()->json([
        'message' => 'Khóa học không có trong danh sách yêu thích'
    ], 404);
}
}
