<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Upload a new media file.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Media::class);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp3,wav,mp4,webm|max:10240', // max 10MB
            'mediable_id' => 'nullable|integer',
            'mediable_type' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Store in a private directory inside storage/app
        $path = $file->storeAs('media', $fileName);

        $media = Media::create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'mediable_id' => $request->mediable_id,
            'mediable_type' => $request->mediable_type,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Media uploaded successfully',
            'media' => $media,
            'url' => route('media.show', $media->id)
        ]);
    }

    /**
     * Securely serve a media file.
     */
    public function show(Media $media)
    {
        $this->authorize('view', $media);

        if (!Storage::exists($media->file_path)) {
            abort(404, 'File not found');
        }

        return response()->file(Storage::path($media->file_path), [
            'Content-Type' => $media->mime_type,
        ]);
    }

    /**
     * Delete a media file.
     */
    public function destroy(Media $media)
    {
        $this->authorize('delete', $media);

        if (Storage::exists($media->file_path)) {
            Storage::delete($media->file_path);
        }

        $media->delete();

        return response()->json(['message' => 'Media deleted successfully']);
    }
}
