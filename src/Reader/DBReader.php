<?php


namespace Nadneb\Smooth\Reader;


use Illuminate\Database\Connection;
use Nadneb\Smooth\Field;

class DBReader
{
    public $fieldMap = [];

    /**
     * @var Connection $connection;
     */
    private $connection;

    public function init(array $config = [])
    {
        $this->connection = \DB::connection($config['connection'] ?? null);

        $tables = $this->getAllTables();

        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . env('DB_DATABASE')};
            $cols      = $this->getAllColumns($tableName);
            foreach ($cols as $col) {
                $field = Field::getInsByStdClass($col);
                $this->fieldMap[$tableName . '.' . $field->name] = $field;
            }
        }
    }

    /**
     * @param $tableName
     * @return array
     */
    public function getAllColumns($tableName)
    {
        return $this->connection->select(sprintf('show full columns from `%s`;', $tableName));
    }

    /**
     * @return array
     */
    public function getAllTables()
    {
        return $this->connection->select('SHOW TABLES');
    }
}
