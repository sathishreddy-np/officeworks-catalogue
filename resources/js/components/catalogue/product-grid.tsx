import { motion, useReducedMotion } from 'motion/react';
import ProductCard from '@/components/catalogue/product-card';
import type { CatalogueProduct } from '@/types/catalogue';

// Draws the product cards in a responsive grid: 1 column on a phone, more on a wide screen.
export default function ProductGrid({
    products,
}: {
    products: CatalogueProduct[];
}) {
    const reduceMotion = useReducedMotion();

    return (
        <motion.ul
            className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            // false means "do not animate the start state" when reduced motion is on.
            initial={reduceMotion ? false : 'hidden'}
            animate="show"
            variants={{
                hidden: {},
                // staggerChildren waits a little before starting each card's animation.
                show: {
                    transition: {
                        staggerChildren: reduceMotion ? 0 : 0.05,
                    },
                },
            }}
        >
            {products.map((product) => (
                <ProductCard key={product.id} product={product} />
            ))}
        </motion.ul>
    );
}
