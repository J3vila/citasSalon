<?php
namespace App\controllers;

use Slim\Routing\RouteCollectorProxy;


$app->group('/api', function(RouteCollectorProxy $api){

  $api->group('/citas_salon',function(RouteCollectorProxy $endpoint){

    $endpoint->get('[/{id}]', CitasSalon::class . ':read');
    $endpoint->post('', CitasSalon::class . ':create');
    $endpoint->put('/{id}', CitasSalon::class . ':update');
    $endpoint->delete('/{id}', CitasSalon::class . ':delete');

  });

});
