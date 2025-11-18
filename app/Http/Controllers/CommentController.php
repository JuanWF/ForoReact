<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

/**
 * CommentController - Maneja comentarios en posts
 * 
 * EXPLICACIÓN:
 * - Solo necesitamos store y destroy
 * - Los comentarios se muestran en la vista del post (PostController@show)
 * - Aquí solo manejamos crear y eliminar
 */
class CommentController extends Controller
{
    /**
     * Crear un nuevo comentario
     * 
     * QUERY EXPLICADA:
     * - Validamos que post_id sea un ObjectId válido de MongoDB
     * - Verificamos que el post exista
     * - Creamos el documento en la colección 'comments'
     * - El evento boot() del modelo Comment incrementa automáticamente el contador del post
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'post_id' => 'required|string', // El ObjectId como string
            'parent_id' => 'nullable|string', // Para respuestas anidadas
        ]);

        // Verificar que el post exista
        $post = Post::findOrFail($validated['post_id']);

        $comment = Comment::create([
            'content' => $validated['content'],
            'post_id' => $validated['post_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'user_id' => (string) auth()->user()->_id,
            'votes_count' => 0,
        ]);

        // Cargar la relación user para retornar en la respuesta
        $comment->load('user');

        // Redirigir de vuelta al post con el comentario en los datos
        return redirect()->route('posts.show', $validated['post_id'])
            ->with('success', 'Comentario publicado exitosamente.');
    }

    /**
     * Eliminar un comentario
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Verificar que el usuario sea el dueño
        if ((string) $comment->user_id !== (string) auth()->user()->_id) {
            abort(403, 'No autorizado');
        }

        // Eliminar respuestas primero
        $comment->replies()->delete();
        
        // Eliminar el comentario
        // El evento boot() decrementará automáticamente el contador del post
        $comment->delete();

        return back()->with('success', 'Comentario eliminado');
    }

    /**
     * Actualizar un comentario (opcional)
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        if ((string) $comment->user_id !== (string) auth()->user()->_id) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment->update($validated);

        return back()->with('success', 'Comentario actualizado');
    }
}
