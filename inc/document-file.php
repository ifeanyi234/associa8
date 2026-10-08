<?php
function associa8_resolve_document_file(string $relativePath): ?string
{
    if (!preg_match('#^uploads/documents/([^/\\\\]+)$#', $relativePath, $matches)) {
        return null;
    }

    $uploadDirectory = realpath(__DIR__ . '/../admin/uploads/documents');
    if ($uploadDirectory === false) {
        return null;
    }

    $candidatePath = $uploadDirectory . DIRECTORY_SEPARATOR . $matches[1];
    if (is_link($candidatePath)) {
        return null;
    }

    $filePath = realpath($candidatePath);
    if ($filePath === false || dirname($filePath) !== $uploadDirectory || !is_file($filePath)) {
        return null;
    }

    return $filePath;
}
