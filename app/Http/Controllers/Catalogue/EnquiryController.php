<?php

// PHP start tag.

// This controller answers POST /products/11/enquiries.

namespace App\Http\Controllers\Catalogue;

use App\Catalogue\CatalogueUnavailable;
use App\Catalogue\ProductCatalogue;
use App\Http\Controllers\Controller;
// The form request checks the fields before this method runs.
use App\Http\Requests\StoreEnquiryRequest;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
// A redirect sends the browser to another address instead of rendering a page here.
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnquiryController extends Controller
{
    // $product is the id from the URL. Validation has already passed when we get here.
    public function store(StoreEnquiryRequest $request, ProductCatalogue $catalogue, int $product): RedirectResponse
    {
        try {
            // Look up the product so we store its real title, not a title typed by the browser.
            $record = $catalogue->find($product);
        } catch (CatalogueUnavailable $exception) {
            Log::warning('Product catalogue is unavailable.', [
                'message' => $exception->getMessage(),
            ]);

            // back() returns the customer to the form, with an error they can read.
            return back()->withErrors([
                'catalogue' => 'The catalogue is unavailable right now. Please try your enquiry again shortly.',
            ]);
        }

        // The product id is not in the catalogue.
        abort_if($record === null, 404);

        // safe()->only keeps just the four validated fields. The product fields come from the server.
        $enquiry = Enquiry::query()->create([
            ...$request->safe()->only(['name', 'email', 'phone', 'message']),
            'product_id' => $record->id,
            'product_title' => $record->title,
        ]);

        // Send the email now, not on a queue, so the log file updates before we say thank you.
        Mail::to((string) config('catalogue.sales_inbox'))->send(new EnquiryReceived($enquiry));

        return redirect()
            ->route('products.show', $record->id)
            // The product page reads this flash message and shows the green thank-you box.
            ->with('enquiry', 'Thank you. A specialist will contact you about '.$record->title.'.');
    }
}
