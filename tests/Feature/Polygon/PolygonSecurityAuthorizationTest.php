<?php

namespace Tests\Feature\Polygon;

use App\Models\PolygonExercise;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolygonSecurityAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PolygonExercise $exercise;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
        $this->exercise = PolygonExercise::factory()->create([
            'created_by' => $this->admin->user_id,
        ]);
    }

    /**
     * Unauthenticated visitors (guests) must be blocked on all endpoints (302/401/403).
     */
    public function test_unauthenticated_guests_are_denied_access_to_all_polygon_endpoints(): void
    {
        $id = $this->exercise->id;

        $endpoints = [
            ['method' => 'get', 'uri' => '/api/backend/polygon'],
            ['method' => 'post', 'uri' => '/api/backend/polygon', 'data' => ['name' => 'Test', 'short_name' => 'A', 'basename' => 'test']],
            ['method' => 'get', 'uri' => "/api/backend/polygon/{$id}"],
            ['method' => 'put', 'uri' => "/api/backend/polygon/{$id}", 'data' => ['name' => 'Updated']],
            ['method' => 'delete', 'uri' => "/api/backend/polygon/{$id}"],
            ['method' => 'post', 'uri' => "/api/backend/polygon/{$id}/compile-generator"],
            ['method' => 'post', 'uri' => "/api/backend/polygon/{$id}/generate-tests"],
            ['method' => 'post', 'uri' => "/api/backend/polygon/{$id}/calibrate"],
            ['method' => 'post', 'uri' => "/api/backend/polygon/{$id}/publish"],
        ];

        foreach ($endpoints as $ep) {
            $method = $ep['method'];
            $uri = $ep['uri'];
            $data = $ep['data'] ?? [];

            // Standard web request
            $response = $this->$method($uri, $data);
            $this->assertContains(
                $response->status(),
                [302, 401, 403],
                "Guest standard request to [{$method} {$uri}] returned unexpected status: {$response->status()}"
            );

            // JSON API request
            $jsonMethod = $method . 'Json';
            $jsonResponse = $this->$jsonMethod($uri, $data);
            $this->assertContains(
                $jsonResponse->status(),
                [302, 401, 403],
                "Guest JSON request to [{$method} {$uri}] returned unexpected status: {$jsonResponse->status()}"
            );
        }
    }

    /**
     * Authenticated users without admin role (team, judge, staff, site, score) must receive 403 Forbidden.
     */
    public function test_authenticated_non_admin_users_receive_403_forbidden_on_all_endpoints(): void
    {
        $id = $this->exercise->id;

        $nonAdminRoles = [
            'team',   // participant
            'judge',  // judge
            'staff',  // staff
            'site',   // site manager
            'score',  // spectator
        ];

        $endpoints = [
            ['method' => 'getJson', 'uri' => '/api/backend/polygon'],
            ['method' => 'postJson', 'uri' => '/api/backend/polygon', 'data' => ['name' => 'Test', 'short_name' => 'A', 'basename' => 'test']],
            ['method' => 'getJson', 'uri' => "/api/backend/polygon/{$id}"],
            ['method' => 'putJson', 'uri' => "/api/backend/polygon/{$id}", 'data' => ['name' => 'Updated']],
            ['method' => 'deleteJson', 'uri' => "/api/backend/polygon/{$id}"],
            ['method' => 'postJson', 'uri' => "/api/backend/polygon/{$id}/compile-generator"],
            ['method' => 'postJson', 'uri' => "/api/backend/polygon/{$id}/generate-tests"],
            ['method' => 'postJson', 'uri' => "/api/backend/polygon/{$id}/calibrate"],
            ['method' => 'postJson', 'uri' => "/api/backend/polygon/{$id}/publish"],
        ];

        foreach ($nonAdminRoles as $role) {
            $user = $this->createTestUser(['user_type' => $role]);

            foreach ($endpoints as $ep) {
                $method = $ep['method'];
                $uri = $ep['uri'];
                $data = $ep['data'] ?? [];

                $response = $this->actingAs($user)->$method($uri, $data);

                $this->assertSame(
                    403,
                    $response->status(),
                    "User with role [{$role}] was not denied with 403 on [{$method} {$uri}]. Got: {$response->status()}"
                );
            }
        }
    }

    /**
     * Admin user is granted access to the polygon endpoints.
     */
    public function test_admin_user_has_authorized_access(): void
    {
        $id = $this->exercise->id;

        // 1. Index returns 200
        $indexResponse = $this->actingAs($this->admin)->getJson('/api/backend/polygon');
        $indexResponse->assertStatus(200);

        // 2. Store returns 201
        $storeResponse = $this->actingAs($this->admin)->postJson('/api/backend/polygon', [
            'name' => 'Exercício Autorizado',
            'short_name' => 'B',
            'basename' => 'exercicio-autorizado',
            'time_limit' => 1,
            'memory_limit' => 256,
        ]);
        $storeResponse->assertStatus(201);

        // 3. Show returns 200
        $showResponse = $this->actingAs($this->admin)->getJson("/api/backend/polygon/{$id}");
        $showResponse->assertStatus(200);

        // 4. Blade routes return 200 and render appropriate views for admin
        $dashboardViewResponse = $this->actingAs($this->admin)->get('/backend/polygon');
        $dashboardViewResponse->assertStatus(200);
        $dashboardViewResponse->assertViewIs('backend.polygon.index');
        $dashboardViewResponse->assertSee('data-feature-page="polygon-dashboard"', false);

        $workspaceViewResponse = $this->actingAs($this->admin)->get("/backend/polygon/{$id}");
        $workspaceViewResponse->assertStatus(200);
        $workspaceViewResponse->assertViewIs('backend.polygon.workspace');
        $workspaceViewResponse->assertSee('data-feature-page="polygon-workspace"', false);
        $workspaceViewResponse->assertSee("data-exercise-id=\"{$id}\"", false);
    }

    /**
     * Blade routes enforce authentication and admin authorization.
     */
    public function test_blade_routes_enforce_security_and_authorization(): void
    {
        $id = $this->exercise->id;

        // Guest is redirected
        $this->get('/backend/polygon')->assertRedirect();
        $this->get("/backend/polygon/{$id}")->assertRedirect();

        // Non-admin receives 403
        $teamUser = $this->createTestUser(['user_type' => 'team']);
        $this->actingAs($teamUser)->get('/backend/polygon')->assertStatus(403);
        $this->actingAs($teamUser)->get("/backend/polygon/{$id}")->assertStatus(403);
    }
}
