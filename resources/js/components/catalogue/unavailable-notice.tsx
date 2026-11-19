// router.reload() asks Laravel for the current page again.
import { router } from '@inertiajs/react';

// Shown when Fake Store is down and there is no cached product list.
export default function UnavailableNotice() {
    return (
        <div
            role="alert"
            className="rounded-2xl border border-[#D71920]/20 bg-white p-6 shadow-sm"
        >
            <h1 className="text-xl font-bold">The catalogue is unavailable</h1>
            <p className="mt-2 max-w-xl text-[#4b4b4b]">
                Product data could not be loaded. Nothing was requested from
                your browser. Try again in a moment.
            </p>
            <button
                type="button"
                onClick={() => router.reload()}
                className="mt-4 rounded-full bg-[#D71920] px-5 py-2 text-sm font-bold text-white hover:bg-[#b8141a]"
            >
                Try again
            </button>
        </div>
    );
}
