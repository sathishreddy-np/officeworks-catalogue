<?php

// PHP start tag.

// This class is the Fake Store implementation of the catalogue promise.

namespace App\Catalogue;

// Thrown when the network connection to Fake Store fails.
use Illuminate\Http\Client\ConnectionException;
// Thrown when Fake Store answers with an error status such as 500.
use Illuminate\Http\Client\RequestException;
// A list object with helpful methods such as filter() and map().
use Illuminate\Support\Collection;
// Remembers data between page loads so we do not call Fake Store every time.
use Illuminate\Support\Facades\Cache;
// Laravel's HTTP client. The browser never uses this. Only PHP does.
use Illuminate\Support\Facades\Http;
// Text helpers such as lower() and contains().
use Illuminate\Support\Str;
// The parent type of every error, used by the retry check.
use Throwable;

// implements ProductCatalogue means this class must provide every method in that interface.
class FakeStoreCatalogue implements ProductCatalogue
{
    // After the first load in one request, keep the products here so we do not read the cache again.
    /** @var Collection<int, Product>|null */
    private ?Collection $products = null;

    /**
     * Home-page products, in the order written in config/catalogue.php.
     *
     * @return list<Product>
     */
    public function featured(): array
    {
        // keyBy makes a lookup table: product id => product object.
        $products = $this->products()->keyBy(fn (Product $product): int => $product->id);

        // Start an empty list. We append only the ids that still exist.
        $featured = [];

        // Walk the configured ids so the home page order matches the config file.
        foreach ($this->featuredIds() as $id) {
            $product = $products->get($id);

            // Skip an id the API no longer returns.
            if ($product instanceof Product) {
                $featured[] = $product;
            }
        }

        return $featured;
    }

    /**
     * Navigation links, sorted by category name.
     *
     * @return list<CategoryLink>
     */
    public function categories(): array
    {
        $links = [];

        // groupBy puts every product with the same slug into one bucket.
        foreach ($this->products()->groupBy(fn (Product $product): string => $product->categorySlug) as $slug => $group) {
            $first = $group->first();

            // Skip a bucket that has no real product or a non-text key.
            if (! $first instanceof Product || ! is_string($slug)) {
                continue;
            }

            // The first product supplies the display name. count() is the badge size.
            $links[] = new CategoryLink($first->category, $slug, $group->count());
        }

        // Sort A to Z by the visible name. <=> returns -1, 0, or 1.
        usort($links, fn (CategoryLink $left, CategoryLink $right): int => $left->name <=> $right->name);

        return $links;
    }

    // Build one category page, or return null when that slug has no products.
    public function category(string $slug): ?CategoryListing
    {
        // Keep only products whose URL slug matches the address the customer opened.
        $products = $this->products()
            ->filter(fn (Product $product): bool => $product->categorySlug === $slug)
            ->values();

        // An unknown slug is not an error. The controller turns null into a 404 page.
        if ($products->isEmpty()) {
            return null;
        }

        /** @var Product $first */
        $first = $products->first();
        $featuredIds = $this->featuredIds();
        $topRated = [];

        // Top rated means "this product is one of the five chosen on the home page".
        foreach ($products as $product) {
            if (in_array($product->id, $featuredIds, true)) {
                $topRated[] = $product;
            }
        }

        // If none of the five live in this category, use the single highest rating instead.
        if ($topRated === []) {
            $best = null;

            foreach ($products as $product) {
                if (! $best instanceof Product || $product->rating > $best->rating) {
                    $best = $product;
                }
            }

            if ($best instanceof Product) {
                $topRated[] = $best;
            }
        }

        // Collect the ids we already showed, so they are not repeated below.
        $topRatedIds = array_map(fn (Product $product): int => $product->id, $topRated);
        $additional = [];

        foreach ($products as $product) {
            if (! in_array($product->id, $topRatedIds, true)) {
                $additional[] = $product;
            }
        }

        return new CategoryListing(
            name: $first->category,
            slug: $slug,
            topRated: $topRated,
            additional: $additional,
        );
    }

    // Find one product by id. Return null when it is not in the catalogue.
    public function find(int $id): ?Product
    {
        $product = $this->products()->first(fn (Product $product): bool => $product->id === $id);

        return $product instanceof Product ? $product : null;
    }

