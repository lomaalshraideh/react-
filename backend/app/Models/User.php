<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'bio',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the blogs created by the user.
     */
    public function blogs()
    {
        return $this->hasMany(Blog::class, 'created_by');
    }

    /**
     * Get the reactions by the user.
     */
    public function reactions()
    {
        return $this->hasMany(BlogReaction::class, 'reacted_by');
    }

    /**
     * Get the comments by the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'commented_by');
    }

    /**
     * Get the users that this user follows.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
                    ->withTimestamp('created_at');
    }

    /**
     * Get the users that follow this user.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
                    ->withTimestamp('created_at');
    }

    /**
     * Check if the user has reacted to a blog with a specific reaction type.
     */
    public function hasReacted($blogId, $reactionType)
    {
        return $this->reactions()
                    ->where('blog_id', $blogId)
                    ->where('reaction_type', $reactionType)
                    ->exists();
    }
}