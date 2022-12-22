<?php

namespace Neartech\DevTool\Console\Commands\Make;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Neartech\DevTool\Console\Commands\AbstractGenerateModule;

class MakeControllerCommand extends AbstractGenerateModule
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neartech:make:controller
                          {module : Name of module}
                          {name : The class name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a controller';

    /**
     * Create a new key generator command.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     *
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $module = Str::lower($this->argument('module'));
        $name = Str::lower($this->argument('name'));

        $validModule = $this->regexArgument($module);
        $validName = $this->regexArgument($name);

        if (!$validModule || !$validName) {
            $this->error('Module Only alphabetic characters are allowed.');
            return self::FAILURE;
        }
    }


    protected function getStub()
    {
        return __DIR__ . '/../../../resources/stubs/module/src/Http/Controllers';
    }
}
