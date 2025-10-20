<?php
// app/Models/StringAnalysis.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StringAnalysis extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'value', 'properties'];
    protected $casts = ['properties' => 'array']; // Auto-cast JSON to array
    public $incrementing = false;
    protected $keyType = 'string';
}
