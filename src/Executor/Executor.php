<?php


namespace Nadneb\Smooth\Executor;


use Illuminate\Database\Schema\Blueprint;
use Nadneb\Smooth\Comparator\M2DComparator;
use Nadneb\Smooth\Reader\MigrateFileReader;

class Executor
{
    public static function exec(array $arr, MigrateFileReader $mReader, $connection = 'mysql')
    {
        if (empty($arr)) {
            printf('Noting.%s', PHP_EOL);
            return;
        }

        $line = readline('Enter "yes" to start:');
        if ($line != 'yes') {
            printf('Goodbye, noting.%s', PHP_EOL);
            return;
        }

        $count = 1;
        foreach ($arr as $key => $value) {
            switch ($value['source']) {
                case M2DComparator::SOURCE_UPDATE:
                    static::execUpdate($count, $mReader, $key, $connection);
                    break;
                case M2DComparator::SOURCE_ADD:
                    static::execAdd($count, $mReader, $key, $connection);
                    break;
            }
        }
    }

    public static function execUpdate(&$count, MigrateFileReader $mReader, $key, $connection)
    {
        $tableName = explode('.', $key)[0];

        $descStr = $mReader->getColumnDescStr(new Blueprint(''), $mReader->columnDefineMap[$key]);
        $sql = sprintf('ALTER TABLE `%s` MODIFY COLUMN %s', $tableName, $descStr);

        printf('%s. Execute: %s%s', $count++, $sql, PHP_EOL);
         \DB::connection($connection)->statement($sql);
        printf('Execute Finished. %s', PHP_EOL.PHP_EOL);
    }

    public static function execAdd(&$count, MigrateFileReader $mReader, $key, $connection)
    {
        $tableName = explode('.', $key)[0];

        $descStr = $mReader->getColumnDescStr(new Blueprint(''), $mReader->columnDefineMap[$key]);
        $sql = sprintf('ALTER TABLE `%s` ADD COLUMN %s', $tableName, $descStr);

        printf('%s. Execute: %s%s', $count++, $sql, PHP_EOL);
        \DB::connection($connection)->statement($sql);
        printf('Execute Finished: %s', PHP_EOL.PHP_EOL);
    }
}
