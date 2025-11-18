<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Vote;
use App\Models\Trend;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * ForoSeeder - Seeder para poblar el foro con datos de prueba
 * 
 * EXPLICACIÓN:
 * - Crea usuarios, posts, comentarios, votos y tendencias
 * - Útil para probar el foro sin tener que crear todo manualmente
 * 
 * USO:
 * php artisan db:seed --class=ForoSeeder
 */
class ForoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar datos existentes (opcional)
        // User::truncate();
        // Post::truncate();
        // Comment::truncate();
        // Vote::truncate();
        // Trend::truncate();

        $this->command->info('🌱 Creando usuarios...');
        
        // Crear usuario admin
        $admin = User::create([
            'name' => 'Admin ForoDB',
            'email' => 'admin@forodb.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Crear usuarios de prueba
        $users = collect([$admin]);
        
        $userNames = [
            'juan_db', 'maria_sql', 'pedro_mongo', 'ana_dev', 'carlos_php',
            'lucia_react', 'miguel_laravel', 'sofia_tech', 'david_code',
        ];

        foreach ($userNames as $name) {
            $users->push(User::create([
                'name' => ucwords(str_replace('_', ' ', $name)),
                'email' => $name . '@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]));
        }

        $this->command->info("✅ Creados {$users->count()} usuarios");

        // Crear tendencias
        $this->command->info('🔥 Creando tendencias...');
        
        $trendsData = [
            ['name' => 'SQL', 'category' => 'SQL'],
            ['name' => 'MySQL', 'category' => 'MySQL'],
            ['name' => 'MongoDB', 'category' => 'MongoDB'],
            ['name' => 'Laravel', 'category' => 'PHP'],
            ['name' => 'React', 'category' => 'JavaScript'],
            ['name' => 'Eficiencia', 'category' => 'Eficiencia'],
            ['name' => 'Optimización', 'category' => 'Eficiencia'],
        ];

        $trends = collect();
        foreach ($trendsData as $trendData) {
            $trends->push(Trend::create([
                'name' => $trendData['name'],
                'slug' => Str::slug($trendData['name']),
                'posts_count' => 0,
                'score' => rand(10, 100),
                'category' => $trendData['category'],
            ]));
        }

        $this->command->info("✅ Creadas {$trends->count()} tendencias");

        // Crear posts
        $this->command->info('📝 Creando posts...');
        
        $postTitles = [
            '¿Problemas al usar $lookup en MongoDB?',
            'Diferencias entre MySQL y MongoDB',
            '¿Cómo optimizar queries en Laravel?',
            'Mejores prácticas con React y TypeScript',
            'Tutorial: MongoDB Aggregation Framework',
            '¿Vale la pena usar Inertia.js?',
            'Comparación: SQL vs NoSQL en 2025',
            'Laravel 12: Novedades y cambios',
            '¿Cómo implementar búsqueda full-text en MongoDB?',
            'Problema con relaciones en Laravel MongoDB',
        ];

        $posts = collect();
        foreach ($postTitles as $index => $title) {
            $user = $users->random();
            $content = $this->generatePostContent($title);
            
            $post = Post::create([
                'title' => $title,
                'content' => $content,
                'user_id' => $user->_id,
                'votes_count' => rand(-5, 50),
                'comments_count' => 0,
                'created_at' => now()->subDays(rand(0, 30)),
            ]);
            
            $posts->push($post);
        }

        $this->command->info("✅ Creados {$posts->count()} posts");

        // Crear comentarios
        $this->command->info('💬 Creando comentarios...');
        
        $totalComments = 0;
        foreach ($posts as $post) {
            $numComments = rand(0, 8);
            
            for ($i = 0; $i < $numComments; $i++) {
                $commenter = $users->random();
                
                Comment::create([
                    'content' => $this->generateCommentContent(),
                    'user_id' => $commenter->_id,
                    'post_id' => $post->_id,
                    'parent_id' => null,
                    'votes_count' => rand(-2, 20),
                    'created_at' => $post->created_at->addHours(rand(1, 48)),
                ]);
                
                $totalComments++;
            }
        }

        $this->command->info("✅ Creados {$totalComments} comentarios");

        // Crear votos
        $this->command->info('👍 Creando votos...');
        
        $totalVotes = 0;
        foreach ($posts as $post) {
            $numVoters = rand(3, min(8, $users->count()));
            $voters = $users->random($numVoters);
            
            foreach ($voters as $voter) {
                Vote::create([
                    'user_id' => $voter->_id,
                    'votable_type' => 'App\Models\Post',
                    'votable_id' => $post->_id,
                    'type' => rand(0, 1) ? 'up' : 'down',
                ]);
                
                $totalVotes++;
            }
        }

        $this->command->info("✅ Creados {$totalVotes} votos");

        // Actualizar contadores
        $this->command->info('🔄 Actualizando contadores...');
        
        foreach ($posts as $post) {
            $post->refresh();
            $post->comments_count = $post->comments()->count();
            $post->updateVotesCount();
            $post->save();
        }

        $this->command->info('');
        $this->command->info('🎉 ¡Seeder completado!');
        $this->command->info('');
        $this->command->info('👤 Usuario admin:');
        $this->command->info('   Email: admin@forodb.com');
        $this->command->info('   Password: password');
        $this->command->info('');
        $this->command->info('👥 Otros usuarios: [nombre]@example.com (password: password)');
    }

    private function generatePostContent($title): string
    {
        $contents = [
            "He estado trabajando con MongoDB y me encuentro con este problema. ¿Alguien tiene experiencia con esto?\n\nHe intentado varias cosas pero no logro resolverlo. Cualquier ayuda sería muy apreciada.",
            "Quiero compartir mi experiencia con este tema. He estado usando esto en producción por varios meses y aquí están mis conclusiones.\n\nEspero que les sea útil para sus proyectos.",
            "Tengo una duda sobre cómo implementar esto correctamente. He leído la documentación pero no me queda claro.\n\n¿Alguien puede explicarlo de forma más simple?",
            "Este es un tutorial paso a paso sobre cómo resolver este problema común. Lo he documentado para que sea fácil de seguir.\n\n1. Primero...\n2. Luego...\n3. Finalmente...",
            "¿Cuál es la mejor práctica para este caso? He visto varias aproximaciones y no sé cuál es la más recomendada.\n\n¿Qué opinan ustedes?",
        ];

        return $contents[array_rand($contents)];
    }

    private function generateCommentContent(): string
    {
        $comments = [
            'Excelente pregunta, yo tuve el mismo problema hace poco.',
            'Gracias por compartir, muy útil esta información.',
            'Yo lo resolví de esta manera: primero verificas X, luego haces Y.',
            '¿Ya intentaste revisar la documentación oficial?',
            'Esto me pasó también, la solución es actualizar a la última versión.',
            'Muy buen aporte, lo voy a probar en mi proyecto.',
            'No estoy seguro, pero creo que el problema es otro.',
            'Gracias! Esto es justo lo que necesitaba.',
        ];

        return $comments[array_rand($comments)];
    }
}
