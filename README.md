# Mi Progreso Fit - Aplicación Full-Stack

Aplicación web multi-cliente de progreso fitness/nutrición para entrenamientos personalizados.

## 🚀 Características

- ✅ Sistema de login/registro multi-usuario
- ✅ Dashboard personalizado por cliente
- ✅ Seguimiento de peso y medidas corporales
- ✅ Registro de sentimientos diarios (energía, hambre, adherencia)
- ✅ Tracking de macronutrientes con anillos visuales
- ✅ Plan de entrenamientos semanal
- ✅ Subida y galería de fotos de progreso
- ✅ Gráficos de evolución de peso
- ✅ KPIs de progreso hacia la meta

## 📋 Requisitos

- PHP 8.x
- MySQL 5.7+
- Hosting compartido compatible (Hostinger)
- Extensiones PHP: PDO, GD (para imágenes)

## 🛠️ Instalación

### 1. Subir archivos a Hostinger

Sube todos los archivos a tu carpeta `public_html` en Hostinger:

```
/public_html
  ├── index.php
  ├── dashboard.php
  ├── config.php
  ├── /api
  │   ├── login.php
  │   ├── register.php
  │   ├── logout.php
  │   ├── save_metrics.php
  │   ├── save_feelings.php
  │   ├── save_food.php
  │   ├── save_workout.php
  │   ├── upload_photo.php
  │   └── get_dashboard_data.php
  ├── /assets
  │   ├── styles.css
  │   └── app.js
  └── /uploads (se crea automáticamente)
```

### 2. Crear la base de datos

1. Accede a **phpMyAdmin** desde el panel de Hostinger
2. Crea una nueva base de datos (ej: `mi_progreso_fit`)
3. Selecciona la base de datos y ve a la pestaña **SQL**
4. Copia y ejecuta el siguiente SQL (también está en `config.php`):

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    initial_weight DECIMAL(5,2) NULL,
    goal_weight DECIMAL(5,2) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE weights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    waist DECIMAL(5,2) NULL,
    hip DECIMAL(5,2) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date),
    INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE feelings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    energy TINYINT CHECK (energy BETWEEN 1 AND 5),
    hunger TINYINT CHECK (hunger BETWEEN 1 AND 5),
    adherence TINYINT CHECK (adherence BETWEEN 1 AND 5),
    water_glasses TINYINT DEFAULT 0,
    note TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date),
    INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    meal_type ENUM('breakfast','mid_morning','lunch','afternoon','dinner','other') NOT NULL,
    name VARCHAR(150) NOT NULL,
    calories INT NOT NULL,
    protein DECIMAL(5,1) NOT NULL,
    carbs DECIMAL(5,1) NOT NULL,
    fats DECIMAL(5,1) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    template VARCHAR(50) NOT NULL,
    completed TINYINT(1) DEFAULT 0,
    effort TINYINT NULL CHECK (effort BETWEEN 1 AND 10),
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    type ENUM('front','side','post_workout','meal') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. Configurar conexión a base de datos

Edita el archivo `config.php` y actualiza las credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tu_base_datos');  // Nombre de tu base de datos
define('DB_USER', 'tu_usuario');      // Usuario de MySQL
define('DB_PASS', 'tu_contraseña');   // Contraseña de MySQL
```

### 4. Verificar permisos

Asegúrate de que la carpeta `/uploads` tenga permisos de escritura (755 o 777).

## 🎯 Uso

1. Accede a tu dominio: `https://app.tupracticafitness.com`
2. Crea una cuenta nueva con tu email y contraseña
3. Completa tu peso inicial y meta (opcional)
4. Accede al dashboard y comienza a registrar tu progreso

## 📱 Funcionalidades del Dashboard

### Pestaña "Hoy"
- Registrar peso y medidas diarias
- Registrar cómo te sientes (energía, hambre, adherencia)
- Subir fotos de progreso

### Pestaña "Nutrición"
- Ver macros objetivo (1800 kcal, 150P/180C/50F)
- Registrar comidas con macronutrientes
- Visualizar progreso con anillos circulares

### Pestaña "Entrenamientos"
- Plan semanal predefinido (Piernas, HIIT, Espalda)
- Marcar entrenamientos completados
- Ver historial de sesiones

### Pestaña "Progreso"
- KPIs: peso actual, pérdida total, % hacia meta, entrenos/semana
- Gráfico de evolución de peso (últimos 30 días)
- Galería de fotos de progreso

## 🔒 Seguridad

- Contraseñas hasheadas con `password_hash()` de PHP
- Consultas preparadas (PDO) para prevenir SQL injection
- Validación de tipos de archivo en subida de imágenes
- Sesiones PHP para autenticación
- Límite de tamaño de archivos (3MB)

## 🌐 Multi-Cliente

Cada usuario tiene su propio dashboard con datos completamente aislados. El mismo dominio sirve a múltiples clientes, cada uno con:
- Su propio login
- Sus propios datos de progreso
- Sus propias fotos
- Sus propios registros

## 🚀 Preparado para IA

La estructura de datos está lista para integrar IA en el futuro:
- Endpoint `get_dashboard_data.php` devuelve JSON estructurado
- Datos organizados por secciones (user, today, history, kpis, photos)
- Fácil de consumir por servicios de IA para generar recomendaciones personalizadas

## 📞 Soporte

Para cualquier duda o problema, contacta al administrador del sistema.

---

**Desarrollado con ❤️ para entrenamientos personalizados**
