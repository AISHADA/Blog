<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function likeOrUnlike($id)
    {

        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'massage' => 'Post not found'
            ], 403);
        }

        $like = $post->likes()
            ->where('user_id', Auth::user()->id)
            ->first();

        if (!$like) {
            Like::create([
                'post_id' => $id,
                'user_id' => auth::user()->id
            ]);

            return response()->json([
                'massage' => 'Liked'
            ], 200);
        }

        $like->delete();

        return response()->json([
            'massage' => 'Disliked'
        ], 200);
    }
}
