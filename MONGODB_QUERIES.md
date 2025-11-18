# 📚 Ejemplos de Queries MongoDB - ForoDB

Este documento contiene ejemplos prácticos de cómo trabajar con MongoDB en el contexto de ForoDB.

## 🔍 Queries Básicas

### Crear Documentos

```php
// Laravel Eloquent
$post = Post::create([
    'title' => '¿Cómo usar MongoDB?',
    'content' => 'Estoy aprendiendo MongoDB...',
    'user_id' => auth()->id(),
]);

// MongoDB Shell equivalente
db.posts.insertOne({
    title: "¿Cómo usar MongoDB?",
    content: "Estoy aprendiendo MongoDB...",
    user_id: ObjectId("507f191e810c19729de860ea"),
    votes_count: 0,
    comments_count: 0,
    created_at: new Date(),
    updated_at: new Date()
});
```

### Leer Documentos

```php
// Obtener todos los posts
$posts = Post::all();

// Con filtro
$posts = Post::where('votes_count', '>', 10)->get();

// Un solo documento
$post = Post::find($id);
$post = Post::where('slug', 'mi-post')->first();

// MongoDB Shell
db.posts.find({});
db.posts.find({ votes_count: { $gt: 10 } });
db.posts.findOne({ _id: ObjectId("...") });
```

### Actualizar Documentos

```php
// Actualizar campo
$post->update(['title' => 'Nuevo título']);

// Incrementar contador
$post->increment('votes_count');
$post->decrement('votes_count');

// Actualizar múltiples
Post::where('user_id', $userId)->update(['status' => 'archived']);

// MongoDB Shell
db.posts.updateOne(
    { _id: ObjectId("...") },
    { $set: { title: "Nuevo título" } }
);

db.posts.updateOne(
    { _id: ObjectId("...") },
    { $inc: { votes_count: 1 } }
);
```

### Eliminar Documentos

```php
// Eliminar un documento
$post->delete();

// Eliminar con condición
Post::where('votes_count', '<', -10)->delete();

// MongoDB Shell
db.posts.deleteOne({ _id: ObjectId("...") });
db.posts.deleteMany({ votes_count: { $lt: -10 } });
```

---

## 🔗 Queries con Relaciones

### Eager Loading (N+1 Problem)

```php
// ❌ MALO: N+1 queries
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name; // Query por cada post
}

// ✅ BUENO: Eager loading (2 queries total)
$posts = Post::with('user')->get();
foreach ($posts as $post) {
    echo $post->user->name; // Sin query adicional
}

// ✅ MEJOR: Eager loading múltiple
$posts = Post::with(['user', 'comments.user'])->get();

// MongoDB Shell (manual)
// Query 1: Obtener posts
var posts = db.posts.find({}).toArray();

// Query 2: Obtener usuarios de esos posts
var userIds = posts.map(p => p.user_id);
var users = db.users.find({ _id: { $in: userIds } }).toArray();
```

### Contar Relaciones

```php
// Contar comentarios de un post
$count = $post->comments()->count();

// Contar posts con comentarios
$postsWithComments = Post::has('comments')->count();

// Filtrar posts con más de 5 comentarios
$popularPosts = Post::has('comments', '>', 5)->get();

// MongoDB Shell
db.comments.countDocuments({ post_id: ObjectId("...") });

db.posts.aggregate([
    {
        $lookup: {
            from: "comments",
            localField: "_id",
            foreignField: "post_id",
            as: "comments"
        }
    },
    {
        $match: {
            $expr: { $gt: [{ $size: "$comments" }, 5] }
        }
    }
]);
```

---

## 🔍 Queries Avanzadas

### Búsqueda por Texto

```php
// Buscar en título o contenido
$posts = Post::where(function($query) use ($search) {
    $query->where('title', 'like', "%{$search}%")
          ->orWhere('content', 'like', "%{$search}%");
})->get();

// MongoDB Shell con regex
db.posts.find({
    $or: [
        { title: { $regex: "mongodb", $options: "i" } },
        { content: { $regex: "mongodb", $options: "i" } }
    ]
});

// Índice de texto (mejor performance)
db.posts.createIndex({ title: "text", content: "text" });
db.posts.find({ $text: { $search: "mongodb laravel" } });
```

