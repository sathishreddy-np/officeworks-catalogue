// Head sets the browser-tab title for this page.
import { Head } from '@inertiajs/react';
import ProductGrid from '@/components/catalogue/product-grid';
import UnavailableNotice from '@/components/catalogue/unavailable-notice';
import type { CatalogueCategory, CatalogueProduct } from '@/types/catalogue';

// The props below are exactly the keys HomeController sends with Inertia::render.
export default function Home({
    catalogueUnavailable,
    products,
}: {
    categories: CatalogueCategory[];
    catalogueUnavailable: boolean;
    products: CatalogueProduct[];
}) {
    return (
        <>
            <Head title="Top rated" />
            {/* The API was down. Show the retry box instead of an empty grid. */}
            {catalogueUnavailable ? (
                <UnavailableNotice />
            ) : (
                <>
                    <div className="max-w-2xl">
                        <p className="text-sm font-bold tracking-wide text-[#D71920] uppercase">
                            Chosen for you
                        </p>
                        <h1 className="mt-2 text-3xl font-black tracking-tight sm:text-4xl">
                            Top rated products
                        </h1>
                        <p className="mt-3 text-[#4b4b4b]">
                            Five products we have selected as top rated. Open a
                            category to see more, or search when you know what
                            you need.
                        </p>
                    </div>
                    <div className="mt-8">
                        {/* products is the five chosen items, already in the right order. */}
                        <ProductGrid products={products} />
                    </div>
                </>
            )}
        </>
    );
}
