<?php

namespace Framework;

use App\Controllers\ErrorController;
use Framework\Middleware\Authorize;

class Router
{
  protected $routes = [];

  /**
   * Add a new route to the routes array
   *
   * @param string $method
   * @param string $uri
   * @param string $controller
   * @param array $middleware
   * @return void
   */
  public function registerRoute($method, $uri, $action, $middleware = [])
  {
    // Extract controller & method from $action
    list($controller, $controllerMethod) = explode('@', $action);

    $this->routes[] = [
      'method' => $method,
      'uri' => $uri,
      'controller' => $controller,
      'controllerMethod' => $controllerMethod,
      'middleware' => $middleware
    ];
  }

  /**
   * Add a GET route
   * 
   * @param string $uri
   * @param string $controller
   * @param array $middleware
   * @return void
   */
  public function get($uri, $controller, $middleware = [])
  {
    $this->registerRoute('GET', $uri, $controller, $middleware);
  }

  /**
   * Add a POST route
   * 
   * @param string $uri
   * @param string $controller
   * @param array $middleware
   * @return void
   */
  public function post($uri, $controller, $middleware = [])
  {
    $this->registerRoute('POST', $uri, $controller, $middleware);
  }

  /**
   * Add a PUT route
   * 
   * @param string $uri
   * @param string $controller
   * @param array $middleware
   * @return void
   */
  public function put($uri, $controller, $middleware = [])
  {
    $this->registerRoute('PUT', $uri, $controller, $middleware);
  }

  /**
   * Add a DELETE route
   * 
   * @param string $uri
   * @param string $controller
   * @param array $middleware
   * @return void
   */
  public function delete($uri, $controller, $middleware = [])
  {
    $this->registerRoute('DELETE', $uri, $controller, $middleware);
  }

  /**
   * Route the request
   * 
   * @param string $uri
   * @param string $method
   * @return void
   */
  public function route($uri)
  {
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Check for _method input
    if ($requestMethod === 'POST' && isset($_POST['_method'])) {
      // Override the request method with the value of _method
      $requestMethod = strtoupper($_POST['_method']);
    }

    foreach ($this->routes as $route) {
      // Split the current URI into segments
      $uriSegments = explode('/', trim($uri, '/'));

      // Split the route URIs into segments
      $routeSegments = explode('/', trim($route['uri'], '/'));

      $match = true;

      // Check if number of segments match, along with current request method
      if (count($uriSegments) === count($routeSegments) && strtoupper($route['method'] === $requestMethod)) {
        $params = [];
        $match = true;

        for ($i = 0; $i < count($uriSegments); $i++) {
          // If the uri's do not match and there is no param {id}
          if ($routeSegments[$i] !== $uriSegments[$i] && !preg_match('/\{(.+?)\}/', $routeSegments[$i])) {
            $match = false;
            break;
          }

          // Evaluate the param and add to the params array the key, value
          if (preg_match('/\{(.+?)\}/', $routeSegments[$i], $matches)) {
            $params[$matches[1]] = $uriSegments[$i];
          }
        }

        if ($match) {
          // Extract middleware (determine authorization & redirect accordingly)
          foreach ($route['middleware'] as $middleware) {
            (new Authorize())->handle($middleware);
          }

          // Extract controller and controller method
          $controller = 'App\\Controllers\\' . $route['controller'];
          $controllerMethod = $route['controllerMethod'];

          // Instantiate the controller and call the method
          $controllerInstance = new $controller();
          $controllerInstance->$controllerMethod($params);

          return;
        }
      }
    }

    // If route or method not found
    ErrorController::notFound();
  }
}


/**
 * The following simple router was
 * refactored into a class method
 * see above class Router
 */
// $routes = require basePath('routes.php');

// if (array_key_exists($uri, $routes)) {
//   require(basePath($routes[$uri]));
// } else {
//   // Set http response code to 404
//   http_response_code(404);
//   require(basePath($routes['404']));
// }
