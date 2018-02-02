<?php
namespace MonitoCli\Command;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;

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

    	

    	$connector  = \MonitoLib\Connector::getInstance();
    	$connection = $connector->getConnection($connectionName);
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

		$class = '\MonitoCli\\' . $this->dbms($this->config->dbms);
		$class = new $class($this->config, $this->connection);
		$tables = $class->listTablesAndColumns($table, $column);

    	// \MonitoLib\Dev::pre($tables);

    	// if ($fromFile && !file_exists(MONITO_CACHE_DIR . $connectionName . '.json')) {
    	// 	$this->createFile();
    	// }

    	foreach ($tables as $table) {
    		if (isset($objectList['controller'])) {
    			$this->createController();
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
    		if (in_array('model', $objectList)) {
    			if (!file_exists($this->outputDir . 'model')) {
    				mkdir($this->outputDir . 'model', 0777);
    			}
    			$this->createModel($table);
    		}
    	}

    	echo 'ok';
    }
	private function createDao ($table)
	{
		$f = "<?php\n"
			. "namespace {$this->namespace}dao;\n"
			. "\n"
			. "class {$table->getClassName()} extends \\MonitoLib\\Database\\{$this->dbms($this->config->dbms)}\\Dao\n"
			. "{\n"
			. "\tconst VERSION = '1.0.0';\n"
			. "\t/**\n"
			. "\t * 1.0.0 - " . date('Y-m-d') . "\n"
			. "\t * initial release\n"
			. "\t */\n";

		if (!is_null($this->connectionName)) {
			$f .= "\tpublic function __construct ()\n"
				. "\t{\n"
				. "\t\t\$connector = \MonitoLib\Connector::getInstance();\n"
				. "\t\t\$connector->setConnection('{$this->connectionName}');\n"
				. "\t\tparent::__construct();\n"
				. "\t}\n";
		}

		$f .= '}';

		file_put_contents(MONITO_SITE_PATH . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace) . 'dao' . DIRECTORY_SEPARATOR . $table->getClassName() . '.php', $f);
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

			$p .= "\tprivate \$$col;\n";
			$g .= "\t/**\n"
				. "\t* $get()\n"
				. "\t*\n"
				. "\t* @return \$$col\n"
				. "\t*/\n"
				. "\tpublic function $get () {\n"
				. "\t\treturn \$this->$col;\n"
				. "\t}\n";
			$s .= "\t/**\n"
				. "\t* $set()\n"
				. "\t*\n"
				. "\t* @return \$this\n"
				. "\t*/\n"
				. "\tpublic function $set (\$$col) {\n"
				. "\t\t\$this->$col = \$$col;\n"
				. "\t\treturn \$this;\n"
				. "\t}\n";
		}

		$f = "<?php\n"
			. "namespace {$this->namespace}dto;\n"
			. "\n"
			. "class {$table->getClassName()}\n"
			. "{\n"
			. "\tconst VERSION = '1.0.0';\n"
			. "\t/**\n"
			. "\t * 1.0.0 - " . date('Y-m-d') . "\n"
			. "\t * initial release\n"
			. "\t */\n"
			. $p
			. $g
			. $s
			. '}';

		// file_put_contents(MONITO_CACHE_DIR . $table->getClassName() . '.php', $f);
		file_put_contents(MONITO_SITE_PATH . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace) . 'dto' . DIRECTORY_SEPARATOR . $table->getClassName() . '.php', $f);
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
	private function createModel ($table)
	{
		$modelDefault = new \MonitoLib\Database\MySQL\Model;

		$output = '';
		$keys = '';

		foreach ($table->getColumns() as $column)
		{
			$cl = strlen($column->getName());
			$ci = $cl;//$bi + $cl;
			$it = floor($ci / 4);
			$is = $ci % 4;
			$li = "\t\t\t";//$util->indent($it, $is);

			$output .= "\t\t'" . $column->getName() . "' => [\n";
			
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

			$output .= "\t\t],\n";
		}

		$keys = substr($keys, 0, -1);

		$c = \MonitoLib\Functions::toUpperCamelCase($table->getClassName());
		$f = "<?php\n"
			// . $this->renderComments()
			. "\n"
			. "namespace {$this->namespace}model;\n"
			. "\n"
			// TODO: checks dbms to extends to right class
			. "class $c extends \\MonitoLib\\Database\\MySQL\\Model\n"
			. "{\n"
			. "\tconst VERSION = '1.0.0';\n"
			. "\n"
			. "\tprotected \$tableName = '" . $table->getTableName() . "';\n"
			. "\n"
			. "\tprotected \$fields = [\n"
			. $output
			. "\t];\n"
			. "\n"
			. "\tprotected \$keys = [$keys];\n"
			. "}"
			;
		// file_put_contents(MONITO_CACHE_DIR . $c . '.php', $f);
			file_put_contents(MONITO_SITE_PATH . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace) . 'model' . DIRECTORY_SEPARATOR . $table->getClassName() . '.php', $f);
	}
	private function dbms ($dbms)
	{
    	$dbms = strtolower($dbms);

		if ($dbms === 'mysql') {
			return 'MySQL';
		}
		if ($dbms === 'oracle') {
			return 'Oracle';
		}
	}
	private function parseNamespace ($namespace)
	{
		$namespace = trim($namespace);
		$parts = explode('\\', $namespace);
		$path = MONITO_SITE_PATH;
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
