<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    /**
     * Display a listing of the blogs.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Blog::with(['author:id,name,username,profile_image', 'categories:id,name,slug'])
            ->withCount(['likes', 'comments', 'bookmarks', 'favorites'])
            ->published();

        // Filter by category if provided
        if ($request->has('category')) {
            $categorySlug = $request->category;
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Filter by author if provided
        if ($request->has('author')) {
            $authorId = $request->author;
            $query->where('created_by', $authorId);
        }

        // Sort by specified field or default to newest
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Only allow certain fields for sorting
        $allowedSortFields = ['created_at', 'title', 'view_count'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = $request->input('per_page', 10);
        $blogs = $query->paginate($perPage);

        return response()->json($blogs);
    }

    /**
     * Store a newly created blog.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:draft,published,archived',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blog_images', 'public');
        }

        $blog = Blog::create([
            'title' => $request->title,
            'content' => $request->content,
            'summary' => $request->summary,
            'created_by' => $request->user()->id,
            'image_url' => $imagePath ? Storage::url($imagePath) : null,
            'status' => $request->input('status', 'published'),
        ]);

        // Attach categories if provided
        if ($request->has('categories')) {
            $blog->categories()->attach($request->categories);
        }

        // Load relationships
        $blog->load(['author:id,name,username', 'categories:id,name,slug']);

        return response()->json([
            'message' => 'Blog created successfully',
            'blog' => $blog
        ], 201);
    }

    /**
     * Display the specified blog.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $blog = Blog::with([
            'author:id,name,username,profile_image',
            'categories:id,name,slug',
            'comments' => function ($query) {
                $query->with('author:id,name,username,profile_image')
                    ->where('parent_comment_id', null)
                    ->orderBy('created_at', 'desc');
            },
            'comments.replies' => function ($query) {
                $query->with('author:id,name,username,profile_image')
                    ->orderBy('created_at', 'asc');
            }
        ])
            ->withCount(['likes', 'comments', 'bookmarks', 'favorites'])
            ->findOrFail($id);

        // Increment view count
        $blog->incrementViewCount();

        return response()->json($blog);
    }

    /**
     * Update the specified blog.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        // Check if the user is the author of the blog
        if ($request->user()->id !== $blog->created_by) {
            return response()->json([
                'message' => 'You are not authorized to update this blog'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'summary' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:draft,published,archived',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = $request->only(['title', 'content', 'summary', 'status']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Remove old image if exists
            if ($blog->image_url && Storage::exists('public/' . str_replace('/storage/', '', $blog->image_url))) {
                Storage::delete('public/' . str_replace('/storage/', '', $blog->image_url));
            }
            $imagePath = $request->file('image')->store('blog_images', 'public');
            $updateData['image_url'] = Storage::url($imagePath);
        }

        // If title is changed, regenerate the slug
        if ($request->has('title') && $request->title !== $blog->title) {
            $updateData['slug'] = $blog->generateUniqueSlug($request->title);
        }

        $blog->update($updateData);

        // Sync categories if provided
        if ($request->has('categories')) {
            $blog->categories()->sync($request->categories);
        }

        // Load relationships
        $blog->load(['author:id,name,username', 'categories:id,name,slug']);

        return response()->json([
            'message' => 'Blog updated successfully',
            'blog' => $blog
        ]);
    }

    /**
     * Remove the specified blog.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        // Check if the user is the author of the blog
        if ($request->user()->id !== $blog->created_by) {
            return response()->json([
                'message' => 'You are not authorized to delete this blog'
            ], 403);
        }

        // Delete the image if exists
        if ($blog->image_url && Storage::exists('public/' . str_replace('/storage/', '', $blog->image_url))) {
            Storage::delete('public/' . str_replace('/storage/', '', $blog->image_url));
        }

        $blog->delete();

        return response()->json([
            'message' => 'Blog deleted successfully'
        ]);
    }

    /**
     * Get blogs by authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function myBlogs(Request $request)
    {
        $status = $request->input('status', null);
        $query = Blog::where('created_by', $request->user()->id)
            ->with(['categories:id,name,slug'])
            ->withCount(['likes', 'comments', 'bookmarks', 'favorites']);

        if ($status) {
            $query->where('status', $status);
        }

        $blogs = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json($blogs);
    }

    /**
     * Search for blogs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $searchTerm = $request->input('query');

        $blogs = Blog::with(['author:id,name,username,profile_image', 'categories:id,name,slug'])
            ->withCount(['likes', 'comments', 'bookmarks', 'favorites'])
            ->published()
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%")
                    ->orWhere('summary', 'like', "%{$searchTerm}%")
                    ->orWhereHas('categories', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json($blogs);
    }
}
