<?php declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnprocessableException extends HttpException {
    public function httpResponseCode(): int {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
