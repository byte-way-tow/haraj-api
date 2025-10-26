<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'currency',
        'condition',
        'location',
        'phone',
        'whatsapp',
        'is_negotiable',
        'expires_at',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'filename',
        'original_name',
        'path',
        'mime_type',
        'size',
        'sort_order',
        'is_primary',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
