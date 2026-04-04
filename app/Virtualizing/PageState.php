<?php

namespace App\Virtualizing;

use JsonSerializable;

class PageState implements JsonSerializable
{
    public function __construct(
        public readonly array $snapshot,
        public readonly array $effects,
        public readonly array $contextData,
    ) {}

    public function toJson(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return static::fromArray($data);
    }

    public function toArray(): array
    {
        return [
            'snapshot' => $this->snapshot,
            'effects' => $this->effects,
            'contextData' => $this->contextData,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            snapshot: $data['snapshot'],
            effects: $data['effects'],
            contextData: $data['contextData'],
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
