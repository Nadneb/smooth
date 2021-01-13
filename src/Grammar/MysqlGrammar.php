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
}
