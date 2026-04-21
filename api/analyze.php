<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    jsonResponse(['error' => 'Unauthorized.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$currentBalance = getWalletBalance();
if ($currentBalance <= 0) {
    jsonResponse(['error' => 'No coins remaining. Each analysis costs 1 Demo Coin.'], 402);
}

if (!isset($_FILES['image'])) {
    jsonResponse(['error' => 'No image file provided.'], 400);
}

$note = trim((string)($_POST['note'] ?? ''));
$file = $_FILES['image'];

if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    jsonResponse(['error' => uploadErrorMessage((int)$file['error'])], 400);
}

if ((int)$file['size'] > MAX_UPLOAD_BYTES) {
    jsonResponse(['error' => 'File is too large. Max size is 5MB.'], 400);
}

$tmpPath = (string)$file['tmp_name'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = (string)$finfo->file($tmpPath);

$allowedMimeToExt = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

if (!isset($allowedMimeToExt[$mimeType])) {
    jsonResponse(['error' => 'Invalid file type. Only JPG, PNG, WEBP are allowed.'], 400);
}

if (!is_dir(UPLOADS_DIR) && !mkdir(UPLOADS_DIR, 0775, true) && !is_dir(UPLOADS_DIR)) {
    jsonResponse(['error' => 'Upload directory is not available.'], 500);
}

$filename = uuidV4() . '.' . $allowedMimeToExt[$mimeType];
$destinationPath = rtrim(UPLOADS_DIR, '/\\') . DIRECTORY_SEPARATOR . $filename;
$webPath = trim(UPLOADS_WEB_PATH, '/\\') . '/' . $filename;

if (!move_uploaded_file($tmpPath, $destinationPath)) {
    jsonResponse(['error' => 'Failed to save uploaded file.'], 500);
}

try {
    [$apiBase64, $apiMime] = optimizeForApi($destinationPath, $mimeType);
} catch (RuntimeException $exception) {
    @unlink($destinationPath);
    jsonResponse(['error' => $exception->getMessage()], 500);
}

try {
    [$tags, $description] = analyzeImageWithRetry($apiBase64, $apiMime, $note);
} catch (RuntimeException $exception) {
    @unlink($destinationPath);
    $code = $exception->getCode();
    $status = ($code >= 400 && $code < 600) ? $code : 500;
    jsonResponse(['error' => $exception->getMessage()], $status);
}

$saved = false;
try {
    require_once __DIR__ . '/../config/db.php';
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO product_images (filename, file_path, user_note, tags, description) VALUES (:filename, :file_path, :user_note, :tags, :description)'
    );
    $stmt->execute([
        ':filename' => $filename,
        ':file_path' => $webPath,
        ':user_note' => $note !== '' ? $note : null,
        ':tags' => json_encode($tags, JSON_UNESCAPED_UNICODE),
        ':description' => $description,
    ]);
    $saved = true;
} catch (Throwable $exception) {
    error_log('DB insert failed for image ' . $filename . ': ' . $exception->getMessage());
}

$newBalance = deductCoin();

jsonResponse([
    'success' => true,
    'saved' => $saved,
    'coins' => max(0, $newBalance),
    'data' => [
        'image_path' => pathToUrl($webPath),
        'tags' => $tags,
        'description' => $description,
        'note' => $note,
    ],
]);

function analyzeImageWithRetry(string $base64Image, string $mimeType, string $note): array
{
    // AI functionality removed. Returning default tags and description.
    $tags = ['product', 'image', 'uploaded'];
    $description = 'This is a product image that has been uploaded. AI analysis is disabled.';

    return [$tags, $description];
}

function requestAnthropicText(string $base64Image, string $mimeType, string $note, int $attempt): string
{
    if (ANTHROPIC_API_KEY === '' || ANTHROPIC_API_KEY === 'replace-with-your-api-key') {
        throw new RuntimeException('Anthropic API key is not configured.', 500);
    }

    $userText = 'Analyze this product image and generate tags and a description.';
    if ($note !== '') {
        $userText .= ' Additional context from the uploader: "' . $note . '"';
    }
    if ($attempt > 1) {
        $userText .= ' Return strict JSON only with keys "tags" and "description".';
    }

    $payload = [
        'model' => ANTHROPIC_MODEL,
        'max_tokens' => 1024,
        'system' => SYSTEM_PROMPT,
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => $mimeType,
                            'data' => $base64Image,
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => $userText,
                    ],
                ],
            ],
        ],
    ];

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'content-type: application/json',
            'x-api-key: ' . ANTHROPIC_API_KEY,
            'anthropic-version: ' . ANTHROPIC_VERSION,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => ANTHROPIC_TIMEOUT_SECONDS,
    ]);

    $response = curl_exec($ch);
    $curlErrNo = curl_errno($ch);
    $curlErr = curl_error($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErrNo !== 0) {
        if ($curlErrNo === CURLE_OPERATION_TIMEDOUT) {
            throw new RuntimeException('Request to Anthropic timed out.', 504);
        }
        throw new RuntimeException('Network error while contacting Anthropic: ' . $curlErr, 502);
    }

    if ($statusCode >= 400) {
        throw new RuntimeException('Anthropic API returned an error (HTTP ' . $statusCode . ').', 502);
    }

    $body = json_decode((string)$response, true);
    if (!is_array($body)) {
        throw new RuntimeException('Invalid response from Anthropic API.', 502);
    }

    if (!isset($body['content']) || !is_array($body['content'])) {
        throw new RuntimeException('Unexpected Anthropic response shape.', 502);
    }

    foreach ($body['content'] as $block) {
        if (is_array($block) && ($block['type'] ?? '') === 'text' && isset($block['text'])) {
            return (string)$block['text'];
        }
    }

    throw new RuntimeException('Anthropic response does not include text content.', 502);
}

