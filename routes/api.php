<?php
use App\Http\Controllers\StringAnalysisController;
use Illuminate\Support\Facades\Route;

// Filter strings using natural language query (e.g., "all single word palindromic strings")
Route::get('/strings/filter-by-natural-language', [StringAnalysisController::class, 'naturalFilter']);

// Create and analyze a new string, storing its properties
Route::post('/strings', [StringAnalysisController::class, 'store']);

// Retrieve a specific string by its value
Route::get('/strings/{string_value}', [StringAnalysisController::class, 'show']);

// Get all strings with optional filtering (e.g., is_palindrome, min_length)
Route::get('/strings', [StringAnalysisController::class, 'index']);

// Delete a specific string by its value
Route::delete('/strings/{string_value}', [StringAnalysisController::class, 'destroy']);
