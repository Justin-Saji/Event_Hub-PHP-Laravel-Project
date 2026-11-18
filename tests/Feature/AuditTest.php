<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    private $testEvent;
    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testEvent = Event::create([
            'event_name' => 'Tech Innovations Summit 2026',
            'event_date' => Carbon::now()->addDays(10)->toDateString(),
            'description' => 'A premier gathering of software developers and engineers.',
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin Tester',
            'email' => 'admin@test.com',
            'password' => Hash::make('AdminSecret123!'),
            'is_admin' => true,
        ]);
    }

    // 1. Homepage loads successfully
    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('EventHub');
    }

    // 2. Events are fetched from database
    public function test_events_are_fetched_from_database(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Tech Innovations Summit 2026');
    }

    // 3. Only upcoming events are displayed
    public function test_only_upcoming_events_are_displayed(): void
    {
        $pastEvent = Event::create([
            'event_name' => 'Old Expired Event 2020',
            'event_date' => Carbon::now()->subMonths(3)->toDateString(),
            'description' => 'Past event description',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Old Expired Event 2020');
    }

    // 4. Register Now opens the correct event registration form
    public function test_register_now_opens_correct_event_form(): void
    {
        $response = $this->get("/events/{$this->testEvent->id}/register");
        $response->assertStatus(200);
        $response->assertSee('Tech Innovations Summit 2026');
    }

    // 5. Invalid event IDs are rejected
    public function test_invalid_event_ids_are_rejected(): void
    {
        $response = $this->get('/events/999999/register');
        $response->assertStatus(404);
    }

    // 6-8. Registration validation checks
    public function test_empty_name_is_rejected(): void
    {
        $response = $this->post("/events/{$this->testEvent->id}/register", [
            'user_name' => '',
            'email' => 'test@example.com',
            'event_id' => $this->testEvent->id,
        ]);
        $response->assertSessionHasErrors(['user_name']);
    }

    public function test_empty_email_is_rejected(): void
    {
        $response = $this->post("/events/{$this->testEvent->id}/register", [
            'user_name' => 'John Doe',
            'email' => '',
            'event_id' => $this->testEvent->id,
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $response = $this->post("/events/{$this->testEvent->id}/register", [
            'user_name' => 'John Doe',
            'email' => 'not-a-valid-email',
            'event_id' => $this->testEvent->id,
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    // 9-11. Valid registration persistence and confirmation
    public function test_valid_registration_is_saved_and_confirmed(): void
    {
        $response = $this->post("/events/{$this->testEvent->id}/register", [
            'user_name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'event_id' => $this->testEvent->id,
        ]);

        $reg = Registration::where('email', 'jane.smith@example.com')->first();
        $this->assertNotNull($reg);
        $this->assertEquals($this->testEvent->id, $reg->event_id);

        $response->assertRedirect(route('registrations.success', $reg));

        $successRes = $this->get(route('registrations.success', $reg));
        $successRes->assertStatus(200);
        $successRes->assertSee('Jane Smith');
        $successRes->assertSee('Tech Innovations Summit 2026');
    }

    // 12. Input preservation on validation error
    public function test_registration_details_preserved_on_validation_failure(): void
    {
        $response = $this->post("/events/{$this->testEvent->id}/register", [
            'user_name' => 'Preserved Name',
            'email' => 'bad-email',
            'event_id' => $this->testEvent->id,
        ]);
        $response->assertSessionHas('_old_input.user_name', 'Preserved Name');
    }

    // 13-17. Admin authentication and authorization
    public function test_admin_can_log_in(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'AdminSecret123!',
        ]);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);
    }

    public function test_incorrect_credentials_are_rejected(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'WrongPassword',
        ]);
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_normal_users_cannot_access_admin_pages(): void
    {
        $normalUser = User::create([
            'name' => 'Regular Member',
            'email' => 'member@test.com',
            'password' => Hash::make('Member123!'),
            'is_admin' => false,
        ]);

        $response = $this->actingAs($normalUser)->get('/admin/dashboard');
        $response->assertRedirect(route('admin.login'));
    }

    public function test_guests_cannot_access_admin_pages(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('admin.login'));
    }

    public function test_logout_works(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/logout');
        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    // 18. Back-button caching prevention
    public function test_back_button_caching_is_prevented_on_admin_pages(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertHeader('Cache-Control');
        $cacheHeader = $response->headers->get('Cache-Control');
        $this->assertTrue(str_contains($cacheHeader, 'no-cache') || str_contains($cacheHeader, 'no-store'));
    }

    // 19-25. Admin management actions
    public function test_admin_dashboard_metrics_and_actions(): void
    {
        $reg = Registration::create([
            'user_name' => 'Attendee Alpha',
            'email' => 'alpha@test.com',
            'event_id' => $this->testEvent->id,
        ]);

        // Dashboard overview
        $dash = $this->actingAs($this->adminUser)->get('/admin/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('Total Registrations');

        // All registrations index
        $index = $this->actingAs($this->adminUser)->get('/admin/registrations');
        $index->assertStatus(200);
        $index->assertSee('Attendee Alpha');

        // View single registration
        $show = $this->actingAs($this->adminUser)->get("/admin/registrations/{$reg->id}");
        $show->assertStatus(200);
        $show->assertSee('Attendee Alpha');
        $show->assertSee('alpha@test.com');

        // Delete registration
        $del = $this->actingAs($this->adminUser)->delete("/admin/registrations/{$reg->id}");
        $del->assertRedirect(route('admin.registrations.index'));
        $this->assertNull(Registration::find($reg->id));
    }
}
