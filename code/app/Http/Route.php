<?php declare(strict_types=1);

namespace App\Http;

use App\Exceptions\UnexpectedInternalException;
use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Route
{
    private string $method;
    private string $path;
    private Closure $handlerFn;

    /** @throws UnexpectedInternalException */
    private function __construct(string $method, string $path, Closure $handlerFn) {
        $this->method    = $method;
        $this->path      = static::sanitizePath($path);
        $this->handlerFn = $handlerFn;
    }

    public function isMatch(Request $request): bool {
        // ToDo: Don't rely on ?path query param, but do proper path parsing
        return ($request->get('path') === $this->path) and ($request->getMethod() === $this->method);
    }

    public function handle(Request $request): SymfonyResponse {
        return ($this->handlerFn)($request);
    }

    public static function GET(string $path, Closure $handler): Route {
        return new static(Request::METHOD_GET, $path, $handler);
    }

    public static function POST(string $path, Closure $handler): Route {
        return new static(Request::METHOD_POST, $path, $handler);
    }

    public static function PATCH(string $path, Closure $handler): Route {
        return new static(Request::METHOD_PATCH, $path, $handler);
    }

    public static function PUT(string $path, Closure $handler): Route {
        return new static(Request::METHOD_PUT, $path, $handler);
    }

    public static function DELETE(string $path, Closure $handler): Route {
        return new static(Request::METHOD_DELETE, $path, $handler);
    }

    /**
     * @param string $path
     * @return string
     * @throws UnexpectedInternalException
     */
    private static function sanitizePath(string $path): string {
        // ensure path starts and ends with a slash and there are no double-slashes.
        $path = preg_replace('/\/+/', '/', '/' . $path . '/');
        if ($path === false or $path === null) {
            throw new UnexpectedInternalException("Failed to replace double slashes with single slashes in path: $path");
        }
        return $path;
    }
}
