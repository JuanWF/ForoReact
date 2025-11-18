# 🌉 Entendiendo Inertia.js - Guía Completa

Esta guía explica cómo funciona Inertia.js en ForoDB, desde lo más básico hasta lo más avanzado.

## 🤔 ¿Qué es Inertia.js?

Inertia.js es un **puente** entre Laravel (backend) y React/Vue/Svelte (frontend) que permite crear **Single Page Applications** sin necesidad de construir una API REST.

### El Problema que Resuelve

**Antes de Inertia (API REST tradicional)**:

```
Frontend (React)          Backend (Laravel)
     │                          │
     │  GET /api/posts/123      │
     ├────────────────────────► │
     │                          │
     │  { id: 123, title: ... } │
     │ ◄────────────────────────┤
     │                          │
```

**Problemas**:
- Código duplicado (validación en backend Y frontend)
- Manejar estados de carga manualmente
- Autenticación más compleja (tokens, CORS)
- Dos sistemas de rutas (Laravel + React Router)
- Sincronización de errores complicada

**Con Inertia**:

```
Frontend (React)          Inertia          Backend (Laravel)
     │                       │                    │
     │  Click en link        │                    │
     ├───────────────────────►                    │
     │                       │  GET /posts/123    │
     │                       ├───────────────────►│
     │                       │                    │
     │                       │  Inertia::render() │
     │                       │ ◄──────────────────┤
     │  Renderiza Post/Show  │                    │
     │ ◄─────────────────────┤                    │
```

**Ventajas**:
- Una sola fuente de verdad (rutas de Laravel)
- Validación solo en backend
- Autenticación automática (sesiones Laravel)
- Sin configuración de CORS
- Errores automáticamente compartidos

---

## 🏗️ Arquitectura

### Flujo Completo de una Petición

```
1. Usuario hace clic en <Link href="/posts/123">

2. Inertia intercepta el clic y hace fetch() AJAX

3. Headers especiales indican que es petición Inertia:
   X-Inertia: true
   X-Inertia-Version: hash

4. Laravel procesa la ruta normalmente

5. Controlador retorna Inertia::render('Post/Show', $data)

6. Inertia convierte los datos a JSON:
   {
     "component": "Post/Show",
     "props": {
       "post": { ... },
       "auth": { ... }
     },
     "url": "/posts/123"
   }

7. React recibe el JSON y renderiza el componente

8. Historial del navegador se actualiza (SPA behavior)
```

### Primera Visita vs Navegación Subsecuente

**Primera visita** (usuario entra directamente a la URL):

```html
<!DOCTYPE html>
<html>
  <head>
    <script src="/build/assets/app.js"></script>
  </head>
  <body>
    <div id="app" data-page='{"component":"Post/Show","props":{...}}'>
    </div>
  </body>
</html>
```

Laravel retorna HTML completo con los datos embebidos.

**Navegación subsecuente** (usuario ya está en la app):

```json
{
  "component": "Post/Show",
  "props": { "post": {...} },
  "url": "/posts/123"
}
```

Inertia solo retorna JSON, React actualiza el componente.

---

## 📝 Componentes de Inertia

### 1. Backend: `Inertia::render()`

```php
// PostController.php
public function show($id)
{
    $post = Post::with('user')->findOrFail($id);
    
    return Inertia::render('Post/Show', [
        'post' => $post,
        'userVote' => auth()->user()?->hasVoted($post->_id)
    ]);
}
```

**¿Qué hace?**
1. Prepara los datos (igual que con `view()`)
2. Los convierte a JSON
3. Los envía a React

**Tipo de respuesta**:
- Primera visita: HTML + JSON embebido
- Visitas AJAX: Solo JSON

### 2. Frontend: Props

```tsx
// pages/Post/Show.tsx
interface PageProps {
  post: {
    _id: string;
    title: string;
    content: string;
  };
  userVote: 'up' | 'down' | null;
}

export default function Show({ post, userVote }: PageProps) {
  return (
    <div>
      <h1>{post.title}</h1>
      <p>{post.content}</p>
    </div>
  );
}
```

**¿Cómo llegan los props?**
- Laravel los envía como JSON
- Inertia los pasa automáticamente al componente
- TypeScript valida los tipos

### 3. Navegación: `<Link>`

