<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class VoteController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'votable_type' => 'required|in:post,comment',
            'votable_id' => 'required|string',
            'type' => 'required|in:up,down',
        ]);

        // Convertir tipo corto a nombre completo del modelo
        $votableTypeMap = [
            'post' => 'App\Models\Post',
            'comment' => 'App\Models\Comment',
        ];
        
        $votableType = $votableTypeMap[$validated['votable_type']];
        
        // Verificar que el votable exista
        $votableModel = $votableType === 'App\Models\Post' ? Post::class : Comment::class;
        $votable = $votableModel::findOrFail($validated['votable_id']);

        // Buscar voto existente del usuario para este item
        $existingVote = Vote::where('user_id', (string) auth()->user()->_id)
            ->where('votable_type', $votableType)
            ->where('votable_id', $validated['votable_id'])
            ->first();

        if ($existingVote) {
            // Si ya votó lo mismo, eliminar el voto (toggle)
            if ($existingVote->type === $validated['type']) {
                $existingVote->delete();
                
                return redirect()->back();
            }
            
            // Si votó diferente, cambiar el voto
            $existingVote->update(['type' => $validated['type']]);
            
            return redirect()->back();
        }

        // Crear nuevo voto
        Vote::create([
            'user_id' => (string) auth()->user()->_id,
            'votable_type' => $votableType,
            'votable_id' => $validated['votable_id'],
            'type' => $validated['type'],
        ]);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $vote = Vote::findOrFail($id);

        if ($vote->user_id !== auth()->id()) {
            abort(403, 'No autorizado');
        }

        $vote->delete();

        return back()->with('success', 'Voto eliminado');
    }

    public function show(Request $request, $votableType, $votableId)
    {
        $votableTypeMap = [
            'post' => 'App\Models\Post',
            'comment' => 'App\Models\Comment',
        ];
        
        $votableType = $votableTypeMap[$votableType] ?? abort(404);

        $upvotes = Vote::where('votable_type', $votableType)
            ->where('votable_id', $votableId)
            ->where('type', 'up')
            ->count();

        $downvotes = Vote::where('votable_type', $votableType)
            ->where('votable_id', $votableId)
            ->where('type', 'down')
            ->count();

        $userVote = null;
        if (auth()->check()) {
            $vote = Vote::where('user_id', auth()->id())
                ->where('votable_type', $votableType)
                ->where('votable_id', $votableId)
                ->first();
            
            $userVote = $vote ? $vote->type : null;
        }

        return response()->json([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
            'total' => $upvotes - $downvotes,
            'userVote' => $userVote,
        ]);
    }
}
