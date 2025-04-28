<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a specific blog.
     */
    public function index(Blog $blog)
    {
        // Get only top-level comments (no parent)
        $comments = $blog->comments()
            ->with(['author:id,name,username,profile_image', 'replies.author:id,name,username,profile_image'])
            ->whereNull('parent_comment_id')
            ->approved()
            ->latest()
            ->paginate(15);

        return response()->json($comments);
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request, Blog $blog)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $blog->comments()->create([
            'commented_by' => Auth::id(),
            'content' => $request->content,
            'status' => 'approved', // You can change this to 'pending' if you want to moderate comments
        ]);

        // Load the author relationship
        $comment->load('author:id,name,username,profile_image');

        return response()->json($comment, 201);
    }

    /**
     * Add a reply to an existing comment.
     */
    public function reply(Request $request, Comment $comment)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        // Create a new comment as a reply
        $reply = new Comment([
            'blog_id' => $comment->blog_id,
            'commented_by' => Auth::id(),
            'parent_comment_id' => $comment->id,
            'content' => $request->content,
            'status' => 'approved', // You can change this to 'pending' if you want to moderate comments
        ]);

        $reply->save();

        // Load the author relationship
        $reply->load('author:id,name,username,profile_image');

        return response()->json($reply, 201);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, Comment $comment)
    {
        // Check if the user is authorized to update this comment
        if ($comment->commented_by !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return response()->json($comment);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment)
    {
        // Check if the user is authorized to delete this comment
        if ($comment->commented_by !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
