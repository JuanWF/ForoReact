<?php

namespace App\Http\Controllers;

use App\Models\Trend;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TrendController extends Controller
{

    public function index()
    {
        // Ya no se usa, redirigir al home
        return redirect()->route('posts.index');
    }

    public function show($slug)
    {
        // Convertir slug a nombre de tag
        $tagName = ucfirst(str_replace('-', ' ', $slug));

        // Obtener todos los posts y filtrar por tag en memoria
        $allPosts = Post::with(['user'])
            ->whereNotNull('tags')
            ->recent()
            ->get();

        $filteredPosts = $allPosts->filter(function($post) use ($tagName, $slug) {
            if (!is_array($post->tags)) {
                return false;
            }
            
            foreach ($post->tags as $tag) {
                if (strtolower(trim($tag)) === strtolower($tagName) || 
                    strtolower(trim($tag)) === strtolower($slug)) {
                    return true;
                }
            }
            return false;
        });

        //paginacion
        $page = request()->get('page', 1);
        $perPage = 15;
        $total = $filteredPosts->count();
        $posts = $filteredPosts->forPage($page, $perPage)->values();

        return Inertia::render('Trend/Show', [
            'trend' => [
                '_id' => md5($slug),
                'name' => $tagName,
                'slug' => $slug,
                'posts_count' => $total,
                'category' => 'General',
            ],
            'posts' => [
                'data' => $posts,
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }



}
