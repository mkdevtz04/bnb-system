<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * AdminController::storeImages() used to save whatever UploadedFile::store()
 * handed back without checking it. The public disk is configured with
 * 'throw' => false (config/filesystems.php), so a write that fails for any
 * reason — wrong ownership on storage/app/public on a real server being the
 * one that actually happened — returns false rather than throwing, and that
 * false was saved straight into ApartmentImage::image_path. The admin saw
 * "success"; the photo never existed. These pin down the fix: a failed
 * write must never become a database row, and must never be silent.
 */
class ApartmentImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_a_successful_upload_is_recorded_against_the_apartment(): void
    {
        Storage::fake('public');

        $apartment = Apartment::factory()->create();

        $this->actingAs($this->admin())->put(route('admin.apartments.update', $apartment), [
            ...Arr::only($apartment->toArray(), ['name', 'floor', 'description', 'price_per_night', 'max_guests', 'bedrooms', 'bathrooms', 'status']),
            'images' => [UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg')],
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $apartment->images()->count());
        Storage::disk('public')->assertExists($apartment->images()->first()->image_path);
    }

    /**
     * The regression this whole test class exists for: simulate the disk
     * write itself failing (a permissions problem, a full disk, anything
     * Flysystem reports as a failure rather than an exception) and confirm
     * nothing broken gets saved, and the admin is actually told.
     */
    public function test_a_failed_write_is_never_saved_and_is_reported(): void
    {
        $failingDisk = \Mockery::mock(Filesystem::class);
        $failingDisk->shouldReceive('putFileAs')->andReturn(false);
        Storage::shouldReceive('disk')->with('public')->andReturn($failingDisk);

        $apartment = Apartment::factory()->create();

        $response = $this->actingAs($this->admin())->put(route('admin.apartments.update', $apartment), [
            ...Arr::only($apartment->toArray(), ['name', 'floor', 'description', 'price_per_night', 'max_guests', 'bedrooms', 'bathrooms', 'status']),
            'images' => [UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg')],
        ]);

        $response->assertSessionHasErrors(['images']);
        $this->assertSame(0, $apartment->images()->count());

        // The rest of the update must still have gone through — one broken
        // photo should never block saving the property itself.
        $response->assertSessionHas('success');
    }

    /**
     * Two files, one failing: the one that worked is kept, and the failure
     * is still reported rather than hidden behind an otherwise-successful save.
     */
    public function test_a_partial_failure_keeps_the_successful_uploads(): void
    {
        $callCount = 0;
        $flakyDisk = \Mockery::mock(Filesystem::class);
        $flakyDisk->shouldReceive('putFileAs')->andReturnUsing(function () use (&$callCount) {
            $callCount++;

            return $callCount === 1 ? 'apartments/ok.jpg' : false;
        });
        Storage::shouldReceive('disk')->with('public')->andReturn($flakyDisk);

        $apartment = Apartment::factory()->create();

        $response = $this->actingAs($this->admin())->put(route('admin.apartments.update', $apartment), [
            ...Arr::only($apartment->toArray(), ['name', 'floor', 'description', 'price_per_night', 'max_guests', 'bedrooms', 'bathrooms', 'status']),
            'images' => [
                UploadedFile::fake()->create('ok.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('broken.jpg', 100, 'image/jpeg'),
            ],
        ]);

        $response->assertSessionHasErrors(['images']);
        $this->assertSame(1, $apartment->images()->count());
        $this->assertSame('apartments/ok.jpg', $apartment->images()->first()->image_path);
    }
}
