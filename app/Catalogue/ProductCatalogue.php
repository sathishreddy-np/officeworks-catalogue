<?php

// PHP start tag.

// Folder address of this interface.
namespace App\Catalogue;

// An interface is a promise: any catalogue class must offer these methods.
// Pages depend on this promise, not on Fake Store specifically.
interface ProductCatalogue
{
    /**
     * The five products chosen for the home page.
     *
     * @return list<Product>
     */
    // featured() returns a plain list of Product objects, in the chosen order.
    public function featured(): array;

    /**
     * One link for every category, used by the red navigation bar.
     *
     * @return list<CategoryLink>
     */
    // categories() returns the names and URL slugs shown in the header.
    public function categories(): array;

    // category() returns one category page, or null when the slug does not exist.
    public function category(string $slug): ?CategoryListing;

    // find() returns one product by its numeric id, or null when that id is missing.
    public function find(int $id): ?Product;

    /**
     * Products whose title or description contains the search words.
     *
     * @return list<Product>
     */
    // search() returns matching products. An empty term returns an empty list.
    public function search(string $term): array;
}
