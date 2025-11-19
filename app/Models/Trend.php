<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trend extends Model
{
    use HasFactory;

    protected $collection = 'trends';
    protected $primaryKey = '_id';
    
    protected $fillable = [
        'name',         // Nombre del tag (ej: "MongoDB", "Laravel")
        'slug',         // URL-friendly: "mongodb", "laravel"
        'posts_count',  // Cuántos posts tienen este tag
        'score',        // Puntuación para ordenar (basada en actividad reciente)
        'category',     // Categoría para agrupar (ej: "Backend", "Frontend", "Bases de Datos")
    ];

    protected $casts = [
        'posts_count' => 'integer',
        'score' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'posts_count' => 0,
        'score' => 0,
    ];

    public function scopePopular($query)
    {
        return $query->orderBy('score', 'desc')
                     ->orderBy('posts_count', 'desc');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function incrementScore($amount = 1)
    {
        $this->increment('score', $amount);
        $this->increment('posts_count');
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['_id'] = (string) $this->_id;
        return $array;
    }
}
