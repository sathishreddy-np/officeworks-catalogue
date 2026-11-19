import { Head } from '@inertiajs/react';
import ProductGrid from '@/components/catalogue/product-grid';
import UnavailableNotice from '@/components/catalogue/unavailable-notice';
import type { CatalogueCategory, CatalogueProduct } from '@/types/catalogue';

// query is the text after ?q= in the address. products were already filtered by PHP.
export default function Search({
    catalogueUnavailable,
    query,
    products,
}: {
    categories: CatalogueCategory[];
    catalogueUnavailable: boolean;
    query: string;
    products: CatalogueProduct[];
}) {
    return (
        <>
            <Head title={query === '' ? 'Search' : `Search: ${query}`} />
            {catalogueUnavailable ? (
                <UnavailableNotice />
            ) : (
                <>
                    <h1 className="text-3xl font-black tracking-tight sm:text-4xl">
                        Search
                    </h1>
                    {query === '' ? (
                        <p className="mt-3 text-[#4b4b4b]">
                            Enter a search term and choose Search. Matching
                            uses the product title and description.
                        </p>
                    ) : (
                        <p className="mt-3 text-[#4b4b4b]">
                            {/* One result says "product". More than one says "products". */}
                            {products.length === 0
                                ? `No products match “${query}”.`
                                : `${products.length} product${products.length === 1 ? '' : 's'} match “${query}”.`}
                        </p>
                    )}
                    {/* && means "only draw the grid when there is at least one product". */}
                    {products.length > 0 && (
                        <div className="mt-8">
                            <ProductGrid products={products} />
                        </div>
                    )}
                </>
            )}
        </>
    );
}
