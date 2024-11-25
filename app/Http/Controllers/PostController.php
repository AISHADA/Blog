<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $queryPosts = Post::orderBy('created_at')
            ->with('user:id,name,image')
            ->withCount('comments', 'likes')
            ->with('likes', function ($like) {
                return $like->where('user_id', auth::user()->id)
                    ->select('id', 'user_id', 'post_id')
                    ->get();
            })
            ->get();

        if (count($queryPosts) == 0) {
            return response()->json([
                'massage' => 'No Posts'
            ], 403);
        } else {
            return response()->json([
                'posts' => $queryPosts
            ], 200);
        }
    }

    public function store(request $request)
    {
        $validate = $request->validate([
            'body' => 'required|string',
            'image' => 'file|mimes:jpg,bmp,png'
        ]);

        if ($request->hasFile('image')) {
            $image = $request->image->getClientOriginalName();
            $path = $request->image->storeAs('posts', $image, 'images');
        }

        $post = Post::create([
            'body' => $validate['body'],
            'image' => $path,
            'user_id' => Auth::user()->id
        ]);

        return response()->json([
            'massage' => 'Post Created .. ',
            'post' => $post
        ], 200);
    }

    public function show($id)
    {

        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'massage' => 'Post not found'
            ], 403);
        }
        $post = Post::where('id', $id)
            ->with('user:id,name,image')
            ->withCount('comments', 'likes')
            ->with('likes', function ($like) {
                return $like->where('user_id', auth::user()->id)
                    ->select('id', 'user_id', 'post_id')
                    ->get();
            })
            ->get();

        if ($post) {
            return response()->json($post, 200);
        }
    }

    public function update(request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'massage' => 'Post not found'
            ], 403);
        }

        if ($post->user_id != Auth::user()->id) {
            return response()->json([
                'massage' => 'Permission denied .'
            ], 403);
        }

        $validate = $request->validate([
            'body' => 'required|string',
            'image' => 'file|mimes:jpg,bmp,png'
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('photo')->getClientOriginalName();
            $path = $request->file('image')->storeAs('posts', $image, 'images');
        }

        $post->update([
            'body' => $validate['body'],
            'image' => $path
        ]);

        return response()->json([
            'massage' => 'Post updated.. ',
            'post' => $post
        ], 200);
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'massage' => 'Post not found'
            ], 403);
        }

        if ($post->user_id != Auth::user()->id) {
            return response()->json([
                'massage' => 'Permission denied .'
            ], 403);
        }

        $post->comments()->delete();
        $post->likes()->delete();
        $post->delete();

        return response()->json([
            'massage' => 'Post deleted.. '
            //,'post' => $post
        ], 200);
    }
}
