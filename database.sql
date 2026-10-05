-- Base de Datos para Plataforma de Gestión de Empleo y Postulaciones
-- Base de datos: u536982963_BDAMAMANIT

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `documentos`;
DROP TABLE IF EXISTS `postulaciones`;
DROP TABLE IF EXISTS `ofertas`;
DROP TABLE IF EXISTS `empresas`;
DROP TABLE IF EXISTS `reclutadores`;
DROP TABLE IF EXISTS `estudiantes`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `configuracion`;
DROP TABLE IF EXISTS `categorias`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabla de Roles
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Estudiante'),
(2, 'Reclutador'),
(3, 'Administrador')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`);

-- 2. Tabla de Usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `apellido` VARCHAR(100) NOT NULL,
    `correo` VARCHAR(150) NOT NULL UNIQUE,
    `contrasena` VARCHAR(255) NOT NULL,
    `rol_id` INT NOT NULL,
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabla de Estudiantes
CREATE TABLE IF NOT EXISTS `estudiantes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL UNIQUE,
    `biografia` TEXT,
    `habilidades` TEXT,
    `disponibilidad` VARCHAR(100) DEFAULT 'Inmediata',
    `telefono` VARCHAR(50) DEFAULT '',
    `ciudad` VARCHAR(100) DEFAULT 'Lima, Perú',
    `foto_url` VARCHAR(500) DEFAULT '',
    `cv_ruta` VARCHAR(500) DEFAULT '',
    CONSTRAINT `fk_estudiante_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabla de Reclutadores
CREATE TABLE IF NOT EXISTS `reclutadores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL UNIQUE,
    `empresa` VARCHAR(150) NOT NULL,
    `descripcion` TEXT,
    `sitio_web` VARCHAR(255) DEFAULT '',
    `sector` VARCHAR(100) DEFAULT 'Tecnología',
    `telefono` VARCHAR(50) DEFAULT '',
    `ubicacion` VARCHAR(150) DEFAULT 'Lima, Perú',
    `logo_url` VARCHAR(500) DEFAULT '',
    CONSTRAINT `fk_reclutador_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabla de Empresas
CREATE TABLE IF NOT EXISTS `empresas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reclutador_id` INT NULL,
    `nombre` VARCHAR(150) NOT NULL,
    `descripcion` TEXT,
    `sitio_web` VARCHAR(255) DEFAULT '',
    `sector` VARCHAR(100) DEFAULT 'Tecnología',
    `logo_url` VARCHAR(500) DEFAULT '',
    `ubicacion` VARCHAR(150) DEFAULT 'Lima, Perú',
    CONSTRAINT `fk_empresa_reclutador` FOREIGN KEY (`reclutador_id`) REFERENCES `reclutadores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabla de Ofertas Laborales
CREATE TABLE IF NOT EXISTS `ofertas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reclutador_id` INT NOT NULL,
    `titulo` VARCHAR(200) NOT NULL,
    `descripcion` TEXT NOT NULL,
    `requisitos` TEXT,
    `funciones` TEXT,
    `ubicacion` VARCHAR(150) NOT NULL DEFAULT 'Remoto',
    `sector` VARCHAR(100) DEFAULT 'Tecnología',
    `tipo_jornada` VARCHAR(100) DEFAULT 'Tiempo Completo',
    `rango_salarial` VARCHAR(100) DEFAULT 'S/ 1,500 - S/ 2,500',
    `salario` DECIMAL(10,2) DEFAULT NULL,
    `imagen_url` VARCHAR(500) DEFAULT '',
    `estado` ENUM('activa', 'pausada', 'cerrada', 'pendiente_aprobacion') DEFAULT 'activa',
    `fecha_publicacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_oferta_reclutador` FOREIGN KEY (`reclutador_id`) REFERENCES `reclutadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabla de Postulaciones
CREATE TABLE IF NOT EXISTS `postulaciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `estudiante_id` INT NOT NULL,
    `oferta_id` INT NOT NULL,
    `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `estado` ENUM('Pendiente', 'En revisión', 'Entrevista programada', 'Aceptada', 'Rechazada') DEFAULT 'Pendiente',
    `mensaje` TEXT,
    `notas_reclutador` TEXT,
    UNIQUE KEY `uk_postulacion_unica` (`estudiante_id`, `oferta_id`),
    CONSTRAINT `fk_postulacion_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_postulacion_oferta` FOREIGN KEY (`oferta_id`) REFERENCES `ofertas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabla de Documentos
CREATE TABLE IF NOT EXISTS `documentos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `nombre_archivo` VARCHAR(255) NOT NULL,
    `ruta` VARCHAR(500) NOT NULL,
    `tipo` VARCHAR(50) DEFAULT 'cv',
    `tamano_kb` INT DEFAULT 0,
    `fecha_subida` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_documento_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Tabla de Configuración General
CREATE TABLE IF NOT EXISTS `configuracion` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `clave` VARCHAR(100) NOT NULL UNIQUE,
    `valor` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('titulo_sistema', 'SENATI - Plataforma de Gestión de Empleo'),
('descripcion_hero', 'Conectamos el talento de nuestros estudiantes con las mejores oportunidades laborales y empresas del país.'),
('banner_url', 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=80'),
('correo_contacto', 'soporte@senati.pe'),
('telefono_contacto', '+51 987 654 321'),
('mensaje_bienvenida', '¡Bienvenido a la red de empleo líder para estudiantes y empresas!')
ON DUPLICATE KEY UPDATE `valor`=VALUES(`valor`);

-- 10. Tabla de Categorías de Empleo
CREATE TABLE IF NOT EXISTS `categorias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL UNIQUE,
    `icono` VARCHAR(50) DEFAULT 'fa-briefcase'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categorias` (`nombre`, `icono`) VALUES
('Tecnología & Desarrollo', 'fa-code'),
('Diseño & Multimedia', 'fa-palette'),
('Administración & Finanzas', 'fa-chart-line'),
('Marketing & Redes', 'fa-bullhorn'),
('Soporte Técnico & Redes', 'fa-network-wired'),
('Logística & Operaciones', 'fa-truck-loading')
ON DUPLICATE KEY UPDATE `icono`=VALUES(`icono`);
