<?php
namespace MonitoCli;

class MySQL
{
	private $conn;
	private $config;
	private $connection;
	private $dbName;
	private $tables = array();
	private $util;

	public function __construct ($config, $connection)
	{
		$this->config     = $config;
		$this->connection = $connection;
	}

	public function addTable ($table)
	{
		$this->tables[] = $table;
	}
	private function labelIt ($label)
	{
		if ($label ==  'id') {
			$label = '#';
		} else {
			$frag = NULL;

			if (preg_match('/_id$/', $label)) {
				//$frag  = '# ';
				$label = substr($label, 0, -3);
			}

			$parts = explode('_', $label);
			$label = '';

			foreach ($parts as $p) {
				$label .= ucfirst($p) . ' ';
			}

			$label = substr($label, 0, -1);
			
			//if (!is_null($frag))
			//{
			//	$label .= ' ' . $frag;
			//}
		}

		return $label;
	}
	public function listColumns ($tableName = NULL)
	{
		$sql = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ?';

		if (!is_null($tableName)) {
			$sql .= ' AND TABLE_NAME ';

			if (is_array($tableName)) {
				$tableName  = "'" . implode("','", $tableName) . "'";
				$sql       .= "IN ($tableName)";
				$tableName  = NULL;
			} else {
				$sql .= '= ?';
			}
		}

		$sth = $this->connection->prepare($sql);
		$sth->bindParam(1, $this->dbName);

		if (!is_null($tableName)) {
			$sth->bindParam(2, $tableName);
		}

		$sth->execute();

		$columns = $sth->fetchAll(\PDO::FETCH_ASSOC);
	
		$data = array();
		
		foreach ($columns as $c) {
			switch ($c['DATA_TYPE']) {
				case 'char':
				case 'varchar':
				case 'text':
					$type = 'char';
					break;
				case 'int':
				case 'bigint':
				case 'smallint':
				case 'tinyint':
					$type = 'int';
					break;
				case 'decimal';
				case 'double';
				case 'float';
					$type = 'float';
					break;
				default:
					$type = $c['DATA_TYPE'];
			}
			
			$columnName          = $c['COLUMN_NAME'];
			$columnType          = $type;
			$columnLabel         = $this->labelIt($c['COLUMN_NAME']);
			$columnDataType      = $c['DATA_TYPE'];
			$columnDefault       = $c['COLUMN_DEFAULT'] == '' ? NULL : $c['COLUMN_DEFAULT'];
			$columnMaxLength     = is_null($c['CHARACTER_MAXIMUM_LENGTH']) ? $c['NUMERIC_PRECISION'] : $c['CHARACTER_MAXIMUM_LENGTH'];
			$columnPrecisionSize = $c['NUMERIC_PRECISION'];
			$columnScale         = $c['NUMERIC_SCALE'];
			$columnCollation     = $c['COLLATION_NAME'];
			$columnCharset       = $c['CHARACTER_SET_NAME'];
			$columnIsPrimary     = $c['COLUMN_KEY'] == 'PRI' ? 1 : 0;
			$columnIsRequired    = $c['IS_NULLABLE'] == 'YES' ? 0 : 1;
			$columnIsBinary      = strpos($c['COLLATION_NAME'], '_bin') !== FALSE ? 0 : 1;
			$columnIsUnsigned    = strpos($c['COLUMN_TYPE'], 'unsigned') !== FALSE ? 0 : 1;
			$columnIsUnique      = $c['COLUMN_KEY'] == 'UNI' ? 1 : 0;
			$columnIsZerofilled  = strpos($c['COLUMN_TYPE'], 'zerofill') !== FALSE ? 1 : 0;
			$columnIsAuto        = $c['EXTRA'] == 'auto_increment' ? 1 : 0;
			$columnIsForeign     = $c['COLUMN_KEY'] == 'MUL' ? 1 : 0;
			$columnActive        = 1;
			$tableName           = $c['TABLE_NAME'];

			//$tableDao    = \dao\Factory::createTable();
			//$tableObject = $tableDao->getByName($tableName);
	
			//if (is_null($tableObject))
			//{
			//	throw new \Exception("Table $tableName not found!");
			//}

			// $columnDao   = \dao\Factory::createColumn();
			// $columnDto = new \dto\Column;
			//$columnDto->setTableId($tableObject->getId());
			// $columnDto->setName($columnName);
			// $columnDto->setType($columnType);
			// $columnDto->setLabel($columnLabel);
			// $columnDto->setDataType($columnDataType);
			// $columnDto->setDefaultValue($columnDefault);
			// $columnDto->setMaxLength($columnMaxLength);
			// $columnDto->setNumericPrecision($columnPrecisionSize);
			// $columnDto->setNumericScale($columnScale);
			// $columnDto->setCollation($columnCollation);
			// $columnDto->setCharset($columnCharset);
			// $columnDto->setIsPrimary($columnIsPrimary);
			// $columnDto->setIsRequired($columnIsRequired);
			// $columnDto->setIsBinary($columnIsBinary);
			// $columnDto->setIsUnsigned($columnIsUnsigned);
			// $columnDto->setIsUnique($columnIsUnique);
			// $columnDto->setIsZerofilled($columnIsZerofilled);
			// $columnDto->setIsAuto($columnIsAuto);
			// $columnDto->setIsForeign($columnIsForeign);
			// $columnDto->setActive($columnActive);
			$columnDto = new \stdClass;
			$columnDto->name = $columnName;
			$columnDto->type = $columnType;
			$columnDto->label = $columnLabel;
			$columnDto->dataType = $columnDataType;
			$columnDto->defaultValue = $columnDefault;
			$columnDto->maxLength = $columnMaxLength;
			$columnDto->numericPrecision = $columnPrecisionSize;
			$columnDto->numericScale = $columnScale;
			$columnDto->collation = $columnCollation;
			$columnDto->charset = $columnCharset;
			$columnDto->isPrimary = $columnIsPrimary;
			$columnDto->isRequired = $columnIsRequired;
			$columnDto->isBinary = $columnIsBinary;
			$columnDto->isUnsigned = $columnIsUnsigned;
			$columnDto->isUnique = $columnIsUnique;
			$columnDto->isZerofilled = $columnIsZerofilled;
			$columnDto->isAuto = $columnIsAuto;
			$columnDto->isForeign = $columnIsForeign;
			$columnDto->active = $columnActive;

			//$columnDao    = \dao\Factory::createColumn();
			//$columnObject = $columnDao->getByName($tableObject->getId(), $columnName);
			//
			//if (is_null($columnObject))
			//{
			//	$columnDao->insert($columnModel);
			//}
			//else
			//{
			//	$columnModel->setId($columnObject->getId());
			//	$columnDao->update($columnModel);
			//}
			$data[] = $columnDto;
		}
		
		return $data;
	}
	public function listRelations ($database, $tableName = NULL)
	{
		$sql = 'SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ?';

		if (!is_null($tableName)) {
			$sql .= ' AND TABLE_NAME ';

			if (is_array($tableName)) {
				$tableName  = "'" . implode("','", $tableName) . "'";
				$sql       .= "IN ($tableName)";
				$tableName  = NULL;
			} else {
				$sql .= '= ?';
			}
		}

		$sth = $this->conn->prepare($sql);
		$sth->bindParam(1, $this->dbName);

		if (!is_null($tableName)) {
			$sth->bindParam(2, $tableName);
		}

		$sth->execute();

		$relations = $sth->fetchAll(\PDO::FETCH_ASSOC);

		$data = array();

		foreach ($relations as $r) {
			if (!is_null($r['REFERENCED_TABLE_NAME'])) {
				$data[] = array(
								'tableNameSource'       => $r['TABLE_NAME'],
								'columnNameSource'      => $r['COLUMN_NAME'],
								'tableNameDestination'  => $r['REFERENCED_TABLE_NAME'],
								'columnNameDestination' => $r['REFERENCED_COLUMN_NAME'],
								'sequence'              => $r['ORDINAL_POSITION'],
								);
			}
		}

		return $data;
	}
	public function listTables ($tableName = NULL)
	{
		$sql = 'SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?';

		if (!is_null($tableName)) {
			$sql .= ' AND TABLE_NAME ';

			if (is_array($tableName)) {
				$tableName  = "'" . implode("','", $tableName) . "'";
				$sql       .= "IN ($tableName)";
				$tableName  = NULL;
			} else {
				$sql .= '= ?';
			}
		}

		//\jLib\Dev::e($sql);

		$sth = $this->connection->prepare($sql);
		$sth->bindParam(1, $this->dbName);

		if (!is_null($tableName)) {
			$sth->bindParam(2, $tableName);
		}

		$sth->execute();

		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}
	public function listTablesAndColumns ($tableName = null, $columns = null)
	{
		$sql = 'SELECT * FROM information_schema.TABLES t '
			. 'INNER JOIN information_schema.COLUMNS c ON t.table_schema = c.table_schema AND t.table_name = c.table_name '
			. 'WHERE t.TABLE_SCHEMA = ? ';
		if (!is_null($tableName)) {
			$sql .= 'AND t.TABLE_NAME IN (' . \MonitoCli\Database\Helper::serialize($tableName) . ') ';
		}
		$sql .= 'ORDER BY t.TABLE_NAME, c.ORDINAL_POSITION';
		$sth = $this->connection->prepare($sql);

		// \MonitoLib\Dev::ee($sql);

		$sth->bindParam(1, $this->config->database);
		$sth->execute();

		$res = $sth->fetchAll(\PDO::FETCH_ASSOC);

		// \MonitoLib\Dev::pre($res);

		$data = [];
		$currentTable = null;

		foreach ($res as $r) {
			if ($currentTable !== $r['TABLE_NAME']) {
				$tableDto = new \MonitoCli\Database\Dto\Table;
				$tableDto->setTableName($tableName);

				if ($r['TABLE_TYPE'] === 'VIEW') {
					$tableDto->setTableType('view');
				} else {
					$tableDto->setTableType('table');
				}

				$data[] = \MonitoCli\Database\Helper::table($tableDto);
			}

			$columnDto = new \MonitoCli\Database\Dto\Column;
			$columnDto->setTable($r['TABLE_NAME']);
			$columnDto->setName($r['COLUMN_NAME']);
			// $columnDto->setType($column['type']);
			// $columnDto->setLabel($column['label']);
			$columnDto->setDatatype($r['DATA_TYPE']);
			$columnDto->setDefaultvalue($r['COLUMN_DEFAULT']);
			// $columnDto->setMaxlength($column['maxLength']);
			// $columnDto->setNumericprecision($column['numericPrecision']);
			// $columnDto->setNumericscale($column['numericScale']);
			// $columnDto->setCollation($column['collation']);
			// $columnDto->setCharset($column['charset']);
			$columnDto->setIsprimary($r['COLUMN_KEY'] == 'PRI' ? 1 : 0);
			$columnDto->setIsrequired($r['IS_NULLABLE'] === 'YES' ? 0 : 1);
			// $columnDto->setIsbinary($column['isBinary']);
			// $columnDto->setIsunsigned($column['isUnsigned']);
			// $columnDto->setIsunique($column['isUnique']);
			// $columnDto->setIszerofilled($column['isZerofilled']);
			$columnDto->setIsauto($r['EXTRA'] == 'auto_increment' ? 1 : 0);
			// $columnDto->setIsforeign($column['isForeign']);
			$tableDto->addColumn($columnDto);

			$currentTable = $r['TABLE_NAME'];
		}

		return $data;
	}
	public function load ()
	{
		// Loads tables
		$this->loadTables();

		// Loads columns
		$this->loadColumns();

		// Loads relations
		$this->loadRelations();
	}
	private function loadColumns ()
	{
		$columns = $this->listColumns($this->connection->getDbName(), $this->tables);

		$data = array();
		
		foreach ($columns as $c)
		{
			$columnName          = $c['COLUMN_NAME'];
			$columnType          = NULL;
			$columnLabel         = $this->labelIt($c['COLUMN_NAME']);
			$columnDataType      = $c['DATA_TYPE'];
			$columnDefault       = $c['COLUMN_DEFAULT'] == '' ? NULL : $c['COLUMN_DEFAULT'];
			$columnMaxLength     = is_null($c['CHARACTER_MAXIMUM_LENGTH']) ? $c['NUMERIC_PRECISION'] : $c['CHARACTER_MAXIMUM_LENGTH'];
			$columnPrecisionSize = $c['NUMERIC_PRECISION'];
			$columnScale         = $c['NUMERIC_SCALE'];
			$columnCollation     = $c['COLLATION_NAME'];
			$columnCharset       = $c['CHARACTER_SET_NAME'];
			$columnIsPrimary     = $c['COLUMN_KEY'] == 'PRI' ? 1 : 0;
			$columnIsRequired    = $c['IS_NULLABLE'] == 'YES' ? 0 : 1;
			$columnIsBinary      = strpos($c['COLLATION_NAME'], '_bin') !== FALSE ? 0 : 1;
			$columnIsUnsigned    = strpos($c['COLUMN_TYPE'], 'unsigned') !== FALSE ? 0 : 1;
			$columnIsUnique      = $c['COLUMN_KEY'] == 'UNI' ? 1 : 0;
			$columnIsZerofilled  = strpos($c['COLUMN_TYPE'], 'zerofill') !== FALSE ? 0 : 1;
			$columnIsAuto        = $c['EXTRA'] == 'auto_increment' ? 1 : 0;
			$columnIsForeign     = $c['COLUMN_KEY'] == 'MUL' ? 1 : 0;
			$columnActive        = 1;
			$tableName           = $c['TABLE_NAME'];

			//$tableDao    = \dao\Factory::createTable();
			//$tableObject = $tableDao->getByName($tableName);
	
			//if (is_null($tableObject))
			//{
			//	throw new \Exception("Table $tableName not found!");
			//}

			$columnDao = \dao\Factory::createColumn();
			$columnDto = new \model\Column;
			//$columnDto->setTableId($tableObject->getId());
			$columnDto->setName($columnName);
			$columnDto->setType($columnType);
			$columnDto->setLabel($columnLabel);
			$columnDto->setDataType($columnDataType);
			$columnDto->setDefaultValue($columnDefault);
			$columnDto->setMaxLength($columnMaxLength);
			$columnDto->setNumericPrecision($columnPrecisionSize);
			$columnDto->setNumericScale($columnScale);
			$columnDto->setCollation($columnCollation);
			$columnDto->setCharset($columnCharset);
			$columnDto->setIsPrimary($columnIsPrimary);
			$columnDto->setIsRequired($columnIsRequired);
			$columnDto->setIsBinary($columnIsBinary);
			$columnDto->setIsUnsigned($columnIsUnsigned);
			$columnDto->setIsUnique($columnIsUnique);
			$columnDto->setIsZerofilled($columnIsZerofilled);
			$columnDto->setIsAuto($columnIsAuto);
			$columnDto->setIsForeign($columnIsForeign);
			$columnDto->setActive($columnActive);

			//$columnDao    = \dao\Factory::createColumn();
			//$columnObject = $columnDao->getByName($tableObject->getId(), $columnName);
			//
			//if (is_null($columnObject))
			//{
			//	$columnDao->insert($columnModel);
			//}
			//else
			//{
			//	$columnModel->setId($columnObject->getId());
			//	$columnDao->update($columnModel);
			//}
			$data[] = $columnDto;
		}
		
		return $data;
	}
	private function loadRelations ()
	{
		$columnDao   = \dao\Factory::createColumn();
		$relationDao = \dao\Factory::createRelation();
		$tableDao    = \dao\Factory::createTable();

		$relations = $this->listRelations($this->connection->getDbName(), $this->tables);

		//\jLib\Dev::pre($relations);

		foreach ($relations as $r) {
			$referencedTableName = $r['REFERENCED_TABLE_NAME'];

			if (!is_null($referencedTableName)) {
				$sourceTableName      = $r['TABLE_NAME'];
				$sourceColumnName     = $r['COLUMN_NAME'];
				$referencedColumnName = $r['REFERENCED_COLUMN_NAME'];
				$sequence             = $r['ORDINAL_POSITION'];
				$active               = 1;

				$sourceTable  = $tableDao->getByName($sourceTableName);
				$sourceColumn = $columnDao->getByName($sourceTable->getId(), $sourceColumnName);

				$referencedTable  = $tableDao->getByName($referencedTableName);
				
				if (!is_null($referencedTable)) {
					$referencedColumn = $columnDao->getByName($referencedTable->getId(), $referencedColumnName);
	
					//$relationModel->setId($id);
					$relationModel = new \model\Relation;
					$relationModel->setColumnIdSource($sourceColumn->getId());
					$relationModel->setColumnIdDestination($referencedColumn->getId());
					$relationModel->setSequence($sequence);
					$relationModel->setActive($active);
	
					$relation = $relationDao->getByColumnsIds($sourceColumn->getId(), $referencedColumn->getId());
	
					if (is_null($relation)) {
						$relationDao->insert($relationModel);
					} else {
						$relationModel->setId($relation->getId());
						$relationDao->update($relationModel);
					}
				}
				//\jLib\Dev::pre($relationModel);
			}
		}
	}
}