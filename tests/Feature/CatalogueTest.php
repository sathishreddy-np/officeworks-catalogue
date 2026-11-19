<?php

// These tests pretend to be a visitor. They never call the real Fake Store API.
// Http::fake supplies a small product list. Mail::fake catches the enquiry email.

namespace Tests\Feature;

use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CatalogueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    // The home page must show exactly the five configured ids, in that order.
    public function test_home_shows_the_five_chosen_top_rated_products(): void
    {
        $this->fakeCatalogue();

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('catalogue/home')
                ->where('catalogueUnavailable', false)
                ->has('products', 5)
                ->where('products.0.id', 11)
                ->where('products.1.id', 12)
                ->where('products.2.id', 3)
                ->where('products.3.id', 18)
                ->where('products.4.id', 5)
                ->where('products.0.priceLabel', 'A$109.00')
            );
    }

    // Electronics contains two chosen products. The third electronics item is "additional".
    public function test_category_page_splits_top_rated_products_from_the_rest(): void
    {
        $this->fakeCatalogue();

        $this->get(route('categories.show', 'electronics'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('catalogue/category')
                ->where('category.name', 'electronics')
                ->has('topRated', 2)
                ->where('topRated.0.id', 11)
                ->where('topRated.1.id', 12)
                ->has('additional', 1)
                ->where('additional.0.id', 9)
            );
    }

    // Books is not in the chosen five, so the highest rating becomes the top-rated product.
    public function test_category_without_a_chosen_product_uses_the_highest_rating(): void
    {
        $this->fakeCatalogue();

        $this->get(route('categories.show', 'books'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('topRated.0.id', 21)
                ->where('additional.0.id', 22)
            );
    }

    // A slug that matches no products is a 404, not an empty category page.
    public function test_unknown_category_is_not_found(): void
    {
        $this->fakeCatalogue();

        $this->get(route('categories.show', 'stationery'))->assertNotFound();
    }

    // Search is case-insensitive and must happen on the server, which is why we assert the HTTP call.
    public function test_search_returns_only_products_matching_the_term(): void
    {
        $this->fakeCatalogue();

        $this->get(route('search', ['q' => 'JACKET']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('catalogue/search')
                ->where('query', 'JACKET')
                ->has('products', 1)
                ->where('products.0.id', 3)
            );

        Http::assertSent(fn ($request): bool => $request->url() === 'https://fakestoreapi.com/products');
    }

    // Pressing Search with an empty box returns no products.
    public function test_empty_search_shows_no_products(): void
    {
        $this->fakeCatalogue();

        $this->get(route('search'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('query', '')
                ->has('products', 0)
            );
    }

    // Opening product 11 shows that product, not the whole catalogue.
    public function test_product_page_shows_the_selected_product(): void
    {
        $this->fakeCatalogue();

        $this->get(route('products.show', 11))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('catalogue/product')
                ->where('product.id', 11)
                ->where('product.title', 'Silicon Power SSD')
                ->where('product.categorySlug', 'electronics')
            );
    }

    // An id that is not in the fake list is a 404.
    public function test_missing_product_is_not_found(): void
    {
        $this->fakeCatalogue();

        $this->get(route('products.show', 999))->assertNotFound();
    }

    // A valid form saves a row, emails the sales inbox, and flashes the thank-you sentence.
    public function test_enquiry_emails_the_sales_inbox_and_thanks_the_customer(): void
    {
        $this->fakeCatalogue();
        Mail::fake();

        $this->post(route('enquiries.store', 11), [
            'name' => 'Ava Nguyen',
            'email' => 'Ava@Example.com',
            'phone' => '0412 345 678',
            'message' => 'Is this drive suitable for a laptop?',
        ])->assertRedirect(route('products.show', 11))
            ->assertSessionHas('enquiry', 'Thank you. A specialist will contact you about Silicon Power SSD.');

        $this->assertDatabaseHas('enquiries', [
            'product_id' => 11,
            'product_title' => 'Silicon Power SSD',
            'name' => 'Ava Nguyen',
            'email' => 'ava@example.com',
            'phone' => '0412 345 678',
        ]);

        Mail::assertSent(EnquiryReceived::class, function (EnquiryReceived $mail): bool {
            return $mail->hasTo('sales@officeworks.example')
                && $mail->enquiry->product_title === 'Silicon Power SSD';
        });
    }

    // The email body itself contains the product name, the message, and the email address.
    public function test_enquiry_mail_includes_the_customer_message(): void
    {
        $mail = new EnquiryReceived(new Enquiry([
            'product_id' => 11,
            'product_title' => 'Silicon Power SSD',
            'name' => 'Ava Nguyen',
            'email' => 'ava@example.com',
            'phone' => '0412 345 678',
            'message' => 'Is this drive suitable for a laptop?',
        ]));

        $mail->assertSeeInHtml('Silicon Power SSD');
        $mail->assertSeeInHtml('Is this drive suitable for a laptop?');
        $mail->assertSeeInHtml('ava@example.com');
    }

    // An empty form is rejected and nothing is saved.
    public function test_enquiry_requires_contact_details(): void
    {
        $this->fakeCatalogue();

        $this->from(route('products.show', 11))
            ->post(route('enquiries.store', 11), [])
            ->assertRedirect(route('products.show', 11))
            ->assertSessionHasErrors([
                'name' => 'Enter your name.',
                'email' => 'Enter your email address.',
                'phone' => 'Enter your phone number.',
                'message' => 'Tell us what you would like to know.',
            ]);

        $this->assertDatabaseCount('enquiries', 0);
    }

    // Letters are not a phone number. The customer sees the phone error sentence.
    public function test_enquiry_rejects_a_phone_number_with_letters(): void
    {
        $this->fakeCatalogue();

        $this->from(route('products.show', 11))
            ->post(route('enquiries.store', 11), [
                'name' => 'Ava Nguyen',
                'email' => 'ava@example.com',
                'phone' => 'call me',
                'message' => 'Is this drive suitable for a laptop?',
            ])
            ->assertSessionHasErrors([
                'phone' => 'Enter a phone number using digits and the characters + ( ) . -.',
            ]);
    }

    // A failed connection must not become a 500 page. The home page stays up and shows the retry flag.
    public function test_pages_stay_available_when_the_catalogue_source_is_down(): void
    {
        Http::fake([
            'fakestoreapi.com/*' => Http::failedConnection(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('catalogue/home')
                ->where('catalogueUnavailable', true)
                ->has('products', 0)
            );
    }

    // Replace the real HTTP call with the small product list below.
    private function fakeCatalogue(): void
    {
        Http::fake([
            'https://fakestoreapi.com/products' => Http::response($this->cataloguePayload()),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    // Nine products that cover every page this file checks.
    private function cataloguePayload(): array
    {
        return [
            $this->product(11, 'Silicon Power SSD', 'A fast internal drive.', 109, 'electronics', 4.8),
            $this->product(12, 'WD Gaming Drive', 'A big external drive.', 114, 'electronics', 4.8),
            $this->product(9, 'Portable drive', 'Everyday storage.', 64, 'electronics', 3.3),
            $this->product(3, 'Mens Cotton Jacket', 'A warm cotton jacket.', 55.99, "men's clothing", 4.7),
            $this->product(1, 'Everyday backpack', 'A bag for commuting.', 109.95, "men's clothing", 3.9),
            $this->product(18, 'Boat neck top', 'A soft short sleeve top.', 9.85, "women's clothing", 4.7),
            $this->product(5, 'Dragon bracelet', 'A gold station bracelet.', 695, 'jewelery', 4.6),
            $this->product(21, 'Desk reference', 'A highly rated office book.', 24, 'books', 4.2),
            $this->product(22, 'Notebook', 'A plain ruled notebook.', 8, 'books', 3.1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    // One Fake Store product, shaped the same way the live API sends it.
    private function product(int $id, string $title, string $description, float $price, string $category, float $rating): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'category' => $category,
            'image' => 'https://example.com/'.$id.'.jpg',
            'rating' => ['rate' => $rating, 'count' => 10],
        ];
    }
}
