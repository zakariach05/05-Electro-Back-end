<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MagicLink extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'token', 'expires_at', 'used_at'];

    protected $dates = ['expires_at', 'used_at', 'created_at', 'updated_at'];
}
