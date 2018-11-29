<?php
namespace MonitoCli;

class Oracle
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
		if ($label ==  'id')
		{
			$label = '#';
		}
		else
		{
			$frag = NULL;

			if (preg_match('/_id$/', $label))
			{
				//$frag  = '# ';
				$label = substr($label, 0, -3);
			}

			$parts = explode('_', $label);
			$label = '';

			foreach ($parts as $p)
			{
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
		$sql = 'SELECT * FROM user_tab_columns';

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
			$columnDto->name             = $columnName;
			$columnDto->type             = $columnType;
			$columnDto->label            = $columnLabel;
			$columnDto->dataType         = $columnDataType;
			$columnDto->defaultValue     = $columnDefault;
			$columnDto->maxLength        = $columnMaxLength;
			$columnDto->numericPrecision = $columnPrecisionSize;
			$columnDto->numericScale     = $columnScale;
			$columnDto->collation        = $columnCollation;
			$columnDto->charset          = $columnCharset;
			$columnDto->isPrimary        = $columnIsPrimary;
			$columnDto->isRequired       = $columnIsRequired;
			$columnDto->isBinary         = $columnIsBinary;
			$columnDto->isUnsigned       = $columnIsUnsigned;
			$columnDto->isUnique         = $columnIsUnique;
			$columnDto->isZerofilled     = $columnIsZerofilled;
			$columnDto->isAuto           = $columnIsAuto;
			$columnDto->isForeign        = $columnIsForeign;
			$columnDto->active           = $columnActive;

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
		$sql = 'SELECT * FROM user_tables';

		if (!is_null($tableName)) {
			$sql .= ' WHERE TABLE_NAME ';

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
		$sql = 'SELECT table_name FROM (SELECT view_name AS table_name FROM user_views '
			. 'UNION ALL SELECT table_name FROM user_tables) ';

		if (!is_null($tableName)) {
			$sql .= 'WHERE UPPER(table_name) IN (UPPER(:tablename)) ';
		}

		$sql .= 'ORDER BY table_name';
		$stt = oci_parse($this->connection, $sql);

		if (!is_null($tableName)) {
			oci_bind_by_name($stt, ':tablename', $tableName);
		}

		$exe = @oci_execute($stt);

		if (!$exe) {
			$e = oci_error($stt);
			throw new \Exception($e['message']);
		}

		$data = [];

		while ($r = oci_fetch_row($stt)) {
			$data = \MonitoCli\Database\Helper::table($r[0], 'T');
		}

		return $data;
	}
	public function listTablesAndColumns ($tableName = null, $columns = null)
	{
		$sql = 'SELECT LOWER(t.table_name) AS table_name, LOWER(c.column_name) AS column_name, c.data_type, c.data_precision, c.data_scale, c.nullable, c.column_id, c.default_length, '
			. 'c.data_default, c.character_set_name, c.char_length '
			. 'FROM (SELECT view_name AS table_name FROM user_views '
			. 'UNION ALL SELECT table_name FROM user_tables) t '
			. 'INNER JOIN user_tab_columns c ON t.table_name = c.table_name ';

		if (!is_null($tableName)) {
			$sql .= "WHERE UPPER(t.table_name) IN (UPPER(:tablename)) ";
		}
		if (!is_null($columns)) {
			$sql .= 'AND LOWER(c.column_name) IN (' . \MonitoCli\Database\Helper::serialize($columns) . ') ';
		}

		$sql .= 'ORDER BY t.table_name, c.column_id';
		// echo $sql;exit;
		$stt = oci_parse($this->connection, $sql);

		if (!is_null($tableName)) {
			oci_bind_by_name($stt, ':tablename', $tableName);
		}
		// if (!is_null($tableName)) {
		// 	echo $sql . PHP_EOL;
		// 	echo $tableName . PHP_EOL;
		// 	$columns = \MonitoCli\Database\Helper::serialize($columns);
		// 	echo $columns . PHP_EOL;
		// 	exit;
		// 	oci_bind_by_name($stt, ':columns', $columns);
		// }		

		$exe = @oci_execute($stt);

		if (!$exe)
		{
			$e = oci_error($stt);
			throw new \Exception($e['message']);
		}

		$data = [];
		$currentTable = null;

		while ($r = oci_fetch_assoc($stt)) {
			// \MonitoLib\Dev::pre($r);

			if ($currentTable !== $r['TABLE_NAME']) {
				$tableDto = new \MonitoCli\Database\Dto\Table;
				$tableDto->setTableName($tableName);
				$tableDto->setTableType('T');
				$data[] = \MonitoCli\Database\Helper::table($tableDto);
			}

			$dataType = $r['DATA_TYPE'];
			$dataScale = $r['DATA_SCALE'];

			if ($dataType == 'NUMBER') {
				$type = 'int';
				if ($dataScale > 0) {
					$type = 'float';
				}
			} elseif ($dataType == 'DATE') {
				$type = 'date';
			} else {
				$type = 'varchar';
			}

			$defaultValue = trim(trim(trim($r['DATA_DEFAULT']), "'"));

			if ($defaultValue == 'NULL') {
				$defaultValue = null;
			}


			$columnDto = new \MonitoCli\Database\Dto\Column;
			$columnDto->setTable($r['TABLE_NAME']);
			$columnDto->setName($r['COLUMN_NAME']);
			$columnDto->setType($type);
			// $columnDto->setLabel($column['label']);
			$columnDto->setDataType($type);
			$columnDto->setDefaultValue($defaultValue);
			// $columnDto->setMaxlength($column['maxLength']);
			// $columnDto->setNumericprecision($column['numericPrecision']);
			// $columnDto->setNumericscale($column['numericScale']);
			// $columnDto->setCollation($column['collation']);
			// $columnDto->setCharset($column['charset']);
			// $columnDto->setIsprimary($column['isPrimary']);
			$columnDto->setIsrequired($r['NULLABLE'] == 'Y' ? 0 : 1);
			// $columnDto->setIsbinary($column['isBinary']);
			// $columnDto->setIsunsigned($column['isUnsigned']);
			// $columnDto->setIsunique($column['isUnique']);
			// $columnDto->setIszerofilled($column['isZerofilled']);
			// $columnDto->setIsauto($column['isAuto']);
			// $columnDto->setIsforeign($column['isForeign']);
			$tableDto->addColumn($columnDto);

			$currentTable = $r['TABLE_NAME'];
		}
	



		// $nrows = oci_fetch_all($stt, $res, null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);

		// \MonitoLib\Dev::pre($res);

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

			$columnDao   = \dao\Factory::createColumn();
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

		foreach ($relations as $r)
		{
			$referencedTableName = $r['REFERENCED_TABLE_NAME'];

			if (!is_null($referencedTableName))
			{
				$sourceTableName      = $r['TABLE_NAME'];
				$sourceColumnName     = $r['COLUMN_NAME'];
				$referencedColumnName = $r['REFERENCED_COLUMN_NAME'];
				$sequence             = $r['ORDINAL_POSITION'];
				$active               = 1;

				$sourceTable  = $tableDao->getByName($sourceTableName);
				$sourceColumn = $columnDao->getByName($sourceTable->getId(), $sourceColumnName);

				$referencedTable  = $tableDao->getByName($referencedTableName);
				
				if (!is_null($referencedTable))
				{
					$referencedColumn = $columnDao->getByName($referencedTable->getId(), $referencedColumnName);
	
					//$relationModel->setId($id);
					$relationModel = new \model\Relation;
					$relationModel->setColumnIdSource($sourceColumn->getId());
					$relationModel->setColumnIdDestination($referencedColumn->getId());
					$relationModel->setSequence($sequence);
					$relationModel->setActive($active);
	
					$relation = $relationDao->getByColumnsIds($sourceColumn->getId(), $referencedColumn->getId());
	
					if (is_null($relation))
					{
						$relationDao->insert($relationModel);
					}
					else
					{
						$relationModel->setId($relation->getId());
						$relationDao->update($relationModel);
					}
				}
				//\jLib\Dev::pre($relationModel);
			}
		}
	}
	private function OLDloadTables ()
	{
		$tables = $this->listTables($this->connection->getDbName(), $this->tables);
		
		foreach ($tables as $t)
		{
			$className    = '';
			$tableName    = $t['TABLE_NAME']; 	
			$tableAlias   = $tableName;
			$className    = '';
			$objectName   = '';
			$viewName     = '';
			$singularName = '';
			$pluralName   = '';
			$active       = 1;
			$frag         = explode('_', $tableName);
	
			foreach ($frag as $f)
			{
				$className .= $this->util->toSingular(ucfirst($f));
			}
	
			$objectName = strtolower(substr($className, 0, 1)) . substr($className, 1);
			$viewName   = str_replace('_', '-', strtolower($tableName));
	
			foreach ($frag as $f)
			{
				$singularName .= $this->util->toSingular(ucfirst($f)) . ' ';
			}
	
			foreach ($frag as $f)
			{
				$pluralName .= $this->util->toPlural(ucfirst($f)) . ' ';
			}
			
			$singularName = substr($singularName, 0, -1);
			$pluralName   = substr($pluralName, 0, -1);
	
			$tableModel = new \model\Table;
			$tableModel->setProjectId($this->projectId);
			$tableModel->setConnectionId($this->connection->getId());
			$tableModel->setTableName($tableName);
			$tableModel->setTableAlias($tableAlias);
			$tableModel->setClassName($className);
			$tableModel->setObjectName($objectName);
			$tableModel->setViewName($viewName);
			$tableModel->setSingularName($singularName);
			$tableModel->setPluralName($pluralName);
			$tableModel->setActive($active);
			//\jLib\Dev::pre($tableModel);
	
			$tableDao    = \dao\Factory::createTable();
			$tableObject = $tableDao->getByName($tableName);
	
			if (is_null($tableObject))
			{
				$tableDao->insert($tableModel);
			}
			else
			{
				$tableModel->setId($tableObject->getId());
				$tableDao->update($tableModel);
			}
		}
	}
}