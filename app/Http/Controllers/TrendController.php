<?php

namespace App\Http\Controllers;

use App\Models\Trend;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

/**
 * TrendController - Maneja las tendencias del foro
 * 
 * EXPLICACIÓN:
 * - Las tendencias son tags o temas populares
 * - Pueden calcularse automáticamente o agregarse manualmente
 * - Se muestran en el sidebar del foro
 */
class TrendController extends Controller
{
    /**
     * Listar todas las tendencias
     */
    public function index()
    {
        $trends = Trend::popular()->paginate(20);

        return Inertia::render('Trend/Index', [
            'trends' => $trends,
        ]);
    }

    /**
     * Mostrar posts de una tendencia específica
     */
    public function show($slug)
    {
        $trend = Trend::where('slug', $slug)->firstOrFail();

        // Buscar posts que tengan este tag
        // MongoDB permite buscar en arrays directamente
        $posts = Post::with(['user'])
            ->where('tags', 'like', "%{$trend->name}%")
            ->recent()
            ->paginate(15);

        return Inertia::render('Trend/Show', [
            'trend' => $trend,
            'posts' => $posts,
        ]);
    }

    /**
     * Actualizar tendencias automáticamente (comando o job)
     * 
     * QUERY EXPLICADA:
     * - Esto normalmente se ejecutaría con un Artisan command o Job
     * - Extrae todos los tags de los posts recientes
     * - Calcula un score basado en frecuencia de uso y recencia
     */
    public function refresh()
    {
        // Obtener todos los posts con tags
        $posts = Post::whereNotNull('tags')
            ->where('created_at', '>', now()->subDays(30))
            ->get();

        // Contar frecuencia de cada tag
        $tagCounts = [];
        foreach ($posts as $post) {
            if (is_array($post->tags)) {
                foreach ($post->tags as $tag) {
                    $normalizedTag = ucfirst(strtolower(trim($tag)));
                    if (!isset($tagCounts[$normalizedTag])) {
                        $tagCounts[$normalizedTag] = 0;
                    }
                    $tagCounts[$normalizedTag]++;
                }
            }
        }

        // Actualizar o crear tendencias basadas en los tags
        foreach ($tagCounts as $tagName => $count) {
            if ($count > 0) {
                $slug = Str::slug($tagName);
                $trend = Trend::firstOrNew(['slug' => $slug]);
                $trend->name = $tagName;
                $trend->slug = $slug;
                $trend->score = $count * 10; // Score basado en frecuencia
                $trend->posts_count = $count;
                $trend->category = $tagName; // Usar el tag como categoría
                $trend->save();
            }
        }

        // Decrementar score de tendencias antiguas
        Trend::where('updated_at', '<', now()->subDays(7))
            ->decrement('score', 5);

        // Eliminar tendencias con score muy bajo
        Trend::where('score', '<=', 0)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tendencias actualizadas',
            'tags_found' => count($tagCounts),
        ]);
    }

    /**
     * Categorizar palabra clave
     */
    private function categorizeKeyword($keyword)
    {
        $categories = [
            'SQL' => 'SQL',
            'MySQL' => 'MySQL',
            'MongoDB' => 'MongoDB',
            'Laravel' => 'PHP',
            'PHP' => 'PHP',
            'JavaScript' => 'JavaScript',
            'React' => 'JavaScript',
        ];

        return $categories[$keyword] ?? 'Eficiencia';
    }
}
