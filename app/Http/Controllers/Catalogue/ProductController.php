<?php

// PHP start tag.

// This controller answers GET /products/11.

namespace App\Http\Controllers\Catalogue;

use App\Catalogue\CatalogueUnavailable;
use App\Catalogue\CategoryLink;
use App\Catalogue\ProductCatalogue;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use HandlesCatalogueFailures;

    // $product is the number from the URL. The route only allows digits.
    public function show(ProductCatalogue $catalogue, int $product): Response
    {
        try {
            $record = $catalogue->find($product);

            // A number that is not in the catalogue is a missing page.
            abort_if($record === null, 404);

            return Inertia::render('catalogue/product', [
                'categories' => array_map(
                    fn (CategoryLink $category): array => $category->toArray(),
                    $catalogue->categories(),
                ),
                'catalogueUnavailable' => false,
                // One product array, including the A$ price label.
                'product' => $record->toArray(),
            ]);
        } catch (CatalogueUnavailable $exception) {
            return $this->unavailablePage('catalogue/product', [
                'product' => null,
            ], $exception);
        }
    }
}
