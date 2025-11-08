<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'likes'
    ];

    // Custom accessor to handle both JSON and text content
    public function getContentAttribute($value)
    {
        // If it's already an array, return it
        if (is_array($value)) {
            return $value;
        }
        
        // If it's a JSON string, decode it
        if (is_string($value) && json_decode($value, true) !== null) {
            return json_decode($value, true);
        }
        
        // If it's a plain string, wrap it in block structure
        if (is_string($value)) {
            return [
                [
                    'content' => $value,
                    'image' => null,
                    'image_caption' => '',
                    'desktop_size' => 'medium',
                    'wbone_image' => false,
                    'display_size' => 'contain'
                ]
            ];
        }
        
        // Fallback
        return [];
    }

    // Custom mutator to always store as JSON
    public function setContentAttribute($value)
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['content'] = json_encode($value);
        } else {
            $this->attributes['content'] = $value;
        }
    }

    // Helper method to get the first image from blocks
    public function getFeaturedImageAttribute()
    {
        $blocks = $this->content ?? [];
        foreach ($blocks as $block) {
            if (!empty($block['image'])) {
                return $block['image'];
            }
        }
        return null;
    }
}