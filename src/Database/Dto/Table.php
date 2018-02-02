<?php
namespace MonitoCli\Database\Dto;

class Table
{
	private $tableName;
	private $tableAlias;
	private $className;
	private $objectName;
	private $tableType;
	private $singularName;
	private $pluralName;
	private $columns = [];

	public function addColumn ($column)
	{
		$this->columns[] = $column;
		return $this;
	}
	/**
	* getTableName
	*
	* @return $tableName
	*/
	public function getTableName ()
	{
		return $this->tableName;
	}
	/**
	* getTableAlias
	*
	* @return $tableAlias
	*/
	public function getTableAlias ()
	{
		return $this->tableAlias;
	}
	/**
	* getClassName
	*
	* @return $className
	*/
	public function getClassName ()
	{
		return $this->className;
	}
	/**
	* getObjectName
	*
	* @return $objectName
	*/
	public function getObjectName ()
	{
		return $this->objectName;
	}
	/**
	* getTableType
	*
	* @return $tableType
	*/
	public function getTableType ()
	{
		return $this->tableType;
	}
	/**
	* getSingularName
	*
	* @return $singularName
	*/
	public function getSingularName ()
	{
		return $this->singularName;
	}
	/**
	* getPluralName
	*
	* @return $pluralName
	*/
	public function getPluralName ()
	{
		return $this->pluralName;
	}
	/**
	* getColumns
	*
	* @return $columns
	*/
	public function getColumns ()
	{
		return $this->columns;
	}
	/**
	 * setTableName
	 *
	 * @param $tableName
	 */
	public function setTableName ($tableName)
	{
		$this->tableName = $tableName;
		return $this;
	}
	/**
	 * setTableAlias
	 *
	 * @param $tableAlias
	 */
	public function setTableAlias ($tableAlias)
	{
		$this->tableAlias = $tableAlias;
		return $this;
	}
	/**
	 * setClassName
	 *
	 * @param $className
	 */
	public function setClassName ($className)
	{
		$this->className = $className;
		return $this;
	}
	/**
	 * setObjectName
	 *
	 * @param $objectName
	 */
	public function setObjectName ($objectName)
	{
		$this->objectName = $objectName;
		return $this;
	}
	/**
	 * setTableType
	 *
	 * @param $tableType
	 */
	public function setTableType ($tableType)
	{
		$this->tableType = $tableType;
		return $this;
	}
	/**
	 * setSingularName
	 *
	 * @param $singularName
	 */
	public function setSingularName ($singularName)
	{
		$this->singularName = $singularName;
		return $this;
	}
	/**
	 * setPluralName
	 *
	 * @param $pluralName
	 */
	public function setPluralName ($pluralName)
	{
		$this->pluralName = $pluralName;
		return $this;
	}
}