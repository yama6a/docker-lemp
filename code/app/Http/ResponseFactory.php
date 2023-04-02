<?php /** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class ResponseFactory
{
    public static function make($data, $code = 200): JsonResponse {
        if ($data instanceof Throwable) {
            return static::error($data);
        }

        $payload = $data;
        if($payload instanceof \JsonSerializable) {
            $payload = $payload->jsonSerialize();
        }

        return new JsonResponse(['code' => $code, 'error' => null, 'payload' => $payload], $code);
    }

    public static function error(Throwable $throwable): JsonResponse {
        $responseCode = ($throwable instanceof HttpException) ? $throwable->httpResponseCode() : 500;

        /** @var string[] $previousErrors */
        $previousErrors = [];
        $previous       = $throwable;
        while ($previous = $previous->getPrevious()) {
            $previousErrors[] = ["exception" => get_class($previous), "message" => $previous->getMessage()];
        }

        // print error to stderr
        $stdOutErrorMsg = sprintf("[http %d] (%s): %s {previous: %s}",
            $responseCode,
            get_class($throwable),
            $throwable->getMessage(),
            implode("; ", $previousErrors) ?: "-none-");
        fwrite(fopen('php://stderr', 'w'), $stdOutErrorMsg . PHP_EOL);

        return new JsonResponse([
            'code'    => $responseCode,
            'error'   => [
                "exception"      => get_class($throwable),
                "message"        => $throwable->getMessage(),
                'previousErrors' => $previousErrors,
                "trace"          => $responseCode >= 500 ? explode("\n", $throwable->getTraceAsString()) : null,
            ],
            'payload' => null,
        ], $responseCode);
    }
}
