<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpVideoYT extends Model
{
    use HasFactory;

    protected $table = 'up_video_yt';

    protected $fillable = [
        'title',
        'description',
        'file_name'
    ];
}
