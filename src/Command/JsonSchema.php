<?php
namespace MonitoCli\Command;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;

class JsonSchema extends Command
{
	private $config;
	private $connection;
	private $connectionName;
	private $namespace = 'app\\';
	private $outputDir;

    public function __construct()
    {
        parent::__construct('json', [$this, 'handle']);

        // $this->addOperands([
        //     Operand::create('object', Operand::REQUIRED)
        // ]);

        // $this->addOperands([
        //     Operand::create('name', Operand::OPTIONAL)
        // ]);

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

    	if ($object === 'all') {
    		// $objectList = ['controller', 'dao', 'dto', 'model'];
    		$objectList = ['dao', 'dto', 'model'];
    	} else {
    		$objectList = explode(',', $object);
    	}

    	// if (!is_null($table)) {
    	// 	$tableList = explode(',', $table);
    	// }

    	// if (!is_null($namespace)) {
    	// 	$this->namespace = $namespace . '\\';
    	// }

    	// \MonitoLib\Dev::pre($objectList);

    	if (!is_null($namespace)) {
    		$this->namespace = $this->parseNamespace($namespace);
    	}

    	// echo "$namespace: $namespace";
    	// exit;

    	

    	$connector  = \MonitoLib\Database\Connector::getInstance();
    	$connection = $connector->getConnection($connectionName)->getConnection();
    	$connectionConfig = $connector->getConfig($connectionName);

    	$this->config     = $connectionConfig;
    	$this->connection = $connection;
    	$this->connectionName = $connectionName;


		// $tableList = null;

		// if (!is_null($table))
		// {
		// 	$tableList = explode(',', $table);
		// }

		// \MonitoLib\Dev::pre($table);

		// \MonitoLib\Dev::pre($this->config);

		$class = '\MonitoCli\\' . $this->dbms($this->config->dbms);
		$class = new $class($this->config, $this->connection);
		$tables = $class->listTablesAndColumns($table, $column);

    	// \MonitoLib\Dev::pre($tables);

    	// if ($fromFile && !file_exists(MONITO_CACHE_DIR . $connectionName . '.json')) {
    	// 	$this->createFile();
    	// }

    	foreach ($tables as $table) {
    		$this->createSchema($table);
    	}

    	echo 'ok';
    }

	private function createSchema ($table)
	{
		$s = [
            'type' => 'object', 
            'properties' => [],
        ];

        $r = [];

        foreach ($table->getColumns() as $column) {

            // \MonitoLib\Dev::pre($column);


            if (!$column->getIsAuto()) {
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
                    $f[$col]['default'] = null;
                    $f[$col]['type'] = [$type, 'null'];
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

		if (count($r) > 0) {
            $s['required'] = $r;
        }

		// file_put_contents(MONITO_CACHE_DIR . $table->getClassName() . '.php', $f);
		file_put_contents(App::getStoragePath('schemas/json') . $table->getClassName() . '.json', json_encode($s, JSON_PRETTY_PRINT));
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
