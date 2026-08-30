<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TourApiTest extends TestCase
{
    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Adventure',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'category_id');

        $this->destinationId = DB::table('destinations')->insertGetId([
            'destination_name' => 'Siem Reap',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'destination_id');
    }

    public function test_can_create_tour()
    {
        $payload = [
            'category_id' => $this->categoryId,
            'destination_id' => $this->destinationId,
            'title' => 'Angkor Wat Sunrise Tour',
            'base_price' => 150.00,
            'duration_days' => 3,
        ];

        $response = $this->postJson('/api/tours', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Tour created successfully',
                'data' => [
                    'title' => 'Angkor Wat Sunrise Tour',
                    'category_name' => 'Adventure',
                    'destination_name' => 'Siem Reap',
                ],
            ]);

        $this->assertDatabaseHas('tours', [
            'title' => 'Angkor Wat Sunrise Tour',
            'duration_days' => 3,
        ]);
    }

    public function test_can_list_tours()
    {
        DB::table('tours')->insert([
            'category_id' => $this->categoryId,
            'destination_id' => $this->destinationId,
            'title' => 'Island Hopping',
            'base_price' => 200.00,
            'duration_days' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/tours');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Tours retrieved successfully',
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_can_show_tour()
    {
        $tourId = DB::table('tours')->insertGetId([
            'category_id' => $this->categoryId,
            'destination_id' => $this->destinationId,
            'title' => 'Mountain Trekking',
            'base_price' => 99.99,
            'duration_days' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'tour_id');

        $response = $this->getJson("/api/tours/{$tourId}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'tour_id' => $tourId,
                    'title' => 'Mountain Trekking',
                ],
            ]);
    }

    public function test_can_update_tour()
    {
        $tourId = DB::table('tours')->insertGetId([
            'category_id' => $this->categoryId,
            'destination_id' => $this->destinationId,
            'title' => 'Old Tour Title',
            'base_price' => 50.00,
            'duration_days' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'tour_id');

        $response = $this->putJson("/api/tours/{$tourId}", [
            'title' => 'Updated Tour Title',
            'base_price' => 75.00,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'title' => 'Updated Tour Title',
                    'base_price' => '75.00',
                ],
            ]);

        $this->assertDatabaseHas('tours', [
            'tour_id' => $tourId,
            'title' => 'Updated Tour Title',
        ]);
    }

    public function test_can_delete_tour()
    {
        $tourId = DB::table('tours')->insertGetId([
            'category_id' => $this->categoryId,
            'destination_id' => $this->destinationId,
            'title' => 'Tour To Delete',
            'base_price' => 10.00,
            'duration_days' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'tour_id');

        $response = $this->deleteJson("/api/tours/{$tourId}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Tour deleted successfully',
            ]);

        $this->assertDatabaseMissing('tours', [
            'tour_id' => $tourId,
        ]);
    }
}
