<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UpdateMemberProfileAction
{
    /** @param array{name: string, date_of_birth: string, nickname?: string|null, phone?: string|null, playing_level?: string|null} $data */
    public function handle(User $member, array $data, ?UploadedFile $avatar = null): void
    {
        $oldAvatar = $member->avatar_path;
        $newAvatar = null;

        if ($avatar !== null) {
            $newAvatar = $avatar->store('member-avatars', 'local');
            if ($newAvatar === false) {
                throw new RuntimeException('Foto profil gagal disimpan.');
            }
            $data['avatar_path'] = $newAvatar;
        }

        try {
            $member->update($data);
        } catch (Throwable $exception) {
            if ($newAvatar !== null) {
                Storage::disk('local')->delete($newAvatar);
            }
            throw $exception;
        }

        if ($newAvatar !== null && $oldAvatar !== null) {
            Storage::disk('local')->delete($oldAvatar);
        }
    }
}
