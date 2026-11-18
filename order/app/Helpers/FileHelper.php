<?php

namespace App\Helpers;

class FileHelper {
    public static function getFilePaths($pattern, $targetFile) {
        $filePaths = array();
        $pattern = rtrim($pattern, '/') . '/*' . $targetFile;
        $files = glob($pattern);
        foreach ($files as $file) {
            if (is_file($file)) {
                $filePaths[] = $file;
            }
        }
        return $filePaths;
    }
}
