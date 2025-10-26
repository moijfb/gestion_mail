<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once BASE_PATH . '/src/controllers/Router.php';

// Démarrer la session
session_start();

// Initialiser le routeur
$router = new Router();
$router->handleRequest();
