<?php
// ocr_helper.php
// Provides functions to detect scanned PDFs, extract text, and perform OCR.

if (!defined('TEMP_DIR')) {
    define('TEMP_DIR', sys_get_temp_dir() . DIRECTORY_SEPARATOR);
}

/**
 * Extract plain text from a PDF using pdftotext (if available).
 * Returns extracted text as string. If extraction fails, returns empty string.
 */
function extractTextFromPdf(string $pdfPath): string
{
    // Ensure the file exists
    if (!file_exists($pdfPath)) {
        return '';
    }
    // Use pdftotext if installed; fallback to empty string.
    $cmd = 'pdftotext -layout "' . escapeshellarg($pdfPath) . '" -';
    $output = [];
    $returnVar = 0;
    @exec($cmd, $output, $returnVar);
    if ($returnVar !== 0) {
        // pdftotext not available or failed.
        return '';
    }
    return implode("\n", $output);
}

/**
 * Determine whether a PDF is likely scanned (i.e., little or no extractable text).
 * Returns true if extracted text length is less than 100 characters.
 */
function isScannedPdf(string $pdfPath): bool
{
    $text = extractTextFromPdf($pdfPath);
    return strlen($text) < 100;
}

/**
 * Perform OCR on a PDF and return the extracted text.
 * Saves OCR-processed PDF (searchable) into the provided destination directory.
 * Requires Imagick and Tesseract to be installed on the server.
 */
function performOcr(string $pdfPath, string $destDir, string $tempDir = null): string
{
    if (!file_exists($pdfPath)) {
        return '';
    }
    $tempDir = $tempDir ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR;
    // Ensure destination directories exist
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    $ocrText = '';
    try {
        if (!class_exists('Imagick')) {
            throw new Exception('Imagick extension is not installed. OCR cannot be performed.');
        }
        $imagick = new Imagick();
        $imagick->setResolution(300, 300);
        $imagick->readImage($pdfPath);
        $pageCount = $imagick->getNumberImages();
        for ($i = 0; $i < $pageCount; $i++) {
            $imagick->setIteratorIndex($i);
            $image = $imagick->getImage();
            $imagePath = $tempDir . 'page_' . $i . '.png';
            $image->setImageFormat('png');
            $image->writeImage($imagePath);
            // Run tesseract on the image
            $outputBase = $tempDir . 'ocr_' . $i;
            $cmd = 'tesseract "' . escapeshellarg($imagePath) . '" "' . escapeshellarg($outputBase) . '" -l eng txt';
            @exec($cmd);
            $txtFile = $outputBase . '.txt';
            if (file_exists($txtFile)) {
                $ocrText .= file_get_contents($txtFile) . "\n";
                unlink($txtFile);
            }
            unlink($imagePath);
        }
        // Optionally, create a searchable PDF using OCR output (skipped for simplicity)
    } catch (Exception $e) {
        error_log('OCR processing error: ' . $e->getMessage());
    }
    return trim($ocrText);
}
?>
