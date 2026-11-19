// These types describe the data Laravel sends to the React pages.
// TypeScript uses them to catch mistakes, such as treating a price as text.

// One product card or product page.
export type CatalogueProduct = {
    id: number; // Fake Store id, used in the URL /products/11.
    title: string; // Name shown on the card.
    description: string; // Longer text on the product page.
    price: number; // The raw number, for example 109.
    priceLabel: string; // The same price already formatted, for example A$109.00.
    category: string; // Display name, which may include an apostrophe.
    categorySlug: string; // URL piece, for example mens-clothing.
    image: string; // Photo address. The browser loads this image directly.
    rating: number; // Average score, for example 4.8.
    ratingCount: number; // How many ratings make up that average.
};

// One item in the red category bar.
export type CatalogueCategory = {
    name: string;
    slug: string;
    count: number; // How many products are in the category.
};
