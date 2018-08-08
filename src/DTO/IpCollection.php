<?php

namespace GlebecV\DTO;

use GlebecV\ItemInterface;

class IpCollection
{
    private $elements = [];

    public function addElement(ItemInterface $element): void
    {
        $this->elements[] = $element;
    }

    public function elements(): array
    {
        return $this->elements;
    }
}