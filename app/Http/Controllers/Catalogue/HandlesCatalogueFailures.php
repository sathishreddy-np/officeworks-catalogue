<?php

// PHP start tag.

// Shared helper used by the catalogue page controllers.
namespace App\Http\Controllers\Catalogue;

// Our "the product API is down" error.
use App\Catalogue\CatalogueUnavailable;
// Writes a line to storage/logs/laravel.log.
use Illuminate\Support\Facades\Log;
// Builds the page data that Inertia sends to React.
use Inertia\Inertia;
// The type of object Inertia::render returns.
use Inertia\Response;

// A trait is a bundle of methods a class can pull in with the word "use".
trait HandlesCatalogueFailures
{
    /**
     * Show the same page shape, but with an empty catalogue and a retry flag.
     *
     * @param  array<string, mixed>  $empty  Page-specific empty values, such as products => [].
     */
    protected function unavailablePage(string $component, array $empty, CatalogueUnavailable $exception): Response
    {
        // Keep a short log line so a developer can see why the retry box appeared.
        Log::warning('Product catalogue is unavailable.', [
            'message' => $exception->getMessage(),
        ]);

        return Inertia::render($component, [
            // The header has no category links when we could not load them.
            'categories' => [],
            // React reads this flag and shows the "try again" box.
            'catalogueUnavailable' => true,
            // ... copies the extra empty values into this same array.
            ...$empty,
        ]);
    }
}
