<?php declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class MethodNotAllowedHttpException extends HttpException {
    public function httpResponseCode(): int {
        return Response::HTTP_METHOD_NOT_ALLOWED;
    }
}
