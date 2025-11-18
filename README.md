# ForoDB - Foro Completo con Laravel 12 + MongoDB + Inertia + React 19

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![MongoDB](https://img.shields.io/badge/MongoDB-5.5-47A248?style=for-the-badge&logo=mongodb&logoColor=white)
![Inertia](https://img.shields.io/badge/Inertia.js-2.0-9553E9?style=for-the-badge&logo=inertia&logoColor=white)
![React](https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react&logoColor=black)
![TypeScript](https://img.shields.io/badge/TypeScript-5.0-3178C6?style=for-the-badge&logo=typescript&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-3.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)

**Un foro moderno construido con las últimas tecnologías web**

[Características](#-características) •
[Instalación](#-instalación-rápida) •
[Documentación](#-documentación) •
[Demo](#-demo)

</div>

---

## 🚀 Características

### ✨ Funcionalidades del Foro

- 📝 **Sistema de Posts** - Crear, editar, eliminar posts con rich content
- 💬 **Comentarios Anidados** - Sistema de comentarios con respuestas
- 👍👎 **Sistema de Votos** - Upvote/downvote en posts y comentarios
- 🔥 **Tendencias** - Sidebar con temas populares y categorías
- 👤 **Autenticación** - Login, registro y 2FA con Laravel Fortify
- 📱 **Responsive** - Diseño adaptable a móvil y desktop
- ⚡ **SPA** - Single Page Application sin recargas

### 🛠️ Stack Tecnológico

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| **Laravel** | 12 | Framework backend |
| **MongoDB** | 5.5 | Base de datos NoSQL |
| **Inertia.js** | 2.0 | Bridge Laravel ↔ React |
| **React** | 19 | Frontend framework |
| **TypeScript** | 5.0 | Type safety |
| **TailwindCSS** | 3.0 | Styling |
| **shadcn/ui** | Latest | Componentes UI |
| **Laravel Fortify** | 1.30 | Autenticación |

---

## 🎯 ¿Por Qué Este Stack?

### Sin API REST
- ✅ Una sola fuente de verdad (rutas Laravel)
- ✅ Validación solo en backend
- ✅ Autenticación integrada
- ✅ Sin configuración CORS
- ✅ Menos código, más productividad

### MongoDB en lugar de MySQL
- ✅ Esquema flexible
- ✅ Documentos JSON nativos
- ✅ Excelente para datos anidados (comentarios)
- ✅ Escalabilidad horizontal
- ✅ Performance en lecturas

### Inertia + React
- ✅ Experiencia SPA completa
- ✅ Sin duplicación de código
- ✅ TypeScript end-to-end
- ✅ Componentes reutilizables
- ✅ Developer experience superior

---

## 📦 Instalación Rápida

### Prerrequisitos

- PHP >= 8.2 con extensión MongoDB
- Composer
- Node.js >= 18
- MongoDB >= 5.0

### Pasos

```bash
# 1. Clonar repositorio
git clone https://github.com/tu-usuario/forodb.git
cd forodb

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Editar .env con tus credenciales MongoDB
# DB_CONNECTION=mongodb
# DB_HOST=127.0.0.1
# DB_PORT=27017
# DB_DATABASE=forodb

# 5. Poblar base de datos (opcional)
php artisan db:seed --class=ForoSeeder

# 6. Compilar assets
npm run build

# 7. Iniciar servidor
php artisan serve
```

**Visita**: http://localhost:8000

**Credenciales de prueba**:
- Email: `admin@forodb.com`
- Password: `password`

---

## 📚 Documentación

Este proyecto incluye documentación completa en español:

### 🎯 Guías Principales

1. **[INICIO_RAPIDO.md](INICIO_RAPIDO.md)** ⭐ **COMIENZA AQUÍ**
   - Instalación paso a paso
   - Configuración de MongoDB
   - Troubleshooting común
   - ~20 minutos de lectura

2. **[FORO_README.md](FORO_README.md)**
   - Documentación técnica completa
   - Arquitectura del proyecto
   - MongoDB para principiantes
   - ~60 minutos de lectura

3. **[MONGODB_QUERIES.md](MONGODB_QUERIES.md)**
   - 50+ ejemplos de queries
   - Comparación SQL vs MongoDB
   - Queries avanzadas y optimización
   - ~40 minutos de lectura

4. **[INERTIA_EXPLICADO.md](INERTIA_EXPLICADO.md)**
   - Guía completa de Inertia.js
   - Flujo de datos explicado
   - Patrones comunes
   - ~50 minutos de lectura

5. **[INDICE_DOCUMENTACION.md](INDICE_DOCUMENTACION.md)**
   - Índice navegable de toda la documentación
   - Mapa de aprendizaje
   - Buscar por tema

### 🔧 Utilidades

- **[verify.php](verify.php)** - Script de verificación del proyecto
- **[RESUMEN.md](RESUMEN.md)** - Overview ejecutivo
- **[PROYECTO_COMPLETADO.txt](PROYECTO_COMPLETADO.txt)** - Resumen visual

---

## 🏗️ Arquitectura

### Base de Datos (MongoDB)

```
┌──────────────┐
│    users     │──┐
└──────────────┘  │
                  │  hasMany
┌──────────────┐  │
│    posts     │◄─┘
└──────────────┘
       │
       │ hasMany
       ▼
┌──────────────┐       ┌──────────────┐
│   comments   │       │    votes     │
└──────────────┘       └──────────────┘
       │                      │
       │ parent_id            │ polymorphic
       └──────►self           └──►posts/comments
```

### Flujo Inertia

```
Usuario → Click/Submit → Inertia → Laravel Route
                                        ↓
                                   Controller
                                        ↓
                                 Inertia::render()
                                        ↓
React ← Component ← Props ← JSON ← Inertia
```

---

## 📁 Estructura del Proyecto

```
ForoReact/
├── app/
│   ├── Http/Controllers/      # Controladores Inertia
│   │   ├── PostController.php
│   │   ├── CommentController.php
│   │   ├── VoteController.php
│   │   └── TrendController.php
│   └── Models/                # Modelos MongoDB
│       ├── User.php
│       ├── Post.php
│       ├── Comment.php
│       ├── Vote.php
│       └── Trend.php
├── resources/
│   └── js/
│       ├── components/
│       │   └── forum/         # Componentes del foro
│       │       ├── Header.tsx
│       │       ├── PostCard.tsx
│       │       ├── CommentCard.tsx
│       │       ├── VoteButton.tsx
│       │       └── SidebarTrends.tsx
│       └── pages/
│           └── Post/          # Páginas Inertia
│               ├── Index.tsx  # Feed
│               ├── Show.tsx   # Detalle
│               └── Create.tsx # Crear
├── routes/
│   └── web.php               # Rutas del foro
└── database/
    └── seeders/
        └── ForoSeeder.php    # Datos de prueba
```

---

## 🎨 Capturas de Pantalla

### Feed Principal
![Feed](docs/screenshots/feed.png)

### Vista de Post
![Post Detail](docs/screenshots/post-detail.png)

### Crear Post
![Create Post](docs/screenshots/create-post.png)

---

## 🧪 Testing

```bash
# Poblar datos de prueba
php artisan db:seed --class=ForoSeeder

# Verificar instalación
php verify.php

# Tests en Tinker
php artisan tinker
> Post::count()
> User::with('posts')->first()
```

---

## 🎓 Aprendizaje

Este proyecto es perfecto para aprender:

- ✅ **MongoDB con Laravel** - Modelos, relaciones, queries
- ✅ **Inertia.js** - SPA sin API REST
- ✅ **React 19** - Componentes modernos con hooks
- ✅ **TypeScript** - Type safety en frontend
- ✅ **shadcn/ui** - Sistema de diseño moderno

### Código Documentado

Todo el código incluye comentarios explicativos:

```php
/**
 * Relación: Un post tiene muchos comentarios
 * 
 * EXPLICACIÓN:
 * - hasMany busca en la colección 'comments' todos los documentos
 *   donde el post_id coincida con el _id de este post
 * - Podemos hacer: $post->comments para obtener todos los comentarios
 */
public function comments()
{
    return $this->hasMany(Comment::class, 'post_id');
}
```

---

## 🚧 Roadmap

### Implementado ✅
- [x] CRUD de posts
- [x] Sistema de comentarios
- [x] Votos en posts y comentarios
- [x] Tendencias
- [x] Autenticación
- [x] Diseño responsive

### Próximamente 🔜
- [ ] Búsqueda full-text
- [ ] Notificaciones en tiempo real
- [ ] Sistema de badges/reputación
- [ ] Markdown en posts
- [ ] Adjuntar imágenes
- [ ] Filtros avanzados

---

## 🤝 Contribuir

¡Las contribuciones son bienvenidas!

1. Fork el proyecto
2. Crea una rama (`git checkout -b feature/mejora`)
3. Commit tus cambios (`git commit -am 'Agrega mejora'`)
4. Push a la rama (`git push origin feature/mejora`)
5. Crea un Pull Request

---

## 📝 Licencia

Este proyecto está bajo la licencia MIT. Ver [LICENSE](LICENSE) para más detalles.

---

## 👥 Autor

**ForoDB Team**

- GitHub: [@tu-usuario](https://github.com/tu-usuario)
- Email: contact@forodb.com

---

## 🙏 Agradecimientos

- [Laravel](https://laravel.com/) - El mejor framework PHP
- [MongoDB](https://www.mongodb.com/) - Base de datos NoSQL moderna
- [Inertia.js](https://inertiajs.com/) - El puente perfecto
- [React](https://react.dev/) - Biblioteca UI líder
- [shadcn/ui](https://ui.shadcn.com/) - Componentes hermosos

---

## 📞 Soporte

¿Necesitas ayuda?

- 📖 Lee la [documentación completa](INDICE_DOCUMENTACION.md)
- 🐛 Reporta un [issue](https://github.com/tu-usuario/forodb/issues)
- 💬 Únete a las [discusiones](https://github.com/tu-usuario/forodb/discussions)
- 📧 Envía un email a support@forodb.com

---

<div align="center">

**Hecho con ❤️ usando Laravel 12, MongoDB, Inertia.js y React 19**

[⬆ Volver arriba](#forodb---foro-completo-con-laravel-12--mongodb--inertia--react-19)

</div>
