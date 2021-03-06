<?php

namespace Fake\Kernels\Cli\Output;

use Neutrino\Cli\Output\Writer;

class StubOutput extends Writer
{
    public $out;

    public function write($message, $newline)
    {
        if (!$this->quiet)
            $this->out .= $message . ($newline ? PHP_EOL : '');
    }
}