### Agregaciones

```php
// Contar posts por usuario
$postsByUser = Post::raw(function($collection) {
    return $collection->aggregate([
        [
            '$group' => [
                '_id' => '$user_id',
                'count' => ['$sum' => 1],
                'total_votes' => ['$sum' => '$votes_count']
            ]
        ],
        ['$sort' => ['count' => -1]],
        ['$limit' => 10]
    ]);
});

// MongoDB Shell
db.posts.aggregate([
    {
        $group: {
            _id: "$user_id",
            count: { $sum: 1 },
            total_votes: { $sum: "$votes_count" }
        }
    },
    { $sort: { count: -1 } },
    { $limit: 10 }
]);
```

### Queries con Fechas

```php
// Posts de hoy
$today = Post::whereDate('created_at', today())->get();

// Posts de la última semana
$lastWeek = Post::where('created_at', '>', now()->subWeek())->get();

// Posts entre fechas
$posts = Post::whereBetween('created_at', [
    now()->subMonth(),
    now()
])->get();

// MongoDB Shell
db.posts.find({
    created_at: {
        $gte: new Date("2025-01-01"),
        $lte: new Date("2025-01-31")
    }
});
```

---

## 💾 Queries de Performance

### Índices

```php
// Crear índices desde Laravel
Schema::connection('mongodb')->collection('posts', function($collection) {
    $collection->index('user_id');
    $collection->index('created_at');
    $collection->index(['votes_count' => -1]);
});

// MongoDB Shell
db.posts.createIndex({ user_id: 1 });
db.posts.createIndex({ created_at: -1 });
db.posts.createIndex({ votes_count: -1 });

// Índice compuesto
db.posts.createIndex({ user_id: 1, created_at: -1 });

// Índice de texto
db.posts.createIndex({ title: "text", content: "text" });

// Ver índices
db.posts.getIndexes();
```

### Proyecciones (Solo Campos Necesarios)

```php
// Solo título e ID
$posts = Post::select('_id', 'title')->get();

// Excluir campos grandes
$posts = Post::all(['_id', 'title', 'votes_count']);

// MongoDB Shell
db.posts.find({}, { title: 1, votes_count: 1 });
db.posts.find({}, { content: 0 }); // Excluir content
```

### Paginación Eficiente

```php
// Laravel pagination
$posts = Post::paginate(15);

// Cursor pagination (mejor para datasets grandes)
$posts = Post::cursorPaginate(15);

// MongoDB Shell (manual)
db.posts.find()
    .sort({ created_at: -1 })
    .skip(0)
    .limit(15);
```

---

## 🎯 Queries Específicas del Foro

### Top Posts por Votos

```php
$topPosts = Post::orderBy('votes_count', 'desc')
    ->limit(10)
    ->get();

// MongoDB Shell
db.posts.find()
    .sort({ votes_count: -1 })
    .limit(10);
```

### Posts sin Comentarios

```php
$postsWithoutComments = Post::where('comments_count', 0)->get();

// O usando relación
$postsWithoutComments = Post::doesntHave('comments')->get();

// MongoDB Shell
db.posts.find({ comments_count: 0 });
```

### Usuarios Más Activos

```php
// Usuarios con más posts
$activeUsers = Post::raw(function($collection) {
    return $collection->aggregate([
        [
            '$group' => [
                '_id' => '$user_id',
                'post_count' => ['$sum' => 1]
            ]
        ],
        ['$sort' => ['post_count' => -1]],
        ['$limit' => 10],
        [
            '$lookup' => [
                'from' => 'users',
                'localField' => '_id',
                'foreignField' => '_id',
                'as' => 'user'
            ]
        ]
    ]);
});

// MongoDB Shell
db.posts.aggregate([
    {
        $group: {
            _id: "$user_id",
            post_count: { $sum: 1 }
        }
    },
    { $sort: { post_count: -1 } },
    { $limit: 10 },
    {
        $lookup: {
            from: "users",
            localField: "_id",
            foreignField: "_id",
            as: "user"
        }
    }
]);
```

