<?php
use Slim\Views\PhpRenderer;
use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    return $this->view->render($response, 'index.twig', [
        'name' => $args['name']
    ]);
})->setName('index');

$app->get('/psvita/[{page}]', function (Request $request, Response $response, array $args) {
    if(empty($args['page'])){
      $page = 1;
    }else{
      $page = $args['page'];
    }
    $apiResults = json_cached_api_results($page);
    return $this->view->render($response, 'results.twig', ['games' => $apiResults, 'page' => $page]);
})->setName('results');
