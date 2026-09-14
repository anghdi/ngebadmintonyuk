<?php

namespace App\Actions;

use App\Models\User;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
            $contents = $this->compressAvatar($avatar);
            $newAvatar = 'member-avatars/'.Str::uuid().'.webp';
            if (! Storage::disk('local')->put($newAvatar, $contents)) {
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

    private function compressAvatar(UploadedFile $avatar): string
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('Kompresi foto membutuhkan GD dengan dukungan WebP.');
        }

        $source = @imagecreatefromstring($avatar->getContent());
        if ($source === false) {
            throw ValidationException::withMessages(['avatar' => 'Foto tidak dapat dibaca. Pilih foto lain.']);
        }

        $source = $this->orientAvatar($source, $avatar);
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, 512 / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($target === false) {
            throw new RuntimeException('Foto profil gagal diproses.');
        }
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        try {
            $encoded = imagewebp($target, null, 80);
            $contents = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        if (! $encoded || $contents === false || $contents === '' || @getimagesizefromstring($contents) === false) {
            throw new RuntimeException('Foto profil gagal dikompres.');
        }

        return $contents;
    }

    private function orientAvatar(GdImage $image, UploadedFile $avatar): GdImage
    {
        if ($avatar->getMimeType() !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }
        $metadata = @exif_read_data($avatar->getPathname(), 'IFD0');
        $orientation = is_array($metadata) ? ($metadata['Orientation'] ?? 1) : 1;
        if (in_array($orientation, [2, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        } elseif ($orientation === 4) {
            imageflip($image, IMG_FLIP_VERTICAL);
        }
        $angle = match ($orientation) {
            3 => 180, 5, 8 => 90, 6, 7 => -90, default => 0,
        };
        if ($angle === 0) {
            return $image;
        }
        $rotated = imagerotate($image, $angle, 0);
        if ($rotated === false) {
            throw new RuntimeException('Orientasi foto gagal diproses.');
        }

        return $rotated;
    }
}
