# ForoDB - Foro Completo con Laravel 12 + MongoDB + Inertia + React 19

Un foro completo construido con las últimas tecnologías web: Laravel 12, MongoDB (paquete oficial), Inertia.js, React 19, TypeScript, TailwindCSS y shadcn/ui.

## 🚀 Stack Tecnológico

- **Backend**: Laravel 12
- **Base de Datos**: MongoDB (mongodb/laravel-mongodb)
- **Frontend**: React 19 + TypeScript
- **Bridge**: Inertia.js (sin API REST)
- **UI**: TailwindCSS + shadcn/ui
- **Autenticación**: Laravel Fortify

## 📋 Requisitos Previos

- PHP >= 8.2
- Composer
- Node.js >= 18
- MongoDB >= 5.0 (local o MongoDB Atlas)
- Extensión PHP MongoDB: `pecl install mongodb`

## 🔧 Instalación

### 1. Clonar e instalar dependencias

```bash
# Instalar dependencias de Composer
composer install

# Instalar dependencias de NPM
npm install
```

### 2. Configurar variables de entorno

```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Generar la clave de la aplicación
php artisan key:generate
```

### 3. Configurar MongoDB

Edita tu archivo `.env`:

```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=forodb
DB_USERNAME=
DB_PASSWORD=

# Si usas MongoDB Atlas, usa el DSN:
# DB_DSN=mongodb+srv://usuario:password@cluster.mongodb.net/forodb
```

### 4. Verificar conexión a MongoDB

```bash
php artisan tinker
> DB::connection()->getMongoDB()->command(['ping' => 1]);
```

Si retorna `['ok' => 1]`, la conexión es exitosa.

### 5. Compilar assets frontend

```bash
# Desarrollo (con watch)
npm run dev

# Producción
npm run build
```

### 6. Iniciar servidor

```bash
php artisan serve
```

Visita: http://localhost:8000

## 📚 Entendiendo MongoDB para Principiantes

### ¿Qué es MongoDB?

MongoDB es una base de datos **NoSQL** que almacena datos en **documentos** (similar a JSON) en lugar de tablas con filas y columnas como SQL.

### Diferencias clave con MySQL/PostgreSQL

| Concepto SQL | Concepto MongoDB | Explicación |
|-------------|------------------|-------------|
| Database | Database | Mismo concepto |
| Table | Collection | Grupo de documentos |
| Row | Document | Un registro individual |
| Column | Field | Propiedad de un documento |
| Primary Key | _id (ObjectId) | Identificador único |
| Foreign Key | Reference (ObjectId) | Referencia a otro documento |
| JOIN | $lookup o populate | Unir colecciones |

### ¿Qué es un ObjectId?

```javascript
// Ejemplo de ObjectId
ObjectId("507f1f77bcf86cd799439011")

// Se compone de:
// - 4 bytes: timestamp de creación
// - 5 bytes: valor aleatorio
// - 3 bytes: contador incremental
```

**Ventajas**:
- Único globalmente (no necesitas auto-incremento)
- Contiene timestamp de creación
- Ordenable por fecha de creación

### Estructura de un Documento

```json
{
  "_id": ObjectId("507f1f77bcf86cd799439011"),
  "title": "¿Cómo usar MongoDB con Laravel?",
  "content": "Estoy aprendiendo MongoDB...",
  "user_id": ObjectId("507f191e810c19729de860ea"),
  "votes_count": 42,
  "comments_count": 8,
  "created_at": ISODate("2025-01-15T10:30:00Z"),
  "updated_at": ISODate("2025-01-15T15:45:00Z")
}
```

## 🗄️ Arquitectura de Base de Datos

### Colecciones

#### 1. `users` - Usuarios del sistema

```javascript
{
  _id: ObjectId,
  name: String,
  email: String,
  password: String (hashed),
  email_verified_at: DateTime,
  two_factor_secret: String,
  two_factor_recovery_codes: String,
  two_factor_confirmed_at: DateTime,
  remember_token: String,
  created_at: DateTime,
  updated_at: DateTime
}
```

#### 2. `posts` - Posts del foro

