<?php

// PHP start tag.

// Folder address of this small data object.
namespace App\Catalogue;

// readonly means the three values cannot be changed after the object is created.
// final means nobody can extend this class and change its behaviour.
final readonly class CategoryLink
{
    // The constructor runs when we write `new CategoryLink(...)`.
    public function __construct(
        // The human name, for example "men's clothing".
        public string $name,
        // The URL piece, for example "mens-clothing".
        public string $slug,
        // How many products sit in this category.
        public int $count,
    ) {}

    /**
     * The shape sent to React. Inertia can only send arrays, not PHP objects.
     *
     * @return array{name: string, slug: string, count: int}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'count' => $this->count,
        ];
    }
}
