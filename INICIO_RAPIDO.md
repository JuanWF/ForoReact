# 🚀 Guía Rápida de Inicio - ForoDB

## ⚡ Iniciar el Proyecto (Ya Configurado)

Si ya tienes todo instalado y configurado, simplemente ejecuta:

```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Compilar assets (modo desarrollo)
npm run dev
```

Visita: **http://localhost:8000**

---

## 🆕 Primera Vez (Instalación Completa)

### 1️⃣ Instalar Extensión MongoDB de PHP

**Windows**:
```powershell
# Descargar DLL desde: https://pecl.php.net/package/mongodb
# Copiar php_mongodb.dll a: C:\php\ext\
# Editar php.ini y agregar:
extension=mongodb
```

**Linux/Mac**:
```bash
sudo pecl install mongodb
echo "extension=mongodb.so" | sudo tee -a /etc/php/8.2/cli/php.ini
```

**Verificar**:
```bash
php -m | grep mongodb
# Debe mostrar: mongodb
```

### 2️⃣ Instalar MongoDB

**Opción 1: MongoDB Local**

Windows (Chocolatey):
```powershell
choco install mongodb
```

Mac (Homebrew):
```bash
brew tap mongodb/brew
brew install mongodb-community
brew services start mongodb-community
```

Linux (Ubuntu):
```bash
wget -qO - https://www.mongodb.org/static/pgp/server-6.0.asc | sudo apt-key add -
echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu focal/mongodb-org/6.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-6.0.list
sudo apt update
sudo apt install -y mongodb-org
sudo systemctl start mongod
```

**Opción 2: MongoDB Atlas (Cloud - Gratis)**

1. Ir a https://www.mongodb.com/cloud/atlas
2. Crear cuenta gratuita
3. Crear un cluster (Free M0)
4. Obtener connection string: `mongodb+srv://user:pass@cluster.mongodb.net/`
5. En `.env`, usar:
   ```env
   DB_DSN=mongodb+srv://user:pass@cluster.mongodb.net/forodb
   ```

**Opción 3: MongoDB con Docker**

```bash
docker run -d \
  --name mongodb \
  -p 27017:27017 \
  -e MONGO_INITDB_ROOT_USERNAME=admin \
  -e MONGO_INITDB_ROOT_PASSWORD=password \
  mongo:latest
```

### 3️⃣ Configurar el Proyecto

```bash
# Copiar archivo de configuración
cp .env.example .env

# Instalar dependencias PHP
composer install

# Instalar dependencias Node
npm install

# Generar clave de aplicación
php artisan key:generate
```

### 4️⃣ Configurar .env

Edita `.env` y configura MongoDB:

```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=forodb
DB_USERNAME=
DB_PASSWORD=

# Si usas MongoDB Atlas:
# DB_DSN=mongodb+srv://usuario:password@cluster.mongodb.net/forodb
```

### 5️⃣ Verificar Conexión

```bash
php artisan tinker
```

Dentro de tinker:
```php
DB::connection()->getMongoDB()->command(['ping' => 1]);
// Debe retornar: ["ok" => 1]

exit
```

### 6️⃣ Poblar Base de Datos

```bash
php artisan db:seed --class=ForoSeeder
```

Esto creará:
- 10 usuarios
- 10 posts
- Comentarios aleatorios
- Votos
- Tendencias

**Credenciales de prueba**:
- Email: `admin@forodb.com`
- Password: `password`

### 7️⃣ Compilar Assets

```bash
# Modo desarrollo (con hot reload)
npm run dev

# Modo producción
npm run build
```

### 8️⃣ Iniciar Servidor

```bash
php artisan serve
```

Visita: **http://localhost:8000**

---

## 🧪 Verificar que Todo Funciona

### Test 1: Ver el Feed
✅ Deberías ver 10 posts en la página principal
✅ Sidebar con tendencias
✅ Header con botones de login

### Test 2: Login
1. Clic en "Iniciar sesión"
2. Email: `admin@forodb.com`
3. Password: `password`
✅ Deberías ver tu avatar en el header
✅ Botón "Nuevo Post" visible

### Test 3: Crear Post
1. Clic en "Nuevo Post"
2. Escribe título y contenido
3. Clic en "Publicar Post"
✅ Deberías ser redirigido al post creado

### Test 4: Votar
1. En cualquier post, clic en ↑ (upvote)
✅ El botón debe cambiar de color
✅ El contador debe incrementar

### Test 5: Comentar
1. Abre un post
2. Escribe un comentario
3. Clic en "Comentar"
✅ El comentario debe aparecer inmediatamente

---

## 🐛 Problemas Comunes

### "Extension mongodb not found"

**Solución**: No tienes la extensión MongoDB instalada
```bash
pecl install mongodb
```

### "Connection refused to 127.0.0.1:27017"

**Solución**: MongoDB no está corriendo
```bash
# Windows
net start MongoDB

# Linux/Mac
sudo systemctl start mongod

# Docker
docker start mongodb
```

### "SQLSTATE[HY000]"

**Solución**: Laravel está intentando usar MySQL/SQLite
- Verifica que `.env` tenga `DB_CONNECTION=mongodb`
- Ejecuta: `php artisan config:clear`

### "Vite manifest not found"

**Solución**: Los assets no están compilados
```bash
npm run build
```

### Los cambios de React no se reflejan

**Solución**: Asegúrate de tener `npm run dev` corriendo

### "404 Not Found" en rutas

**Solución**: Limpia caché de rutas
```bash
php artisan route:clear
php artisan optimize:clear
```

---

## 📱 Estructura de Desarrollo

```
Terminal 1: Servidor Backend
┌─────────────────────────────┐
│ php artisan serve           │
│ → http://localhost:8000     │
└─────────────────────────────┘

Terminal 2: Compilador Frontend
┌─────────────────────────────┐
│ npm run dev                 │
│ → Compila React + Inertia   │
│ → Hot reload automático     │
└─────────────────────────────┘

MongoDB:
┌─────────────────────────────┐
│ Puerto 27017                │
│ Base de datos: forodb       │
└─────────────────────────────┘
```

---

## 🎯 Próximos Pasos

1. **Explorar el código**:
   - Backend: `app/Http/Controllers/`
   - Modelos: `app/Models/`
   - Frontend: `resources/js/pages/`

2. **Leer documentación completa**: `FORO_README.md`

3. **Personalizar**:
   - Cambiar colores en `resources/css/app.css`
   - Agregar campos a los modelos
   - Crear nuevas funcionalidades

4. **Desplegar a producción**:
   - Usar MongoDB Atlas
   - Configurar Vapor/Forge/Heroku
   - Ejecutar `npm run build`

---

## 🆘 Ayuda

Si tienes problemas:

1. Revisa `FORO_README.md` para documentación completa
2. Verifica logs: `storage/logs/laravel.log`
3. Ejecuta: `php artisan optimize:clear`
4. Reinicia servidor y compilador

---

**¡Todo listo! 🎉**

Ahora puedes empezar a desarrollar tu foro personalizado.
