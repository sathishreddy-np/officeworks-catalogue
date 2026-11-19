<?php

// PHP start tag.

// Folder address of the Product class.

namespace App\Catalogue;

// Str is Laravel's text helper. We use it to make a safe URL from a category name.
use Illuminate\Support\Str;

// One product from the catalogue. readonly means its fields cannot change later.
final readonly class Product
{
    // These public fields are filled once, when the object is created.
    public function __construct(
        public int $id, // The number Fake Store uses, for example 11.
        public string $title, // The product name shown on the card.
        public string $description, // The longer text on the product page.
        public float $price, // The shelf price. We show it as Australian dollars.
        public string $category, // The original category name, which may contain an apostrophe.
        public string $categorySlug, // The URL version of that name, for example mens-clothing.
        public string $image, // A web address of the product photo.
        public float $rating, // The average star score, for example 4.8.
        public int $ratingCount, // How many people left that score.
    ) {}

    // Build a Product from one JSON row. Return null when the row is missing required fields.
    public static function fromApi(mixed $row): ?self
    {
        // The API should send an array. Anything else cannot be a product.
        if (! is_array($row)) {
            return null;
        }

        // ?? means "use null when this key is missing".
        $id = $row['id'] ?? null;
        $title = $row['title'] ?? null;
        $description = $row['description'] ?? null;
        $price = $row['price'] ?? null;
        $category = $row['category'] ?? null;
        $image = $row['image'] ?? null;
        // Rating is nested: rating.rate and rating.count. Guard the nested array first.
        $rating = is_array($row['rating'] ?? null) ? ($row['rating']['rate'] ?? null) : null;
        $ratingCount = is_array($row['rating'] ?? null) ? ($row['rating']['count'] ?? 0) : 0;

        // Reject the row when any required value has the wrong type or is blank.
        if (! is_numeric($id) || ! is_string($title) || $title === '' || ! is_string($description) || ! is_numeric($price) || ! is_string($category) || $category === '' || ! is_string($image) || $image === '' || ! is_numeric($rating) || ! is_numeric($ratingCount)) {
            return null;
        }

        // Build the object. (int) and (float) turn numeric strings into real numbers.
        return new self(
            id: (int) $id,
            title: $title,
            description: $description,
            price: (float) $price,
            category: $category,
            // "men's clothing" becomes "mens-clothing", which is safe inside a URL.
            categorySlug: Str::slug($category),
            image: $image,
            rating: (float) $rating,
            ratingCount: (int) $ratingCount,
        );
    }

    /**
     * Turn this object into an array React can read.
     *
     * @return array{id: int, title: string, description: string, price: float, priceLabel: string, category: string, categorySlug: string, image: string, rating: float, ratingCount: int}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            // A$ is the Australian dollar sign. number_format adds two decimal places.
            'priceLabel' => 'A$'.number_format($this->price, 2),
            'category' => $this->category,
            'categorySlug' => $this->categorySlug,
            'image' => $this->image,
            'rating' => $this->rating,
            'ratingCount' => $this->ratingCount,
        ];
    }
}
