<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'summary',
        'created_by',
        'image_url',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'view_count' => 'integer',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            $blog->slug = $blog->generateUniqueSlug($blog->title);
        });
    }

    /**
     * Generate a unique slug based on the blog title.
     */
    public function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

    /**
     * Get the user that created the blog.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reactions for the blog.
     */
    public function reactions()
    {
        return $this->hasMany(BlogReaction::class);
    }

    /**
     * Get the comments for the blog.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the categories for the blog.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_categories');
    }

    /**
     * Get all likes for this blog.
     */
    public function likes()
    {
        return $this->reactions()->where('reaction_type', 'like');
    }

    /**
     * Get all bookmarks for this blog.
     */
    public function bookmarks()
    {
        return $this->reactions()->where('reaction_type', 'bookmark');
    }

    /**
     * Get all favorites for this blog.
     */
    public function favorites()
    {
        return $this->reactions()->where('reaction_type', 'favorite');
    }

    /**
     * Increment the view count.
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
        return $this;
    }

    /**
     * Scope a query to only include published blogs.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}