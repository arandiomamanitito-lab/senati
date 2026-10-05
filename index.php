<?php
// ========================================================
// PLATAFORMA DE GESTIÓN DE EMPLEO Y POSTULACIONES
// Página Principal / Portada Pública (index.php)
// ========================================================

require_once __DIR__ . '/config/conexion.php';

// Cargar configuración institucional de forma segura
$configRaw = [];
$ofertasDestacadas = [];
$totalEstudiantes = 0;
$totalOfertas = 0;
$totalReclutadores = 0;

try {
    $configRaw = $conn->query("SELECT clave, valor FROM configuracion")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $eConf) {
    // Si la tabla no existe en el hosting, intentar crearla al vuelo
    if (file_exists(__DIR__ . '/database.sql')) {
        try {
            $conn->exec(file_get_contents(__DIR__ . '/database.sql'));
            $configRaw = $conn->query("SELECT clave, valor FROM configuracion")->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $eRetry) {}
    }
}

$tituloSistema = $configRaw['titulo_sistema'] ?? 'SENATI - Plataforma de Gestión de Empleo';
$descripcionHero = $configRaw['descripcion_hero'] ?? 'Conectamos a estudiantes con reclutadores y empresas líderes para impulsar su crecimiento profesional.';
$bannerUrl = !empty($configRaw['banner_url']) ? $configRaw['banner_url'] : 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=80';

// Cargar ofertas recientes activas
try {
    $stmtOf = $conn->query("
        SELECT o.*, r.empresa, r.logo_url 
        FROM ofertas o
        JOIN reclutadores r ON o.reclutador_id = r.id
        WHERE o.estado = 'activa'
        ORDER BY o.fecha_publicacion DESC
        LIMIT 6
    ");
    $ofertasDestacadas = $stmtOf->fetchAll();
} catch (Exception $eOf) {}

// Cargar métricas totales
try {
    $totalEstudiantes = (int)$conn->query("SELECT COUNT(*) FROM usuarios WHERE rol_id = 1")->fetchColumn();
    $totalOfertas = (int)$conn->query("SELECT COUNT(*) FROM ofertas WHERE estado = 'activa'")->fetchColumn();
    $totalReclutadores = (int)$conn->query("SELECT COUNT(*) FROM usuarios WHERE rol_id = 2")->fetchColumn();
} catch (Exception $eMet) {}

// Verificar si hay sesión activa para mostrar botón al panel
$estaLogueado = isset($_SESSION['usuario_id'], $_SESSION['rol_id']);
$urlMiPanel = url('sesion/iniciar.php');
if ($estaLogueado) {
    $rol = (int)$_SESSION['rol_id'];
    if ($rol === ROL_ESTUDIANTE) $urlMiPanel = url('paneles/estudiante.php');
    elseif ($rol === ROL_RECLUTADOR) $urlMiPanel = url('paneles/reclutador.php');
    elseif ($rol === ROL_ADMIN) $urlMiPanel = url('paneles/admin.php');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloSistema) ?></title>
    <!-- Iconos Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilo Base General -->
    <link rel="stylesheet" href="<?= url('estilo.css') ?>">
    <style>
        .landing-nav {
            background: #ffffff;
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        .hero-banner {
            position: relative;
            background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.85)), url('<?= e($bannerUrl) ?>') center/cover no-repeat;
            color: #ffffff;
            padding: 90px 24px;
            text-align: center;
        }
        .hero-container {
            max-width: 850px;
            margin: 0 auto;
        }
        .hero-title {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 18px;
        }
        .hero-subtitle {
            font-size: 1.15rem;
            color: #e2e8f0;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .hero-search-box {
            background: #ffffff;
            padding: 10px;
            border-radius: 14px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            display: flex;
            gap: 10px;
            max-width: 700px;
            margin: 0 auto;
        }
        .hero-search-box input {
            flex: 1;
            border: none;
            padding: 12px 18px;
            font-size: 1rem;
            outline: none;
            color: #1e293b;
        }
        .stats-strip {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 30px 20px;
        }
        .stats-strip-grid {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            text-align: center;
            gap: 20px;
        }
        .strip-item h3 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary);
        }
        .strip-item p {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 600;
        }
        .section-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 24px;
        }
        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }
        .section-title h2 {
            font-size: 2rem;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .section-title p {
            color: var(--text-muted);
            font-size: 1rem;
        }
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 40px;
        }
        .step-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 32px 24px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        .step-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        @media (max-width: 768px) {
            .landing-nav { padding: 0 20px; }
            .hero-title { font-size: 2rem; }
            .hero-search-box { flex-direction: column; }
            .stats-strip-grid { grid-template-columns: 1fr; gap: 24px; }
        }
    </style>
