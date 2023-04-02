<?php declare(strict_types=1);

namespace App\Pkg\Animal;

use JetBrains\PhpStorm\ArrayShape;

class AnimalModel implements \JsonSerializable
{
    public function __construct(
        private string $id,
        private string $species,
        private string $name,
        private string $color,
        private bool   $hasFur
    ) {
    }

    public static function fromAssocArray(array $data): AnimalModel {
        return new self($data['id'] ?? "", $data['species'] ?? "", $data['name'] ?? "", $data['color'] ?? "", $data['hasFur'] ?? false);
    }

    public function hasFur(): bool { return $this->hasFur; }

    public function getId(): string { return $this->id; }

    public function getSpecies(): string { return $this->species; }

    public function getName(): string { return $this->name; }

    public function getColor(): string { return $this->color; }

    #[ArrayShape(['id' => "string", 'species' => "string", 'name' => "string", 'color' => "string", 'hasFur' => "bool"])]
    public function jsonSerialize(): array {
        return [
            'id'      => $this->id,
            'species' => $this->species,
            'name'    => $this->name,
            'color'   => $this->color,
            'hasFur'  => $this->hasFur,
        ];
    }
}

