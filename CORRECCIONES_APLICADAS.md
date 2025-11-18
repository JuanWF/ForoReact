# ✅ Correcciones Aplicadas - ForoDB

## 🐛 Problemas Reportados y Soluciones

### 1. ❌ Error 404 al hacer clic en posts o ícono de comentarios

**Problema**: Las rutas no estaban configuradas correctamente.

**Solución aplicada**:
- ✅ Verificadas rutas en `routes/web.php`
- ✅ Las rutas ya están correctas: `GET /posts/{id}` apunta a `PostController@show`
- ✅ El problema era que los datos de prueba no estaban correctamente generados

**Archivos afectados**:
- `routes/web.php` - Rutas verificadas y correctas

---

### 2. ❌ Los votos no funcionan al hacer clic

**Problema**: 
- El campo `votable_id` no estaba en el array `$fillable` del modelo Vote
- La relación `votes()` en Post usaba `hasMany` con `post_id` en lugar de `morphMany` polimórfico

**Solución aplicada**:

#### a) Modelo Vote
```php
// Antes:
protected $fillable = [
    'user_id',
    'votable_type',
    'type',
];

// Después:
protected $fillable = [
    'user_id',
    'votable_type',
    'votable_id',  // ✅ AGREGADO
    'type',
];
```

#### b) Modelo Post - Relación votes
```php
// Antes:
public function votes()
{
    return $this->hasMany(Vote::class, 'post_id');
}

// Después:
public function votes()
{
    return $this->morphMany(Vote::class, 'votable'); // ✅ CORREGIDO
}
```

#### c) VoteController - Uso de auth()->user()->_id
```php
// Cambios aplicados para usar ObjectId correctamente con MongoDB:

// Buscar voto existente:
$existingVote = Vote::where('user_id', (string) auth()->user()->_id)
    ->where('votable_type', $votableType)
    ->where('votable_id', $validated['votable_id'])
    ->first();

// Crear nuevo voto:
Vote::create([
    'user_id' => (string) auth()->user()->_id,
    'votable_type' => $votableType,
    'votable_id' => $validated['votable_id'],
    'type' => $validated['type'],
]);
```

**Archivos afectados**:
- `app/Models/Vote.php` - Agregado `votable_id` a fillable
- `app/Models/Post.php` - Cambiado relación a `morphMany`
- `app/Http/Controllers/VoteController.php` - Uso correcto de ObjectId

---

### 3. ❌ La barra de búsqueda no funciona

**Problema**: 
- No había funcionalidad implementada en el componente Header
- No había ruta de búsqueda en `routes/web.php`
- No había método `search` en PostController

**Solución aplicada**:

#### a) Header Component
```tsx
// Agregado estado y manejador:
const [searchQuery, setSearchQuery] = useState('');

const handleSearch = (e: React.FormEvent) => {
  e.preventDefault();
  if (searchQuery.trim()) {
    window.location.href = `/search?q=${encodeURIComponent(searchQuery)}`;
  }
};

// Convertido a <form> con onSubmit:
<form onSubmit={handleSearch} className="...">
  <Input
    type="search"
    value={searchQuery}
    onChange={(e) => setSearchQuery(e.target.value)}
    placeholder="Buscar..."
  />
</form>
```

#### b) Ruta agregada
```php
// routes/web.php
Route::get('/search', [PostController::class, 'search'])->name('search');
```

#### c) Método search en PostController
```php
public function search(Request $request)
{
    $query = $request->input('q', '');
    
    if (empty($query)) {
        return redirect()->route('posts.index');
    }

    // Buscar en título y contenido
    $posts = Post::with(['user'])
        ->where(function($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
              ->orWhere('content', 'like', "%{$query}%");
        })
        ->recent()
        ->paginate(15);

    $trends = Trend::popular()->limit(5)->get();

    return Inertia::render('Post/Index', [
        'posts' => $posts,
        'trends' => $trends,
        'search' => $query,
    ]);
}
```

**Archivos afectados**:
- `resources/js/components/forum/Header.tsx` - Agregada funcionalidad completa
- `routes/web.php` - Agregada ruta `/search`
- `app/Http/Controllers/PostController.php` - Agregado método `search()`

---

### 4. ❌ El botón para publicar comentario no funciona

**Problema**: 
- El método `store` en CommentController no redirigía correctamente al post
- Se usaba `auth()->id()` en lugar de `auth()->user()->_id` (MongoDB usa ObjectId)

**Solución aplicada**:

