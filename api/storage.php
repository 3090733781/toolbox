<?php
function storagePut($name, $tmpFile) {
    return move_uploaded_file($tmpFile, __DIR__ . '/../uploads/' . $name);
}
function storageDelete($name) {
    $fp = __DIR__ . '/../uploads/' . $name;
    return file_exists($fp) ? unlink($fp) : false;
}
function storageList() {
    $dir = __DIR__ . '/../uploads/'; $files = [];
    if (!is_dir($dir)) return $files;
    foreach (scandir($dir) as $fn) {
        if ($fn === '.' || $fn === '..') continue;
        $fp = $dir . $fn;
        $files[] = ['name' => $fn, 'size' => filesize($fp), 'time' => filemtime($fp)];
    }
    usort($files, fn($a, $b) => $b['time'] - $a['time']);
    return $files;
}
function storageDownload($name) {
    $fp = __DIR__ . '/../uploads/' . basename($name);
    if (!file_exists($fp)) { http_response_code(404); exit; }
    $bn = basename($name);
    header('Content-Type: ' . (function_exists('mime_content_type') ? (mime_content_type($fp) ?: 'application/octet-stream') : 'application/octet-stream'));
    header('Content-Length: ' . filesize($fp));
    header("Content-Disposition: inline; filename=\"" . str_replace(['"', "\r", "\n"], '', $bn) . "\"; filename*=UTF-8''" . rawurlencode($bn));
    readfile($fp); exit;
}
