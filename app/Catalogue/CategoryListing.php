<?php

// PHP start tag.

// Folder address of the category-page data object.

namespace App\Catalogue;

// One category page: its name, its URL slug, the top-rated products, and the rest.
final readonly class CategoryListing
{
    /**
     * @param  list<Product>  $topRated  Products shown in the "Top rated" section.
     * @param  list<Product>  $additional  Every other product in the same category.
     */
    public function __construct(
        // Display name, for example "electronics".
        public string $name,
        // URL slug, for example "electronics".
        public string $slug,
        // The highlighted products for this category.
        public array $topRated,
        // The remaining products, shown under "More in this category".
        public array $additional,
    ) {}
}
