<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Upload image for vehicle
     */
    public function uploadVehicleImage(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120' // 5MB
            ]
        ]);

        try {
            // Remove existing image from collection
            $vehicle->clearMediaCollection('images');

            // Add new image
            $media = $vehicle
                ->addMediaFromRequest('image')
                ->toMediaCollection('images');

            return response()->json([
                'message' => 'Vehicle image uploaded successfully',
                'media' => [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'url' => $media->getUrl(),
                    'thumb_url' => $media->getUrl('thumb'),
                    'preview_url' => $media->getUrl('preview'),
                ]
            ]);

        } catch (FileDoesNotExist $e) {
            return response()->json(['error' => 'File does not exist'], 400);
        } catch (FileIsTooBig $e) {
            return response()->json(['error' => 'File is too big'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to upload file'], 500);
        }
    }

    /**
     * Upload document for vehicle
     */
    public function uploadVehicleDocument(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $request->validate([
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpeg,jpg,png',
                'max:10240' // 10MB
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500']
        ]);

        try {
            $media = $vehicle
                ->addMediaFromRequest('document')
                ->usingName($request->name)
                ->withCustomProperties([
                    'description' => $request->description ?? '',
                ])
                ->toMediaCollection('documents');

            return response()->json([
                'message' => 'Vehicle document uploaded successfully',
                'media' => [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'url' => $media->getUrl(),
                    'description' => $media->getCustomProperty('description'),
                ]
            ]);

        } catch (FileDoesNotExist $e) {
            return response()->json(['error' => 'File does not exist'], 400);
        } catch (FileIsTooBig $e) {
            return response()->json(['error' => 'File is too big'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to upload document'], 500);
        }
    }

    /**
     * Get all media for vehicle
     */
    public function getVehicleMedia(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('view', $vehicle);

        $images = $vehicle->getMedia('images')->map(function (Media $media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'url' => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
                'preview_url' => $media->getUrl('preview'),
                'created_at' => $media->created_at,
            ];
        });

        $documents = $vehicle->getMedia('documents')->map(function (Media $media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'url' => $media->getUrl(),
                'description' => $media->getCustomProperty('description'),
                'created_at' => $media->created_at,
            ];
        });

        return response()->json([
            'vehicle_id' => $vehicle->id,
            'images' => $images,
            'documents' => $documents,
        ]);
    }

    /**
     * Delete media
     */
    public function deleteMedia(Request $request, Media $media): JsonResponse
    {
        // Check if user can modify the media's model
        $model = $media->model;
        $this->authorize('update', $model);

        try {
            $mediaName = $media->name;
            $media->delete();

            return response()->json([
                'message' => "Media '{$mediaName}' deleted successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete media'], 500);
        }
    }

    /**
     * Download media
     */
    public function downloadMedia(Media $media): mixed
    {
        // Check if user can view the media's model
        $model = $media->model;
        $this->authorize('view', $model);

        try {
            return response()->download($media->getPath(), $media->file_name);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to download file'], 404);
        }
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $request->validate([
            'files' => ['required', 'array', 'max:10'],
            'files.*' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,webp,pdf',
                'max:10240' // 10MB
            ],
            'collection' => ['required', 'string', 'in:images,documents']
        ]);

        $uploadedFiles = [];
        $errors = [];

        foreach ($request->file('files') as $index => $file) {
            try {
                if ($request->collection === 'images') {
                    // For images, clear existing and upload only the last one
                    $vehicle->clearMediaCollection('images');
                    $media = $vehicle
                        ->addMedia($file)
                        ->toMediaCollection('images');
                } else {
                    // For documents, allow multiple
                    $media = $vehicle
                        ->addMedia($file)
                        ->toMediaCollection('documents');
                }

                $uploadedFiles[] = [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'url' => $media->getUrl(),
                ];

            } catch (\Exception $e) {
                $errors[] = [
                    'file_index' => $index,
                    'file_name' => $file->getClientOriginalName(),
                    'error' => 'Failed to upload file'
                ];
            }
        }

        return response()->json([
            'message' => 'Files upload completed',
            'uploaded' => $uploadedFiles,
            'errors' => $errors,
            'stats' => [
                'total' => count($request->file('files')),
                'success' => count($uploadedFiles),
                'failed' => count($errors)
            ]
        ]);
    }
}
