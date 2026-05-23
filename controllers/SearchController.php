<?php

namespace Controllers;

use Models\Property;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class SearchController
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
        return $this->view->renderWithLayout($response, 'search/search.php', [
            'title' => 'Property Search',
        ]);
    }

    public function search(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $filters = [];
        if (!empty($params['search'])) {
            $filters['search'] = $params['search'];
        }
        if (isset($params['bedrooms']) && $params['bedrooms'] !== '') {
            $filters['bedrooms'] = (int)$params['bedrooms'];
        }
        if (isset($params['bathrooms']) && $params['bathrooms'] !== '') {
            $filters['bathrooms'] = (int)$params['bathrooms'];
        }
        if (!empty($params['price_min'])) {
            $filters['price_min'] = (float)$params['price_min'];
        }
        if (!empty($params['price_max'])) {
            $filters['price_max'] = (float)$params['price_max'];
        }
        if (!empty($params['type'])) {
            $filters['type'] = $params['type'];
        }
        $page = max(1, (int)($params['page'] ?? 1));
        $order = empty($params['search']) ? 'DESC' : 'ASC';
        $result = $this->property->all($filters, 'id', $order, $page, 15);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function detail(Request $request, Response $response): Response
    {
        $route = \Slim\Routing\RouteContext::fromRequest($request)->getRoute();
        $id = (int)$route->getArgument('id');
        $property = $this->property->find($id);
        if (!$property) {
            $response->getBody()->write('Property not found');
            return $response->withStatus(404);
        }
        return $this->view->renderWithLayout($response, 'property/detail.php', [
            'title' => $property['displayable_address'],
            'property' => $property,
        ]);
    }

    public function autocomplete(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query = trim($params['q'] ?? '');
        if (strlen($query) < 2) {
            $response->getBody()->write(json_encode([]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $results = $this->property->autocomplete($query);
        $response->getBody()->write(json_encode($results));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
