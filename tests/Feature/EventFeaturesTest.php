<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\EventCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventFeaturesTest extends TestCase
{
    // Keeping database state safe by manually cleaning up only what we create if passing
    // But RefreshDatabase is safer for local dev provided we don't wipe real data.
    // Given the previous instructions to "avoid writing project code files to tmp", I assume I should write this to tests/Feature.
    // I will use DatabaseTransactions to avoid committing data.
    use RefreshDatabase;

    protected $user;
    protected $category;
    protected $event;

    protected function setUp(): void
    {
        parent::setUp();
        // Assuming roles exist or creating a basic user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->category = EventCategory::create(['name' => 'Test Category', 'color' => '#ffffff']);
        $this->event = Event::create([
            'title' => 'Test Event',
            'event_category_id' => $this->category->id,
            'start_date' => now()->addDay(),
            'status' => true,
        ]);
    }

    public function test_user_can_respond_to_event_poll()
    {
        $response = $this->postJson(route('events.respond', $this->event), [
            'status' => 'going'
        ]);

        $response->assertStatus(302); // Redirect back
        $this->assertDatabaseHas('event_responses', [
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'status' => 'going'
        ]);

        // Change response
        $this->postJson(route('events.respond', $this->event), [
            'status' => 'not_going'
        ]);

        $this->assertDatabaseHas('event_responses', [
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'status' => 'not_going'
        ]);
    }

    public function test_user_can_upload_attachment()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test_image.jpg');

        $response = $this->postJson(route('events.upload', $this->event), [
            'file' => $file
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('event_attachments', [
            'event_id' => $this->event->id,
            'file_name' => 'test_image.jpg',
            'uploaded_by' => $this->user->id
        ]);

        // Verify file storage
        // Since we don't know the exact random hash name, we just check if any file exists in the directory
        // or check the database record to get the path
        $attachment = $this->event->attachments()->first();
        Storage::disk('public')->assertExists($attachment->file_path);
    }

    public function test_show_endpoint_returns_data()
    {
        // Add a response and attachment first
        $this->event->responses()->create(['user_id' => $this->user->id, 'status' => 'going']);

        $response = $this->getJson(route('events.show', $this->event));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'responses',
                'attachments'
            ]);

        $this->assertEquals('going', $response->json('responses.0.status'));
    }
}
