// Form sends the search to Laravel. Link changes page without a full reload.
// usePage reads data Laravel shares with every page, such as the signed-in user.
import { Form, Link, usePage } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { motion, useReducedMotion } from 'motion/react';
import type { ReactNode } from 'react';
import { useEffect, useRef, useState } from 'react';
import { dashboard, home, login, search } from '@/routes';
import { show } from '@/routes/categories';
import type { CatalogueCategory, CatalogueProduct } from '@/types/catalogue';

// The category bar's horizontal scroll, and the search box text.
// They live outside the component so a trip to sign-in and back can restore them.
// A full reload clears them, which is the same as a fresh visit.
let savedCategoryScroll = 0;
let savedSearchTerm: string | null = null;

// The red store frame. Inertia keeps this mounted while catalogue pages change,
// so the header, search text, and category bar do not rebuild on each visit.
export default function StoreLayout({
    categories = [],
    category = null,
    product = null,
    query = '',
    children,
}: {
    categories?: CatalogueCategory[];
    category?: { slug: string } | null;
    product?: Pick<CatalogueProduct, 'categorySlug'> | null;
    query?: string;
    children: ReactNode;
}) {
    // True when the visitor's operating system asks sites to skip motion.
    const reduceMotion = useReducedMotion();
    const page = usePage<{
        auth?: { user?: { name?: string } | null };
    }>();
    const pageAuth = page.props.auth;
    // Highlight the category for this page. Home and search leave this empty.
    const currentSlug = category?.slug ?? product?.categorySlug;
    // term is what is typed in the box. It stays while the visitor moves around,
    // including a trip through sign-in in this same tab.
    const [term, setTerm] = useState(() => savedSearchTerm ?? query);
    const categoryNav = useRef<HTMLUListElement>(null);

    useEffect(() => {
        savedSearchTerm = term;
    }, [term]);

    useEffect(() => {
        const path = page.url.split('?')[0];

        // Only the search results page should replace what the visitor typed.
        if (path === '/search') {
            setTerm(query);
        }
    }, [page.url, query]);

    useEffect(() => {
        const nav = categoryNav.current;

        if (!nav) {
            return;
        }

        nav.scrollLeft = savedCategoryScroll;

        const remember = () => {
            savedCategoryScroll = nav.scrollLeft;
        };

        nav.addEventListener('scroll', remember, { passive: true });

        return () => nav.removeEventListener('scroll', remember);
    }, []);

    return (
        <div className="min-h-screen bg-[#f3f3f3] text-[#1a1a1a]">
            {/* Hidden until keyboard focus. It jumps past the header for people using a keyboard. */}
            <a
                href="#catalogue-main"
                className="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50 focus:rounded-md focus:bg-white focus:px-3 focus:py-2"
            >
                Skip to products
            </a>
            <header className="bg-[#D71920] text-white">
                <div className="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 lg:px-6">
                    <div className="flex items-center justify-between gap-4">
                        <Link href={home()} prefetch className="leading-none">
                            <span className="block text-2xl font-black tracking-tight">
                                Officeworks
                            </span>
                            <span className="text-xs font-medium tracking-[0.16em] text-white/80 uppercase">
                                Australia
                            </span>
                        </Link>
                        <Link
                            href={pageAuth?.user ? dashboard() : login()}
                            prefetch
                            className="text-sm font-semibold text-white/90 underline-offset-4 hover:underline"
                        >
                            {pageAuth?.user ? 'Account' : 'Sign in'}
                        </Link>
                    </div>
                    {/* search.form() is a GET form. The input name "q" becomes ?q= in the address. */}
                    <Form
                        {...search.form()}
                        className="flex w-full overflow-hidden rounded-full bg-white shadow-sm"
                    >
                        <label htmlFor="catalogue-search" className="sr-only">
                            Search products
                        </label>
                        <input
                            id="catalogue-search"
                            name="q"
                            value={term}
                            onChange={(event) => setTerm(event.target.value)}
                            placeholder="Search products, brands and categories"
                            maxLength={100}
                            className="min-w-0 flex-1 bg-transparent px-5 py-3 text-[#1a1a1a] outline-none placeholder:text-[#767676]"
                        />
                        <motion.button
                            type="submit"
                            whileTap={
                                reduceMotion ? undefined : { scale: 0.98 }
                            }
                            className="m-1 inline-flex items-center gap-2 rounded-full bg-[#D71920] px-5 py-2 text-sm font-bold text-white hover:bg-[#b8141a]"
                        >
                            <Search className="size-4" />
                            Search
                        </motion.button>
                    </Form>
                </div>
                <nav
                    aria-label="Categories"
                    className="border-t border-white/15"
                >
                    <ul
                        ref={categoryNav}
                        className="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-3 lg:px-6"
                    >
                        <li>
                            <Link
                                href={home()}
                                prefetch
                                className={`inline-flex rounded-full px-3 py-1.5 text-sm font-semibold whitespace-nowrap ${
                                    currentSlug
                                        ? 'text-white/85 hover:bg-white/10'
                                        : 'bg-white text-[#D71920]'
                                }`}
                            >
                                Top rated
                            </Link>
                        </li>
                        {categories.map((item) => (
                            <li key={item.slug}>
                                {/* prefetch starts loading this category before the click. */}
                                <Link
                                    href={show(item.slug)}
                                    prefetch
                                    className={`inline-flex rounded-full px-3 py-1.5 text-sm font-semibold whitespace-nowrap capitalize ${
                                        currentSlug === item.slug
                                            ? 'bg-white text-[#D71920]'
                                            : 'text-white/85 hover:bg-white/10'
                                    }`}
                                >
                                    {item.name}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>
            </header>
            <main
                id="catalogue-main"
                className="mx-auto max-w-7xl px-4 py-8 lg:px-6"
            >
                {/* key={url} swaps the page body only. The header above stays mounted. */}
                <motion.div
                    key={page.url}
                    initial={reduceMotion ? false : { opacity: 0, y: 8 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: reduceMotion ? 0 : 0.25 }}
                >
                    {children}
                </motion.div>
            </main>
        </div>
    );
}
