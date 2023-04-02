<?php declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class BadRequestHttpException extends HttpException {
    public function httpResponseCode(): int {
        return Response::HTTP_BAD_REQUEST;
    }
}
