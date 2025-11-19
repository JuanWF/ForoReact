<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Trend;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{

    public function index(Request $request)
    {
        // Determinar orden: 'recent' o 'popular'
        $sort = $request->get('sort', 'recent');

        $query = Post::with(['user']);

        if ($sort === 'popular') {
            $query->popular();
        } else {
            $query->recent();
        }

        $posts = $query->paginate(15);

        // Calcular tendencias en tiempo real desde los tags de posts recientes
        $trends = $this->getTopTrends();

        // Si el usuario está autenticado, obtener sus votos
        $userVotes = [];
        if (auth()->check()) {
            $postIds = $posts->pluck('_id')->toArray();
            $votes = auth()->user()->votes()
                ->where('votable_type', 'App\Models\Post')
                ->whereIn('votable_id', $postIds)
                ->get();

            // Crear un mapa de post_id => tipo de voto
            foreach ($votes as $vote) {
                $userVotes[$vote->votable_id] = $vote->type;
            }
        }

        return Inertia::render('Post/Index', [
            'posts' => $posts,
            'trends' => $trends,
            'userVotes' => $userVotes,
            'sort' => $sort,
        ]);
    }

    public function show($id)
    {
        $post = Post::with(['user'])->findOrFail($id);

        // Cargar comentarios raíz (no respuestas) con sus respuestas
        $post->load([
            'comments' => function ($query) {
                $query->root()->recent()->with(['user', 'replies.user']);
            }
        ]);

        // Verificar voto del usuario en este post
        $userVote = null;
        $userCommentVotes = [];
        
        if (auth()->check()) {
            $userVote = auth()->user()->hasVoted('App\Models\Post', $post->_id);
            
            // Obtener votos del usuario en los comentarios de este post
            $commentIds = $post->comments->pluck('_id')->toArray();
            
            // Incluir IDs de las respuestas también
            foreach ($post->comments as $comment) {
                if ($comment->replies) {
                    $commentIds = array_merge($commentIds, $comment->replies->pluck('_id')->toArray());
                }
            }
            
            if (!empty($commentIds)) {
                $votes = auth()->user()->votes()
                    ->where('votable_type', 'App\Models\Comment')
                    ->whereIn('votable_id', $commentIds)
                    ->get();
                
                foreach ($votes as $vote) {
                    $userCommentVotes[$vote->votable_id] = $vote->type;
                }
            }
        }

        return Inertia::render('Post/Show', [
            'post' => $post,
            'userVote' => $userVote,
            'userCommentVotes' => $userCommentVotes,
        ]);
    }

    public function create()
    {
        return Inertia::render('Post/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $post = Post::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'user_id' => (string) auth()->user()->_id,
            'votes_count' => 0,
            'comments_count' => 0,
            'tags' => $validated['tags'] ?? [],
        ]);

        return redirect()->route('posts.show', $post->_id)
            ->with('success', 'Post creado exitosamente');
    }

    /**
     * Actualizar un post existente
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Verificar que el usuario sea el dueño
        if ((string) $post->user_id !== (string) auth()->user()->_id) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // En MongoDB, update() funciona igual que en SQL
        $post->update($validated);

        return redirect()->route('posts.show', $post->_id)
            ->with('success', 'Post actualizado');
    }

    /**
     * Eliminar un post
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Comparar user_id del post con el _id del usuario autenticado
        if ((string) $post->user_id !== (string) auth()->user()->_id) {
            abort(403, 'No autorizado');
        }

        // Eliminar comentarios asociados primero
        $post->comments()->delete();
        
        // Eliminar votos asociados
        $post->votes()->delete();
        
        // Eliminar el post
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post eliminado');
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (empty($query)) {
            return redirect()->route('posts.index');
        }

        // Buscar en título y contenido usando MongoDB text search
        $posts = Post::with(['user'])
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->recent()
            ->paginate(15);

        $trends = $this->getTopTrends();

        // Si el usuario está autenticado, obtener sus votos
        $userVotes = [];
        if (auth()->check()) {
            $postIds = $posts->pluck('_id')->toArray();
            $votes = auth()->user()->votes()
                ->where('votable_type', 'App\Models\Post')
                ->whereIn('votable_id', $postIds)
                ->get();

            foreach ($votes as $vote) {
                $userVotes[$vote->votable_id] = $vote->type;
            }
        }

        return Inertia::render('Post/Index', [
            'posts' => $posts,
            'trends' => $trends,
            'userVotes' => $userVotes,
            'search' => $query,
            'sort' => 'recent',
        ]);
    }

    private function getTopTrends($limit = 5)
    {
        // Posts de los últimos 30 días
        $posts = Post::whereNotNull('tags')
            ->where('created_at', '>', now()->subDays(30))
            ->get();

        // Contar tags
        $tagCounts = [];
        foreach ($posts as $post) {
            if (is_array($post->tags)) {
                foreach ($post->tags as $tag) {
                    $tag = trim($tag);
                    if (!isset($tagCounts[$tag])) {
                        $tagCounts[$tag] = 0;
                    }
                    $tagCounts[$tag]++;
                }
            }
        }

        // Ordenar por más usados
        arsort($tagCounts);
        
        // Tomar los top N
        $topTags = array_slice($tagCounts, 0, $limit, true);

        // Mapeo simple de categorías
        $categoryMap = [
            'MongoDB' => 'Bases de Datos',
            'MySQL' => 'Bases de Datos',
            'SQL' => 'Bases de Datos',
            'Laravel' => 'Backend',
            'PHP' => 'Backend',
            'React' => 'Frontend',
            'JavaScript' => 'Frontend',
            'TypeScript' => 'Frontend',
        ];

        // Convertir a formato de tendencias
        $trends = [];
        foreach ($topTags as $tag => $count) {
            $trends[] = [
                '_id' => md5($tag),
                'name' => $tag,
                'slug' => \Illuminate\Support\Str::slug($tag),
                'posts_count' => $count,
                'category' => $categoryMap[$tag] ?? 'General',
                'score' => $count * 10,
            ];
        }

        return $trends;
    }
}
