<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    protected $fillable = ['name', 'path', 'mime_type', 'size', 'is_managed'];

    protected function casts(): array
    {
        return ['is_managed' => 'boolean'];
    }
}
