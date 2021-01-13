<?php

namespace Nadneb\Smooth\Commands;

use Illuminate\Console\Command;
use Nadneb\Smooth\Comparator\M2DComparator;
use Nadneb\Smooth\Executor\Executor;
use Nadneb\Smooth\Reader\DBReader;
use Nadneb\Smooth\Reader\MigrateFileReader;

class SmoothDBCommand extends Command
{
    protected $signature = "smooth:db
        {connection? : 指定连接，默认 null，对应 config/database.php 中的配置.}
        {path? : 迁移文件目录，相对于在 databases 目录，默认 migrations.}";

    protected $description = 'test command';

    public function handle()
    {
        $mReader = new MigrateFileReader();
        $mReader->init([
            'path' => database_path($this->argument('path') ?? 'migrations'),
        ]);

        $dbReader = new DBReader();
        
        $dbReader->init([
            'connection' => $this->argument('connection') ?? null,
        ]);

        $diffArr = M2DComparator::compare($mReader->fieldMap, $dbReader->fieldMap);

        M2DComparator::printDiff($diffArr, $mReader, $dbReader);

        Executor::exec($diffArr, $mReader);
    }
}