```javascript
{
  _id: ObjectId,
  title: String,
  content: String,
  user_id: ObjectId,           // Referencia a users
  votes_count: Integer,
  comments_count: Integer,
  created_at: DateTime,
  updated_at: DateTime
}
```

#### 3. `comments` - Comentarios en posts

```javascript
{
  _id: ObjectId,
  content: String,
  user_id: ObjectId,           // Referencia a users
  post_id: ObjectId,           // Referencia a posts
  parent_id: ObjectId | null,  // Para respuestas anidadas
  votes_count: Integer,
  created_at: DateTime,
  updated_at: DateTime
}
```

#### 4. `votes` - Sistema de votos

```javascript
{
  _id: ObjectId,
  user_id: ObjectId,           // Quién votó
  votable_type: String,        // 'App\Models\Post' o 'App\Models\Comment'
  votable_id: ObjectId,        // ID del post o comment
  type: String,                // 'up' o 'down'
  created_at: DateTime,
  updated_at: DateTime
}
```

#### 5. `trends` - Tendencias del foro

```javascript
{
  _id: ObjectId,
  name: String,
  slug: String,
  posts_count: Integer,
  score: Integer,
  category: String,
  created_at: DateTime,
  updated_at: DateTime
}
```

### Relaciones en MongoDB

#### Opción 1: Referencias (Usado en ForoDB)

**Ventajas**:
- Datos normalizados
- Sin duplicación
- Fácil actualización

**Ejemplo**:
```javascript
// Post
{ _id: 1, user_id: 100, title: "Post 1" }

// User
{ _id: 100, name: "Juan" }
```

**En Laravel**:
```php
// Obtener post con usuario
$post = Post::with('user')->find($id);
echo $post->user->name; // "Juan"
```

#### Opción 2: Documentos Embebidos (No usado aquí)

**Ventajas**:
- Lectura más rápida (todo en un documento)
- No necesitas "joins"

**Desventajas**:
- Duplicación de datos
- Difícil actualizar

**Ejemplo**:
```javascript
{
  _id: 1,
  title: "Post 1",
  user: {
    name: "Juan",
    email: "juan@example.com"
  }
}
```

## 🔍 Queries de MongoDB Explicadas

### 1. Crear un documento

```php
// Laravel Eloquent
Post::create([
    'title' => 'Mi post',
    'content' => 'Contenido',
    'user_id' => auth()->id()
]);

// MongoDB query equivalente
db.posts.insertOne({
    title: "Mi post",
    content: "Contenido",
    user_id: ObjectId("..."),
    created_at: new Date(),
    updated_at: new Date()
});
```

### 2. Leer documentos

```php
// Obtener todos los posts
Post::all();

// MongoDB
db.posts.find({});

// Con filtro
Post::where('user_id', $userId)->get();

// MongoDB
db.posts.find({ user_id: ObjectId("...") });

// Con ordenamiento
Post::orderBy('created_at', 'desc')->get();

// MongoDB
db.posts.find({}).sort({ created_at: -1 });
```

### 3. Relaciones (Eager Loading)

```php
// Laravel: Cargar post con usuario y comentarios
$post = Post::with(['user', 'comments.user'])->find($id);

// MongoDB hace múltiples queries:
// 1. db.posts.findOne({ _id: ObjectId("...") })
// 2. db.users.findOne({ _id: post.user_id })
// 3. db.comments.find({ post_id: post._id })
// 4. db.users.find({ _id: { $in: [comment1.user_id, comment2.user_id] } })
```

### 4. Actualizar documentos

```php
// Laravel
$post->update(['title' => 'Nuevo título']);

// MongoDB
db.posts.updateOne(
    { _id: ObjectId("...") },
    { $set: { title: "Nuevo título", updated_at: new Date() } }
);

// Incrementar contador
$post->increment('votes_count');

// MongoDB
db.posts.updateOne(
    { _id: ObjectId("...") },
    { $inc: { votes_count: 1 } }
);
```

### 5. Eliminar documentos

```php
// Laravel
$post->delete();

// MongoDB
db.posts.deleteOne({ _id: ObjectId("...") });
```

### 6. Queries complejas

