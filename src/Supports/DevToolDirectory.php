<?php

namespace Neartech\DevTool\Supports;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DevToolDirectory
{
    /**
     * Easy way to get rid of the dots that scandir() picks up in Linux environments:
     * @param $directory
     * @param $ignoreFiles
     * @return array|false
     */
    public function scanDirectory(string $directory,array $ignoreFiles = [])
    {
        $result = [];
        if (!is_dir($directory)) {
            return $result;
        }

        try {
            return array_diff(scandir($directory), array_merge(['.', '..'], $ignoreFiles));
        } catch (Exception $exception) {
            return $result;
        }
    }

    public function getDirAllFiles(string $directory, $relativePath = false)
    {
        $fileList = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $path = $file->getPathname();
            if ($relativePath) {
                $path = str_replace($directory, '', $path);
                $path = ltrim($path, '/\\');
            }
            $fileList[] = $path;
        }
        return $fileList;
    }
}
