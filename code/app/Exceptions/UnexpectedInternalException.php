<?php declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnexpectedInternalException extends HttpException {
    public function httpResponseCode(): int {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
