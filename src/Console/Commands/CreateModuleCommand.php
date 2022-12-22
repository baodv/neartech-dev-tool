<?php

namespace Neartech\DevTool\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Neartech\DevTool\Console\Commands\AbstractGenerateModule;
use Symfony\Component\Process\Process;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class CreateModuleCommand extends AbstractGenerateModule
{
    const BASE_FOLDER = 'platform';

    const TYPE_CORE = 'core';

    const TYPE_PLUGINS = 'plugins';

    const TYPE_PACKAGES = 'packages';

    const MODE_DIR = 0777;

    protected $arrModuleTypes = [
        1 => self::TYPE_CORE,
        2 => self::TYPE_PLUGINS,
        3 => self::TYPE_PACKAGES
    ];

    protected $moduleType;

    protected $moduleFolderName;

    protected $modulePath;

    protected $composerInfo = [];

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neartech:module:create
                          {name : Name of module}
                          {--autoload : Autoload module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->files = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $moduleName = Str::lower($this->argument('name'));
        if (empty($moduleName)) {
            $this->error('Something went wrong! Not enough arguments (missing: "name").');
        }

        $validArgument = $this->regexArgument($moduleName);

        if (!$validArgument) {
            $this->error('Module Only alphabetic characters are allowed.');
            return self::FAILURE;
        }

        $this->moduleType = $this->choice(
            'What is module type?',
            $this->arrModuleTypes,
            self::TYPE_PACKAGES
        );

        $this->info('Your choice module type: ' . $this->moduleType);

        // Prepare information before create module
        $this->prepareModule($moduleName);
        // Generating module
        $this->generatingModule();

        // Autoload Module
        if ($this->option('autoload')) {
            $this->autoloadModule();
        }
        return self::SUCCESS;
    }


    private function prepareModule($moduleName)
    {
        $this->composerInfo['moduleName'] = $moduleName;
        $this->moduleFolderName = Str::slug($this->ask('Module folder name:', $this->composerInfo['moduleName']));

        $this->info('Your create module folder name: ' . $this->moduleFolderName);

        $this->composerInfo['modulePath'] = base_path(self::BASE_FOLDER . DIRECTORY_SEPARATOR . $this->moduleType . DIRECTORY_SEPARATOR . $this->moduleFolderName);

        if (is_dir($this->composerInfo['modulePath'])) {
            $this->error('Module path: '.$this->composerInfo['modulePath'].' already exists.');
            exit();
        }

        if (is_dir($this->composerInfo['modulePath'])) {
            $this->error('Module path: '.$this->composerInfo['modulePath'].' already exists.');
            exit();
        }

        $moduleType = $this->moduleType;
        $otherModules = array_filter($this->arrModuleTypes, function($e) use ($moduleType) {
            return ($e !== $moduleType);
        });
        foreach ($otherModules as $other) {
            $otherPath = base_path(self::BASE_FOLDER . DIRECTORY_SEPARATOR . $other . DIRECTORY_SEPARATOR . $this->moduleFolderName);
            if (is_dir($this->composerInfo['modulePath'])) {
                $this->error('Module path: '.$otherPath.' already exists.');
                exit();
            }
        }

        $this->composerInfo['description'] = $this->ask('Description of module:', '');

//        $this->composerInfo['namespace'] = $this->ask(
//            'Namespace of module:',
//            Str::studly($this->moduleType) . '\\' . Str::studly($this->composerInfo['moduleName'])
//        );

        $this->composerInfo['namespace'] = Str::studly($this->moduleType) . '\\' . Str::studly($this->composerInfo['moduleName']);

        $this->info('Namespace of module is: ' . $this->composerInfo['namespace']);
    }

    private function generatingModule()
    {
        try {
            // Step1: Copy source stubs to directory module
            $this->publishStubs($this->getStub(), $this->composerInfo['modulePath']);

            // Step2: Rename file from stub to php file
            $this->renameFiles($this->composerInfo['moduleName'], $this->composerInfo['modulePath']);

            // Step3: search and replace content in file
            $this->searchAndReplaceInFiles($this->composerInfo['moduleName'], $this->composerInfo['modulePath']);

        }  catch (Exception $exception) {
            $this->deleteDirectory($this->composerInfo['modulePath']);

            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    protected function autoloadModule()
    {
        $command = 'composer require neartech' . '/' . $this->moduleFolderName.':*@dev';
        $this->info($command);
        $process = Process::fromShellCommandline($command);
        $process->run();
        $this->info($process->getOutput());
    }

    protected function getStub()
    {
        return __DIR__ . '/../../../resources/stubs/module';
    }
}
