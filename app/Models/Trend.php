<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Trend para MongoDB
 * 
 * EXPLICACIÓN:
 * - Guardamos temas o tags que están siendo tendencia
 * - Esto puede ser calculado por:
 *   1. Posts más populares en las últimas 24 horas
 *   2. Tags más usados
 *   3. Palabras clave más mencionadas
 * - Este modelo es opcional, también podríamos calcular tendencias en tiempo real
 */
class Trend extends Model
{
    use HasFactory;

    protected $collection = 'trends';
    protected $primaryKey = '_id';
    
    protected $fillable = [
        'name',         // Nombre del tema/tag
        'slug',         // URL-friendly version
        'posts_count',  // Cuántos posts tienen este tag
        'score',        // Puntuación para ordenar (basada en actividad reciente)
        'category',     // Categoría del trend (ej: 'SQL', 'MySQL', 'Eficiencia')
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

    /**
     * Scope: Tendencias más populares
     * 
     * USO: Trend::popular()->limit(5)->get()
     */
    public function scopePopular($query)
    {
        return $query->orderBy('score', 'desc')
                     ->orderBy('posts_count', 'desc');
    }

    /**
     * Scope: Tendencias por categoría
     * 
     * USO: Trend::byCategory('SQL')->get()
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Incrementar el score de la tendencia
     * 
     * EXPLICACIÓN:
     * - Cada vez que se crea un post con este tag, incrementamos el score
     * - El score puede decaer con el tiempo (implementar con un job/command)
     */
    public function incrementScore($amount = 1)
    {
        $this->increment('score', $amount);
        $this->increment('posts_count');
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
