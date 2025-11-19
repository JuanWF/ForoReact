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
        
        // Crear o encontrar usuario admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@forodb.com'],
            [
                'name' => 'Admin ForoDB',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Crear usuarios de prueba
        $users = collect([$admin]);
        
        $userNames = [
            'juan_db', 'maria_sql', 'pedro_mongo', 'ana_dev', 'carlos_php',
            'lucia_react', 'miguel_laravel', 'sofia_tech', 'david_code',
        ];

        foreach ($userNames as $name) {
            $user = User::firstOrCreate(
                ['email' => $name . '@example.com'],
                [
                    'name' => ucwords(str_replace('_', ' ', $name)),
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $users->push($user);
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
        
        $postData = [
            [
                'title' => '¿Problemas al usar $lookup en MongoDB?',
                'tags' => ['MongoDB', 'Aggregation', 'Consultas'],
            ],
            [
                'title' => 'Diferencias entre MySQL y MongoDB',
                'tags' => ['MySQL', 'MongoDB', 'Comparación'],
            ],
            [
                'title' => '¿Cómo optimizar queries en Laravel?',
                'tags' => ['Laravel', 'Optimización', 'Performance'],
            ],
            [
                'title' => 'Mejores prácticas con React y TypeScript',
                'tags' => ['React', 'TypeScript', 'JavaScript'],
            ],
            [
                'title' => 'Tutorial: MongoDB Aggregation Framework',
                'tags' => ['MongoDB', 'Tutorial', 'Aggregation'],
            ],
            [
                'title' => '¿Vale la pena usar Inertia.js?',
                'tags' => ['Inertia', 'Laravel', 'React'],
            ],
            [
                'title' => 'Comparación: SQL vs NoSQL en 2025',
                'tags' => ['SQL', 'NoSQL', 'Bases de Datos'],
            ],
            [
                'title' => 'Laravel 12: Novedades y cambios',
                'tags' => ['Laravel', 'PHP', 'Novedades'],
            ],
            [
                'title' => '¿Cómo implementar búsqueda full-text en MongoDB?',
                'tags' => ['MongoDB', 'Búsqueda', 'Indexación'],
            ],
            [
                'title' => 'Problema con relaciones en Laravel MongoDB',
                'tags' => ['Laravel', 'MongoDB', 'Eloquent'],
            ],
        ];

        $posts = collect();
        foreach ($postData as $data) {
            $user = $users->random();
            $content = $this->generatePostContent($data['title']);
            
            $post = Post::create([
                'title' => $data['title'],
                'content' => $content,
                'user_id' => $user->_id,
                'tags' => $data['tags'],
                'votes_count' => 0,
                'comments_count' => 0,
                'created_at' => now()->subDays(rand(0, 30)),
            ]);
            
            $posts->push($post);
        }

        $this->command->info("✅ Creados {$posts->count()} posts");

        // Crear comentarios raíz
        $this->command->info('💬 Creando comentarios raíz...');
        
        $rootComments = collect();
        $totalComments = 0;
        
        foreach ($posts as $post) {
            $numComments = rand(2, 6); // Comentarios directos al post
            
            for ($i = 0; $i < $numComments; $i++) {
                $commenter = $users->random();
                
                $comment = Comment::create([
                    'content' => $this->generateCommentContent(),
                    'user_id' => $commenter->_id,
                    'post_id' => $post->_id,
                    'parent_id' => null, // Comentario raíz
                    'votes_count' => 0,
                    'created_at' => $post->created_at->addHours(rand(1, 48)),
                ]);
                
                $rootComments->push($comment);
                $totalComments++;
            }
        }

        $this->command->info("✅ Creados {$totalComments} comentarios raíz");
        
        // Crear respuestas a comentarios (comentarios anidados)
        $this->command->info('💬 Creando respuestas a comentarios...');
        
        $totalReplies = 0;
        foreach ($rootComments as $rootComment) {
            // 50% de probabilidad de que un comentario tenga respuestas
            if (rand(1, 10) <= 5) {
                $numReplies = rand(1, 3); // 1-3 respuestas por comentario
                
                for ($i = 0; $i < $numReplies; $i++) {
                    $replier = $users->random();
                    
                    Comment::create([
                        'content' => $this->generateReplyContent(),
                        'user_id' => $replier->_id,
                        'post_id' => $rootComment->post_id,
                        'parent_id' => $rootComment->_id, // Referencia al comentario padre
                        'votes_count' => 0,
                        'created_at' => $rootComment->created_at->addHours(rand(1, 24)),
                    ]);
                    
                    $totalReplies++;
                    $totalComments++;
                }
            }
        }

        $this->command->info("✅ Creadas {$totalReplies} respuestas anidadas");
        $this->command->info("📊 Total de comentarios: {$totalComments}");

        // Crear votos para posts
        $this->command->info('👍 Creando votos para posts...');
        
        $totalVotes = 0;
        foreach ($posts as $post) {
            // Más votos para posts más antiguos (simulando actividad acumulada)
            $daysOld = now()->diffInDays($post->created_at);
            $maxVoters = min(3 + floor($daysOld / 3), $users->count());
            $numVoters = rand(1, $maxVoters);
            
            $voters = $users->random(min($numVoters, $users->count()));
            
            foreach ($voters as $voter) {
                // 80% probabilidad de upvote, 20% downvote (más realista)
                $voteType = rand(1, 10) <= 8 ? 'up' : 'down';
                
                Vote::create([
                    'user_id' => $voter->_id,
                    'votable_type' => 'App\Models\Post',
                    'votable_id' => $post->_id,
                    'type' => $voteType,
                ]);
                
                $totalVotes++;
            }
        }

        $this->command->info("✅ Creados {$totalVotes} votos para posts");
        
        // Crear votos para comentarios
        $this->command->info('👍 Creando votos para comentarios...');
        
        $commentVotes = 0;
        $allComments = Comment::all();
        
        foreach ($allComments as $comment) {
            // Algunos comentarios tienen votos, otros no
            if (rand(1, 10) <= 6) { // 60% de comentarios tienen votos
                $numVoters = rand(1, 4);
                $voters = $users->random(min($numVoters, $users->count()));
                
                foreach ($voters as $voter) {
                    // No votar tu propio comentario
                    if ($voter->_id == $comment->user_id) {
                        continue;
                    }
                    
                    $voteType = rand(1, 10) <= 9 ? 'up' : 'down'; // 90% upvotes para comentarios
                    
                    Vote::create([
                        'user_id' => $voter->_id,
                        'votable_type' => 'App\Models\Comment',
                        'votable_id' => $comment->_id,
                        'type' => $voteType,
                    ]);
                    
                    $commentVotes++;
                }
            }
        }

        $this->command->info("✅ Creados {$commentVotes} votos para comentarios");

        // Actualizar contadores de posts
        $this->command->info('🔄 Actualizando contadores de posts...');
        
        foreach ($posts as $post) {
            $post->refresh();
            $post->comments_count = $post->comments()->count();
            $post->updateVotesCount();
            $post->save();
        }
        
        // Actualizar contadores de comentarios
        $this->command->info('🔄 Actualizando contadores de comentarios...');
        
        foreach ($allComments as $comment) {
            $comment->refresh();
            $upvotes = $comment->votes()->where('type', 'up')->count();
            $downvotes = $comment->votes()->where('type', 'down')->count();
            $comment->votes_count = $upvotes - $downvotes;
            $comment->save();
        }

        // Actualizar contadores de tendencias basado en tags de posts
        $this->command->info('🔄 Actualizando contadores de tendencias...');
        
        foreach ($trends as $trend) {
            $count = 0;
            foreach ($posts as $post) {
                if (isset($post->tags) && in_array($trend->name, $post->tags)) {
                    $count++;
                }
            }
            $trend->posts_count = $count;
            $trend->save();
        }

        $this->command->info('');
        $this->command->info('🎉 ¡Seeder completado exitosamente!');
        $this->command->info('');
        $this->command->info('📊 Resumen:');
        $this->command->info("   • {$users->count()} usuarios");
        $this->command->info("   • {$posts->count()} posts");
        $this->command->info("   • {$totalComments} comentarios");
        $this->command->info("   • {$totalVotes} votos en posts");
        $this->command->info("   • {$commentVotes} votos en comentarios");
        $this->command->info("   • {$trends->count()} tendencias");
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
            "He estado trabajando con este tema y me encuentro con un problema interesante. ¿Alguien tiene experiencia con esto?\n\nHe intentado varias aproximaciones pero no logro encontrar la solución óptima. Cualquier ayuda o sugerencia sería muy apreciada.\n\nAdjunto algunos detalles de lo que he intentado hasta ahora...",
            
            "Quiero compartir mi experiencia con este tema. He estado usando esto en producción por varios meses y aquí están mis conclusiones:\n\n✅ Ventajas:\n- Mejora significativa en el rendimiento\n- Más fácil de mantener\n- Mejor escalabilidad\n\n❌ Desventajas:\n- Curva de aprendizaje inicial\n- Documentación limitada en español\n\nEspero que les sea útil para sus proyectos.",
            
            "Tengo una duda sobre cómo implementar esto correctamente. He leído la documentación oficial pero algunos conceptos no me quedan del todo claros.\n\n¿Alguien puede explicarlo de forma más simple o compartir un ejemplo práctico?\n\nGracias de antemano por su ayuda.",
            
            "📚 Tutorial paso a paso sobre cómo resolver este problema común:\n\n1. **Preparación**: Asegúrate de tener todo configurado correctamente\n2. **Implementación**: Sigue estos pasos específicos\n3. **Validación**: Verifica que todo funcione como esperado\n4. **Optimización**: Mejora el rendimiento\n\nHe documentado todo el proceso para que sea fácil de seguir. Si tienen dudas, déjenlas en los comentarios.",
            
            "¿Cuál es la mejor práctica para este caso en 2025? He visto varias aproximaciones diferentes y no estoy seguro cuál es la más recomendada por la comunidad.\n\nOpción A: [descripción]\nOpción B: [descripción]\n\n¿Qué opinan ustedes? ¿Cuál usan en sus proyectos?",
            
            "Después de investigar bastante sobre este tema, encontré una solución que funciona muy bien.\n\nEl problema principal era [X], y la solución consiste en [Y]. Aquí les comparto el código que implementé:\n\n```\n// Código de ejemplo\n```\n\n¿Alguien más ha usado este enfoque?",
            
            "⚠️ Advertencia: Si están usando esto en producción, hay un problema conocido que puede causar issues.\n\nLa solución temporal es [X], pero el equipo de desarrollo está trabajando en un fix oficial.\n\n¿Alguien más se ha encontrado con esto?",
            
            "Pregunta rápida: ¿Existe alguna forma más eficiente de hacer esto?\n\nActualmente estoy usando [método actual], pero siento que hay una mejor manera. He visto algunas referencias en la documentación pero no estoy seguro de cómo aplicarlo a mi caso específico.\n\n¡Gracias por la ayuda!",
        ];

        return $contents[array_rand($contents)];
    }

    private function generateCommentContent(): string
    {
        $comments = [
            'Excelente pregunta, yo tuve exactamente el mismo problema hace poco y lo resolví así: [explicación]. ¿Te funciona?',
            'Gracias por compartir, muy útil esta información. Lo voy a implementar en mi proyecto.',
            'Yo lo resolví de esta manera: primero verificas la configuración, luego actualizas las dependencias y finalmente reinicias el servicio.',
            '¿Ya intentaste revisar la documentación oficial? Hay una sección específica sobre esto que puede ayudarte.',
            'Esto me pasó también la semana pasada. La solución fue actualizar a la última versión, al parecer era un bug conocido.',
            'Muy buen aporte, lo voy a probar en mi proyecto y te cuento cómo me va.',
            'No estoy completamente seguro, pero creo que el problema podría estar en otro lado. ¿Revisaste [X]?',
            'Gracias! Esto es justo lo que necesitaba. Llevaba días buscando una solución así.',
            '+1 a esta pregunta, yo también tengo el mismo problema.',
            'Interesante enfoque, no había pensado en eso. ¿Tienes algún benchmark de rendimiento?',
            'En mi experiencia, es mejor usar [alternativa] porque [razón]. Pero depende del caso de uso.',
            'Puedes compartir más detalles sobre tu implementación? Me gustaría probarlo.',
            'Cuidado con ese enfoque, puede causar problemas de escalabilidad a largo plazo.',
            'Excelente tutorial! Muy bien explicado y fácil de seguir. 👍',
            '¿Esto funciona también con [otra tecnología/versión]?',
            'Buen punto. Otra cosa a considerar es [aspecto adicional].',
        ];

        return $comments[array_rand($comments)];
    }

    private function generateReplyContent(): string
    {
        $replies = [
            'Totalmente de acuerdo contigo.',
            'Gracias por la respuesta! Me ayudó mucho.',
            'Interesante punto de vista, no lo había pensado así.',
            '¿Podrías dar más detalles sobre esto?',
            'Yo hice algo similar y me funcionó bien.',
            'Eso no me funcionó en mi caso, ¿alguna otra idea?',
            'Perfecto, lo voy a intentar ahora mismo.',
            'Gracias! Exactamente lo que necesitaba.',
            'Hmm, no estoy seguro de que eso sea la mejor opción...',
            'Me pasó lo mismo, tuve que hacer un workaround.',
            '👍 Excelente explicación!',
            'Confirmo que esto funciona, lo acabo de probar.',
            '¿Y si en lugar de eso intentas [otra alternativa]?',
            'Buena observación, hay que tener eso en cuenta.',
            'Lo intenté pero me dio otro error diferente.',
            'Muchas gracias por tomarte el tiempo de responder!',
            'Esa es una forma más elegante de resolverlo.',
            '¿Has considerado usar [herramienta/librería]?',
            'Yo también recomiendo ese enfoque.',
            'Creo que el problema está en [otra cosa].',
        ];

        return $replies[array_rand($replies)];
    }
}
