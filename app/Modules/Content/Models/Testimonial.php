<?php

namespace App\Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name',
        'profession',
        'message',
    ];
}
