<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function view(User $user, Media $media): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('kurikulum')) {
            return true;
        }
        
        // If it's a question version, verify question access
        if ($media->mediable_type === \App\Models\QuestionVersion::class) {
            $version = \App\Models\QuestionVersion::find($media->mediable_id);
            if ($version && $version->question) {
                if ($user->can('view', $version->question)) {
                    return true;
                }
            }
            // Allow participants in an active exam session to view
            return true; // We can restrict this further based on active exam sessions if needed
        }

        return $media->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        // Users who can create or update questions can upload media
        return $user->hasPermissionTo('questions.create') || $user->hasPermissionTo('questions.update');
    }

    public function delete(User $user, Media $media): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('kurikulum')) {
            return true;
        }

        return $media->created_by === $user->id;
    }
}
