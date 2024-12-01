<?php

namespace App\Admin\Controllers;

use App\Models\CourseType;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Tree;
use Encore\Admin\Layout\Content;

class CourseController extends AdminController
{

    protected function grid()
    {
        $grid = new Grid(new Course());
    
        if(Admin::user()->isRole('teacher')) {
            $token = Admin::user()->token;
            $grid->model()->where('token', '=', $token);
        }
    
        $grid->column('id', __('Id'));
        $grid->column('token', __('Teachers'))->display(
            function($token) {
                return DB::table("admin_users")->where('token', '=', $token)->value('username');
            }
        );
        $grid->column('name', __('Name'));
        $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
        $grid->column('description', __('Description'));
        $grid->column('type_id', __('Type id'));
        $grid->column('price', __('Price'));
        $grid->column('lesson_num', __('Lesson num'));
        $grid->column('video_length', __('Video length'));
        $grid->column('created_at', __('Created at'));
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Course::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('thumbnail', __('Thumbnail'));
        $show->field('video', __('Video'));
        $show->field('description', __('Description'));
        $show->field('price', __('Price'));
        $show->field('lesson_num', __('Lesson num'));
        $show->field('video_length', __('Video length'));
        $show->field('follow', __('Follow'));
        $show->field('score', __('Score'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }


    protected function form()
    {
        $form = new Form(new Course());
        $form->text('name', __('Name'));
        $result = CourseType::pluck('title', 'id');
        $form->select('type_id', __('Category'))->options($result);
        $form->image('thumbnail', __('Thumbnail'))->uniqueName();
        $form->file('video', __('Video'))->uniqueName();
     
        $form->text('description', __('Description'));
        $form->decimal('price', __('Price'));
        $form->number('lesson_num', __('Lesson number'));
        $form->number('video_length', __('Video length'));



        if(Admin::user()->isRole('teacher')){
            $token = Admin::user()->token;
            $username = Admin::user()->username;
            $form->select('token', __('Teacher'))->options([$token=>$username])->default($token)->readonly();
        }else{
            $result = DB::table('admin_users')->pluck('username', 'token');
            $form->select('token', __('Teacher'))->options($result);
        }

        $form->display('created_at', __('Created at'));
        $form->display('updated_at', __('Updated at'));
        $form->switch('recommended', __('Recommended'))->default(0);
        return $form;
    }

}
