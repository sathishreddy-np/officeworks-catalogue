# AI usage

This catalogue was planned and implemented with Cursor’s coding agent (Grok 4.7) in this workspace. The agent read the Officeworks “Code Challenge: AI-Native Engineer” brief, then the Laravel, Inertia, and testing skills already in the repository, and implemented against that plan.

## Instruction files

The repository already contains Laravel Boost skills under `.cursor/skills` (Laravel best practices, Inertia React, Wayfinder, and PHPUnit testing). No extra agent rules were added for this pass. Those skills are the instruction files that shaped the controllers, the HTTP client, the form request, and the tests.

## Plan

The plan is the Officeworks catalogue proof of concept: a swappable `ProductCatalogue`, five chosen featured products, category and search pages, an enquiry mailable, and an Officeworks-style Inertia storefront. It lives with the chat that produced this submission.

## Moments that shaped the result

1. The brief requires the client not to call Fake Store. The agent put every product read behind `ProductCatalogue` and a cached `FakeStoreCatalogue`, instead of fetching JSON in React.
2. “Top rated” was treated as a merchandising choice. Featured ids are config, and a category with none of those ids falls back to its highest-rated product so the category criteria still hold.
3. Enquiry mail stays synchronous and uses the log mailer. A queued mailable would depend on a worker the reviewer might not watch, so the thank-you message would lie.
4. The existing Fortify starter was kept. The home route became the catalogue, and sign-in stayed a secondary link, so setup commands and the current auth tests still apply.
5. Motion is limited to card entrance, page entrance, and the enquire button, and it respects `prefers-reduced-motion`.

## Where the agent was corrected

- The first search box blocked an empty submit. That hid the empty-search state the page is supposed to explain. The Search button now always submits, and an empty term returns no products.
- A shell in this session could not see `php` on `PATH` even though Laravel Herd Lite was already serving the app. Tests were run with `C:\Users\sathi\.config\herd-lite\bin\php.exe` after that path was taken from the running process.
- Featured product ids were checked against the live Fake Store payload (with a user-agent header, because a bare request was rejected) before they were written into config. The home page does not invent products the API does not have.

The agent wrote the code. The structure, the assumptions in `DECISIONS.md`, and the tests are the review of that output.
