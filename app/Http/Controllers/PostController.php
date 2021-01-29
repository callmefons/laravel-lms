<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Course;
use App\Post;
use App\ReplyComment;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct()
    {

        $this->middleware('jwt.auth', [
            'only' => [
                'createPost',
                'getPost',
                'commentPost',
                'editComment',
                'deleteComment',
                'replyComment',
                'deleteReplyComment',
                'editReplyComment',
                'editPost',
                'deletePost'
            ]
        ]);

        $this->middleware('teacher', [
            'only' => [
                'createPost',
                'editPost',
                'deletePost'
            ]
        ]);

    }


    public function getPost($id)
    {

        try {
            $post = Post::where('course_id', '=', $id)->firstOrFail();
            $comments = Comment::where('post_id', '=', $post->id)->get();

            $temp = array();
            foreach ($comments as $comment) {
                $reply_comments = ReplyComment::where('comment_id', '=', $comment->id)->get();
                array_push($temp, array('comment' => $comment, 'reply_comments' => $reply_comments));

            }

            return response()->json(
                [
                    'status' => 'success',
                    'data' => [
                        'post' => $post,
                        'comments' => $temp
                    ]
                ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'status' => 'error'
                ], 200);
        }


    }

    public function createPost(Request $request)
    {
        $course = Course::find($request->input('id'));
        $post = new Post([
            'title' => $request->input('title'),
            'detail' => $request->input('detail')
        ]);
        $course->posts()->save($post);

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'course' => $course,
                    'post' => $post
                ]
            ], 200);
    }

    public function editPost(Request $request)
    {
        $post = Post::find($request->input('id'));


        $post->title = $request->input('title');
        $post->detail = $request->input('detail');
        $post->save();


        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'post' => $post
                ]
            ], 200);
    }

    public function deletePost(Request $request)
    {
        $post = Post::find($request->input('id'));
        $post->delete();


        return response()->json(
            [
                'status' => 'success'
            ], 200);
    }

    public function commentPost(Request $request)
    {
        $course_id = $request->input('id');
        $post = Post::where('course_id', '=', $course_id)->first();

        $comment = new Comment([
            'name' => $request->input('name'),
            'detail' => $request->input('detail')
        ]);

        $comment->user()->associate(Auth::user());
        $comment->post()->associate($post);
        $comment->save();


        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'post' => $post,
                    'comment' => $comment
                ]
            ], 200);
    }

    public function editComment(Request $request)
    {
        $comment = Comment::find($request->input('id'));
        if (Auth::user()->id == $comment->user_id) {
            $status = 'success';
            $comment->detail = $request->input('detail');
            $comment->save();
        } else {
            $status = 'cant edit comment';
        }

        return response()->json(
            [
                'status' => $status,
                'data' => [
                    'user_id' => Auth::user()->id,
                    'comment' => $comment->detail
                ]
            ], 200);
    }

    public function deleteComment(Request $request)
    {
        $comment = Comment::find($request->input('id'));
        if (Auth::user()->id == $comment->user_id) {
            $status = 'success';
            $comment->delete();
        } else {
            $status = 'cant delete comment';
        }

        return response()->json(
            [
                'status' => $status
            ], 200);
    }

    public function replyComment(Request $request)
    {
        $comment_id = $request->input('id');
        $comment = Comment::find($comment_id);

        $reply_comment = new ReplyComment([
            'name' => $request->input('name'),
            'detail' => $request->input('detail')
        ]);

        $reply_comment->user()->associate(Auth::user());
        $reply_comment->comment()->associate($comment);
        $reply_comment->save();


        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'comment' => $comment,
                    'reply_comment' => $reply_comment
                ]
            ], 200);
    }

    public function editReplyComment(Request $request)
    {

        $comment_id = $request->input('id');
        $reply_comment = ReplyComment::find($comment_id);
        if (Auth::user()->id == $reply_comment->user_id) {
            $status = 'success';
            $reply_comment->detail = $request->input('detail');
            $reply_comment->save();
        } else {
            $status = 'cant edit comment';
        }


        return response()->json(
            [
                'status' => $status,
                'data' => [
                    'user_id' => Auth::user()->id,
                    'reply_comment' => $reply_comment->detail
                ]
            ], 200);
    }

    public function deleteReplyComment(Request $request)
    {
        $comment = ReplyComment::find($request->input('id'));
        if (Auth::user()->id == $comment->user_id) {
            $status = 'success';
            $comment->delete();
        } else {
            $status = 'cant delete comment';
        }

        return response()->json(
            [
                'status' => $status
            ], 200);
    }

}
