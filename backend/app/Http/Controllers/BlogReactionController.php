<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogReactionController extends Controller
{
    /**
     * Add a reaction to a blog.
     */
    public function addReaction(Request $request, Blog $blog)
    {
        $request->validate([
            'reaction_type' => 'required|string|in:like,bookmark,favorite',
        ]);

        $reactionType = $request->reaction_type;
        $userId = Auth::id();

        // Check if the reaction already exists
        $existingReaction = $blog->reactions()
            ->where('reacted_by', $userId)
            ->where('reaction_type', $reactionType)
            ->first();

        // If it exists, return success (idempotent operation)
        if ($existingReaction) {
            return response()->json([
                'message' => 'Reaction already exists',
                'reaction' => $existingReaction,
            ]);
        }

        // Create the new reaction
        $reaction = $blog->reactions()->create([
            'reacted_by' => $userId,
            'reaction_type' => $reactionType,
        ]);

        return response()->json([
            'message' => 'Reaction added successfully',
            'reaction' => $reaction,
        ], 201);
    }

    /**
     * Remove a reaction from a blog.
     */
    public function removeReaction(Request $request, Blog $blog)
    {
        $request->validate([
            'reaction_type' => 'required|string|in:like,bookmark,favorite',
        ]);

        $reactionType = $request->reaction_type;
        $userId = Auth::id();

        // Delete the reaction if it exists
        $deleted = $blog->reactions()
            ->where('reacted_by', $userId)
            ->where('reaction_type', $reactionType)
            ->delete();

        if ($deleted) {
            return response()->json([
                'message' => 'Reaction removed successfully',
            ]);
        }

        return response()->json([
            'message' => 'Reaction not found',
        ], 404);
    }

    /**
     * Get the reaction counts for a blog.
     */
    public function getReactionCounts(Blog $blog)
    {
        $counts = [
            'likes' => $blog->likes()->count(),
            'bookmarks' => $blog->bookmarks()->count(),
            'favorites' => $blog->favorites()->count(),
        ];

        return response()->json($counts);
    }

    /**
     * Check if the current user has reacted to a blog.
     */
    public function getUserReactions(Blog $blog)
    {
        $userId = Auth::id();

        $reactions = [
            'like' => false,
            'bookmark' => false,
            'favorite' => false,
        ];

        // Get all reactions by this user for this blog
        $userReactions = $blog->reactions()
            ->where('reacted_by', $userId)
            ->get();

        // Update the reaction status based on what was found
        foreach ($userReactions as $reaction) {
            $reactions[$reaction->reaction_type] = true;
        }

        return response()->json($reactions);
    }

    /**
     * Get all blogs liked by the current user.
     */
    public function getLikedBlogs()
    {
        $likedBlogs = Blog::whereHas('likes', function ($query) {
            $query->where('reacted_by', Auth::id());
        })
            ->with('author:id,name,username')
            ->latest()
            ->paginate(10);

        return response()->json($likedBlogs);
    }

    /**
     * Get all blogs bookmarked by the current user.
     */
    public function getBookmarkedBlogs()
    {
        $bookmarkedBlogs = Blog::whereHas('bookmarks', function ($query) {
            $query->where('reacted_by', Auth::id());
        })
            ->with('author:id,name,username')
            ->latest()
            ->paginate(10);

        return response()->json($bookmarkedBlogs);
    }

    /**
     * Get all blogs favorited by the current user.
     */
    public function getFavoritedBlogs()
    {
        $favoritedBlogs = Blog::whereHas('favorites', function ($query) {
            $query->where('reacted_by', Auth::id());
        })
            ->with('author:id,name,username')
            ->latest()
            ->paginate(10);

        return response()->json($favoritedBlogs);
    }
}
