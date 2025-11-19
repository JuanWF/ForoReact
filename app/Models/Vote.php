<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function votable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeUp($query)
    {
        return $query->where('type', 'up');
    }

    public function scopeDown($query)
    {
        return $query->where('type', 'down');
    }

    public function scopeForUserAndVotable($query, $userId, $votableType, $votableId)
    {
        return $query->where('user_id', $userId)
                     ->where('votable_type', $votableType)
                     ->where('votable_id', $votableId);
    }

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

    public function toArray()
    {
        $array = parent::toArray();
        $array['_id'] = (string) $this->_id;
        return $array;
    }
}
