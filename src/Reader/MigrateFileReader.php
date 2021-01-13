<?php


namespace Nadneb\Smooth\Reader;


use Illuminate\Database\Schema\Blueprint;
use Nadneb\Smooth\Field;
use Nadneb\Smooth\Grammar\MysqlGrammar;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class MigrateFileReader
{
    private $files;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeFinder
     */
    private $finder;

    /**
     * @var Standard
     */
    private $printer;

    /**
     * @var MysqlGrammar
     */
    public $grammar;

    public $fieldMap;

    public $columnDefineMap;

    private function initFieldMap()
    {
        foreach ($this->files as $file) {
            $this->readFile($file);
        }
    }

    private function readFile($file)
    {
        $code     = file_get_contents($file);
        $ast      = $this->parser->parse($code);
        $class    = $this->finder->findFirstInstanceOf($ast, Class_::class);
        $upMethod = $this->finder->findFirst($class, function (Node $node) {
            return $node instanceof Node\Stmt\ClassMethod && $node->name->name === 'up';
        });

        $createTableMethods = $this->finder->find($upMethod, function (Node $node) {
            if ($node instanceof Node\Expr\StaticCall
                && $node->class->parts[0] == 'Schema'
                && $node->name->name == 'create') {
                return true;
            }
            return false;
        });

        $alterTableMethods = $this->finder->find($upMethod, function (Node $node) {
            if ($node instanceof Node\Expr\StaticCall
                && $node->class->parts[0] == 'Schema'
                && $node->name->name == 'table') {
                return true;
            }
            return false;
        });

        foreach ($createTableMethods as $createTableMethod) {
            /**
             * @var Node\Expr\StaticCall $createTableMethod
             */
            $this->handleCreateTableMethod($createTableMethod);
        }

        foreach ($alterTableMethods as $alterTableMethod) {
            /**
             * @var Node\Expr\StaticCall $alterTableMethod
             */
            $this->handleAlterTableMethod($alterTableMethod);
        }
    }

    private function handleCreateTableMethod(Node\Expr\StaticCall $method)
    {
        $tableName = $method->args[0]->value->value;

        $closure = $this->finder->findFirstInstanceOf($method, Closure::class);
        $stmts   = $closure->stmts;
        $code    = $this->printer->prettyPrint($stmts);

        $table = new Blueprint($tableName);
        eval($code);
        foreach ($table->getColumns() as $column) {
            $field                                                  = Field::getInsBySql($this->getColumnDescStr($table, $column));
            $this->fieldMap[$tableName . '.' . $field->name]        = $field;
            $this->columnDefineMap[$tableName . '.' . $field->name] = $column;
        }
    }

    public function getColumnDescStr($table, $column)
    {
        $sql = $this->grammar->wrap($column) . ' ' . $this->grammar->getType($column);
        return $this->grammar->addModifiers($sql, $table, $column);
    }

    private function handleAlterTableMethod(Node\Expr\StaticCall $method)
    {
        $this->handleCreateTableMethod($method);
    }

    public function init(array $config = [])
    {
        $this->files = $this->getMigrateFiles([$config['path'] ?? database_path('migrations')]);
        
        if (empty($this->files)) {
            throw new \Exception('未找到任何文件，请检查迁移文件路径是否正确');
        }

        $this->parser  = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->finder  = new NodeFinder();
        $this->printer = new Standard();

        $this->grammar = new MySqlGrammar();

        $this->initFieldMap();
    }

    /**
     * @param $paths
     * @return mixed
     */
    private function getMigrateFiles($paths)
    {
        $migrator = app('migrator');
        return $migrator->getMigrationFiles($paths);
    }
}
