<?php
use Slim\Views\PhpRenderer;
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register service provider with the container
$container = new \Slim\Container;
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

// Add middleware to the application
$app = new \Slim\App($container);
$app->add(new \Slim\HttpCache\Cache('public', 86400));


// Create app
$app = new \Slim\App();

// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    // $view = new \Slim\Views\Twig('../templates', [
    //     'cache' => '../templates/cache'
    // ]);
    $view = new \Slim\Views\Twig('../templates', array('cache' => false));
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    return $view;
};


// Register routes
require __DIR__ . '/../src/routes.php';

// Create your application routes...

// Run app
$app->run();