```php
// Posts con más de 10 votos, ordenados por fecha
Post::where('votes_count', '>', 10)
    ->orderBy('created_at', 'desc')
    ->limit(20)
    ->get();

// MongoDB
db.posts.find({ votes_count: { $gt: 10 } })
    .sort({ created_at: -1 })
    .limit(20);
```

## 🌉 Entendiendo Inertia.js

### ¿Qué es Inertia?

Inertia es un **puente** entre Laravel (backend) y React (frontend) que permite crear **Single Page Applications** sin necesidad de una API REST.

### Flujo tradicional vs Inertia

#### Tradicional (API REST + React Router)

```
1. React Router detecta /posts/123
2. React hace fetch('/api/posts/123')
3. Laravel procesa y retorna JSON
4. React recibe JSON y renderiza
```

**Desventajas**:
- Código duplicado (validación en backend y frontend)
- Manejar estados de carga manualmente
- Autenticación más compleja

#### Con Inertia

```
1. Usuario visita /posts/123
2. Laravel procesa la ruta
3. Controlador retorna Inertia::render('Post/Show', $data)
4. Inertia convierte $data a JSON
5. React recibe y renderiza automáticamente
6. Navegación subsecuente es AJAX automático
```

**Ventajas**:
- Una sola fuente de verdad (rutas de Laravel)
- Validación solo en backend
- Autenticación integrada
- Menos código

### Ejemplo Completo

#### Backend (Laravel)

```php
// routes/web.php
Route::get('/posts/{id}', [PostController::class, 'show']);

// PostController.php
public function show($id)
{
    $post = Post::with('user')->findOrFail($id);
    
    return Inertia::render('Post/Show', [
        'post' => $post,
        'userVote' => auth()->user()->hasVoted($post->_id)
    ]);
}
```

#### Frontend (React)

```tsx
// pages/Post/Show.tsx
export default function Show({ post, userVote }) {
  return (
    <div>
      <h1>{post.title}</h1>
      <p>{post.content}</p>
      <VoteButton vote={userVote} />
    </div>
  );
}
```

### Características de Inertia

#### 1. `<Link>` - Navegación sin recarga

```tsx
import { Link } from '@inertiajs/react';

<Link href="/posts/123">Ver post</Link>
// Hace petición AJAX, actualiza contenido sin recargar
```

#### 2. `router` - Navegación programática

```tsx
import { router } from '@inertiajs/react';

router.visit('/posts/123');
router.post('/posts', data);
router.delete('/posts/123');
```

#### 3. `useForm` - Manejo de formularios

```tsx
import { useForm } from '@inertiajs/react';

const { data, setData, post, processing, errors } = useForm({
  title: '',
  content: ''
});

const submit = (e) => {
  e.preventDefault();
  post('/posts');
};
```

#### 4. Props compartidos

```php
// Middleware HandleInertiaRequests.php
public function share(Request $request)
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user(),
        ],
    ]);
}
```

Ahora `auth.user` está disponible en **todos** los componentes React.

## 🎯 Funcionalidades Implementadas

### ✅ Sistema de Posts
- [x] Crear posts
- [x] Listar posts (feed)
- [x] Ver post individual
- [x] Eliminar posts (solo dueño)
- [x] Ordenar por recientes/populares

### ✅ Sistema de Comentarios
- [x] Comentar en posts
- [x] Respuestas anidadas
- [x] Eliminar comentarios (solo dueño)
- [x] Contador de comentarios

### ✅ Sistema de Votos
- [x] Upvote/downvote en posts
- [x] Upvote/downvote en comentarios
- [x] Toggle de votos
- [x] Contador de votos

### ✅ Tendencias
- [x] Mostrar temas populares
- [x] Filtrar posts por tendencia
- [x] Actualización automática

### ✅ Autenticación
- [x] Login/Register (Laravel Fortify)
- [x] 2FA opcional
- [x] Protección de rutas

## 📁 Estructura del Proyecto

