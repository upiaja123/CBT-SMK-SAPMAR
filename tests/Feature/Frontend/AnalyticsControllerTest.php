<?php

namespace Tests\Feature\Frontend;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_super_admin_can_view_analytics_index()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->get(route('analytics.index'));
        
        // Wait, route requires auth middleware, maybe need to check if it has it in web.php
        // Let's assert it's accessible. In web.php we didn't put it in auth middleware group yet!
        // Actually, looking at web.php I just put it at the root of web.php? Let me check web.php
        // Oh, wait, I just appended it to web.php, but it might be outside the auth middleware group. Let me fix web.php
        $response->assertStatus(200);
    }
}
