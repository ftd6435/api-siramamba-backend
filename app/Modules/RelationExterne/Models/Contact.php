<?php

namespace App\Modules\RelationExterne\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $fillable = ['name', 'email', 'telephone', 'subject', 'message'];
}
