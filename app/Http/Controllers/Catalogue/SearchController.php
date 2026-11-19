<?php

// PHP start tag.

// This controller answers GET /search?q=jacket.
namespace App\Http\Controllers\Catalogue;

use App\Catalogue\CatalogueUnavailable;
use App\Catalogue\CategoryLink;
use App\Catalogue\Product;
use App\Catalogue\ProductCatalogue;
use App\Http\Controllers\Controller;
// Request holds the query string the browser sent.
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    use HandlesCatalogueFailures;

    public function index(Request $request, ProductCatalogue $catalogue): Response
    {
        // q may be missing. It must be text, and it cannot be longer than 100 characters.
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        // Missing q becomes an empty string. trim removes spaces the customer typed by accident.
        $query = trim((string) ($validated['q'] ?? ''));

        try {
            return Inertia::render('catalogue/search', [
                'categories' => array_map(
                    fn (CategoryLink $category): array => $category->toArray(),
                    $catalogue->categories(),
                ),
                'catalogueUnavailable' => false,
                // Echo the term back so the search box stays filled.
                'query' => $query,
                // Matching happens here, on the server, not in the browser.
                'products' => array_map(
                    fn (Product $product): array => $product->toArray(),
                    $catalogue->search($query),
                ),
            ]);
        } catch (CatalogueUnavailable $exception) {
            return $this->unavailablePage('catalogue/search', [
                'query' => $query,
                'products' => [],
            ], $exception);
        }
    }
}
