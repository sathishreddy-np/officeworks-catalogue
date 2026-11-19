<?php

// PHP start tag.

// This controller answers GET /categories/{category}.
namespace App\Http\Controllers\Catalogue;

use App\Catalogue\CatalogueUnavailable;
use App\Catalogue\CategoryLink;
use App\Catalogue\Product;
use App\Catalogue\ProductCatalogue;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use HandlesCatalogueFailures;

    // $category is the slug from the URL, for example "mens-clothing".
    public function show(ProductCatalogue $catalogue, string $category): Response
    {
        try {
            $listing = $catalogue->category($category);

            // null means that slug is not a real category. 404 is "page not found".
            abort_if($listing === null, 404);

            return Inertia::render('catalogue/category', [
                'categories' => array_map(
                    fn (CategoryLink $link): array => $link->toArray(),
                    $catalogue->categories(),
                ),
                'catalogueUnavailable' => false,
                // The heading needs the display name and the slug of the current category.
                'category' => [
                    'name' => $listing->name,
                    'slug' => $listing->slug,
                ],
                'topRated' => array_map(
                    fn (Product $product): array => $product->toArray(),
                    $listing->topRated,
                ),
                'additional' => array_map(
                    fn (Product $product): array => $product->toArray(),
                    $listing->additional,
                ),
            ]);
        } catch (CatalogueUnavailable $exception) {
            return $this->unavailablePage('catalogue/category', [
                'category' => null,
                'topRated' => [],
                'additional' => [],
            ], $exception);
        }
    }
}
