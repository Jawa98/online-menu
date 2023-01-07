<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromArray;


class CategoryExport implements FromArray
{
    protected $categories;

    public function __construct(array $categories)
    {
        $this->categories = $categories;
    }

    public function array(): array
    {
        return $this->categories;
    }
}
