<?php
require_once __DIR__ . '/config.php';

try {
    echo "Iniciando instalación y migración de base de datos...\n";

    // 0. Si las tablas existentes son incompatibles, limpiarlas para una migración limpia
    try {
        $checkCol = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'correo'")->fetch();
        $checkRol = $conn->query("SHOW COLUMNS FROM roles LIKE 'id'")->fetch();
        if (!$checkCol || !$checkRol) {
            $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            $tablas = ['documentos', 'postulaciones', 'ofertas', 'empresas', 'reclutadores', 'estudiantes', 'usuarios', 'roles', 'configuracion', 'categorias'];
            foreach ($tablas as $t) {
                $conn->exec("DROP TABLE IF EXISTS `$t`");
            }
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        }
    } catch (Exception $eCheck) {}

    // 1. Ejecutar database.sql
    $sql = file_get_contents(__DIR__ . '/database.sql');
    $conn->exec($sql);
    echo "Tablas creadas correctamente.\n";

    // Clave común para todos los usuarios de prueba
    $password_demo = 'Senati2026.';
    $hash = password_hash($password_demo, PASSWORD_BCRYPT);

    // 2. Insertar o actualizar usuarios para Estudiantes, Reclutadores y Administradores
    $usuarios = [
        // Administradores (Rol 3)
        [
            'nombre' => 'Karen',
            'apellido' => 'Arandio',
            'correo' => 'admin@senati.pe',
            'contrasena' => $hash,
            'rol_id' => 3, // Administrador
            'estado' => 'activo'
        ],
        [
            'nombre' => 'Arandio',
            'apellido' => 'Admin',
            'correo' => 'arandio.admin@senati.pe',
            'contrasena' => $hash,
            'rol_id' => 3, // Administrador
            'estado' => 'activo'
        ],
        [
            'nombre' => 'Francklin',
            'apellido' => 'Admin',
            'correo' => 'francklin.admin@senati.pe',
            'contrasena' => $hash,
            'rol_id' => 3, // Administrador
            'estado' => 'activo'
        ],

        // Reclutadores (Rol 2)
        [
            'nombre' => 'Reclutador',
            'apellido' => 'SENATI',
            'correo' => 'reclutador@senati.pe',
            'contrasena' => $hash,
            'rol_id' => 2, // Reclutador
            'estado' => 'activo'
        ],
        [
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'correo' => 'reclutador@techcorp.com',
            'contrasena' => $hash,
            'rol_id' => 2, // Reclutador
            'estado' => 'activo'
        ],
        [
            'nombre' => 'Patricia',
            'apellido' => 'Luna',
            'correo' => 'talento@innovasolutions.com',
            'contrasena' => $hash,
            'rol_id' => 2, // Reclutador
            'estado' => 'activo'
        ],

        // Estudiantes (Rol 1)
        [
            'nombre' => 'Arandio',
            'apellido' => 'Estudiante',
            'correo' => 'arandio@senati.pe',
            'contrasena' => $hash,
            'rol_id' => 1, // Estudiante
            'estado' => 'activo'
        ],
        [
            'nombre' => 'Francklin',
            'apellido' => 'Estudiante',
            'correo' => 'francklin@senati.pe',
            'contrasena' => $hash,
            'rol_id' => 1, // Estudiante
            'estado' => 'activo'
        ],
        [
            'nombre' => 'Juan',
            'apellido' => 'Pérez Ramos',
            'correo' => 'estudiante@senati.pe',
            'contrasena' => $hash,
            'rol_id' => 1, // Estudiante
            'estado' => 'activo'
        ],
        [
            'nombre' => 'Ana',
            'apellido' => 'García Rojas',
            'correo' => 'ana.garcia@senati.pe',
            'contrasena' => $hash,
            'rol_id' => 1, // Estudiante
            'estado' => 'activo'
        ]
    ];

    $stmtUser = $conn->prepare("
        INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol_id, estado)
        VALUES (:nombre, :apellido, :correo, :contrasena, :rol_id, :estado)
        ON DUPLICATE KEY UPDATE 
            nombre = VALUES(nombre),
            apellido = VALUES(apellido),
            contrasena = VALUES(contrasena),
            rol_id = VALUES(rol_id),
            estado = VALUES(estado)
    ");

    foreach ($usuarios as $u) {
        $stmtUser->execute($u);
    }
    echo "Usuarios sembrados con éxito.\n";

    // Obtener IDs
    $getId = function($correo) use ($conn) {
        $st = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $st->execute([$correo]);
        return $st->fetchColumn();
    };

    $idAdmin = $getId('admin@senati.pe') ?: $getId('admin@arandio.com');
    $idRecSenati = $getId('reclutador@senati.pe');
    $idRec1 = $getId('reclutador@techcorp.com');
    $idRec2 = $getId('talento@innovasolutions.com');
    $idEstArandio = $getId('arandio@senati.pe');
    $idEstFrancklin = $getId('francklin@senati.pe');
    $idEst1 = $getId('estudiante@senati.pe');
    $idEst2 = $getId('ana.garcia@senati.pe');

    // 3. Insertar Reclutadores
    $stmtRec = $conn->prepare("
        INSERT INTO reclutadores (usuario_id, empresa, descripcion, sitio_web, sector, telefono, ubicacion, logo_url)
        VALUES (:usuario_id, :empresa, :descripcion, :sitio_web, :sector, :telefono, :ubicacion, :logo_url)
        ON DUPLICATE KEY UPDATE
            empresa = VALUES(empresa),
            descripcion = VALUES(descripcion),
            sitio_web = VALUES(sitio_web),
            sector = VALUES(sector),
            telefono = VALUES(telefono),
            ubicacion = VALUES(ubicacion),
            logo_url = VALUES(logo_url)
    ");

    if ($idRecSenati) {
        $stmtRec->execute([
            'usuario_id' => $idRecSenati,
            'empresa' => 'Bolsa de Empleo SENATI',
            'descripcion' => 'Plataforma oficial de intermediación laboral y vinculación empresarial de SENATI.',
            'sitio_web' => 'https://www.senati.edu.pe',
            'sector' => 'Tecnología & Formación Dual',
            'telefono' => '+51 980 000 111',
            'ubicacion' => 'Lima, Sede Central',
            'logo_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=200&q=80'
        ]);
    }

    $stmtRec->execute([
        'usuario_id' => $idRec1,
        'empresa' => 'TechCorp Solutions',
        'descripcion' => 'Líderes en desarrollo de software empresarial, transformación digital y computación en la nube para empresas globales.',
        'sitio_web' => 'https://techcorp.example.com',
        'sector' => 'Tecnología & Desarrollo',
        'telefono' => '+51 987 111 222',
        'ubicacion' => 'Lima, San Isidro (Híbrido)',
        'logo_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=200&q=80'
    ]);

    $stmtRec->execute([
        'usuario_id' => $idRec2,
        'empresa' => 'Innova Marketing & Media',
        'descripcion' => 'Agencia creativa de marketing de rendimiento, diseño experiencial y estrategias de contenido digital.',
        'sitio_web' => 'https://innovamedia.example.com',
        'sector' => 'Marketing & Redes',
        'telefono' => '+51 987 333 444',
        'ubicacion' => 'Lima, Miraflores (Remoto)',
        'logo_url' => 'https://images.unsplash.com/photo-1572021335469-31706a17aaef?auto=format&fit=crop&w=200&q=80'
    ]);

    // 4. Insertar Estudiantes
    $stmtEst = $conn->prepare("
        INSERT INTO estudiantes (usuario_id, biografia, habilidades, disponibilidad, telefono, ciudad, foto_url, cv_ruta)
        VALUES (:usuario_id, :biografia, :habilidades, :disponibilidad, :telefono, :ciudad, :foto_url, :cv_ruta)
        ON DUPLICATE KEY UPDATE
            biografia = VALUES(biografia),
            habilidades = VALUES(habilidades),
            disponibilidad = VALUES(disponibilidad),
            telefono = VALUES(telefono),
            ciudad = VALUES(ciudad),
            foto_url = VALUES(foto_url)
    ");

    if ($idEstArandio) {
        $stmtEst->execute([
            'usuario_id' => $idEstArandio,
            'biografia' => 'Estudiante y Desarrollador de la plataforma de empleo SENATI. Especialista en arquitectura web, desarrollo con PHP y bases de datos MySQL.',
            'habilidades' => 'PHP, MySQL, JavaScript, HTML5, CSS3, Arquitectura Web, Git',
            'disponibilidad' => 'Tiempo Completo / Inmediata',
            'telefono' => '+51 999 111 222',
            'ciudad' => 'Lima, Perú',
            'foto_url' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=300&q=80',
            'cv_ruta' => 'uploads/cv/cv_estudiante_senati.pdf'
        ]);
    }

    if ($idEstFrancklin) {
        $stmtEst->execute([
            'usuario_id' => $idEstFrancklin,
            'biografia' => 'Estudiante y Desarrollador de la plataforma de empleo SENATI. Especialista en interfaces de usuario responsivas, frontend interactivo y control de calidad.',
            'habilidades' => 'Frontend UI/UX, JavaScript, PHP, MySQL, CSS responsivo, Seguridad Web',
            'disponibilidad' => 'Tiempo Completo / Inmediata',
            'telefono' => '+51 999 333 444',
            'ciudad' => 'Lima, Perú',
            'foto_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
            'cv_ruta' => 'uploads/cv/cv_estudiante_senati.pdf'
        ]);
    }

    $stmtEst->execute([
        'usuario_id' => $idEst1,
        'biografia' => 'Estudiante de últimos ciclos de Ingeniería y Desarrollo de Software. Apasionado por el desarrollo web backend con PHP/MySQL y frontend interactivo. Proactivo y con ganas de aprender nuevas tecnologías.',
        'habilidades' => 'PHP, MySQL, JavaScript, HTML5, CSS3, Git, Bootstrap, APIs RESTful',
        'disponibilidad' => 'Tiempo Completo / Inmediata',
        'telefono' => '+51 912 345 678',
        'ciudad' => 'Lima, Perú',
        'foto_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
        'cv_ruta' => 'uploads/cv/cv_estudiante_senati.pdf'
    ]);

    $stmtEst->execute([
        'usuario_id' => $idEst2,
        'biografia' => 'Diseñadora UI/UX en formación con sólida experiencia en prototipado con Figma, investigación de usuarios, wireframing y sistemas de diseño colaborativo.',
        'habilidades' => 'Figma, Adobe XD, Photoshop, Illustrator, UI/UX, Design Systems, HTML/CSS',
        'disponibilidad' => 'Medio Tiempo / Mañanas',
        'telefono' => '+51 987 654 321',
        'ciudad' => 'Arequipa, Perú',
        'foto_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=300&q=80',
        'cv_ruta' => 'uploads/cv/cv_ana_garcia.pdf'
    ]);

    // IDs de perfil reclutador y estudiante
    $rec1_id = $conn->query("SELECT id FROM reclutadores WHERE usuario_id = $idRec1")->fetchColumn();
    $rec2_id = $conn->query("SELECT id FROM reclutadores WHERE usuario_id = $idRec2")->fetchColumn();
    $est1_id = $conn->query("SELECT id FROM estudiantes WHERE usuario_id = $idEst1")->fetchColumn();
    $est2_id = $conn->query("SELECT id FROM estudiantes WHERE usuario_id = $idEst2")->fetchColumn();

    // 5. Insertar Ofertas
    $conn->exec("DELETE FROM ofertas"); // reset para demo limpia
    $stmtOferta = $conn->prepare("
        INSERT INTO ofertas (reclutador_id, titulo, descripcion, requisitos, funciones, ubicacion, sector, tipo_jornada, rango_salarial, salario, imagen_url, estado, fecha_publicacion)
        VALUES (:reclutador_id, :titulo, :descripcion, :requisitos, :funciones, :ubicacion, :sector, :tipo_jornada, :rango_salarial, :salario, :imagen_url, :estado, NOW())
    ");

    $ofertas = [
        [
            'reclutador_id' => $rec1_id,
            'titulo' => 'Desarrollador Web Junior (PHP & MySQL)',
            'descripcion' => 'Buscamos un estudiante o egresado entusiasta para unirse a nuestro equipo de ingeniería web, desarrollando nuevos módulos e integrando servicios REST.',
            'requisitos' => "• Conocimientos sólidos en PHP 8 y bases de datos MySQL/MariaDB.\n• Manejo básico de JavaScript, CSS y HTML semántico.\n• Familiaridad con Git y control de versiones.\n• Trabajo en equipo y comunicación asertiva.",
            'funciones' => "• Programar y dar mantenimiento a módulos backend.\n• Crear consultas SQL optimizadas.\n• Realizar pruebas de funcionalidad y depuración.",
            'ubicacion' => 'Lima (Híbrido)',
            'sector' => 'Tecnología & Desarrollo',
            'tipo_jornada' => 'Tiempo Completo',
            'rango_salarial' => 'S/ 1,800 - S/ 2,400',
            'salario' => 2000.00,
            'imagen_url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=600&q=80',
            'estado' => 'activa'
        ],
        [
            'reclutador_id' => $rec1_id,
            'titulo' => 'Practicante de Soporte Técnico y Redes',
            'descripcion' => 'Oportunidad de prácticas pre-profesionales para dar soporte a infraestructura local, equipos y cuentas de usuario.',
            'requisitos' => "• Estudiante de carreras de Computación, Redes o Sistemas.\n• Conocimientos en configuración de redes LAN/Wi-Fi y soporte Windows.\n• Proactividad y orientación al cliente interno.",
            'funciones' => "• Diagnóstico y mantenimiento preventivo de hardware.\n• Instalación y configuración de software corporativo.\n• Atención de tickets de soporte.",
            'ubicacion' => 'San Isidro, Lima',
            'sector' => 'Soporte Técnico & Redes',
            'tipo_jornada' => 'Medio Tiempo',
            'rango_salarial' => 'S/ 1,100 - S/ 1,300',
            'salario' => 1200.00,
            'imagen_url' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80',
            'estado' => 'activa'
        ],
        [
            'reclutador_id' => $rec2_id,
            'titulo' => 'Diseñador UI/UX Junior & Contenido Visual',
            'descripcion' => 'Diseño de interfaces intuitivas, maquetas interactivas y material gráfico publicitario para marcas líderes del mercado digital.',
            'requisitos' => "• Manejo fluido de Figma y suite Adobe (Photoshop, Illustrator).\n• Criterio estético, tipografía y paletas de color.\n• Portafolio con proyectos estudiantiles o personales.",
            'funciones' => "• Diseñar wireframes y prototipos navegables en Figma.\n• Crear piezas gráficas para redes sociales y banners web.\n• Colaborar con los desarrolladores en la implementación UI.",
            'ubicacion' => 'Remoto',
            'sector' => 'Diseño & Multimedia',
            'tipo_jornada' => 'Tiempo Completo',
            'rango_salarial' => 'S/ 1,600 - S/ 2,200',
            'salario' => 1800.00,
            'imagen_url' => 'https://images.unsplash.com/photo-1542744094-3a31f272c490?auto=format&fit=crop&w=600&q=80',
            'estado' => 'activa'
        ],
        [
            'reclutador_id' => $rec2_id,
            'titulo' => 'Asistente de Marketing Digital y Redes Sociales',
            'descripcion' => 'Gestión de comunidades virtuales, creación de calendarios de publicación y análisis de métricas de interacción.',
            'requisitos' => "• Estudiante de Marketing, Publicidad o Comunicación.\n• Excelente redacción y ortografía.\n• Conocimiento de Meta Ads y Google Analytics deseable.",
            'funciones' => "• Programación de publicaciones en redes sociales.\n• Monitoreo y respuesta a comentarios de clientes.\n• Generación de reportes quincenales de rendimiento.",
            'ubicacion' => 'Miraflores, Lima (Híbrido)',
            'sector' => 'Marketing & Redes',
            'tipo_jornada' => 'Medio Tiempo',
            'rango_salarial' => 'S/ 1,200 - S/ 1,500',
            'salario' => 1300.00,
            'imagen_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80',
            'estado' => 'activa'
        ],
        [
            'reclutador_id' => $rec1_id,
            'titulo' => 'Analista de Datos Trainee (SQL & Power BI)',
            'descripcion' => 'Aprende y crece en la creación de tableros de control y extracción de datos para la toma de decisiones estratégicas.',
            'requisitos' => "• Manejo de consultas SQL y Excel intermedio/avanzado.\n• Deseable conocimiento básico en Power BI o Python.\n• Alta capacidad analítica y atención a los detalles.",
            'funciones' => "• Limpieza y transformación de bases de datos.\n• Creación de dashboards e indicadores clave (KPIs).\n• Automatización de reportes semanales.",
            'ubicacion' => 'Remoto',
            'sector' => 'Tecnología & Desarrollo',
            'tipo_jornada' => 'Tiempo Completo',
            'rango_salarial' => 'S/ 2,000 - S/ 2,800',
            'salario' => 2200.00,
            'imagen_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=600&q=80',
            'estado' => 'activa'
        ]
    ];

    foreach ($ofertas as $of) {
        $stmtOferta->execute($of);
    }
    echo "Ofertas sembradas con éxito.\n";

    // 6. Insertar Postulaciones de prueba
    $ofertaIds = $conn->query("SELECT id FROM ofertas ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($ofertaIds) && $est1_id && $est2_id) {
        $stmtPost = $conn->prepare("
            INSERT INTO postulaciones (estudiante_id, oferta_id, estado, mensaje, notas_reclutador, fecha)
            VALUES (:estudiante_id, :oferta_id, :estado, :mensaje, :notas_reclutador, NOW())
            ON DUPLICATE KEY UPDATE estado = VALUES(estado)
        ");

        // Postulación 1: Juan a Desarrollador Web (En revisión)
        $stmtPost->execute([
            'estudiante_id' => $est1_id,
            'oferta_id' => $ofertaIds[0],
            'estado' => 'En revisión',
            'mensaje' => 'Hola, me interesa mucho la vacante de programador web. Cuento con proyectos en PHP y MySQL.',
            'notas_reclutador' => 'Perfil sólido en PHP, convocar a prueba técnica la próxima semana.'
        ]);

        // Postulación 2: Juan a Soporte Técnico (Aceptada)
        if (isset($ofertaIds[1])) {
            $stmtPost->execute([
                'estudiante_id' => $est1_id,
                'oferta_id' => $ofertaIds[1],
                'estado' => 'Aceptada',
                'mensaje' => 'Cuento con experiencia en soporte y redes LAN.',
                'notas_reclutador' => 'Aceptado para proceso de inducción el lunes.'
            ]);
        }

        // Postulación 3: Juan a Analista de Datos (Pendiente)
        if (isset($ofertaIds[4])) {
            $stmtPost->execute([
                'estudiante_id' => $est1_id,
                'oferta_id' => $ofertaIds[4],
                'estado' => 'Pendiente',
                'mensaje' => 'Tengo sólidos conocimientos de SQL y modelamiento de datos.',
                'notas_reclutador' => null
            ]);
        }

        // Postulación 4: Ana a Diseñador UI/UX (Entrevista programada)
        if (isset($ofertaIds[2])) {
            $stmtPost->execute([
                'estudiante_id' => $est2_id,
                'oferta_id' => $ofertaIds[2],
                'estado' => 'Entrevista programada',
                'mensaje' => 'Adjunto mi portafolio con prototipos interactivos en Figma.',
                'notas_reclutador' => 'Entrevista agendada por Google Meet para el jueves a las 11:00 am.'
            ]);
        }

        echo "Postulaciones de prueba registradas con éxito.\n";
    }

    echo "=== INSTALACIÓN Y CARGA DE DATOS COMPLETADA CON ÉXITO ===\n";

    if (php_sapi_name() !== 'cli') {
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Instalación Exitosa | SENATI Empleo</title>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
            <style>
                body { font-family: system-ui, sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
                .card { background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); text-align: center; max-width: 520px; border-top: 6px solid #10b981; }
                .icon { font-size: 3.5rem; color: #10b981; margin-bottom: 16px; }
                h1 { color: #0f172a; margin-bottom: 8px; font-size: 1.5rem; }
                p { color: #64748b; line-height: 1.6; margin-bottom: 24px; }
                .btn { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 1rem; }
                .btn:hover { background: #1d4ed8; }
            </style>
        </head>
        <body>
            <div class='card'>
                <i class='fas fa-check-circle icon'></i>
                <h1>¡Base de Datos Instalada con Éxito!</h1>
                <p>Todas las tablas, usuarios y vacantes iniciales han sido creadas en tu hosting <strong>amamanit.jeracorp.es</strong>.</p>
                <a href='index.php' class='btn'><i class='fas fa-arrow-right'></i> Ir a la Plataforma</a>
            </div>
        </body>
        </html>";
    }

} catch (Exception $e) {
    echo "Error durante la migración: " . $e->getMessage() . "\n";
}
?>
