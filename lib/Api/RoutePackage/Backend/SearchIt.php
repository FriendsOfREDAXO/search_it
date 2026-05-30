<?php

namespace FriendsOfRedaxo\SearchIt\Api\RoutePackage\Backend;

use FriendsOfRedaxo\Api\Auth\BackendUser;
use FriendsOfRedaxo\Api\RouteCollection;
use FriendsOfRedaxo\SearchIt\Api\RoutePackage\SearchIt as TokenSearchIt;

use function strlen;

class SearchIt extends TokenSearchIt
{
    public function loadRoutes()
    {
        $routes = RouteCollection::getRoutes();

        foreach ($routes as $route) {
            if ('search_it/' === substr($route['scope'], 0, strlen('search_it/'))) {
                $scope = 'backend/' . $route['scope'];
                $newRoute = clone $route['route'];
                $newRoute->setPath('backend/' . ltrim($newRoute->getPath(), '/'));

                RouteCollection::registerRoute(
                    $scope,
                    $newRoute,
                    $route['description'],
                    $route['responses'],
                    new BackendUser(),
                    ['backend']
                );
            }
        }
    }
}
