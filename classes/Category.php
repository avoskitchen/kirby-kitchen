<?php

namespace AvosKitchen\Kitchen;

use Kirby\Toolkit\Obj;

class Category extends Obj
{
    public function __construct(string $id, array $data)
    {
        $this->id = $id;
        parent::__construct($data);
    }

    public function id(): string
    {
        return $this->id;
    }
}