    /**
     * Case-insensitive match against the title or the description.
     *
     * @return list<Product>
     */
    public function search(string $term): array
    {
        // trim removes spaces. lower makes "JACKET" and "jacket" the same search.
        $needle = Str::lower(trim($term));

        // An empty search is valid and returns nothing. The page asks the customer to type.
        if ($needle === '') {
            return [];
        }

        $matches = [];

        foreach ($this->products() as $product) {
            $title = Str::lower($product->title);
            $description = Str::lower($product->description);

            // contains() is true when the search text appears anywhere in the field.
            if (Str::contains($title, $needle) || Str::contains($description, $needle)) {
                $matches[] = $product;
            }
        }

        return $matches;
    }

    /**
     * Load every product once per request, using the cache when it is still valid.
     *
     * @return Collection<int, Product>
     */
    private function products(): Collection
    {
        // The property is already filled, so return it and do no more work.
        if ($this->products instanceof Collection) {
            return $this->products;
        }

        // Cache::get returns null on a miss, or whatever was stored last time.
        $cached = Cache::get($this->cacheKey());

        // A good cache entry is a JSON list. Anything else is thrown away and downloaded again.
        if (! is_array($cached) || ! array_is_list($cached)) {
            Cache::forget($this->cacheKey());
            $cached = $this->fetchPayload();
            Cache::put($this->cacheKey(), $cached, $this->ttl());
        }

        // Turn each raw row into a Product. Drop rows that fromApi rejected.
        $products = collect($cached)
            ->map(fn (mixed $row): ?Product => Product::fromApi($row))
            ->filter(fn (mixed $product): bool => $product instanceof Product)
            ->values();

        // Do not keep a cache entry that produced zero usable products.
        if ($products->isEmpty()) {
            Cache::forget($this->cacheKey());

            throw new CatalogueUnavailable('The product catalogue cache was unreadable.');
        }

        // Save on the object and return it. The next method in this request reuses it.
        return $this->products = $products;
    }

    /**
     * Download the product list from Fake Store.
     *
     * @return list<array<string, mixed>>
     */
    private function fetchPayload(): array
    {
        try {
            // Each arrow adds one setting, then get() sends the request.
            $response = Http::acceptJson()
                // Some hosts reject a request that has no name. This is ours.
                ->withUserAgent('OfficeworksCatalogue/1.0')
                // rtrim removes a trailing slash so we do not build "//products".
                ->baseUrl(rtrim((string) config('catalogue.base_url'), '/'))
                // Give up if the connection is not open within 3 seconds.
                ->connectTimeout(3)
                // Give up if the full answer takes longer than 8 seconds.
                ->timeout(8)
                // Wait 200ms, then 500ms, and only retry connection or server failures.
                ->retry([200, 500], 0, function (Throwable $exception): bool {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof RequestException
                            && ($exception->response->serverError() || $exception->response->status() === 429));
                })
                // throw() turns a 4xx or 5xx answer into an exception.
                ->throw()
                // GET /products is a read. It is safe to retry.
                ->get('/products');
        } catch (ConnectionException|RequestException $exception) {
            // Wrap the HTTP error in our own type so pages can show a retry state.
            throw new CatalogueUnavailable('The product catalogue could not be loaded.', previous: $exception);
        }

        // json() turns the response body into PHP arrays.
        $payload = $response->json();

        // We expect a list of products, not a single object and not a string.
        if (! is_array($payload) || ! array_is_list($payload)) {
            throw new CatalogueUnavailable('The product catalogue returned an unexpected payload.');
        }

        // Confirm at least one row can become a Product before we cache the list.
        $products = collect($payload)
            ->map(fn (mixed $row): ?Product => Product::fromApi($row))
            ->filter(fn (mixed $product): bool => $product instanceof Product);

        if ($products->isEmpty()) {
            throw new CatalogueUnavailable('The product catalogue returned no products.');
        }

        /** @var list<array<string, mixed>> $payload */
        return $payload;
    }

    /**
     * The five chosen ids from config, always as integers.
     *
     * @return list<int>
     */
    private function featuredIds(): array
    {
        $ids = config('catalogue.featured_ids', []);

        // A broken config value should not crash the page. Return no featured products.
        if (! is_array($ids)) {
            return [];
        }

        // intval(...) converts each entry. array_values rebuilds a 0, 1, 2... list.
        return array_values(array_map(intval(...), $ids));
    }

    // The cache name includes the API address, so a new URL does not reuse old products.
    private function cacheKey(): string
    {
        return 'catalogue.products.v2.'.md5((string) config('catalogue.base_url'));
    }

    // Never cache for 0 seconds. A missing setting falls back to 10 minutes.
    private function ttl(): int
    {
        return max(1, (int) config('catalogue.cache_seconds', 600));
    }
}
