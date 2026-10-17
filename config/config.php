<?php
// Konfigurasi koneksi database (XAMPP default)
return [
    'driver'   => getenv('SIMADA_DRIVER') ?: 'mysql',
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'database' => 'simada',
    'username' => 'root',
    'password' => '',
    'sqlite'   => __DIR__ . '/../database/simada.sqlite', // dipakai hanya untuk uji tanpa MySQL
];
