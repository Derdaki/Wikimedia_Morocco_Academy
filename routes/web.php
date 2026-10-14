<?php

declare(strict_types=1);

use App\Controllers\Admin\ContactController;
use App\Controllers\Admin\ContentController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\EventController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\UserController;
use App\Controllers\AuthController;
use App\Controllers\PublicController;
use App\Core\Router;

$router = new Router();

$router->get('/', [PublicController::class, 'home']);
$router->get('/sitemap.xml', [PublicController::class, 'sitemap']);
$router->get('/lang/{locale}', [PublicController::class, 'language']);
$router->get('/evenements', [PublicController::class, 'events']);
$router->get('/evenements/{slug}', [PublicController::class, 'eventShow']);
$router->get('/nos-projets', [PublicController::class, 'projects']);
$router->get('/nos-projets/{slug}', [PublicController::class, 'projectShow']);
$router->get('/actualites', [PublicController::class, 'news']);
$router->get('/actualites/{slug}', [PublicController::class, 'articleShow']);
$router->get('/a-propos', [PublicController::class, 'about']);
$router->get('/credits', [PublicController::class, 'credits']);
$router->get('/notre-equipe', [PublicController::class, 'team']);
$router->get('/ressources', [PublicController::class, 'resources']);
$router->get('/contact', [PublicController::class, 'contact']);
$router->post('/contact', [PublicController::class, 'contactSubmit']);
$router->get('/rejoindre-la-communaute', [PublicController::class, 'join']);

$router->get('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/login', [AuthController::class, 'authenticate']);
$router->post('/admin/logout', [AuthController::class, 'logout']);
$router->get('/admin', [DashboardController::class, 'index']);

$router->get('/admin/events', [EventController::class, 'index']);
$router->get('/admin/events/create', [EventController::class, 'create']);
$router->post('/admin/events', [EventController::class, 'store']);
$router->get('/admin/events/{id}/edit', [EventController::class, 'edit']);
$router->post('/admin/events/{id}', [EventController::class, 'update']);
$router->post('/admin/events/{id}/archive', [EventController::class, 'archive']);
$router->post('/admin/events/{id}/delete', [EventController::class, 'delete']);

$router->get('/admin/media', [MediaController::class, 'index']);
$router->post('/admin/media', [MediaController::class, 'store']);
$router->post('/admin/media/{id}/delete', [MediaController::class, 'delete']);
$router->get('/admin/contact-messages', [ContactController::class, 'index']);
$router->post('/admin/contact-messages/{id}/read', [ContactController::class, 'markRead']);
$router->post('/admin/contact-messages/{id}/delete', [ContactController::class, 'delete']);
$router->get('/admin/users', [UserController::class, 'index']);
$router->post('/admin/users', [UserController::class, 'store']);
$router->post('/admin/users/{id}/toggle', [UserController::class, 'toggle']);
$router->get('/admin/settings', [SettingsController::class, 'edit']);
$router->post('/admin/settings', [SettingsController::class, 'update']);

$router->get('/admin/{type}', [ContentController::class, 'index']);
$router->get('/admin/{type}/create', [ContentController::class, 'create']);
$router->post('/admin/{type}', [ContentController::class, 'store']);
$router->get('/admin/{type}/{id}/edit', [ContentController::class, 'edit']);
$router->post('/admin/{type}/{id}', [ContentController::class, 'update']);
$router->post('/admin/{type}/{id}/delete', [ContentController::class, 'delete']);

return $router;
