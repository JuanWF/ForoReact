<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Post para MongoDB
 * 
 * EXPLICACIÓN PARA PRINCIPIANTES:
 * 
 * 1. En MongoDB, cada documento tiene un _id único (ObjectId) generado automáticamente
 * 2. Este modelo extiende de MongoDB\Laravel\Eloquent\Model (no el Model de Laravel tradicional)
 * 3. Las relaciones se manejan con referencias (IDs) o documentos embebidos
 * 4. Aquí usamos REFERENCIAS: guardamos el user_id que apunta a un documento en users
 * 5. MongoDB es schemaless, pero definimos $fillable para seguridad
 */
class Post extends Model
{
    use HasFactory;

    // Nombre de la colección en MongoDB
    protected $collection = 'posts';
    
    // Primary key para MongoDB
    protected $primaryKey = '_id';
    
    // Campos que se pueden asignar masivamente
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
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Valores por defecto
    protected $attributes = [
        'votes_count' => 0,
        'comments_count' => 0,
    ];

    /**
     * Relación: Un post pertenece a un usuario
     * 
     * EXPLICACIÓN:
     * - belongsTo busca el documento en la colección 'users' 
     *   donde el _id coincida con el user_id de este post
     * - Es como un JOIN en SQL, pero MongoDB lo hace en memoria
     * - Podemos hacer: $post->user para obtener el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Un post tiene muchos comentarios
     * 
     * EXPLICACIÓN:
     * - hasMany busca en la colección 'comments' todos los documentos
     *   donde el post_id coincida con el _id de este post
     * - Podemos hacer: $post->comments para obtener todos los comentarios
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    /**
     * Relación: Un post tiene muchos votos
     * 
     * EXPLICACIÓN:
     * - Cada voto es un documento separado que guarda: user_id, votable_id, type (up/down)
     * - Usamos relación polimórfica porque un voto puede ser para Post o Comment
     * - También guardamos votes_count en el post para consultas rápidas
     */
    public function votes()
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    /**
     * Scope: Ordenar por más recientes
     * 
     * USO: Post::recent()->get()
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Ordenar por más populares (más votos)
     * 
     * USO: Post::popular()->get()
     */
    public function scopePopular($query)
    {
        return $query->orderBy('votes_count', 'desc');
    }

    /**
     * Scope: Posts con al menos 1 comentario
     */
    public function scopeWithComments($query)
    {
        return $query->where('comments_count', '>', 0);
    }

    /**
     * Incrementar contador de comentarios
     * 
     * EXPLICACIÓN:
     * - En MongoDB usamos $inc para incrementar atómicamente
     * - Laravel lo hace automático con increment()
     */
    public function incrementCommentsCount()
    {
        $this->increment('comments_count');
    }

    /**
     * Actualizar contador de votos
     */
    public function updateVotesCount()
    {
        $upvotes = $this->votes()->where('type', 'up')->count();
        $downvotes = $this->votes()->where('type', 'down')->count();
        
        $this->votes_count = $upvotes - $downvotes;
        $this->save();
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
