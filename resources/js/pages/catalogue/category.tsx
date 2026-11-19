import { Head } from '@inertiajs/react';
import ProductGrid from '@/components/catalogue/product-grid';
import UnavailableNotice from '@/components/catalogue/unavailable-notice';
import type { CatalogueCategory, CatalogueProduct } from '@/types/catalogue';

// category is null only when the catalogue could not be loaded.
export default function Category({
    catalogueUnavailable,
    category,
    topRated,
    additional,
}: {
    categories: CatalogueCategory[];
    catalogueUnavailable: boolean;
    category: { name: string; slug: string } | null;
    topRated: CatalogueProduct[];
    additional: CatalogueProduct[];
}) {
    return (
        <>
            {/* ?? uses "Category" when the name is missing. */}
            <Head title={category?.name ?? 'Category'} />
            {catalogueUnavailable || category === null ? (
                <UnavailableNotice />
            ) : (
                <>
                    <h1 className="text-3xl font-black tracking-tight capitalize sm:text-4xl">
                        {category.name}
                    </h1>
                    {/* First section: the chosen or highest-rated products. */}
                    <section className="mt-8">
                        <h2 className="text-lg font-bold">Top rated</h2>
                        <p className="mt-1 text-sm text-[#4b4b4b]">
                            The strongest picks in this category.
                        </p>
                        <div className="mt-4">
                            <ProductGrid products={topRated} />
                        </div>
                    </section>
                    {/* Second section: every other product in the category. */}
                    <section className="mt-10">
                        <h2 className="text-lg font-bold">
                            More in this category
                        </h2>
                        {additional.length === 0 ? (
                            <p className="mt-3 text-sm text-[#4b4b4b]">
                                Every product in this category is already in the
                                top rated set.
                            </p>
                        ) : (
                            <div className="mt-4">
                                <ProductGrid products={additional} />
                            </div>
                        )}
                    </section>
                </>
            )}
        </>
    );
}
