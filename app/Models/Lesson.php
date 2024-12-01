<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    use DefaultDatetimeFormat;

    protected $casts = [
        'video'=>'json'
    ];

    public function setVideoAttribute($value){

        $newVideo =[];
        foreach($value as $k=>$v){
            $videoVal = [];
            if(!empty($v["old_thumbnail"])){
                $videoVal["thumbnal"]=$v["old_thumbnail"];
            }else{
                $videoVal["thumbnail"]=$v["thumbnail"];
            }
            if(!empty($v["old_url"])){
                $videoVal["url"]=$v["old_url"];
            }else{
                $videoVal["url"]=$v["url"];
            }
            $videoVal=$v["name"];
            array_push($newVideo,$videoVal);
        }

        $this->attributes['video'] =
        json_encode(array_values($value));
    }

    public function getVideoAttribute($value){

        $resVideo = json_decode($value, true)?:[];
        if(!empty($resVideo)){
            foreach($resVideo as $k=>$v){
                $resVideo[$k]["url"]=env("APP_URL")."uploads/".$v["url"];
                $resVideo[$k]["thumbnail"]=$v["thumbnail"];
            }
        }


        return $resVideo;
    }
    public function getThubnailAttribute($value){
        return env("APP_URL")."uploads/".$value;
    }
}
