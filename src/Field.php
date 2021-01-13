<?php


namespace Nadneb\Smooth;


class Field
{
    public $name;
    public $type;
    public $nullable;
    public $default;
    public $comment;

    public static function getInsBySql($sql)
    {
        $ins = new Field();
        $ins->setBySql($sql);
        return $ins;
    }

    public static function getInsByStdClass($obj)
    {
        $ins           = new Field();
        $ins->name     = $obj->Field;
        $ins->type     = $ins->getFieldTypeByStdClass($obj);
        $ins->nullable = $obj->Null == 'YES';
        $ins->default  = $obj->Default;
        $ins->comment  = $obj->Comment;

        return $ins;
    }

    public function getFieldTypeByStdClass($obj)
    {
        if (strpos($obj->Type, 'int') !== false) {
            return preg_replace('/\(.*?\)/', '', $obj->Type);
        }
        return $obj->Type;
    }

    public function setBySql($sql)
    {
        $this->name     = $this->getFieldNameBySql($sql);
        $this->type     = $this->getFieldTypeBySql($sql);
        $this->nullable = $this->getFieldNullableBySql($sql);
        $this->default  = $this->getFieldDefaultBySql($sql);
        $this->comment  = $this->getFieldCommentBySql($sql);
    }

    public function getFieldCommentBySql($sql)
    {
        $r = [];
        preg_match('/comment \'(.*?)\'/', $sql, $r);
        if (empty($r)) {
            return '';
        }
        return $r[1];
    }

    public function getFieldDefaultBySql($sql)
    {
        $r = [];
        preg_match('/default \'(.*?)\'/', $sql, $r);
        if (empty($r)) {
            return null;
        }
        return $r[1];
    }

    public function getFieldNullableBySql($sql)
    {
        $r = [];
        preg_match('/(not null)/', $sql, $r);
        return empty($r);
    }

    public function getFieldTypeBySql($sql)
    {
        $r = [];
        preg_match('/`.*?`\s(.*?)\s(?=[a-zA-Z])/', $sql, $r);
        $str = $r[1];
        if (strpos($str, 'int') !== false) {
            $str = preg_replace('/\(.*?\)/', '', $str);
        }
        $str = str_replace('/\(.*?\)/', '', $str);
        return str_replace(' ', '', $str);
    }

    public function getFieldNameBySql($sql)
    {
        $r = [];
        preg_match('/`(.*?)`/', $sql, $r);
        return $r[1];
    }


    public function __toString()
    {
        return json_encode([
            'name'     => $this->name,
            'type'     => $this->type,
            'nullable' => $this->nullable,
            'default'  => $this->default,
            'comment'  => $this->comment,
        ]);
    }
}
