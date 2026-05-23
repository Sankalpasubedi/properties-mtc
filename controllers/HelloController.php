<?php

namespace Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Class HelloController
 *
 * @package Controllers
 */
class HelloController
{
    /**
     * @var \Slim\Container Stores the container for dependency purposes.
     */
    protected $container;
    private ViewController $view;


    /**
     * Store the container during class construction.
     *
     * @param \Slim\Container $container
     */
    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
        $this->view = $container->get('view');
    }

    /**
     * Output a hello message to a specified name.
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Array    $args
     * @return mixed
     */
    public function index(Request $request, Response $response, $args)
    {
        return $this->view->renderWithLayout($response, 'search/search.php', [
            'title' => 'Property Search',
        ]);
    }
}