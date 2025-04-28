<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogReactionController;
use App\Http\Controllers\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::get('/users/{id}/followers', [UserController::class, 'followers']);
Route::get('/users/{id}/following', [UserController::class, 'following']);

// Public blog routes
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{id}', [BlogController::class, 'show']);
Route::get('/blogs/search', [BlogController::class, 'search']);

// Public comment routes
Route::get('/blogs/{blog}/comments', [CommentController::class, 'index']);

// Public reaction routes
Route::get('/blogs/{blog}/reactions', [BlogReactionController::class, 'getReactionCounts']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::post('/logout', [UserController::class, 'logout']);
    
    // User follow/unfollow
    Route::post('/users/{id}/follow', [UserController::class, 'follow']);
    Route::delete('/users/{id}/follow', [UserController::class, 'unfollow']);
    
    // Blog routes
    Route::post('/blogs', [BlogController::class, 'store']);
    Route::put('/blogs/{id}', [BlogController::class, 'update']);
    Route::delete('/blogs/{id}', [BlogController::class, 'destroy']);
    Route::get('/my-blogs', [BlogController::class, 'myBlogs']);

    // Protected comment routes
    Route::post('/blogs/{blog}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/comments/{comment}/replies', [CommentController::class, 'reply']);

    // Adding and removing reactions
    Route::post('/blogs/{blog}/react', [BlogReactionController::class, 'addReaction']);
    Route::delete('/blogs/{blog}/react', [BlogReactionController::class, 'removeReaction']);
    
    // Check current user's reactions to a blog
    Route::get('/blogs/{blog}/my-reactions', [BlogReactionController::class, 'getUserReactions']);
    
    // Get lists of reacted blogs
    Route::get('/my-likes', [BlogReactionController::class, 'getLikedBlogs']);
    Route::get('/my-bookmarks', [BlogReactionController::class, 'getBookmarkedBlogs']);
    Route::get('/my-favorites', [BlogReactionController::class, 'getFavoritedBlogs']);
});

Route::post('/admin/login', [AdminController::class, 'login']);

// Admin Protected Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'ability:admin'])->group(function () {
    // Admin profile routes
    Route::get('/profile', [AdminController::class, 'profile']);
    Route::put('/profile', [AdminController::class, 'updateProfile']);
    Route::post('/logout', [AdminController::class, 'logout']);
    
    // Admin resource routes
    Route::apiResource('admins', AdminController::class);
});