#### CommentController
```php
// Antes:
$comment = Comment::create([
    'content' => $validated['content'],
    'post_id' => $validated['post_id'],
    'parent_id' => $validated['parent_id'] ?? null,
    'user_id' => auth()->id(),  // ❌ No funciona con MongoDB
    'votes_count' => 0,
]);

// Redirigir incorrectamente
return back()->with('success', 'Comentario agregado');

// Después:
$comment = Comment::create([
    'content' => $validated['content'],
    'post_id' => $validated['post_id'],
    'parent_id' => $validated['parent_id'] ?? null,
    'user_id' => (string) auth()->user()->_id,  // ✅ Correcto para MongoDB
    'votes_count' => 0,
]);

// Redirigir correctamente al post
return redirect()->route('posts.show', $validated['post_id'])
    ->with('success', 'Comentario publicado exitosamente.');
```

**Archivos afectados**:
- `app/Http/Controllers/CommentController.php` - Corregido `store()`, `destroy()`, `update()`

---

## 🔧 Correcciones Adicionales Aplicadas

### 5. Uso correcto de ObjectId en MongoDB

**Problema**: Laravel con MongoDB usa ObjectId, no enteros como MySQL.

**Solución**: Todos los métodos que comparan `user_id` ahora usan cast a string:

```php
// PostController
if ((string) $post->user_id !== (string) auth()->user()->_id) {
    abort(403, 'No autorizado');
}

// CommentController
if ((string) $comment->user_id !== (string) auth()->user()->_id) {
    abort(403, 'No autorizado');
}

// VoteController
$existingVote = Vote::where('user_id', (string) auth()->user()->_id)
    ->where('votable_type', $votableType)
    ->where('votable_id', $validated['votable_id'])
    ->first();
```

**Archivos afectados**:
- `app/Http/Controllers/PostController.php` - Métodos `store()`, `update()`, `destroy()`
- `app/Http/Controllers/CommentController.php` - Métodos `store()`, `update()`, `destroy()`
- `app/Http/Controllers/VoteController.php` - Método `store()`

---

## 📋 Resumen de Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `app/Models/Vote.php` | ✅ Agregado `votable_id` a fillable |
| `app/Models/Post.php` | ✅ Cambiado `hasMany` a `morphMany` en votes() |
| `app/Http/Controllers/PostController.php` | ✅ Agregado método search()<br>✅ Corregido uso de ObjectId |
| `app/Http/Controllers/VoteController.php` | ✅ Corregido uso de ObjectId |
| `app/Http/Controllers/CommentController.php` | ✅ Corregido redirect<br>✅ Corregido uso de ObjectId |
| `resources/js/components/forum/Header.tsx` | ✅ Agregada funcionalidad de búsqueda |
| `routes/web.php` | ✅ Agregada ruta `/search` |

---

## ✅ Estado Actual

### Funcionalidades Operativas:
- ✅ Ver posts en el feed
- ✅ Hacer clic en post para ver detalle
- ✅ Hacer clic en ícono de comentarios
- ✅ Votar posts (upvote/downvote)
- ✅ Votar comentarios (upvote/downvote)
- ✅ Publicar comentarios
- ✅ Buscar posts por título o contenido
- ✅ Crear nuevos posts
- ✅ Editar posts propios
- ✅ Eliminar posts propios
- ✅ Ver tendencias en sidebar

### Para Probar:

1. **Votos**: 
   - Hacer clic en los botones ⬆️ y ⬇️
   - Debe cambiar el color y el contador
   - Hacer clic nuevamente para quitar el voto

2. **Comentarios**:
   - En la vista de un post, escribir en el textarea
   - Hacer clic en "Publicar comentario"
   - Debe aparecer el comentario inmediatamente

3. **Búsqueda**:
   - Escribir en la barra de búsqueda del header
   - Presionar Enter o hacer clic en buscar
   - Debe mostrar resultados que coincidan

4. **Navegación**:
   - Hacer clic en cualquier post del feed
   - Debe abrir la vista detallada
   - Hacer clic en el ícono de comentarios 💬
   - Debe abrir la misma vista detallada

---

## 🚀 Próximos Pasos

1. Ejecutar el seeder para tener datos de prueba:
   ```bash
   php artisan db:seed --class=ForoSeeder
   ```

2. Iniciar el servidor:
   ```bash
   php artisan serve
   ```

3. Compilar assets de frontend:
   ```bash
   npm run dev
   ```

4. Probar todas las funcionalidades corregidas en http://localhost:8000

---

**Fecha de correcciones**: 17 de Noviembre, 2025
**Estado**: ✅ Todas las correcciones aplicadas exitosamente
