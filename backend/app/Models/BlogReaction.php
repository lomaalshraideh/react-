<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogReaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'blog_id',
        'reacted_by',
        'reaction_type',
    ];

    /**
     * Get the blog that has this reaction.
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * Get the user who made this reaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'reacted_by');
    }
}