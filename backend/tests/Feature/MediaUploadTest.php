<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Vehicle $vehicle;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->tenant = Tenant::create([
            'name' => 'Test Company',
            'domain' => 'test.flotteq.local',
            'database' => 'test_company_db',
        ]);
        
        $this->tenant->makeCurrent();
        
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        
        $this->user->givePermissionTo([
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',
        ]);
        
        $this->vehicle = Vehicle::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_upload_vehicle_image_successfully(): void
    {
        $file = UploadedFile::fake()->image('vehicle.jpg', 800, 600);

        $response = $this->actingAs($this->user)
            ->postJson("/api/vehicles/{$this->vehicle->id}/media/image", [
                'image' => $file
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'media' => [
                    'id',
                    'name',
                    'file_name',
                    'mime_type',
                    'size',
                    'url',
                    'thumb_url',
                    'preview_url'
                ]
            ]);

        // Verify media was added to vehicle
        $this->assertCount(1, $this->vehicle->getMedia('images'));
        
        $media = $this->vehicle->getFirstMedia('images');
        $this->assertEquals('vehicle', $media->name);
        $this->assertEquals('image/jpeg', $media->mime_type);
    }

    public function test_upload_vehicle_image_validates_file_type(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson("/api/vehicles/{$this->vehicle->id}/media/image", [
                'image' => $file
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_upload_vehicle_image_validates_file_size(): void
    {
        $file = UploadedFile::fake()->create('large.jpg', 6000); // 6MB

        $response = $this->actingAs($this->user)
            ->postJson("/api/vehicles/{$this->vehicle->id}/media/image", [
                'image' => $file
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_upload_vehicle_image_replaces_existing(): void
    {
        // Upload first image
        $firstImage = UploadedFile::fake()->image('first.jpg');
        $this->actingAs($this->user)
            ->postJson("/api/vehicles/{$this->vehicle->id}/media/image", [
                'image' => $firstImage
            ]);

        $this->assertCount(1, $this->vehicle->getMedia('images'));

        // Upload second image
        $secondImage = UploadedFile::fake()->image('second.jpg');
        $response = $this->actingAs($this->user)
            ->postJson("/api/vehicles/{$this->vehicle->id}/media/image", [
                'image' => $secondImage
            ]);

        $response->assertStatus(200);
        
        // Should still have only one image (replaced)
        $this->vehicle->refresh();
        $this->assertCount(1, $this->vehicle->getMedia('images'));
        $this->assertEquals('second', $this->vehicle->getFirstMedia('images')->name);
    }

    public function test_upload_vehicle_document_successfully(): void
    {
        $file = UploadedFile::fake()->create('manual.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson("/api/vehicles/{$this->vehicle->id}/media/document", [
                'document' => $file,
                'name' => 'Vehicle Manual',
                'description' => 'Official vehicle manual document'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'media' => [
                    'id',
                    'name',
                    'file_name',
                    'mime_type',
                    'size',
                    'url',
                    'description'
                ]
            ]);

        // Verify document was added
        $this->assertCount(1, $this->vehicle->getMedia('documents'));
        
        $media = $this->vehicle->getFirstMedia('documents');
        $this->assertEquals('Vehicle Manual', $media->name);
        $this->assertEquals('Official vehicle manual document', $media->getCustomProperty('description'));
    }

    public function test_upload_multiple_documents(): void
    {
        $file1 = UploadedFile::fake()->create('manual.pdf', 1000, 'application/pdf');
        $file2 = UploadedFile::fake()->create('insurance.pdf', 800, 'application/pdf');

        // Upload first document
        $this->actingAs($this->user)
            ->postJson("/api/vehicles/{$this->vehicle->id}/media/document", [
                'document' => $file1,
                'name' => 'Manual'
            ]);

        // Upload second document
        $this->actingAs($this->user)
            ->postJson("/api/vehicles/{$this->vehicle->id}/media/document", [
                'document' => $file2,
                'name' => 'Insurance'
            ]);

        // Should have both documents
        $this->vehicle->refresh();
        $this->assertCount(2, $this->vehicle->getMedia('documents'));
    }

    public function test_get_vehicle_media(): void
    {
        // Add image and document
        $image = UploadedFile::fake()->image('vehicle.jpg');
        $document = UploadedFile::fake()->create('manual.pdf', 1000, 'application/x-empty');

        $this->vehicle->addMedia($image)->toMediaCollection('images');
        $this->vehicle->addMedia($document)->usingName('Manual')->toMediaCollection('documents');

        $response = $this->actingAs($this->user)
            ->getJson("/api/vehicles/{$this->vehicle->id}/media");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'vehicle_id',
                'images' => [
                    '*' => ['id', 'name', 'file_name', 'url', 'thumb_url', 'preview_url']
                ],
                'documents' => [
                    '*' => ['id', 'name', 'file_name', 'url', 'description']
                ]
            ]);

        $this->assertCount(1, $response->json('images'));
        $this->assertCount(1, $response->json('documents'));
    }

    public function test_delete_media(): void
    {
        $file = UploadedFile::fake()->image('vehicle.jpg');
        $media = $this->vehicle->addMedia($file)->toMediaCollection('images');

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/media/{$media->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => "Media '{$media->name}' deleted successfully"
            ]);

        // Verify media was deleted
        $this->assertCount(0, $this->vehicle->fresh()->getMedia('images'));
    }

    public function test_upload_multiple_files(): void
    {
        $files = [
            UploadedFile::fake()->create('doc1.pdf', 1000, 'application/pdf'),
            UploadedFile::fake()->create('doc2.pdf', 800, 'application/pdf'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/vehicles/{$this->vehicle->id}/media/multiple", [
                'files' => $files,
                'collection' => 'documents'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'uploaded',
                'errors',
                'stats' => ['total', 'success', 'failed']
            ]);

        $this->assertEquals(2, $response->json('stats.total'));
        $this->assertEquals(2, $response->json('stats.success'));
        $this->assertEquals(0, $response->json('stats.failed'));

        // Verify documents were uploaded
        $this->assertCount(2, $this->vehicle->getMedia('documents'));
    }

    public function test_unauthorized_user_cannot_upload(): void
    {
        $otherUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $file = UploadedFile::fake()->image('vehicle.jpg');

        $response = $this->actingAs($otherUser)
            ->postJson("/api/vehicles/{$this->vehicle->id}/media/image", [
                'image' => $file
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_view_media(): void
    {
        $otherUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/vehicles/{$this->vehicle->id}/media");

        $response->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_delete_media(): void
    {
        $file = UploadedFile::fake()->image('vehicle.jpg');
        $media = $this->vehicle->addMedia($file)->toMediaCollection('images');

        $otherUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/media/{$media->id}");

        $response->assertStatus(403);
    }

    public function test_download_media(): void
    {
        $file = UploadedFile::fake()->create('manual.pdf', 1000, 'application/x-empty');
        $media = $this->vehicle->addMedia($file)->toMediaCollection('documents');

        $response = $this->actingAs($this->user)
            ->get("/api/media/{$media->id}/download");

        $response->assertStatus(200);
        $this->assertEquals('application/x-empty', $response->headers->get('content-type'));
    }
}
