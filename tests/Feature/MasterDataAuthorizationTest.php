<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_can_access_master_data(): void
    {
        $admin = User::where('username', 'superadmin')->first();
        $this->assertNotNull($admin, 'Superadmin user should exist from seeder.');

        $response = $this->actingAs($admin)->get(route('master.classes.index'));

        $response->assertStatus(200);
    }

    public function test_guru_cannot_access_master_data_management(): void
    {
        $guru = User::where('username', 'budi.santoso')->first();
        $this->assertNotNull($guru, 'Guru user should exist from seeder.');

        $response = $this->actingAs($guru)->get(route('master.classes.index'));

        $response->assertStatus(403);
    }

    public function test_siswa_cannot_access_master_data_management(): void
    {
        $siswa = User::where('username', 'ahmad.fauzi')->first();
        $this->assertNotNull($siswa, 'Siswa user should exist from seeder.');

        $response = $this->actingAs($siswa)->get(route('master.classes.index'));

        $response->assertStatus(403);
    }
}
