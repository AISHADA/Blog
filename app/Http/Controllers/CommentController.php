<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index($id)
    {

        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'massage' => 'Post not found'
            ], 403);
        }

        $queryComments = Comment::where('post_id', $id)
            ->orderBy('created_at')
            ->with('user:id,name,image')
            ->get();

        if (count($queryComments) == 0) {
            return response()->json([
                'massage' => 'No Comments'
            ], 403);
        } else {
            return response()->json([
                'comments' => $queryComments
            ], 200);
        }
    }

    public function store(request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'massage' => 'Post not found'
            ], 403);
        }

        $validate = $request->validate([
            'comment' => 'required|string'
        ]);

        $comment = Comment::create([
            'comment' => $validate['comment'],
            'user_id' => Auth::user()->id,
            'post_id' => $id
        ]);

        return response()->json([
            'post' => $comment
        ], 200);
    }

    public function update(request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json([
                'massage' => 'Comment not found'
            ], 403);
        }

        if ($comment->user_id != Auth::user()->id) {
            return response()->json([
                'massage' => 'Permission denied .'
            ], 403);
        }


        $validate = $request->validate([
            'comment' => 'required|string'
        ]);

        $comment->update($validate);

        return response()->json([
            'massage' => 'Comment updated.. ',
            'post' => $comment
        ], 200);
    }

    public function destroy($id)
    {

        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json([
                'massage' => 'Comment not found'
            ], 403);
        }

        if ($comment->user_id != Auth::user()->id) {
            return response()->json([
                'massage' => 'Permission denied .'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'massage' => 'Comment deleted.. '
            //,'post' => $comment
        ], 200);
    }
}
