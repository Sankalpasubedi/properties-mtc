<?php

namespace Controllers;

use Models\Property;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class LocationController
{
    private Property $property;
    private ViewController $view;
    public function __construct(Property $property, ViewController $view)
    {
        $this->property = $property;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->renderWithLayout($response, 'location/location.php', [
            'title' => 'Location Search',
        ]);
    }

    public function search(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $lat = (float)($params['lat'] ?? 0);
        $lng = (float)($params['lng'] ?? 0);
        $radius = (float)($params['radius'] ?? 10);
        if ($lat === 0.0 && $lng === 0.0) {
            $result = ['data' => [], 'total' => 0];
        } else {
            $properties = $this->property->searchByLocation($lat, $lng, $radius);
            $result = ['data' => $properties, 'total' => count($properties)];
        }
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
