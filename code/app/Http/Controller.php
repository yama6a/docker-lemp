<?php declare(strict_types=1);

namespace App\Http;

interface Controller
{
    /** @return Route[] */
    public function routes(): array;
}
