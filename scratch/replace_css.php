<?php
$base = BASE_URL; // defined in config.php
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('c:/xampp/htdocs/studyshare'));
foreach ($dir as $file) {
    if ($file->isFile()) {
        $path = $file->getPathname();
        if (preg_match('/\.(php|html)$/i', $path)) {
            $content = file_get_contents($path);
            // replace href="../assets/css/" and href="assets/css/"
            $content = preg_replace('#href="(?:\../)?assets/css/#', 'href="' . $base . '/assets/css/', $content);
            file_put_contents($path, $content);
        }
    }
}
?>
