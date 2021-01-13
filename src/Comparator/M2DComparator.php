<?php

namespace Nadneb\Smooth\Comparator;


use Illuminate\Database\Schema\Blueprint;
use MathieuViossat\Util\ArrayToTextTable;
use Nadneb\Smooth\Field;
use Nadneb\Smooth\Reader\DBReader;
use Nadneb\Smooth\Reader\MigrateFileReader;

class M2DComparator
{
    const SOURCE_UPDATE = 1;
    const SOURCE_ADD    = 2;

    public static function compare($mFieldMap, $dFieldMap)
    {
        $arr = [];
        foreach ($mFieldMap as $key => $field) {
            if (!isset($dFieldMap[$key])) {
                $arr[$key] = [
                    'source' => self::SOURCE_ADD,
                ];
                continue;
            }
            if ($dFieldMap[$key] != $field) {
                $arr[$key] = [
                    'source' => self::SOURCE_UPDATE,
                ];
            }
        }
        return $arr;
    }

    public static function printDiff(array $arr, MigrateFileReader $mReader, DBReader $dReader)
    {
        $count = 1;
        foreach ($arr as $key => $value) {
            switch ($value['source']) {
                case self::SOURCE_UPDATE:
                    static::printForUpdate($count, $key, $mReader, $dReader);
                    break;
                case self::SOURCE_ADD:
                    static::printForAdd($count, $key, $mReader);
                    break;
            }
        }
    }

    private static function printForAdd(&$count, $key, MigrateFileReader $mReader)
    {
        $data = [];

        $tableName = explode('.', $key)[0];
        $colName   = explode('.', $key)[1];

        printf('%s. TABLE: %s, ADD COLUMN: %s %s', $count++, $tableName, $colName, PHP_EOL);
        $data[] = self::addArrayToTextTableItem($mReader->fieldMap[$key], 'Migration');

        $renderer = new ArrayToTextTable($data);
        echo $renderer->getTable();

        $sql = $mReader->getColumnDescStr(new Blueprint(''), $mReader->columnDefineMap[$key]);
        printf('SQL: ALTER TABLE `%s` ADD COLUMN %s%s', $tableName, $sql, PHP_EOL.PHP_EOL);
    }

    private static function printForUpdate(&$count, $key, MigrateFileReader $mReader, DBReader $dReader)
    {
        $data = [];

        $tableName = explode('.', $key)[0];
        $colName   = explode('.', $key)[1];

        printf('%s. TABLE: %s, MODIFY COLUMN: %s %s', $count++, $tableName, $colName, PHP_EOL);
        $data[] = self::addArrayToTextTableItem($dReader->fieldMap[$key], 'DB');
        $data[] = self::addArrayToTextTableItem($mReader->fieldMap[$key], 'Migration');

        $renderer = new ArrayToTextTable($data);
        echo $renderer->getTable();

        $sql = $mReader->getColumnDescStr(new Blueprint(''), $mReader->columnDefineMap[$key]);
        printf('SQL: ALTER TABLE `%s` MODIFY COLUMN %s%s', $tableName, $sql, PHP_EOL.PHP_EOL);
    }

    public static function addArrayToTextTableItem(Field $field, $source)
    {
        return [
            'Source'   => $source,
            'Name'     => $field->name,
            'Type'     => $field->type,
            'Nullable' => $field->nullable ? 'true' : 'false',
            'Default'  => $field->default,
            'Comment'  => $field->comment,
        ];
    }
}
