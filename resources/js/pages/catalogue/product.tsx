import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Star } from 'lucide-react';
import { motion, useReducedMotion } from 'motion/react';
import UnavailableNotice from '@/components/catalogue/unavailable-notice';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { show } from '@/routes/categories';
import { store } from '@/routes/enquiries';
import type { CatalogueCategory, CatalogueProduct } from '@/types/catalogue';

// The product page: photo, details, and the enquire form.
export default function Product({
    catalogueUnavailable,
    product,
}: {
    categories: CatalogueCategory[];
    catalogueUnavailable: boolean;
    product: CatalogueProduct | null;
}) {
    const reduceMotion = useReducedMotion();
    const { flash, errors } = usePage<{
        flash?: { enquiry?: string | null };
        errors?: { catalogue?: string };
    }>().props;
    // thankYou is the sentence saved in the session after a successful enquiry.
    const thankYou = flash?.enquiry;

    return (
        <>
            <Head title={product?.title ?? 'Product'} />
            {catalogueUnavailable || product === null ? (
                <UnavailableNotice />
            ) : (
                <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                    <div className="flex items-center justify-center rounded-3xl bg-white p-8 shadow-sm ring-1 ring-black/5">
                        <img
                            src={product.image}
                            alt={product.title}
                            className="max-h-[28rem] w-full object-contain"
                        />
                    </div>
                    <div>
                        <Link
                            href={show(product.categorySlug)}
                            prefetch
                            className="text-sm font-bold tracking-wide text-[#D71920] uppercase hover:underline"
                        >
                            {product.category}
                        </Link>
                        <h1 className="mt-2 text-3xl font-black tracking-tight">
                            {product.title}
                        </h1>
                        <p className="mt-3 flex items-center gap-1 text-sm text-[#4b4b4b]">
                            <Star className="size-4 fill-[#F5B400] text-[#F5B400]" />
                            {product.rating.toFixed(1)} ({product.ratingCount}{' '}
                            reviews)
                        </p>
                        <p className="mt-4 text-3xl font-black">
                            {product.priceLabel}
                        </p>
                        <p className="mt-1 text-xs text-[#767676]">
                            Price shown in AUD for this catalogue.
                        </p>
                        <p className="mt-6 leading-7 text-[#333]">
                            {product.description}
                        </p>

                        <section
                            id="enquire"
                            className="mt-8 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5"
                        >
                            <h2 className="text-xl font-bold">Enquire</h2>
                            <p className="mt-1 text-sm text-[#4b4b4b]">
                                Send your details and a specialist will follow
                                up. This does not place an order.
                            </p>
                            {/* The green box appears only after a successful enquiry. */}
                            {thankYou && (
                                <p
                                    role="status"
                                    className="mt-4 rounded-xl bg-[#e8f6ee] px-4 py-3 text-sm font-semibold text-[#0f6b32]"
                                >
                                    {thankYou}
                                </p>
                            )}
                            <InputError
                                message={errors?.catalogue}
                                className="mt-4"
                            />
                            {/* store.form(id) posts to /products/{id}/enquiries. resetOnSuccess clears the boxes. */}
                            <Form
                                {...store.form(product.id)}
                                resetOnSuccess
                                options={{ preserveScroll: true }}
                                className="mt-5 grid gap-4"
                            >
                                {/* processing is true while the form is being sent. formErrors come from Laravel. */}
                                {({ processing, errors: formErrors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="name">Name</Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                autoComplete="name"
                                                required
                                            />
                                            <InputError
                                                message={formErrors.name}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="email">
                                                Email
                                            </Label>
                                            <Input
                                                id="email"
                                                name="email"
                                                type="email"
                                                autoComplete="email"
                                                required
                                            />
                                            <InputError
                                                message={formErrors.email}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="phone">
                                                Phone
                                            </Label>
                                            <Input
                                                id="phone"
                                                name="phone"
                                                type="tel"
                                                autoComplete="tel"
                                                required
                                            />
                                            <InputError
                                                message={formErrors.phone}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="message">
                                                Message
                                            </Label>
                                            <textarea
                                                id="message"
                                                name="message"
                                                required
                                                rows={4}
                                                className="border-input w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                                            />
                                            <InputError
                                                message={formErrors.message}
                                            />
                                        </div>
                                        <motion.div
                                            whileTap={
                                                reduceMotion
                                                    ? undefined
                                                    : { scale: 0.98 }
                                            }
                                        >
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                                className="h-11 rounded-full bg-[#D71920] px-6 font-bold text-white hover:bg-[#b8141a]"
                                            >
                                                {processing
                                                    ? 'Sending...'
                                                    : 'Enquire'}
                                            </Button>
                                        </motion.div>
                                    </>
                                )}
                            </Form>
                        </section>
                    </div>
                </div>
            )}
        </>
    );
}
