<?php
namespace MonitoCli\Command;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use MonitoLib\App;

class Create extends Command
{
    private $config;
    private $connection;
    private $connectionName;
    private $namespace = 'app\\';
    private $outputDir;

    public function __construct()
    {
        parent::__construct('create', [$this, 'handle']);

        $this->addOperands([
            Operand::create('object', Operand::REQUIRED)
        ]);

        $this->addOperands([
            Operand::create('name', Operand::OPTIONAL)
        ]);

        // Connection name
        $option = new \GetOpt\Option('c', 'connection', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
        $option->setDescription('Connection name');
        $this->addOption($option);
        // Origin
        $option = new \GetOpt\Option(null, 'from-file', \GetOpt\GetOpt::NO_ARGUMENT);
        $option->setDescription('Origin');
        $this->addOption($option);

        // Table name
        $option = new \GetOpt\Option('t', 'table', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
        $option->setDescription('Table name');
        $this->addOption($option);

        // Column name
        $option = new \GetOpt\Option(null, 'column', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
        $option->setDescription('Column name');
        $this->addOption($option);

        // Namespace
        $option = new \GetOpt\Option('n', 'namespace', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
        $option->setDescription('Namespace');
        $this->addOption($option);


        // $optionConnection->setValidation('is_numeric');

        // $this->addOption($optionConnection);

        // $this->addOperands([
        //     Operand::create('file', Operand::REQUIRED)
        //         ->setValidation('is_readable'),
        //     Operand::create('destination', Operand::REQUIRED)
        //         ->setValidation('is_writable')
        // ]);

    }

    public function handle (GetOpt $getOpt)
    {
        $object     = $getOpt->getOperand('object');
        $objectName = $getOpt->getOperand('objectName');

        $fromFile       = $getOpt->getOption('from-file');
        $connectionName = $getOpt->getOption('connection');
        $namespace      = $getOpt->getOption('namespace');
        $table          = $getOpt->getOption('table');
        $column         = $getOpt->getOption('column');

        switch ($object) {
            case 'all':
                $objectList = ['dao', 'dto', 'model', 'controller', 'json'];
                break;
            case 'db':
                $objectList = ['dao', 'dto', 'model'];
                break;
            default:
                $objectList = explode(',', $object);
        }

        // if (!is_null($table)) {
        //  $tableList = explode(',', $table);
        // }

        // if (!is_null($namespace)) {
        //  $this->namespace = $namespace . '\\';
        // }

        // \MonitoLib\Dev::pre($objectList);

        if (!is_null($namespace)) {
            $this->namespace = $this->parseNamespace($namespace);
        }

        // echo "$namespace: $namespace";
        // exit;

        // \MonitoLib\Dev::e($connectionName);

        $connector  = \MonitoLib\Database\Connector::getInstance();
        $connection = $connector->getConnection($connectionName)->getConnection();
        $connectionConfig = $connector->getConfig($connectionName);

        $this->config     = $connectionConfig;
        $this->connection = $connection;
        $this->connectionName = $connectionName;


        // $tableList = null;

        // if (!is_null($table))
        // {
        //  $tableList = explode(',', $table);
        // }

        // \MonitoLib\Dev::pre($table);

        // \MonitoLib\Dev::pre($this->config);

        $class = '\MonitoCli\\' . $this->dbms($this->config->dbms);
        $class = new $class($this->config, $this->connection);
        $tables = $class->listTablesAndColumns($table, $column);

        // \MonitoLib\Dev::pre($tables);

        // if ($fromFile && !file_exists(MONITO_CACHE_DIR . $connectionName . '.json')) {
        //  $this->createFile();
        // }

        if (count($tables) > 0) {
            foreach ($tables as $table) {
                if (in_array('controller', $objectList)) {
                    if (!file_exists($this->outputDir . 'controller')) {
                        mkdir($this->outputDir . 'controller', 0777);
                    }
                    $this->createController($table);
                }
                if (in_array('dao', $objectList)) {
                    if (!file_exists($this->outputDir . 'dao')) {
                        mkdir($this->outputDir . 'dao', 0777);
                    }
                    $this->createDao($table);
                }
                if (in_array('dto', $objectList)) {
                    if (!file_exists($this->outputDir . 'dto')) {
                        mkdir($this->outputDir . 'dto', 0777);
                    }
                    $this->createDto($table);
                }
                if (isset($objectList['file'])) {
                    $this->createFile();
                }
                if (in_array('json', $objectList)) {
                    $this->createJsonSchema($table);
                }
                if (in_array('model', $objectList)) {
                    if (!file_exists($this->outputDir . 'model')) {
                        mkdir($this->outputDir . 'model', 0777);
                    }
                    $this->createModel($table);
                }
               echo $table->getTableName() . " ok\n";
            }
        } else {
            echo "no tables\n";
        }

    }
    private function createController ($table)
    {
        $objectName = $table->getObjectName();
        $className = $table->getClassName();

        $filePath = App::getRootPath() . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace) . 'controller' . DIRECTORY_SEPARATOR . $className . '.php';

        if (file_exists($filePath)) {
            echo "file $filePath exists!\n";
            // return false;
        }

        $f = "<?php\n"
            . "namespace {$this->namespace}controller;\n"
            . "\n"
            . "use \MonitoLib\App;\n"
            . "\n"
            . "class {$table->getClassName()} extends \\MonitoLib\\Controller\n"
            . "{\n"
            . "    const VERSION = '1.0.0';\n"
            . "    /**\n"
            . "     * 1.0.0 - " . date('Y-m-d') . "\n"
            . "     * initial release\n"
            . "     */\n"
            . "\n"
            . "    public function create ()\n"
            . "    {\n"
            . "        \$json = \$this->request->getJson();\n"
            . "\n"
            . "        // Valida o json recebido\n"
            . "        if (!is_null(\$errors = \$this->validateJson(\$json, App::getStoragePath('schemas/json') . '{$table->getObjectName()}_create.json'))) {\n"
            . "            throw new \MonitoLib\Exception\BadRequest('Não foi possível validar o schema!', \$errors);\n"
            . "        }\n"
            . "\n"
            . "        \${$table->getObjectName()}Dao = new \\{$this->namespace}dao\\{$table->getClassName()};\n"
            . "        // \${$table->getObjectName()}Dto = \${$table->getObjectName()}Dao->get();\n"
            . "\n"
            . "        // if (!is_null(\${$table->getObjectName()}Dto)) {\n"
            . "        //     throw new \MonitoLib\Exception\BadRequest('Registro já existe!');\n"
            . "        // }\n"
            . "\n"
            . "        \${$table->getObjectName()}Dto = new \\{$this->namespace}dto\\{$table->getClassName()};\n";

        $ml = 0;

        foreach ($table->getColumns() as $column) {
            // \MonitoLib\Dev::pre($column);
            $c = \MonitoLib\Functions::toLowerCamelCase($column->getName());
            if (!in_array($column->getName(), ['upd_time', 'upd_user_id']) && !$column->getIsPrimary()) {
                $value = "\$json->{$c}";

                if ($column->getName() == 'ins_time') {
                    $value = "date('Y-m-d H:i:s')";
                }
                if ($column->getName() == 'ins_user_id') {
                    $value = "User::getId()";
                }

                $f .= "        \${$table->getObjectName()}Dto->set" . ucfirst($c) . "($value);\n";
            }

            if (strlen($c) > $ml) {
                $ml = strlen($c);
            }
        }

        $f .= "        \${$table->getObjectName()}Dto = \${$table->getObjectName()}Dao->insert(\${$table->getObjectName()}Dto);\n"
            . "\n"
            . "        \$this->response->setData(\$this->toArray(\${$table->getObjectName()}Dto))\n"
            . "            ->setHttpResponseCode(201);\n"
            . "    }\n"
            . "    public function dao ()\n"
            . "    {\n"
            . "        \${$objectName}Dao = new \\{$this->namespace}dao\\{$className};\n"
            . "\n"
            . "        // Lista os campos do filtro\n"
            . "        \$params = [\n";

        $fs = '';

        foreach ($table->getColumns() as $column) {
            $fs .= "            '" . str_pad(str_replace('_', '', $column->getName() . '\''), $ml + 1) .  " => '{$column->getName()}', \n";
        }

        $f .= "$fs"
            . "        ];\n"
            . "\n"
            . "        // Lista os campos que podem ser ordenados\n"
            . "        \$sort = [\n"
            . "$fs"
            . "        ];\n"
            . "\n"
            . "        \$queryString = array_change_key_case(\$this->request->getQueryString());\n"
            . "\n"
            . "        foreach (\$queryString as \$key => \$value) {\n"
            . "            if (isset(\$params[\$key])) {\n"
            . "                \${$objectName}Dao->andEqual(\$params[\$key], \$value);\n"
            . "            }\n"
            . "            if (strtolower(\$key) === 'orderby') {\n"
            . "                foreach (\$value as \$v) {\n"
            . "                    \$parts = explode(',', \$v);\n"
            . "\n"
            . "                    if (isset(\$sort[strtolower(\$parts[0])])) {\n"
            . "                        \${$objectName}Dao->orderBy(\$sort[strtolower(\$parts[0])], isset(\$parts[1]) ? \$parts[1] : 'ASC');\n"
            . "                    }\n"
            . "                }\n"
            . "            }\n"
            . "        }\n"
            . "\n"
            . "        return \${$objectName}Dao;\n"
            . "    }\n"
            . "    public function delete (\$id)\n"
            . "    {\n"
            . "        \${$table->getObjectName()}Dao = new \\{$this->namespace}dao\\{$table->getClassName()};\n"
            . "        \$deleted = \${$table->getObjectName()}Dao->andEqual('id', \$id)->delete();\n"
            . "\n"
            . "        if (\$deleted > 0) {\n"
            . "            \$this->response->setHttpResponseCode(204);\n"
            . "        } else {\n"
            . "            throw new \MonitoLib\Exception\BadRequest('Não foi possível deletar!');\n"
            . "        }\n"
            . "    }\n"
            . "    public function get (\$id)\n"
            . "    {\n"
            . "        \${$table->getObjectName()}Dao = new \\{$this->namespace}dao\\{$table->getClassName()};\n"
            . "        \${$table->getObjectName()}Dto = \${$table->getObjectName()}Dao->andEqual('id', \$id)->get();\n"
            . "\n"
            . "        if (is_null(\${$table->getObjectName()}Dto)) {\n"
            . "            throw new \MonitoLib\Exception\NotFound('Registro não encontrado!');\n"
            . "        } else {\n"
            . "            \$this->response->setData(\$this->toArray(\${$table->getObjectName()}Dto));\n"
            . "        }\n"
            . "    }\n"
            . "    public function list ()\n"
            . "    {\n"
            . "        \${$objectName}Ds  = \$this->dao()->dataset();\n"
            . "\n"
            . "        \${$objectName}Ds['data'] = \$this->toArray(\${$objectName}Ds['data']);\n"
            . "        \$this->response->setDataset(\${$objectName}Ds);\n"
            . "    }\n"
            . "    public function update (\$id)\n"
            . "    {\n"
            . "        \$json = \$this->request->getJson();\n"
            . "\n"
            . "        // Valida o json recebido\n"
            . "        if (!is_null(\$errors = \$this->validateJson(\$json, App::getStoragePath('schemas/json') . '{$objectName}_patch.json'))) {\n"
            . "            throw new \MonitoLib\Exception\BadRequest('Não foi possível validar o schema!', \$errors);\n"
            . "        }\n"
            . "\n"
            . "        \${$objectName}Dao = new \\{$this->namespace}dao\\{$className};\n"
            . "        \${$objectName}Dto = \${$objectName}Dao->andEqual('id', \$id)->get();\n"
            . "\n"
            . "        if (is_null(\${$objectName}Dto)) {\n"
            . "            throw new \MonitoLib\Exception\NotFound('Registro não encontrado!');\n"
            . "        }\n"
            . "\n";

            foreach ($table->getColumns() as $column) {
                $c = \MonitoLib\Functions::toLowerCamelCase($column->getName());
                $s = 'set' . ucfirst($c);

                if (!in_array($column->getName(), ['ins_time', 'ins_user_id']) && !$column->getIsPrimary()) {
                    if (in_array($column->getName(), ['upd_time', 'upd_user_id'])) {
                        if ($column->getName() == 'upd_time') {
                            $value = "date('Y-m-d H:i:s')";
                        }
                        if ($column->getName() == 'upd_user_id') {
                            $value = "User::getId()";
                        }

                        $f .= "        \${$objectName}Dto->{$s}($value);\n"
                            . "\n";
                    } else {
                        $f .= "        if (isset(\$json->{$c})) {\n"
                            . "            \${$objectName}Dto->{$s}(\$json->{$c});\n"
                            . "        }\n"
                            . "\n";
                    }
                }
            }

            $f .= "        \$updated = \${$objectName}Dao->update(\${$objectName}Dto);\n"
                . "\n"
                . "        if (\$updated > 0) {\n"
                . "            \$this->response->setMessage('Registro atualizado com sucesso!')->setHttpResponseCode(200);\n"
                . "        } else {\n"
                . "            throw new \MonitoLib\Exception\InternalError('Não foi possível atualizar!');\n"
                . "        }\n"
                . "    }\n"
            . "}";

        file_put_contents($filePath, $f);
    }
    private function createDao ($table)
    {
        $filePath = App::getRootPath() . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace) . 'dao' . DIRECTORY_SEPARATOR . $table->getClassName() . '.php';

        if (file_exists($filePath)) {
            return false;
        }

        $f = "<?php\n"
            . "namespace {$this->namespace}dao;\n"
            . "\n"
            . "class {$table->getClassName()} extends \\MonitoLib\\Database\\Dao\\{$this->dbms($this->config->dbms)}\n"
            . "{\n"
            . "    const VERSION = '1.0.0';\n"
            . "    /**\n"
            . "     * 1.0.0 - " . date('Y-m-d') . "\n"
            . "     * initial release\n"
            . "     */\n";

        if (!is_null($this->connectionName)) {
            $f .= "    public function __construct ()\n"
                . "    {\n"
                . "        \$connector = \MonitoLib\Database\Connector::getInstance();\n"
                . "        \$connector->setConnection('{$this->connectionName}');\n"
                . "        parent::__construct();\n"
                . "    }\n";
        }

        $f .= '}';

        file_put_contents($filePath, $f);
    }
    private function createDto ($table)
    {
        $p = '';
        $g = '';
        $s = '';

        foreach ($table->getColumns() as $column) {
            $cou = \MonitoLib\Functions::toUpperCamelCase($column->getName());
            $col = \MonitoLib\Functions::toLowerCamelCase($column->getName());
            $get = 'get' . $cou;
            $set = 'set' . $cou;

            $p .= "    private \$$col;\n";
            $g .= "    /**\n"
                . "    * $get()\n"
                . "    *\n"
                . "    * @return \$$col\n"
                . "    */\n"
                . "    public function $get () {\n"
                . "        return \$this->$col;\n"
                . "    }\n";
            $s .= "    /**\n"
                . "    * $set()\n"
                . "    *\n"
                . "    * @return \$this\n"
                . "    */\n"
                . "    public function $set (\$$col) {\n"
                . "        \$this->$col = \$$col;\n"
                . "        return \$this;\n"
                . "    }\n";
        }

        $f = "<?php\n"
            . "namespace {$this->namespace}dto;\n"
            . "\n"
            . "class {$table->getClassName()}\n"
            . "{\n"
            . "    const VERSION = '1.0.0';\n"
            . "    /**\n"
            . "     * 1.0.0 - " . date('Y-m-d') . "\n"
            . "     * initial release\n"
            . "     */\n"
            . $p
            . $g
            . $s
            . '}';

        // file_put_contents(MONITO_CACHE_DIR . $table->getClassName() . '.php', $f);
        file_put_contents(App::getRootPath() . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace) . 'dto' . DIRECTORY_SEPARATOR . $table->getClassName() . '.php', $f);
    }
    private function createFile ()
    {
        $dbms  = $this->config->dbms;

        switch (strtolower($dbms)) {
            case 'mysql':
                $dbms = 'MySQL';
                break;
            case 'oracle':
                $dbms = 'Oracle';
                break;
        }

        $class = '\MonitoCli\\' . $dbms;

        $class = new $class($this->config, $this->connection);
        $tables = $class->listTablesAndColumns();

        // \MonitoLib\Dev::pre($tables);

        $r = "{\r\n";
        $t = null;

        foreach ($tables as $table)
        {
            $c = '      "' . $table['COLUMN_NAME'] . "\": \"" . \MonitoLib\Functions::toLowerCamelCase($table['COLUMN_NAME']) . "\",\n";

            if ($t != $table['TABLE_NAME'])
            {
                if (!is_null($t))
                {
                    $r = substr($r, 0, -2) . "\n    }\n  },\n";
                }

                $c = "  \"" . $table['TABLE_NAME'] . "\": {\n"
                    . "    \"className\": \"" . \MonitoLib\Functions::toUpperCamelCase(\MonitoLib\Functions::toSingular($table['TABLE_NAME'])) . "\",\n"
                    . "    \"fields\": {"
                    . "\n" . $c;
            }

            $t = $table['TABLE_NAME'];
            $r .= $c;
        }

        $r = substr($r, 0, -2) . "\n    }\n  }\n}";

        file_put_contents(MONITO_CACHE_DIR . $this->config->name . '.json', $r);
    }
    private function createJsonSchema ($table)
    {
        $s = [
            'type' => 'object',
            'properties' => [],
        ];

        $r = [];

        foreach ($table->getColumns() as $column) {

            // \MonitoLib\Dev::pre($column);


            if (!$column->getIsAuto() && !in_array($column->getName(), ['ins_time', 'ins_user_id', 'upd_time', 'upd_user_id'])) {
                $col = \MonitoLib\Functions::toLowerCamelCase($column->getName());

                switch ($column->getDataType()) {
                    case 'date':
                        $format = 'date';
                        break;
                    case 'datetime':
                        $format = 'date-time';
                        break;
                    case 'time':
                        $format = 'time';
                        break;
                    case 'decimal':
                    case 'float':
                        $type = 'number';
                        break;
                    case 'int':
                    case 'mediumint':
                    case 'tinyint':
                        $type = 'integer';
                        break;
                    default:
                        $format = null;
                        $type = 'string';
                        break;
                }

                $f = [
                    $col => [
                        'type' => $type
                    ]
                ];

                if (is_null($column->getDefaultValue())) {
                    if (!$column->getIsRequired()) {
                        $f[$col]['default'] = null;
                        $f[$col]['type'] = [$type, 'null'];
                    }
                } else {
                    $f[$col]['default'] = $column->getDefaultValue();
                }

                // if () {

                // }

                if ($column->getIsRequired()) {
                    $r[] = $col;
                }

                $s['properties'][$col] = $f[$col];
            }
        }

        $p = $s;

        if (count($r) > 0) {
            $s['required'] = $r;
        }

        // file_put_contents(MONITO_CACHE_DIR . $table->getClassName() . '.php', $f);
        file_put_contents(App::getStoragePath('schemas/json') . $table->getObjectName() . '_create.json', json_encode($s, JSON_PRETTY_PRINT));
        file_put_contents(App::getStoragePath('schemas/json') . $table->getObjectName() . '_patch.json', json_encode($p, JSON_PRETTY_PRINT));
    }
    private function createModel ($table)
    {
        $modelDefault = new \MonitoLib\Database\Model\MySQL;

        $output = '';
        $keys = '';

        foreach ($table->getColumns() as $column)
        {
            $cl = strlen($column->getName());
            $ci = $cl;//$bi + $cl;
            $it = floor($ci / 4);
            $is = $ci % 4;
            $li = "            ";//$util->indent($it, $is);

            $output .= "        '" . $column->getName() . "' => [\n";

            if ($column->getIsAuto())
            {
                $output .= "$li'auto' => true,\n";
            }

            if ($column->getType() == 'char')
            {
                if ($column->getCharset() != $modelDefault->getDefaults('charset'))
                {
                    $output .= "$li'charset'   => '{$column->getCharset()}',\n";
                }
                if ($column->getCollation() != $modelDefault->getDefaults('collation'))
                {
                    $output .= "$li'collation' => '{$column->getCollation()}',\n";
                }
            }
            if (!is_null($column->getDefaultValue()))
            {
                //if ()
                //{
                //
                //}

                $output .= "$li'defaultValue' => '{$column->getDefaultValue()}',\n";
            }
            if (!is_null($column->getLabel()))
            {
                $output .= "$li'label' => '{$column->getLabel()}',\n";
            }
            if (!is_null($column->getMaxLength()) && $column->getMaxLength() > 0)
            {
                $output .= "$li'maxLength' => {$column->getMaxLength()},\n";
            }
            if ($column->getIsPrimary())
            {
                $keys .= "'" . $column->getName() . "',";
                $output .= "$li'primary' => true,\n";
            }
            if ($column->getIsRequired())
            {
                $output .= "$li'required' => true,\n";
            }
            if ($modelDefault->getDefaults('type') != $column->getDatatype())
            {
                $output .= "$li'type' => '{$column->getDatatype()}',\n";
            }
            if ($modelDefault->getDefaults('unique') != $column->getIsUnique())
            {
                $output .= "$li'unique' => {$column->getIsUnique()},\n";
            }
            if ($modelDefault->getDefaults('unsigned') != $column->getIsUnsigned())
            {
                $output .= "$li'unsigned' => {$column->getIsUnsigned()},\n";
            }


        //'maxValue'         => 0,
        //'minValue'         => 0,
        //'numericPrecision' => null,
        //'numericScale'     => null,

            $output .= "        ],\n";
        }

        $keys = substr($keys, 0, -1);

        $c = \MonitoLib\Functions::toUpperCamelCase($table->getClassName());
        $f = "<?php\n"
            // . $this->renderComments()
            . "\n"
            . "namespace {$this->namespace}model;\n"
            . "\n"
            // TODO: checks dbms to extends to right class
            . "class $c extends \\MonitoLib\\Database\\Model\\MySQL\n"
            . "{\n"
            . "    const VERSION = '1.0.0';\n"
            . "\n"
            . "    protected \$tableName = '" . $table->getTableName() . "';\n"
            . "\n"
            . "    protected \$fields = [\n"
            . $output
            . "    ];\n"
            . "\n"
            . "    protected \$keys = [$keys];\n"
            . "}"
            ;
        // file_put_contents(MONITO_CACHE_DIR . $c . '.php', $f);
            file_put_contents(App::getRootPath() . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace) . 'model' . DIRECTORY_SEPARATOR . $table->getClassName() . '.php', $f);
    }
    private function dbms ($dbms)
    {
        $dbms = strtolower($dbms);

        switch ($dbms) {
            case 'mysql':
            case 'mysql-pdo':
                return 'MySQL';
            case 'oracle':
                return 'Oracle';
        }
    }
    private function parseNamespace ($namespace)
    {
        $namespace = trim($namespace);
        $parts = explode('\\', $namespace);
        $path = App::getRootPath();
        $namespace = '';

        foreach ($parts as $p) {
            if ($p != '') {
                $path .= $p . DIRECTORY_SEPARATOR;

                if (!file_exists($path)) {
                    mkdir($path, 0777);
                }

                // mkdir()

                $namespace .= $p . '\\';
            }
        }

        $this->outputDir = $path;

        return $namespace;
    }
}