function parseModelJson(string $text): array
{
    $parsed = json_decode(trim($text), true);
    if (is_array($parsed)) {
        return $parsed;
    }

    if (preg_match('/\{[\s\S]*\}/', $text, $matches) === 1) {
        $parsed = json_decode($matches[0], true);
        if (is_array($parsed)) {
            return $parsed;
        }
    }

    throw new RuntimeException('AI returned an invalid JSON payload.', 500);
}

function normalizeTags(array $tags): array
{
    $clean = [];
    foreach ($tags as $tag) {
        $value = strtolower(trim((string)$tag));
        if ($value === '') {
            continue;
        }
        $clean[$value] = true;
    }

    return array_slice(array_keys($clean), 0, 12);
}

function pathToUrl(string $relativePath): string
{
    $normalized = str_replace('\\', '/', ltrim($relativePath, '/\\'));
    if (APP_BASE_URL === '') {
        return $normalized;
    }

    return rtrim(APP_BASE_URL, '/') . '/' . $normalized;
}

function uploadErrorMessage(int $code): string
{
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'File is too large.';
        case UPLOAD_ERR_PARTIAL:
            return 'File upload was incomplete.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file uploaded.';
        default:
            return 'Upload failed.';
    }
}

function uuidV4(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

    $hex = bin2hex($bytes);

    return sprintf(
        '%s-%s-%s-%s-%s',
        substr($hex, 0, 8),
        substr($hex, 8, 4),
        substr($hex, 12, 4),
        substr($hex, 16, 4),
        substr($hex, 20, 12)
    );
}

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function optimizeForApi(string $filePath, string $originalMime): array
{
    if (!extension_loaded('gd')) {
        return [base64_encode((string)file_get_contents($filePath)), $originalMime];
    }

    $gd = loadGdImage($filePath, $originalMime);
    if ($gd === null) {
        return [base64_encode((string)file_get_contents($filePath)), $originalMime];
    }

    $cropped = autoCrop($gd);
    imagedestroy($gd);

    $resized = resizeToMaxWidth($cropped, 400);
    if ($resized !== $cropped) {
        imagedestroy($cropped);
    }

    ob_start();
    imagejpeg($resized, null, 85);
    $jpegData = ob_get_clean();
    imagedestroy($resized);

    return [base64_encode((string)$jpegData), 'image/jpeg'];
}

function loadGdImage(string $filePath, string $mime): ?GdImage
{
    switch ($mime) {
        case 'image/jpeg':
            return imagecreatefromjpeg($filePath) ?: null;
        case 'image/png':
            return imagecreatefrompng($filePath) ?: null;
        case 'image/webp':
            return imagecreatefromwebp($filePath) ?: null;
        default:
            return null;
    }
}

