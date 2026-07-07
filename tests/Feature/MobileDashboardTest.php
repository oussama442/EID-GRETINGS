<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Car;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_dashboard_is_scoped_to_their_branch_and_returns_operational_summary(): void
    {
        [$agentBranch, $otherBranch] = Branch::factory(2)->create();
        $agent = User::factory()->create([
            'branch_id' => $agentBranch->id,
            'role' => 'Agent',
        ]);
        Sanctum::actingAs($agent);

        $client = Client::factory()->create(['branch_id' => $agentBranch->id]);
        $agentCar = Car::factory()->create(['branch_id' => $agentBranch->id, 'status' => 'available']);
        $otherCar = Car::factory()->create(['branch_id' => $otherBranch->id, 'status' => 'available']);

        Booking::factory()->create([
            'client_id' => $client->id,
            'car_id' => $agentCar->id,
            'branch_id' => $agentBranch->id,
            'pickup_datetime' => now()->addHour(),
            'return_datetime_planned' => now()->addDays(2),
            'status' => 'reserved',
        ]);

        Booking::factory()->create([
            'client_id' => $client->id,
            'car_id' => $otherCar->id,
            'branch_id' => $otherBranch->id,
            'pickup_datetime' => now()->addHour(),
            'return_datetime_planned' => now()->addDays(2),
            'status' => 'reserved',
        ]);

        $response = $this->getJson('/api/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('pickups_count', 1)
            ->assertJsonPath('car_status.available', 1)
            ->assertJsonStructure([
                'pickups_today',
                'returns_today',
                'overdue_returns',
                'pickups_count',
                'returns_count',
                'overdue_count',
                'car_status' => ['available', 'reserved', 'rented', 'maintenance', 'out_of_service'],
            ]);
    }

    public function test_manager_dashboard_without_branch_sees_all_branches(): void
    {
        [$firstBranch, $secondBranch] = Branch::factory(2)->create();
        $manager = User::factory()->create([
            'branch_id' => null,
            'role' => 'Manager',
        ]);
        Sanctum::actingAs($manager);

        $firstCar = Car::factory()->create(['branch_id' => $firstBranch->id, 'status' => 'available']);
        $secondCar = Car::factory()->create(['branch_id' => $secondBranch->id, 'status' => 'available']);
        $firstClient = Client::factory()->create(['branch_id' => $firstBranch->id]);
        $secondClient = Client::factory()->create(['branch_id' => $secondBranch->id]);

        Booking::factory()->create([
            'client_id' => $firstClient->id,
            'car_id' => $firstCar->id,
            'branch_id' => $firstBranch->id,
            'pickup_datetime' => now()->addHour(),
            'return_datetime_planned' => now()->addDays(1),
            'status' => 'reserved',
        ]);

        Booking::factory()->create([
            'client_id' => $secondClient->id,
            'car_id' => $secondCar->id,
            'branch_id' => $secondBranch->id,
            'pickup_datetime' => now()->addHour(),
            'return_datetime_planned' => now()->addDays(1),
            'status' => 'reserved',
        ]);

        $response = $this->getJson('/api/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('pickups_count', 2)
            ->assertJsonPath('car_status.available', 2);
    }
}
