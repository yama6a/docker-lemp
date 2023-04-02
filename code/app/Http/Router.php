<?php declare(strict_types=1);

namespace App\Http;

use App\Exceptions\MethodNotAllowedHttpException;
use Symfony\Component\HttpFoundation\Request;

class Router
{
    /** @var array|Route[] $routes */
    private array $routes = [];

    /** @param Controller[] $controllers */
    public function __construct(...$controllers) {
        foreach ($controllers as $controller) {
            $this->routes = array_merge($this->routes, $controller->routes());
        }
    }

    public function handle(Request $request): \Symfony\Component\HttpFoundation\Response {
        foreach ($this->routes as $route) {
            if ($route->isMatch($request)) {
                try {
                    return $route->handle($request);
                } catch (\Exception $e) {
                    return ResponseFactory::error($e);
                }
            }
        }
        return ResponseFactory::error(new MethodNotAllowedHttpException("Path or Method not found."));
    }
}
