<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use App\Meteo\HelpController;
use App\Meteo\JsonController;

require __DIR__ . '/../vendor/autoload.php';

// .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
$app->get('/', [new HelpController, 'showStartPage']);

$app->group('/api/1.0/json', function (RouteCollectorProxy $group) {
    $group->get('/single/{type}', [new JsonController, 'single']);
    $group->get('/multiple', [new JsonController, 'multiple']);
});

$app->run();