function autoCrop(GdImage $img): GdImage
{
    $w = imagesx($img);
    $h = imagesy($img);

    if ($w < 3 || $h < 3) {
        return $img;
    }

    $bgColor = detectBackgroundColor($img, $w, $h);
    $tolerance = 45;
    $padding = 10;

    $top = 0;
    $bottom = $h - 1;
    $left = 0;
    $right = $w - 1;

    for ($y = 0; $y < $h; $y++) {
        if (!isRowBackground($img, $y, $w, $bgColor, $tolerance)) {
            $top = $y;
            break;
        }
    }

    for ($y = $h - 1; $y > $top; $y--) {
        if (!isRowBackground($img, $y, $w, $bgColor, $tolerance)) {
            $bottom = $y;
            break;
        }
    }

    for ($x = 0; $x < $w; $x++) {
        if (!isColBackground($img, $x, $top, $bottom, $bgColor, $tolerance)) {
            $left = $x;
            break;
        }
    }

    for ($x = $w - 1; $x > $left; $x--) {
        if (!isColBackground($img, $x, $top, $bottom, $bgColor, $tolerance)) {
            $right = $x;
            break;
        }
    }

    $left = max(0, $left - $padding);
    $top = max(0, $top - $padding);
    $right = min($w - 1, $right + $padding);
    $bottom = min($h - 1, $bottom + $padding);

    $cropW = $right - $left + 1;
    $cropH = $bottom - $top + 1;

    if ($cropW < 20 || $cropH < 20 || ($cropW >= $w - 2 && $cropH >= $h - 2)) {
        return $img;
    }

    $cropped = imagecrop($img, [
        'x' => $left,
        'y' => $top,
        'width' => $cropW,
        'height' => $cropH,
    ]);

    return ($cropped instanceof GdImage) ? $cropped : $img;
}

function detectBackgroundColor(GdImage $img, int $w, int $h): array
{
    $corners = [
        imagecolorat($img, 0, 0),
        imagecolorat($img, $w - 1, 0),
        imagecolorat($img, 0, $h - 1),
        imagecolorat($img, $w - 1, $h - 1),
    ];

    $rSum = 0;
    $gSum = 0;
    $bSum = 0;

    foreach ($corners as $c) {
        $rSum += ($c >> 16) & 0xFF;
        $gSum += ($c >> 8) & 0xFF;
        $bSum += $c & 0xFF;
    }

    return [
        (int)round($rSum / 4),
        (int)round($gSum / 4),
        (int)round($bSum / 4),
    ];
}

function colorDistance(int $pixel, array $bg): int
{
    $r = (($pixel >> 16) & 0xFF) - $bg[0];
    $g = (($pixel >> 8) & 0xFF) - $bg[1];
    $b = ($pixel & 0xFF) - $bg[2];

    return (int)round(sqrt($r * $r + $g * $g + $b * $b));
}

function isRowBackground(GdImage $img, int $y, int $w, array $bg, int $tol): bool
{
    $step = max(1, (int)floor($w / 30));
    for ($x = 0; $x < $w; $x += $step) {
        if (colorDistance(imagecolorat($img, $x, $y), $bg) > $tol) {
            return false;
        }
    }
    return true;
}

function isColBackground(GdImage $img, int $x, int $yStart, int $yEnd, array $bg, int $tol): bool
{
    $h = $yEnd - $yStart + 1;
    $step = max(1, (int)floor($h / 30));
    for ($y = $yStart; $y <= $yEnd; $y += $step) {
        if (colorDistance(imagecolorat($img, $x, $y), $bg) > $tol) {
            return false;
        }
    }
    return true;
}

function resizeToMaxWidth(GdImage $img, int $maxWidth): GdImage
{
    $w = imagesx($img);
    $h = imagesy($img);

    if ($w <= $maxWidth) {
        return $img;
    }

    $ratio = $maxWidth / $w;
    $newW = $maxWidth;
    $newH = max(1, (int)round($h * $ratio));

    $resized = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);

    return $resized;
}
