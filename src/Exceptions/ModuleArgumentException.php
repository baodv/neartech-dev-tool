<?php

namespace Neartech\DevTool\Exceptions;

use App\Exceptions\Handler;
use Throwable;


class ModuleArgumentException extends Handler
{

    public function render($request, Throwable $e)
    {
        dd('vao day');
    }
}
