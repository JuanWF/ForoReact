<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $collection = 'comments';
    protected $primaryKey = '_id';
    
    protected $fillable = [
        'content',
        'user_id',      // Quién comentó
        'post_id',      // En qué post
        'parent_id',    // Para respuestas anidadas (opcional, null si es comentario raíz)
        'votes_count',  // Los comentarios también pueden tener votos
    ];

    protected $casts = [
        'votes_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'votes_count' => 0,
        'parent_id' => null,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }


    public function votes()
    {
        return $this->morphMany(Vote::class, 'votable');
    }


    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }


    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }


    public function updateVotesCount()
    {
        $upvotes = Vote::where('votable_type', 'App\Models\Comment')
                      ->where('votable_id', $this->_id)
                      ->where('type', 'up')
                      ->count();
                      
        $downvotes = Vote::where('votable_type', 'App\Models\Comment')
                        ->where('votable_id', $this->_id)
                        ->where('type', 'down')
                        ->count();
        
        $this->votes_count = $upvotes - $downvotes;
        $this->save();
    }


    public function toArray()
    {
        $array = parent::toArray();
        $array['_id'] = (string) $this->_id;
        return $array;
    }


    protected static function boot()
    {
        parent::boot();

        // Al crear un comentario, incrementar el contador del post
        static::created(function ($comment) {
            $post = $comment->post;
            if ($post) {
                $post->incrementCommentsCount();
            }
        });

        // Al eliminar un comentario, decrementar el contador
        static::deleted(function ($comment) {
            $post = $comment->post;
            if ($post) {
                $post->decrement('comments_count');
            }
        });
    }
}
