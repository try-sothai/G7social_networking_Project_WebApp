<?php
// Cloudinary upload helper — persists files across Render server restarts

function lc_cloudinary_upload(string $filePath, string $folder = 'social7'): ?string
{
    $cloudName = lc_env('CLOUDINARY_CLOUD_NAME');
    $apiKey    = lc_env('CLOUDINARY_API_KEY');
    $apiSecret = lc_env('CLOUDINARY_API_SECRET');

    if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
        return null;
    }

    $timestamp = time();
    $paramsToSign = 'folder=' . $folder . '&timestamp=' . $timestamp;
    $signature    = sha1($paramsToSign . $apiSecret);

    // Detect resource type
    $mime = (string)@mime_content_type($filePath);
    $resourceType = str_starts_with($mime, 'video/') ? 'video' : 'image';

    $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/{$resourceType}/upload");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => [
            'file'      => new CURLFile($filePath),
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder'    => $folder,
        ],
        CURLOPT_TIMEOUT => 120,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['secure_url'] ?? null;
    }

    error_log('Cloudinary upload error [' . $httpCode . ']: ' . ($curlErr ?: $response));
    return null;
}

function lc_cloudinary_enabled(): bool
{
    return lc_env('CLOUDINARY_CLOUD_NAME') !== ''
        && lc_env('CLOUDINARY_API_KEY') !== ''
        && lc_env('CLOUDINARY_API_SECRET') !== '';
}
