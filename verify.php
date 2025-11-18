#!/usr/bin/env php
<?php

/**
 * Script de verificación del proyecto ForoDB
 * 
 * Ejecuta: php verify.php
 */

echo "🔍 Verificando proyecto ForoDB...\n\n";

$checks = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
];

function check($description, $condition, $failMessage = '', $isWarning = false) {
    global $checks;
    
    if ($condition) {
        echo "✅ {$description}\n";
        $checks['passed']++;
        return true;
    } else {
        $icon = $isWarning ? '⚠️' : '❌';
        echo "{$icon} {$description}\n";
        if ($failMessage) {
            echo "   → {$failMessage}\n";
        }
        if ($isWarning) {
            $checks['warnings']++;
        } else {
            $checks['failed']++;
        }
        return false;
    }
}

echo "📦 Dependencias PHP\n";
echo "─────────────────\n";

check(
    'Extensión MongoDB instalada',
    extension_loaded('mongodb'),
    'Ejecuta: pecl install mongodb'
);

check(
    'PHP >= 8.2',
    version_compare(PHP_VERSION, '8.2.0', '>='),
    'Actualiza tu versión de PHP'
);

check(
    'Composer instalado',
    file_exists(__DIR__ . '/vendor/autoload.php'),
    'Ejecuta: composer install'
);

echo "\n📁 Archivos del Proyecto\n";
echo "────────────────────────\n";

// Backend
check('Modelo Post', file_exists(__DIR__ . '/app/Models/Post.php'));
check('Modelo Comment', file_exists(__DIR__ . '/app/Models/Comment.php'));
check('Modelo Vote', file_exists(__DIR__ . '/app/Models/Vote.php'));
check('Modelo Trend', file_exists(__DIR__ . '/app/Models/Trend.php'));

check('PostController', file_exists(__DIR__ . '/app/Http/Controllers/PostController.php'));
check('CommentController', file_exists(__DIR__ . '/app/Http/Controllers/CommentController.php'));
check('VoteController', file_exists(__DIR__ . '/app/Http/Controllers/VoteController.php'));
check('TrendController', file_exists(__DIR__ . '/app/Http/Controllers/TrendController.php'));

// Frontend
check('Página Index', file_exists(__DIR__ . '/resources/js/pages/Post/Index.tsx'));
check('Página Show', file_exists(__DIR__ . '/resources/js/pages/Post/Show.tsx'));
check('Página Create', file_exists(__DIR__ . '/resources/js/pages/Post/Create.tsx'));

check('Componente Header', file_exists(__DIR__ . '/resources/js/components/forum/Header.tsx'));
check('Componente PostCard', file_exists(__DIR__ . '/resources/js/components/forum/PostCard.tsx'));
check('Componente VoteButton', file_exists(__DIR__ . '/resources/js/components/forum/VoteButton.tsx'));

echo "\n⚙️  Configuración\n";
echo "────────────────\n";

check(
    'Archivo .env existe',
    file_exists(__DIR__ . '/.env'),
    'Ejecuta: cp .env.example .env'
);

if (file_exists(__DIR__ . '/.env')) {
    $env = file_get_contents(__DIR__ . '/.env');
    
    check(
        'DB_CONNECTION=mongodb',
        strpos($env, 'DB_CONNECTION=mongodb') !== false,
        'Cambia DB_CONNECTION a mongodb en .env'
    );
    
    check(
        'APP_KEY configurado',
        strpos($env, 'APP_KEY=base64:') !== false,
        'Ejecuta: php artisan key:generate'
    );
}

check(
    'node_modules existe',
    file_exists(__DIR__ . '/node_modules'),
    'Ejecuta: npm install'
);

check(
    'package.json existe',
    file_exists(__DIR__ . '/package.json')
);

echo "\n📚 Documentación\n";
echo "────────────────\n";

check('RESUMEN.md', file_exists(__DIR__ . '/RESUMEN.md'));
check('FORO_README.md', file_exists(__DIR__ . '/FORO_README.md'));
check('INICIO_RAPIDO.md', file_exists(__DIR__ . '/INICIO_RAPIDO.md'));
check('MONGODB_QUERIES.md', file_exists(__DIR__ . '/MONGODB_QUERIES.md'));
check('INERTIA_EXPLICADO.md', file_exists(__DIR__ . '/INERTIA_EXPLICADO.md'));

echo "\n🔌 Conexión MongoDB (opcional)\n";
echo "──────────────────────────────\n";

if (extension_loaded('mongodb')) {
    try {
        require __DIR__ . '/vendor/autoload.php';
        
        $app = require_once __DIR__ . '/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        $config = config('database.connections.mongodb');
        
        if ($config) {
            try {
                $client = new MongoDB\Client(
                    $config['dsn'] ?? "mongodb://{$config['host']}:{$config['port']}"
                );
                
                $result = $client->selectDatabase('admin')->command(['ping' => 1]);
                
                check(
                    'Conexión a MongoDB exitosa',
                    true
                );
            } catch (Exception $e) {
                check(
                    'Conexión a MongoDB',
                    false,
                    'MongoDB no está accesible. Verifica que esté corriendo.',
                    true
                );
            }
        }
    } catch (Exception $e) {
        check(
            'Verificación de MongoDB',
            false,
            'No se pudo verificar: ' . $e->getMessage(),
            true
        );
    }
} else {
    echo "⚠️  Extensión MongoDB no disponible - omitiendo verificación de conexión\n";
    $checks['warnings']++;
}

echo "\n" . str_repeat('─', 50) . "\n\n";

// Resumen
$total = $checks['passed'] + $checks['failed'] + $checks['warnings'];
$percentage = $total > 0 ? round(($checks['passed'] / $total) * 100) : 0;

echo "📊 Resumen\n";
echo "──────────\n";
echo "✅ Pasadas: {$checks['passed']}\n";
echo "❌ Fallidas: {$checks['failed']}\n";
echo "⚠️  Advertencias: {$checks['warnings']}\n";
echo "\n";

if ($checks['failed'] === 0) {
    echo "🎉 ¡Todo está listo! El proyecto está correctamente configurado.\n\n";
    
    echo "🚀 Próximos pasos:\n";
    echo "   1. Asegúrate de que MongoDB esté corriendo\n";
    echo "   2. Ejecuta: php artisan db:seed --class=ForoSeeder\n";
    echo "   3. Terminal 1: php artisan serve\n";
    echo "   4. Terminal 2: npm run dev\n";
    echo "   5. Visita: http://localhost:8000\n\n";
    
    echo "👤 Credenciales de prueba:\n";
    echo "   Email: admin@forodb.com\n";
    echo "   Password: password\n\n";
    
    echo "📖 Lee INICIO_RAPIDO.md para más información.\n";
    
    exit(0);
} else {
    echo "⚠️  Hay {$checks['failed']} problemas que deben resolverse.\n";
    echo "   Lee los mensajes arriba para saber cómo solucionarlos.\n\n";
    
    exit(1);
}
