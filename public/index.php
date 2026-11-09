<?php
require __DIR__ . '/../src/bootstrap.php';
header('Location: ' . (Auth::user($db) ? '/dashboard.php' : '/login.php'));
