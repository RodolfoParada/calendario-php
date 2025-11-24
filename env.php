<?php
// env.php

// Define las constantes de conexión a tu base de datos

// Host (Servidor) donde se encuentra la base de datos (Ej: localhost)
define('DB_HOST', 'localhost');

// Puerto (El predeterminado de MySQL es 3306)
define('DB_PORT', '3306');

// Nombre de la base de datos (debe existir en tu servidor MySQL)
define('DB_NAME', 'calendario'); // <-- ¡CAMBIAR!

// Usuario de la base de datos (Ej: root)
define('DB_USER', 'root'); // <-- ¡CAMBIAR!

// Contraseña del usuario de la base de datos (vacío si no tienes contraseña)
define('DB_PASS', 'admin'); // <-- ¡CAMBIAR!

// Codificación de caracteres (utf8mb4 es recomendado)
define('DB_CHARSET', 'utf8mb4');