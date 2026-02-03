<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_to_setup_when_school_not_completed(): void
    {
        Setting::create([
            'school_name' => 'Test School',
            'setup_completed_school_info' => false,
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertRedirect(route('setup.overview'))
            ->assertSessionHas('missing_setup', 'school')
            ->assertSessionHas('error');
    }

    public function test_inactive_user_is_logged_out_and_redirected_to_deactivated(): void
    {
        // Ensure setup is complete so we hit the active check path.
        Setting::create([
            'school_name' => 'Test School',
            'setup_completed_school_info' => true,
            'setup_completed_academic' => true,
            'setup_completed_event_and_announcements' => true,
            'setup_completed_time_table_and_attendance' => true,
            'setup_completed_finance' => true,
        ]);

        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('deactivated'));
        $this->assertGuest();
    }
}
