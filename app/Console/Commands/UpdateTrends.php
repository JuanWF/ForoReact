<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\Trend;
use Illuminate\Support\Str;

class UpdateTrends extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trends:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza las tendencias del foro basándose en los posts recientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Actualizando tendencias...');

        // Obtener todos los posts con tags de los últimos 30 días
        $posts = Post::whereNotNull('tags')
            ->where('created_at', '>', now()->subDays(30))
            ->get();

        if ($posts->isEmpty()) {
            $this->warn('⚠️  No se encontraron posts con tags en los últimos 30 días');
            return;
        }

        // Contar ocurrencias de cada tag
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

        // Mapeo de tags a categorías
        $categoryMap = [
            'SQL' => 'Bases de Datos',
            'MySQL' => 'Bases de Datos',
            'MongoDB' => 'Bases de Datos',
            'PostgreSQL' => 'Bases de Datos',
            'Laravel' => 'Backend',
            'PHP' => 'Backend',
            'Node.js' => 'Backend',
            'Express' => 'Backend',
            'React' => 'Frontend',
            'Vue' => 'Frontend',
            'Angular' => 'Frontend',
            'TypeScript' => 'Frontend',
            'JavaScript' => 'Frontend',
            'Inertia' => 'Frontend',
            'Performance' => 'Optimización',
            'Optimización' => 'Optimización',
            'Seguridad' => 'Optimización',
            'Testing' => 'Desarrollo',
            'DevOps' => 'Desarrollo',
            'Docker' => 'Desarrollo',
            'Git' => 'Desarrollo',
            'Consultas' => 'Bases de Datos',
            'Aggregation' => 'Bases de Datos',
            'Comparación' => 'General',
            'Tutorial' => 'General',
            'Búsqueda' => 'Optimización',
            'Indexación' => 'Optimización',
            'Eloquent' => 'Backend',
            'Novedades' => 'General',
        ];

        // Actualizar o crear tendencias
        $updated = 0;
        $created = 0;

        foreach ($tagCounts as $tagName => $count) {
            if ($count > 0) {
                $slug = Str::slug($tagName);
                $trend = Trend::where('slug', $slug)->first();

                if ($trend) {
                    $trend->posts_count = $count;
                    $trend->score = $count * 10;
                    $trend->save();
                    $updated++;
                } else {
                    Trend::create([
                        'name' => $tagName,
                        'slug' => $slug,
                        'posts_count' => $count,
                        'score' => $count * 10,
                        'category' => $categoryMap[$tagName] ?? 'General',
                    ]);
                    $created++;
                }

                $this->line("  ✓ {$tagName}: {$count} posts");
            }
        }

        // Decrementar score de tendencias antiguas
        $decayed = Trend::where('updated_at', '<', now()->subDays(7))
            ->decrement('score', 5);

        // Eliminar tendencias con score negativo
        $deleted = Trend::where('score', '<=', 0)->delete();

        $this->newLine();
        $this->info("✅ Tendencias actualizadas:");
        $this->line("   • Actualizadas: {$updated}");
        $this->line("   • Creadas: {$created}");
        $this->line("   • Decaídas: {$decayed}");
        $this->line("   • Eliminadas: {$deleted}");

        return Command::SUCCESS;
    }
}
