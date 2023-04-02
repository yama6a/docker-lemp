<?php declare(strict_types=1);

namespace App\Exceptions;

abstract class HttpException extends \Exception {
    public abstract function httpResponseCode(): int;
}
