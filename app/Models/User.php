<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use MongoDB\Laravel\Auth\User as Authenticatable;

/**
 * Modelo User para MongoDB
 * 
 * EXPLICACIÓN:
 * - Usamos MongoDB\Laravel\Auth\User como base (en lugar de Illuminate\Foundation\Auth\User)
 * - Esto permite que la autenticación funcione correctamente con MongoDB
 * - El resto funciona igual que en Laravel tradicional
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $collection = 'users';
    protected $primaryKey = '_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Relación: Un usuario tiene muchos posts
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    /**
     * Relación: Un usuario tiene muchos comentarios
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    /**
     * Relación: Un usuario tiene muchos votos
     */
    public function votes()
    {
        return $this->hasMany(Vote::class, 'user_id');
    }

    /**
     * Verificar si el usuario ha votado un post/comment específico
     * 
     * EXPLICACIÓN:
     * - Esto nos permite saber si mostrar el botón de upvote/downvote activo
     * - Retorna: null (no votó), 'up', o 'down'
     */
    public function hasVoted($votableType, $votableId)
    {
        $vote = $this->votes()
            ->where('votable_type', $votableType)
            ->where('votable_id', $votableId)
            ->first();
        
        return $vote ? $vote->type : null;
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