```
ForoReact/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── PostController.php       # Gestión de posts
│   │       ├── CommentController.php    # Gestión de comentarios
│   │       ├── VoteController.php       # Sistema de votos
│   │       └── TrendController.php      # Tendencias
│   └── Models/
│       ├── User.php                     # Usuario (MongoDB)
│       ├── Post.php                     # Post del foro
│       ├── Comment.php                  # Comentarios
│       ├── Vote.php                     # Votos (polimórfico)
│       └── Trend.php                    # Tendencias
├── resources/
│   └── js/
│       ├── components/
│       │   ├── forum/
│       │   │   ├── Header.tsx           # Header del foro
│       │   │   ├── PostCard.tsx         # Tarjeta de post
│       │   │   ├── CommentCard.tsx      # Tarjeta de comentario
│       │   │   ├── VoteButton.tsx       # Botones de voto
│       │   │   └── SidebarTrends.tsx    # Sidebar de tendencias
│       │   └── ui/                      # Componentes shadcn/ui
│       └── pages/
│           └── Post/
│               ├── Index.tsx            # Feed principal
│               ├── Show.tsx             # Detalle del post
│               └── Create.tsx           # Crear post
├── routes/
│   └── web.php                          # Rutas del foro
└── config/
    └── database.php                     # Configuración MongoDB
```

## 🔐 Seeders y Datos de Prueba

Para crear datos de prueba:

```php
// database/seeders/ForoSeeder.php
php artisan make:seeder ForoSeeder
```

```php
public function run()
{
    // Crear usuarios
    $users = User::factory(10)->create();
    
    // Crear posts
    foreach ($users->random(5) as $user) {
        $post = Post::create([
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'user_id' => $user->_id,
        ]);
        
        // Crear comentarios
        foreach ($users->random(3) as $commenter) {
            Comment::create([
                'content' => fake()->paragraph(),
                'user_id' => $commenter->_id,
                'post_id' => $post->_id,
            ]);
        }
    }
    
    // Crear tendencias
    $trends = ['SQL', 'MySQL', 'MongoDB', 'Laravel', 'React'];
    foreach ($trends as $trend) {
        Trend::create([
            'name' => $trend,
            'slug' => Str::slug($trend),
            'posts_count' => rand(5, 50),
            'score' => rand(10, 100),
            'category' => $trend,
        ]);
    }
}
```

Ejecutar:
```bash
php artisan db:seed --class=ForoSeeder
```

## 🚨 Troubleshooting

### Error: "Call to undefined function MongoDB\..."

**Solución**: Instala la extensión MongoDB de PHP
```bash
pecl install mongodb
echo "extension=mongodb.so" >> php.ini
```

### Error: "Connection refused"

**Solución**: Verifica que MongoDB esté corriendo
```bash
# Linux/Mac
sudo systemctl start mongodb

# Windows
net start MongoDB

# Docker
docker run -d -p 27017:27017 mongo:latest
```

### Error: Inertia no actualiza la página

**Solución**: Limpia caché y recompila
```bash
php artisan config:clear
php artisan cache:clear
npm run build
```

### Los votos no se actualizan

**Solución**: Verifica que los eventos boot() estén funcionando
```php
// En Vote.php, verifica:
protected static function boot()
{
    parent::boot();
    
    static::created(function ($vote) {
        logger('Voto creado', ['vote' => $vote]);
        // ...
    });
}
```

## 📖 Recursos Adicionales

- [Documentación Laravel](https://laravel.com/docs)
- [MongoDB Laravel Driver](https://www.mongodb.com/docs/drivers/php/laravel-mongodb/)
- [Inertia.js Docs](https://inertiajs.com/)
- [React 19 Docs](https://react.dev/)
- [shadcn/ui Components](https://ui.shadcn.com/)
- [TailwindCSS](https://tailwindcss.com/)

## 🤝 Contribuir

Si quieres mejorar el foro:

1. Fork el proyecto
2. Crea una rama (`git checkout -b feature/mejora`)
3. Commit tus cambios (`git commit -am 'Agrega mejora'`)
4. Push a la rama (`git push origin feature/mejora`)
5. Crea un Pull Request

## 📄 Licencia

MIT License - ForoDB 2025

---

**¡Disfruta construyendo con ForoDB! 🚀**

Si tienes preguntas, revisa la documentación o abre un issue en GitHub.