### Posts Trending (24 horas)

```php
$trending = Post::where('created_at', '>', now()->subDay())
    ->orderBy('votes_count', 'desc')
    ->orderBy('comments_count', 'desc')
    ->limit(10)
    ->get();

// MongoDB Shell
db.posts.find({
    created_at: { $gt: new Date(Date.now() - 86400000) }
})
.sort({ votes_count: -1, comments_count: -1 })
.limit(10);
```

### Votos de un Usuario

```php
// Verificar si un usuario votó un post
$userVote = Vote::where('user_id', auth()->id())
    ->where('votable_type', 'App\Models\Post')
    ->where('votable_id', $postId)
    ->first();

// Obtener todos los votos de un usuario
$userVotes = Vote::where('user_id', auth()->id())
    ->with('votable')
    ->get();

// MongoDB Shell
db.votes.findOne({
    user_id: ObjectId("..."),
    votable_type: "App\\Models\\Post",
    votable_id: ObjectId("...")
});
```

---

## 🧪 Testing Queries

### En Tinker

```bash
php artisan tinker
```

```php
// Crear un post
$post = Post::create([
    'title' => 'Test',
    'content' => 'Contenido de prueba',
    'user_id' => User::first()->_id
]);

// Ver el post
$post->toArray();

// Cargar relaciones
$post->load('user');
$post->user->name;

// Ejecutar query
$posts = Post::where('votes_count', '>', 10)->get();
$posts->count();

// Ver SQL (MongoDB query)
Post::where('votes_count', '>', 10)->toSql();
```

### Debugging de Queries

```php
// Habilitar logging de queries
DB::enableQueryLog();

// Ejecutar queries
$posts = Post::with('user')->get();

// Ver queries ejecutadas
dd(DB::getQueryLog());
```

---

## 📊 Análisis de Datos

### Estadísticas del Foro

```php
// Total de posts
$totalPosts = Post::count();

// Total de comentarios
$totalComments = Comment::count();

// Promedio de votos por post
$avgVotes = Post::avg('votes_count');

// Post más popular
$mostPopular = Post::orderBy('votes_count', 'desc')->first();

// MongoDB Shell
db.posts.countDocuments({});
db.comments.countDocuments({});

db.posts.aggregate([
    { $group: { _id: null, avg_votes: { $avg: "$votes_count" } } }
]);
```

---

## 🔒 Seguridad

### Prevenir Injection

```php
// ✅ BUENO: Usar Eloquent
$posts = Post::where('user_id', $userId)->get();

// ✅ BUENO: Bindings
$posts = Post::whereRaw(['user_id' => $userId])->get();

// ❌ MALO: Concatenación directa (nunca hacer esto)
$posts = Post::whereRaw("user_id = {$userId}")->get();
```

### Validar ObjectIds

```php
use MongoDB\BSON\ObjectId;

// Verificar si es un ObjectId válido
if (ObjectId::isValid($id)) {
    $post = Post::find($id);
}

// O usar try-catch
try {
    $post = Post::findOrFail($id);
} catch (ModelNotFoundException $e) {
    abort(404);
}
```

---

## 📖 Recursos

- [MongoDB PHP Driver Docs](https://www.mongodb.com/docs/drivers/php/)
- [Laravel MongoDB Package](https://www.mongodb.com/docs/drivers/php/laravel-mongodb/)
- [MongoDB Query Operators](https://www.mongodb.com/docs/manual/reference/operator/query/)
- [Aggregation Framework](https://www.mongodb.com/docs/manual/aggregation/)

---

**Tip**: Usa MongoDB Compass (GUI) para explorar tu base de datos visualmente y generar queries.

Download: https://www.mongodb.com/products/compass