</head>
<body>
    <!-- Barra de Navegación Superior -->
    <nav class="landing-nav">
        <a href="<?= url('index.php') ?>" class="brand-logo">
            <i class="fas fa-briefcase"></i>
            <span>SENATI <small style="font-weight:400;font-size:0.85em;color:#64748b;">Empleo</small></span>
        </a>

        <div style="display:flex;align-items:center;gap:14px;">
            <a href="<?= url('modulos/ofertas_lista.php') ?>" class="btn btn-secondary">
                <i class="fas fa-search"></i> Ver Empleos
            </a>
            <?php if ($estaLogueado): ?>
                <a href="<?= $urlMiPanel ?>" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i> Mi Panel (<?= e($_SESSION['nombre']) ?>)
                </a>
                <a href="<?= url('sesion/cerrar.php') ?>" class="btn btn-danger btn-sm" title="Salir">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            <?php else: ?>
                <a href="<?= url('sesion/iniciar.php') ?>" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Banner Hero Principal -->
    <section class="hero-banner">
        <div class="hero-container">
            <h1 class="hero-title"><?= e($tituloSistema) ?></h1>
            <p class="hero-subtitle"><?= e($descripcionHero) ?></p>

            <form action="<?= url('modulos/ofertas_lista.php') ?>" method="GET" class="hero-search-box">
                <i class="fas fa-search" style="margin:auto 0 auto 12px;color:#94a3b8;font-size:1.1rem;"></i>
                <input type="text" name="buscar" placeholder="¿Qué puesto o tecnología estás buscando? (Ej: PHP, Redes, Diseño)">
                <button type="submit" class="btn btn-primary" style="padding:12px 24px;border-radius:10px;">
                    Buscar Ofertas
                </button>
            </form>
        </div>
    </section>

    <!-- Franja de Métricas -->
    <section class="stats-strip">
        <div class="stats-strip-grid">
            <div class="strip-item">
                <h3><?= $totalEstudiantes ?>+</h3>
                <p>Estudiantes Conectados</p>
            </div>
            <div class="strip-item">
                <h3><?= $totalOfertas ?></h3>
                <p>Vacantes Disponibles Hoy</p>
            </div>
            <div class="strip-item">
                <h3><?= $totalReclutadores ?>+</h3>
                <p>Empresas Reclutando</p>
            </div>
        </div>
    </section>

    <!-- Ofertas Destacadas -->
    <section class="section-container">
        <div class="section-title">
            <h2>Vacantes Recientes</h2>
            <p>Oportunidades laborales publicadas recientemente por empresas verificadas.</p>
        </div>

        <?php if (!empty($ofertasDestacadas)): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:24px;">
                <?php foreach ($ofertasDestacadas as $of): ?>
                    <div class="card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                                <div style="width:48px;height:48px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid #e2e8f0;flex-shrink:0;">
                                    <?php if (!empty($of['logo_url'])): ?>
                                        <img src="<?= e($of['logo_url']) ?>" alt="Logo">
                                    <?php else: ?>
                                        <i class="fas fa-building" style="color:#94a3b8;"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 style="font-size:1.05rem;color:#0f172a;margin:0;"><?= e($of['titulo']) ?></h3>
                                    <span style="font-size:0.85rem;color:#64748b;"><i class="fas fa-building"></i> <?= e($of['empresa']) ?></span>
                                </div>
                            </div>
                            <p style="font-size:0.88rem;color:#475569;margin-bottom:14px;line-height:1.5;">
                                <?= mb_strimwidth(strip_tags($of['descripcion']), 0, 110, '...') ?>
                            </p>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;">
                                <span style="font-size:0.78rem;background:#f1f5f9;padding:3px 8px;border-radius:6px;color:#475569;">
                                    <i class="fas fa-map-marker-alt"></i> <?= e($of['ubicacion']) ?>
                                </span>
                                <span style="font-size:0.78rem;background:#f1f5f9;padding:3px 8px;border-radius:6px;color:#475569;">
                                    <i class="fas fa-clock"></i> <?= e($of['tipo_jornada']) ?>
                                </span>
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid #f1f5f9;">
                            <span style="color:#059669;font-weight:700;font-size:0.95rem;">
                                <?= !empty($of['rango_salarial']) ? e($of['rango_salarial']) : ($of['salario'] ? 'S/ ' . number_format($of['salario'], 2) : 'A convenir') ?>
                            </span>
                            <a href="<?= url('modulos/ofertas_lista.php?id=' . $of['id']) ?>" class="btn btn-primary btn-sm">
                                Postular
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:36px;">
                <a href="<?= url('modulos/ofertas_lista.php') ?>" class="btn btn-secondary">
                    Explorar Todas las Convocatorias <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Cómo Funciona la Plataforma -->
    <section style="background:#f8fafc;padding:60px 24px;border-top:1px solid var(--border-color);border-bottom:1px solid var(--border-color);">
        <div style="max-width:1100px;margin:0 auto;">
            <div class="section-title">
                <h2>¿Cómo Funciona SENATI Empleo?</h2>
                <p>Nuestra plataforma conecta a estudiantes, reclutadores y administración con paneles exclusivos adaptados a cada necesidad.</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-icon" style="background:#dbeafe;color:#1d4ed8;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 style="font-size:1.2rem;margin-bottom:10px;">Estudiantes</h3>
                    <p style="color:#64748b;font-size:0.92rem;line-height:1.5;">
                        Crea tu perfil profesional, sube tu currículum vitae en PDF, busca ofertas según tus competencias y monitorea tus postulaciones con estados en color.
                    </p>
                </div>

                <div class="step-card">
                    <div class="step-icon" style="background:#ccfbf1;color:#0f766e;">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 style="font-size:1.2rem;margin-bottom:10px;">Reclutadores</h3>
                    <p style="color:#64748b;font-size:0.92rem;line-height:1.5;">
                        Publica y gestiona tus ofertas laborales con todos los detalles. Revisa postulaciones recibidas, descarga CVs y cambia el estado de cada candidato.
                    </p>
                </div>

                <div class="step-card">
                    <div class="step-icon" style="background:#ede9fe;color:#6d28d9;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 style="font-size:1.2rem;margin-bottom:10px;">Administración</h3>
                    <p style="color:#64748b;font-size:0.92rem;line-height:1.5;">
                        Supervisión integral de usuarios y vacantes, moderación de contenidos, reportes estadísticos y personalización de la plataforma.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Apartado de Autores del Proyecto -->
    <section style="background:#ffffff;padding:50px 24px;border-top:1px solid var(--border-color);">
        <div style="max-width:900px;margin:0 auto;text-align:center;">
            <div style="display:inline-flex;align-items:center;gap:8px;background:#e0f2fe;color:#0284c7;padding:6px 16px;border-radius:20px;font-size:0.85rem;font-weight:600;margin-bottom:12px;">
                <i class="fas fa-users-cog"></i> Créditos y Desarrollo
            </div>
            <h2 style="font-size:1.8rem;color:#0f172a;margin-bottom:8px;">Autores del Proyecto</h2>
            <p style="color:#64748b;font-size:0.95rem;margin-bottom:30px;max-width:600px;margin-left:auto;margin-right:auto;">
                Plataforma de Gestión de Empleo y Postulaciones desarrollada para la comunidad de <strong>SENATI</strong>.
            </p>
            <div style="display:flex;justify-content:center;gap:24px;flex-wrap:wrap;">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:22px 28px;display:flex;align-items:center;gap:16px;min-width:260px;box-shadow:0 2px 8px rgba(0,0,0,0.02);text-align:left;">
                    <div style="width:50px;height:50px;border-radius:50%;background:#0284c7;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.2rem;box-shadow:0 4px 10px rgba(2,132,199,0.3);">
                        A
                    </div>
                    <div>
                        <div style="font-weight:700;color:#0f172a;font-size:1.15rem;">Arandio</div>
                        <div style="font-size:0.83rem;color:#0284c7;font-weight:600;"><i class="fas fa-user-check"></i> Autor &bull; Desarrollador</div>
                    </div>
                </div>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:22px 28px;display:flex;align-items:center;gap:16px;min-width:260px;box-shadow:0 2px 8px rgba(0,0,0,0.02);text-align:left;">
                    <div style="width:50px;height:50px;border-radius:50%;background:#0f766e;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.2rem;box-shadow:0 4px 10px rgba(15,118,110,0.3);">
                        F
                    </div>
                    <div>
                        <div style="font-weight:700;color:#0f172a;font-size:1.15rem;">Francklin</div>
                        <div style="font-size:0.83rem;color:#0f766e;font-weight:600;"><i class="fas fa-user-check"></i> Autor &bull; Desarrollador</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pie de Página -->
    <footer style="background:#0f172a;color:#94a3b8;padding:40px 24px;font-size:0.9rem;">
        <div style="max-width:1100px;margin:0 auto;display:flex;justify-content:space-between;flex-wrap:wrap;gap:20px;">
            <div>
                <h4 style="color:#fff;font-size:1.1rem;margin-bottom:8px;">SENATI Empleo</h4>
                <p>Plataforma de Gestión de Empleo y Postulaciones.</p>
                <p style="margin-top:6px;"><i class="fas fa-envelope"></i> <?= e($configRaw['correo_contacto'] ?? 'soporte@senati.pe') ?></p>
            </div>
            <div>
                <h5 style="color:#fff;margin-bottom:8px;">Autores del Proyecto</h5>
                <p style="color:#cbd5e1;margin-bottom:4px;font-weight:600;"><i class="fas fa-users"></i> Arandio y Francklin</p>
                <p style="font-size:0.8rem;color:#64748b;">Desarrollo e Implementación</p>
            </div>
            <div>
                <h5 style="color:#fff;margin-bottom:8px;">Accesos Rápidos</h5>
                <ul style="list-style:none;line-height:1.8;">
                    <li><a href="<?= url('sesion/iniciar.php') ?>" style="color:#cbd5e1;"><i class="fas fa-chevron-right" style="font-size:0.7rem;"></i> Iniciar Sesión</a></li>
                    <li><a href="<?= url('modulos/ofertas_lista.php') ?>" style="color:#cbd5e1;"><i class="fas fa-chevron-right" style="font-size:0.7rem;"></i> Ver Ofertas de Empleo</a></li>
                </ul>
            </div>
        </div>
        <div style="max-width:1100px;margin:30px auto 0;padding-top:20px;border-top:1px solid #1e293b;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;font-size:0.82rem;">
            <div>
                &copy; <?= date('Y') ?> <strong>SENATI Empleo</strong>. Todos los derechos reservados.
            </div>
            <div>
                Autores: <strong style="color:#f1f5f9;">Arandio y Francklin</strong>
            </div>
        </div>
    </footer>

    <!-- Script Global -->
    <script src="<?= url('main.js') ?>"></script>
</body>
</html>
