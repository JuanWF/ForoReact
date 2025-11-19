<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Comment para MongoDB
 * 
 * EXPLICACIÓN:
 * - Un comentario pertenece a un post y a un usuario
 * - Guardamos referencias (post_id, user_id) en lugar de embedir el documento completo
 * - Esto evita duplicación y mantiene los datos consistentes
 */
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

    /**
     * Relación: Un comentario pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Un comentario pertenece a un post
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Relación: Comentario padre (para respuestas anidadas)
     * 
     * EXPLICACIÓN:
     * - Si parent_id no es null, este comentario es una respuesta a otro comentario
     * - Esto permite hilos de conversación
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Relación: Respuestas a este comentario
     */
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

    /**
     * Boot del modelo para ejecutar acciones automáticas
     */
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
