<?php

namespace App\Admin\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Facades\Admin;

class LessonController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Lesson';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Lesson());

        if(Admin::user()->isRole('teacher')){
            $token = Admin::user()->token;
            $ids = DB::table('courses')->where('token','=', $token)->pluck("id")->toArray();

            $grid->model()->whereIn('course_id', $ids);
        }

        $grid->column('id', __('Id'));
        $grid->column('course_id', __('Course'))->display(function($id){
            $item = Course::where('id',"=", $id)->value("name");
            return $item;
        });
        $grid->column('name', __('Name'));
        $grid->column('thumbnail', __('Thumbnail'))->image('',50, 50);
        $grid->column('description', __('Description'));

        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Lesson::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('course_id', __('Course id'));
        $show->field('name', __('Name'));
        $show->field('thumbnail', __('Thumbnail'));
        $show->field('description', __('Description'));
        $show->field('video', __('Video'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Lesson());


        if(Admin::user()->isRole('teacher')){
            $token = Admin::user()->token;
            $id = DB::table('courses')->where('token','=', $token)->pluck("name", "id");
            $form->select('course_id', __("Courses"))->options($id);
        }else{
            $result = DB::table('courses')->pluck('name', 'id');
            $form->select('course_id', __('Courses'))->options($result);
        }

        $form->text('name', __('Name'));



        $form->image('thumbnail', __('Thumbnail'))->uniqueName();
        $form->textarea('description', __('Description'));


        if($form->isEditing()){

            $form->table('video', function($form){
                $form->text('name')->rules('required');
                $form->image('thumbnail')->uniqueName()->rules('required');
                $form->file('url')->rules('required');

                $form->hidden('old_thumnbail');
                $form->hidden('orl_url');
            });

        }else{
            $form->table('video', function($form){
                $form->text('name')->rules('required');
                $form->image('thumbnail')->uniqueName()->rules('required');
                $form->file('url')->rules('required');

            });

        };

        $form->display('created_at', __('Created at'));
        $form->display('updated_at', __('Updated at'));

        $form->saving(function (Form $form){

            if($form->isEditing()){
                $video = $form->video;
                $result = $form->model()->video;
                $newVideos = [];

                $path = env("APP_URL") . '/uploads/';
                foreach($video as $k=>$v){
                    $videoVal = [];
                    if (empty($v["url"])) {
                        $videoVal["old_url"] = empty($result[$k]["url"]) ? "" : str_replace("", "", $result[$k]["url"]);
                    } else {
                        $videoVal["url"] = str_replace(env("APP_URL"), "", $v["url"]);
                    }

                    if(empty($v["thumbnail"])){
                        $videoVal["old_thumbnail"] = str_replace($path, "", $result[$k]["thumbnail"]);
                    }else{
                        $videoVal["thumbnail"] = $v["thumbnail"];
                    }

                    if(empty($v["name"])){
                        $videoVal["name"] = str_replace($path, "", $result[$k]["name"]);
                    }else{
                        $videoVal["name"] = $v["name"];
                    }

                    array_push($newVideos, $videoVal);
                }
                $form->viddeo = $newVideos;
            }

        });


        return $form;
    }
}
