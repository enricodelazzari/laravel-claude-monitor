<?php

namespace EnricoDeLazzari\ClaudeMonitor\Commands;

use Illuminate\Console\Command;

class ClaudeMonitorCommand extends Command
{
    public $signature = 'laravel-claude-monitor';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
