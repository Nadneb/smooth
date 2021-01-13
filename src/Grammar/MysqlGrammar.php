<?php


namespace Nadneb\Smooth\Grammar;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;

class MysqlGrammar extends \Illuminate\Database\Schema\Grammars\MySqlGrammar
{
    public function addModifiers($sql, Blueprint $blueprint, Fluent $column)
    {
        return parent::addModifiers($sql, $blueprint, $column);
    }

    /**
     * Get the SQL for the column data type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function getType(Fluent $column)
    {
        return $this->{'type'.ucfirst($column->type)}($column);
    }
}
