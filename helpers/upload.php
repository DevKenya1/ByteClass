<?php
function upload_profile_photo(array $file, string $prefix = 'user'): ?string {
    $allowed = ['image/jpeg','image/jpg','image/png','image/webp'];
    if (!in_array($file['type'], $allowed)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null;

    $ext   = match($file['type']) {
        'image/jpeg','image/jpg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };
    $fname = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest  = $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/uploads/profile_photos/' . $fname;

    // Resize using GD
    [$src_w, $src_h, $type] = getimagesize($file['tmp_name']);
    $max  = 200;
    $ratio= min($max/$src_w, $max/$src_h, 1);
    $nw   = (int)($src_w * $ratio);
    $nh   = (int)($src_h * $ratio);

    $canvas = imagecreatetruecolor($nw, $nh);

    // Preserve transparency for PNG
    if ($ext === 'png') {
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
    }

    $src = match($type) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($file['tmp_name']),
        IMAGETYPE_PNG  => imagecreatefrompng($file['tmp_name']),
        IMAGETYPE_WEBP => imagecreatefromwebp($file['tmp_name']),
        default        => null,
    };
    if (!$src) return null;

    imagecopyresampled($canvas, $src, 0, 0, 0, 0, $nw, $nh, $src_w, $src_h);

    $saved = match($ext) {
        'jpg'  => imagejpeg($canvas, $dest, 90),
        'png'  => imagepng($canvas, $dest),
        'webp' => imagewebp($canvas, $dest, 90),
        default=> false,
    };

    imagedestroy($canvas);
    imagedestroy($src);

    return $saved ? APP_URL . '/uploads/profile_photos/' . $fname : null;
}
