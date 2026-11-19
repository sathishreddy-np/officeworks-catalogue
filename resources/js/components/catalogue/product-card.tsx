// Link moves to another page without a full browser reload.
import { Link } from '@inertiajs/react';
// Star is a small icon.
import { Star } from 'lucide-react';
// motion animates the card. useReducedMotion is true when the person asked the OS to reduce animation.
import { motion, useReducedMotion } from 'motion/react';
// show() builds the URL /products/{id} from the Wayfinder route helper.
import { show } from '@/routes/products';
import type { CatalogueProduct } from '@/types/catalogue';

// One clickable product tile.
export default function ProductCard({
    product,
}: {
    product: CatalogueProduct;
}) {
    const reduceMotion = useReducedMotion();

    return (
        // motion.li is a list item that can fade in. The parent grid staggers these.
        <motion.li
            variants={{
                hidden: { opacity: 0, y: reduceMotion ? 0 : 14 },
                show: { opacity: 1, y: 0 },
            }}
            transition={{ duration: reduceMotion ? 0 : 0.28 }}
        >
            {/* prefetch loads the product page in the background when the pointer hovers. */}
            <Link
                href={show(product.id)}
                prefetch
                className="group flex h-full flex-col rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D71920]"
            >
                {/* The photo is decorative here because the title is the link name. alt="" tells screen readers to skip it. */}
                <div className="flex h-44 items-center justify-center rounded-xl bg-[#fafafa] p-4">
                    <img
                        src={product.image}
                        alt=""
                        className="max-h-full max-w-full object-contain transition duration-300 group-hover:scale-[1.03]"
                    />
                </div>
                <p className="mt-4 text-xs font-semibold tracking-wide text-[#D71920] uppercase">
                    {product.category}
                </p>
                {/* line-clamp-2 keeps long titles to two lines so the cards stay the same height. */}
                <h2 className="mt-1 line-clamp-2 min-h-12 text-base font-semibold text-[#1a1a1a]">
                    {product.title}
                </h2>
                <p className="mt-3 flex items-center gap-1 text-sm text-[#4b4b4b]">
                    <Star className="size-4 fill-[#F5B400] text-[#F5B400]" />
                    <span>{product.rating.toFixed(1)}</span>
                    <span className="text-[#767676]">
                        ({product.ratingCount})
                    </span>
                </p>
                {/* mt-auto pushes the price to the bottom of the card. */}
                <p className="mt-auto pt-4 text-xl font-bold text-[#1a1a1a]">
                    {product.priceLabel}
                </p>
            </Link>
        </motion.li>
    );
}
