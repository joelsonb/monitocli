<?php
namespace MonitoCli\Database;

class Helper
{
	static public function column ($column)
	{
		$columnDto = new \MonitoCli\Database\Dto\Column;
		$columnDto->setTable($column['table_name']);
		$columnDto->setName($column['column_name']);
		$columnDto->setType($column['type']);
		$columnDto->setLabel($column['label']);
		$columnDto->setDatatype($column['data_type']);
		$columnDto->setDefaultvalue($column['default_value']);
		$columnDto->setMaxlength($column['max_length']);
		$columnDto->setNumericprecision($column['numeric_precision']);
		$columnDto->setNumericscale($column['numeric_scale']);
		$columnDto->setCollation($column['collation']);
		$columnDto->setCharset($column['charset']);
		$columnDto->setIsprimary($column['is_primary']);
		$columnDto->setIsrequired($column['is_required']);
		$columnDto->setIsbinary($column['is_binary']);
		$columnDto->setIsunsigned($column['is_unsigned']);
		$columnDto->setIsunique($column['is_unique']);
		$columnDto->setIszerofilled($column['is_zerofilled']);
		$columnDto->setIsauto($column['is_auto']);
		$columnDto->setIsforeign($column['is_foreign']);


	}
	static public function serialize ($string)
	{
		$parts = explode(',', $string);
		return "'" . implode("','", array_map('trim', $parts)) . "'";
	}
	static public function table ($tableDto)
	{
		$tableName    = $tableDto->getTableName();
		$className    = '';
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
			$className .= self::toSingular(ucfirst($f));
		}

		$objectName = strtolower(substr($className, 0, 1)) . substr($className, 1);
		$viewName   = str_replace('_', '-', strtolower($tableName));

		foreach ($frag as $f)
		{
			$singularName .= self::toSingular(ucfirst($f)) . ' ';
		}

		foreach ($frag as $f)
		{
			$pluralName .= self::toPlural(ucfirst($f)) . ' ';
		}
		
		$singularName = substr($singularName, 0, -1);
		$pluralName   = substr($pluralName, 0, -1);

		$tableDto->setTableName($tableName);
		$tableDto->setTableAlias($tableAlias);
		$tableDto->setClassName($className);
		$tableDto->setObjectName($objectName);
		$tableDto->setSingularName($singularName);
		$tableDto->setPluralName($pluralName);
		return $tableDto;
	}
	static public function indent ($tabs, $spaces = 0)
	{
		return str_repeat("\t", $tabs) . str_repeat(' ', $spaces);
	}
	static public function toLowerCamelCase ($string)
	{
		$frag  = explode('_', $string);
		$count = count($frag);
		$newString = $frag[0];
		
		for ($i = 1; $i < $count; $i++)
		{
			$newString .= ucfirst($frag[$i]);
		}
		
		return $newString;
	}
	static public function toPlural ($string)
	{
		return $string;
	}
	static public function toSingular ($string)
	{
		if (strtolower($string) == 'status')
		{
			return $string;
		}
		if (preg_match('/ens$/', $string))
		{
			$string = substr($string, 0, -3) . 'em';
		}
		if (preg_match('/oes$/', $string))
		{
			$string = substr($string, 0, -3) . 'ao';
		}
		if (preg_match('/ais$/', $string))
		{
			$string = substr($string, 0, -3) . 'al';
		}
		if (preg_match('/res$/', $string))
		{
			$string = substr($string, 0, -2);
		}
		if (preg_match('/tchs$/', $string))
		{
			$string = substr($string, 0, -1);
		}
		if (preg_match('/[adeiouglmnprt]s$/', $string))
		{
			$string = substr($string, 0, -1);
		}

		return $string;
	}
}