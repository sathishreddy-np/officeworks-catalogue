<?php

// PHP start tag.

// This controller answers GET /, the home page.
namespace App\Http\Controllers\Catalogue;

use App\Catalogue\CatalogueUnavailable;
use App\Catalogue\CategoryLink;
use App\Catalogue\Product;
// The interface, not the Fake Store class. Laravel injects the real class for us.
use App\Catalogue\ProductCatalogue;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    // Pull in unavailablePage() from the shared trait.
    use HandlesCatalogueFailures;

    // index is the action for the home route. The catalogue is filled in by Laravel.
    public function index(ProductCatalogue $catalogue): Response
    {
        try {
            // catalogue/home is resources/js/pages/catalogue/home.tsx.
            return Inertia::render('catalogue/home', [
                // Turn each category object into an array React can read.
                'categories' => array_map(
                    fn (CategoryLink $category): array => $category->toArray(),
                    $catalogue->categories(),
                ),
                // false means the data loaded. The page shows the product grid.
                'catalogueUnavailable' => false,
                // The five chosen products, already in config order.
                'products' => array_map(
                    fn (Product $product): array => $product->toArray(),
                    $catalogue->featured(),
                ),
            ]);
        } catch (CatalogueUnavailable $exception) {
            // The API is down and the cache is empty. Show the retry box, not a 500 page.
            return $this->unavailablePage('catalogue/home', [
                'products' => [],
            ], $exception);
        }
    }
}
