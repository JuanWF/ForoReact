<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Vote para MongoDB
 * 
 * EXPLICACIÓN:
 * - Cada voto es un documento independiente
 * - Guardamos: quién votó (user_id), qué votó (post_id o comment_id), y tipo (up/down)
 * - Esto nos permite:
 *   1. Saber exactamente quién votó
 *   2. Prevenir votos duplicados
 *   3. Permitir cambiar el voto
 *   4. Generar estadísticas
 */
class Vote extends Model
{
    use HasFactory;

    protected $collection = 'votes';
    protected $primaryKey = '_id';
    
    protected $fillable = [
        'user_id',      // Quién votó
        'votable_type', // Tipo: 'post' o 'comment'
        'votable_id',   // ID del post o comment
        'type',         // 'up' o 'down'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación polimórfica: Un voto pertenece a un votable (post o comment)
     * 
     * EXPLICACIÓN POLIMORFISMO:
     * - votable_type guarda el nombre del modelo: "App\Models\Post" o "App\Models\Comment"
     * - votable_id guarda el ObjectId del documento
     * - Laravel automáticamente resuelve a qué modelo pertenece
     * - Podemos hacer: $vote->votable y obtener el post o comment correspondiente
     */
    public function votable()
    {
        return $this->morphTo();
    }

    /**
     * Relación: Un voto pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: Solo upvotes
     * 
     * USO: Vote::up()->count()
     */
    public function scopeUp($query)
    {
        return $query->where('type', 'up');
    }

    /**
     * Scope: Solo downvotes
     */
    public function scopeDown($query)
    {
        return $query->where('type', 'down');
    }

    /**
     * Scope: Votos de un usuario específico en un post específico
     * 
     * USO: Vote::forUserAndPost($userId, $postId)->first()
     */
    public function scopeForUserAndVotable($query, $userId, $votableType, $votableId)
    {
        return $query->where('user_id', $userId)
                     ->where('votable_type', $votableType)
                     ->where('votable_id', $votableId);
    }

    /**
     * Boot del modelo para actualizar contadores automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        // Al crear un voto, actualizar el contador del votable
        static::created(function ($vote) {
            if ($vote->votable) {
                $vote->votable->updateVotesCount();
            }
        });

        // Al actualizar un voto (cambiar de up a down o viceversa)
        static::updated(function ($vote) {
            if ($vote->votable) {
                $vote->votable->updateVotesCount();
            }
        });

        // Al eliminar un voto
        static::deleted(function ($vote) {
            if ($vote->votable) {
                $vote->votable->updateVotesCount();
            }
        });
    }

    /**
     * Sobrescribir toArray para incluir _id
     */
    public function toArray()
    {
        $array = parent::toArray();
        $array['_id'] = (string) $this->_id;
        return $array;
    }
}