```tsx
import { Link } from '@inertiajs/react';

<Link href="/posts/123">Ver post</Link>
```

**¿Qué hace?**
1. Intercepta el clic
2. Hace fetch() AJAX
3. Actualiza el componente
4. Actualiza la URL sin recargar

**Opciones**:

```tsx
// Método HTTP
<Link href="/posts/123" method="post">Crear</Link>

// Datos adicionales
<Link href="/posts/123" data={{ sort: 'popular' }}>Ver</Link>

// Preservar scroll
<Link href="/posts/123" preserveScroll>Ver</Link>

// Confirmación
<Link href="/posts/123" method="delete" 
      onBefore={() => confirm('¿Seguro?')}>
  Eliminar
</Link>
```

### 4. Router: Navegación Programática

```tsx
import { router } from '@inertiajs/react';

// GET
router.visit('/posts/123');

// POST
router.post('/posts', { title: 'Nuevo post' });

// PUT
router.put('/posts/123', { title: 'Actualizado' });

// DELETE
router.delete('/posts/123');

// Opciones
router.visit('/posts', {
  method: 'get',
  data: { sort: 'popular' },
  preserveState: true,
  preserveScroll: true,
  only: ['posts'], // Solo recargar prop 'posts'
  headers: { 'X-Custom': 'value' }
});
```

### 5. Formularios: `useForm`

```tsx
import { useForm } from '@inertiajs/react';

export default function Create() {
  const { data, setData, post, processing, errors, reset } = useForm({
    title: '',
    content: ''
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/posts', {
      onSuccess: () => reset()
    });
  };

  return (
    <form onSubmit={submit}>
      <input
        value={data.title}
        onChange={e => setData('title', e.target.value)}
      />
      {errors.title && <span>{errors.title}</span>}
      
      <button disabled={processing}>
        {processing ? 'Guardando...' : 'Guardar'}
      </button>
    </form>
  );
}
```

**¿Qué hace useForm?**
- Maneja el estado del formulario
- Envía datos automáticamente
- Recibe errores de validación de Laravel
- Maneja loading state

---

## 🔄 Estados y Ciclo de Vida

### Estados de una Petición

```tsx
import { router } from '@inertiajs/react';
import { useState } from 'react';

function Example() {
  const [loading, setLoading] = useState(false);

  const handleAction = () => {
    setLoading(true);
    
    router.post('/posts', data, {
      onStart: () => {
        console.log('Petición iniciada');
      },
      onProgress: (progress) => {
        console.log('Progreso:', progress);
      },
      onSuccess: (page) => {
        console.log('Éxito:', page);
      },
      onError: (errors) => {
        console.log('Errores:', errors);
      },
      onFinish: () => {
        setLoading(false);
        console.log('Petición finalizada');
      }
    });
  };
}
```

### Eventos Globales

```tsx
// app.tsx
import { router } from '@inertiajs/react';

router.on('start', (event) => {
  console.log('Navegación iniciada');
});

router.on('progress', (event) => {
  // Mostrar barra de progreso
  NProgress.start();
});

router.on('finish', (event) => {
  NProgress.done();
});

router.on('error', (event) => {
  console.error('Error:', event.detail.errors);
});
```

---

## 💾 Optimizaciones

### 1. Preservar Estado

```tsx
// No recargar el estado del componente
router.visit('/posts', {
  preserveState: true
});

// Ejemplo: mantener scroll position
router.visit('/posts?page=2', {
  preserveState: true,
  preserveScroll: true
});
```

### 2. Partial Reloads (Solo actualizar algunas props)

```tsx
// Solo recargar la lista de posts, no el usuario
router.reload({
  only: ['posts']
});

// Backend debe marcar props como "lazy"
return Inertia::render('Post/Index', [
  'posts' => $posts,
  'trends' => Inertia::lazy(fn () => Trend::popular()->get())
]);
```

### 3. Prefetching

```tsx
<Link href="/posts/123" 
      onMouseEnter={() => router.prefetch('/posts/123')}>
  Ver post
</Link>
```

### 4. Cache

Inertia automáticamente cachea el componente actual:

```tsx
// Al navegar atrás, Inertia usa el cache
// No hace nueva petición al servidor
```

---

## 🎯 Patrones Comunes

### Patrón 1: Lista con Filtros

