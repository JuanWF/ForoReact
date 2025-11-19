<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $collection = 'posts';
    
    protected $primaryKey = '_id';
    
    protected $fillable = [
        'title',
        'content',
        'user_id',      // Referencia al ObjectId del usuario
        'votes_count',  // Contador de votos (upvotes - downvotes)
        'comments_count', // Contador de comentarios
        'tags',         // Array de tags para categorizar el post
    ];

    // Casting de tipos
    protected $casts = [
        'votes_count' => 'integer',
        'comments_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Valores por defecto
    protected $attributes = [
        'votes_count' => 0,
        'comments_count' => 0,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function votes()
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopePopular($query)
    {
        return $query->orderBy('votes_count', 'desc');
    }

    public function scopeWithComments($query)
    {
        return $query->where('comments_count', '>', 0);
    }

    public function incrementCommentsCount()
    {
        $this->increment('comments_count');
    }

    public function updateVotesCount()
    {
        $upvotes = $this->votes()->where('type', 'up')->count();
        $downvotes = $this->votes()->where('type', 'down')->count();
        
        $this->votes_count = $upvotes - $downvotes;
        $this->save();
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['_id'] = (string) $this->_id;
        return $array;
    }
}
