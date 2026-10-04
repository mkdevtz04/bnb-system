<?php

namespace Tests\Feature;

use App\Models\Apartment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public pages must render for a signed-out visitor.
 *
 * This previously ran without RefreshDatabase, so it queried a schema that was
 * never migrated and failed the moment the homepage started listing properties.
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_renders(): void
    {
        Apartment::factory()->count(3)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('Coastal', escape: false);
    }

    public function test_the_homepage_renders_with_no_properties(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_search_renders(): void
    {
        Apartment::factory()->count(2)->create();

        $this->get(route('apartments.search'))->assertOk();
    }

    public function test_a_property_page_renders(): void
    {
        $apartment = Apartment::factory()->create();

        $this->get(route('apartments.show', $apartment))
            ->assertOk()
            ->assertSee($apartment->name, escape: false);
    }

    public function test_the_old_search_url_still_resolves(): void
    {
        $this->get('/apartments/search')->assertRedirect('/search');
    }
}
