<?php

namespace App\Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = [
        'name',
        'email',
        'status',
    ];
}
