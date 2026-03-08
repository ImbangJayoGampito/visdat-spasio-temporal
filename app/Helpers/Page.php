<?php

namespace App\Helpers;

class Page
{
    public string $title;
    public string $description;
    public string $author;
    public string $route;
    public string $category;

    public function __construct(
        string $title,
        string $description,
        string $author,
        string $route,
        string $category,
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->author = $author;
        $this->route = $route;
        $this->category = $category;
    }
}
