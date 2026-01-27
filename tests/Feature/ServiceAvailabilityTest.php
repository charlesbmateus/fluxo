<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_checks_service_availability()
    {
        $provider = User::factory()->provider()->create();
        $category = Category::factory()->create();

        $service = Service::factory()->create([
            'user_id'     => $provider->id,
            'category_id' => $category->id,
        ]);

        ServiceAvailability::factory()->create([
            'service_id' => $service->id,
            'day_of_week' => now()->dayOfWeek,
            'start_time' => '09:00:00',
            'end_time'   => '17:00:00',
        ]);

        $response = $this->getJson(
            "/api/services/{$service->id}/availability?start=" .
            now()->setHour(10) . "&end=" . now()->setHour(12)
        );

        $response->assertOk()
            ->assertJson([
                'available' => true,
            ]);
    }
}