```php
// Controller
public function index(Request $request)
{
    $posts = Post::query()
        ->when($request->sort, fn($q, $sort) => $q->orderBy('votes_count', $sort))
        ->paginate(15);

    return Inertia::render('Post/Index', [
        'posts' => $posts,
        'filters' => $request->only('sort')
    ]);
}
```

```tsx
// Component
export default function Index({ posts, filters }) {
  const handleSort = (value: string) => {
    router.get('/', { sort: value }, {
      preserveState: true,
      preserveScroll: true
    });
  };

  return (
    <Select value={filters.sort} onValueChange={handleSort}>
      <SelectItem value="asc">Ascendente</SelectItem>
      <SelectItem value="desc">Descendente</SelectItem>
    </Select>
  );
}
```

### Patrón 2: Formulario con Validación

```php
// Controller
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required'
    ]);

    $post = Post::create($validated);

    return redirect()->route('posts.show', $post->_id)
        ->with('success', 'Post creado');
}
```

```tsx
// Component
const { data, setData, post, errors } = useForm({
  title: '',
  content: ''
});

const submit = (e: React.FormEvent) => {
  e.preventDefault();
  post('/posts');
};

// Los errores de validación llegan automáticamente en 'errors'
{errors.title && <span>{errors.title}</span>}
```

### Patrón 3: Confirmación de Eliminación

```tsx
const handleDelete = () => {
  router.delete(`/posts/${post._id}`, {
    onBefore: () => confirm('¿Estás seguro?')
  });
};
```

### Patrón 4: Flash Messages

```php
// Controller
return redirect()->route('posts.index')
    ->with('success', 'Post eliminado');
```

```tsx
// Middleware compartido (HandleInertiaRequests.php)
public function share(Request $request)
{
    return [
        ...parent::share($request),
        'flash' => [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
        ],
    ];
}

// Component
export default function Index({ flash }) {
  return (
    <>
      {flash.success && <Alert>{flash.success}</Alert>}
      {flash.error && <Alert variant="destructive">{flash.error}</Alert>}
    </>
  );
}
```

---

## 🐛 Debugging

### Ver Peticiones Inertia

```tsx
// Instalar Inertia DevTools
// Chrome: https://chrome.google.com/webstore (buscar "Inertia")

// O manualmente:
router.on('navigate', (event) => {
  console.log('Navigate to:', event.detail.page.url);
  console.log('Component:', event.detail.page.component);
  console.log('Props:', event.detail.page.props);
});
```

### Logs en Laravel

```php
// En cualquier controlador
\Log::info('Inertia render', [
    'component' => 'Post/Show',
    'props' => $props
]);
```

---

## 🎓 Comparación con Otras Tecnologías

| Feature | Inertia + Laravel | API REST + React | Livewire | Blade SSR |
|---------|-------------------|------------------|----------|-----------|
| SPA | ✅ | ✅ | ✅ | ❌ |
| No API necesaria | ✅ | ❌ | ✅ | ✅ |
| React/Vue | ✅ | ✅ | ❌ | ❌ |
| SEO-friendly | ✅ | ⚠️ | ✅ | ✅ |
| Autenticación | ✅ Simple | ⚠️ Complejo | ✅ Simple | ✅ Simple |
| TypeScript | ✅ | ✅ | ❌ | ❌ |
| Tiempo real | ⚠️ | ✅ | ✅ | ❌ |

---

## 📚 Recursos

- [Documentación Oficial Inertia](https://inertiajs.com/)
- [Inertia Laravel](https://inertiajs.com/server-side-setup)
- [Inertia React](https://inertiajs.com/client-side-setup)
- [Ejemplos en GitHub](https://github.com/inertiajs)

---

## ✅ Checklist: ¿Entiendo Inertia?

- [ ] Sé qué problema resuelve Inertia
- [ ] Entiendo el flujo de una petición
- [ ] Puedo usar `<Link>` y `router`
- [ ] Sé usar `useForm` para formularios
- [ ] Entiendo cómo llegan los errores de validación
- [ ] Sé compartir props globalmente
- [ ] Puedo optimizar con preserveState/preserveScroll
- [ ] Entiendo la diferencia entre primera visita y navegación

---

**¡Inertia hace que Laravel + React sea tan simple como usar Blade! 🚀**
