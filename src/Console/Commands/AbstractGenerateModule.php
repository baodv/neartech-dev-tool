<?php

namespace Neartech\DevTool\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Neartech\DevTool\Supports\DevToolDirectory;

abstract class AbstractGenerateModule extends Command
{
    const SUCCESS = 1;

    const FAILURE = 0;

    const MODE_DIR = 0755;

    /**
     * Validate argrument command
     * @param string $argument
     * @return bool
     */
    protected function regexArgument(string $argument): bool
    {
        if (!preg_match('/^[a-z0-9\-]+$/i', $argument)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $from
     * @param string $to
     * @return void
     */
    protected function publishStubs(string $from, string $to)
    {
        $this->createParentDirectory(File::dirname($to));

        if (File::isDirectory($from)) {
            $this->publishDirectory($from, $to);
        } else {
            File::copy($from, $to);
        }
    }

    /**
     * @param string $path
     * @return false
     */
    protected function createParentDirectory(string $path): bool
    {
        if (File::isDirectory($path)) {
            return false;
        }
        return File::makeDirectory($path, self::MODE_DIR, true);
    }

    /**
     * Coppy directory
     * @param string $from
     * @param string $to
     * @return void
     */
    protected function publishDirectory(string $from, string $to)
    {
        File::copyDirectory($from, $to);
    }

    /**
     * @return mixed
     */
    abstract protected function getStub();

    /**
     * Find and replace file name
     * @param string $pattern
     * @param string $directory
     * @return false|void
     */
    protected function renameFiles(string $pattern, string $directory)
    {
        $devToolDir = new DevToolDirectory();
        $paths = $devToolDir->scanDirectory($directory);

        if(empty($paths)) {
            return false;
        }

        foreach ($paths as $path) {
            $path = $directory . DIRECTORY_SEPARATOR . $path;
            $newPath = $this->replaceFileName($pattern, $path);
            rename($path, $newPath);

            $this->renameFiles($pattern, $newPath);
        }

    }

    /**
     * Search and replace file name
     * @param string $pattern
     * @param string $pathReplace
     * @return array|string|string[]
     */
    public function replaceFileName(string $pattern, string $pathReplace)
    {
        $fileRename = $this->keywordReplaces($pattern);
        $newPath = str_replace(array_keys($fileRename) , array_values($fileRename), $pathReplace);
        return $newPath;
    }

    /**
     * .stub => File name
     * {Module} => Namespace
     * {modules} => database , table
     * {MODULE} => constants
     * {migrate_date} => migration file name
     * @param string $txtReplace
     * @return array
     */
    public function keywordReplaces(string $txtReplace)
    {
        return [
            '.stub'          => '.php',
            '{Module}'       => ucfirst(Str::camel($txtReplace)),
            '{modules}'      => Str::plural(Str::snake(str_replace('-', '_', $txtReplace))),
            '{module}'       => Str::snake(str_replace('-', '_', $txtReplace)),
            '{MODULE}'       => strtoupper(Str::snake(str_replace('-', '_', $txtReplace))),
            '{migrate_date}' => Carbon::now(config('app.timezone'))->format('Y_m_d_His'),
        ];
    }

    /**
     * Search and replace content in files
     * @param string $module
     * @param string $location
     * @param $stubFile
     * @return bool|void
     */
    public function searchAndReplaceInFiles(string $module, string $location, $stubFile = null)
    {
        if (File::isFile($location)) {
            if (!$stubFile) {
                $stubFile = File::get($this->getStub());
            }

            return $this->searchAndReplaceInFile($module, $location, $stubFile);
        }

        $allFiles = $this->scanFileInModule($location);

        foreach ($allFiles as $file) {
            $fileContent = File::get($file);
            $this->searchAndReplaceInFile($module, $file, $fileContent);
        }
    }

    /**
     * Search and replace content in file
     * @param string $module
     * @param string $location
     * @param $file
     * @return bool
     */
    public function searchAndReplaceInFile(string $module, string $location, $file)
    {
        $replace = $this->keywordReplaces($module);
        $content = str_replace(array_keys($replace), $replace, $file);
        File::put($location, $content);
        return true;
    }

    /**
     * Get all files in directory
     * @param $directory
     * @return array
     */
    protected function scanFileInModule($directory)
    {
        $devToolDir = new DevToolDirectory();
        return $devToolDir->getDirAllFiles($directory);
    }

    /**
     * Delete Directory when error
     * @param $directory
     * @return false|void
     */
    protected function deleteDirectory($directory)
    {
        if (!File::isDirectory($directory)) {
            return false;
        }
        File::deleteDirectories($directory);
    }
}
