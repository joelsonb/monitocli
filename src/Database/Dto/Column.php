<?php
namespace MonitoCli\Database\Dto;

class Column
{
	private $table;
	private $name;
	private $type;
	private $label;
	private $dataType;
	private $defaultValue;
	private $maxLength;
	private $numericPrecision;
	private $numericScale;
	private $collation;
	private $charset;
	private $isPrimary;
	private $isRequired;
	private $isBinary;
	private $isUnsigned;
	private $isUnique;
	private $isZerofilled;
	private $isAuto;
	private $isForeign;

	/**
	* getTable
	*
	* @return $table
	*/
	public function getTable ()
	{
		return $this->table;
	}
	/**
	* getName
	*
	* @return $name
	*/
	public function getName ()
	{
		return $this->name;
	}
	/**
	* getType
	*
	* @return $type
	*/
	public function getType ()
	{
		return $this->type;
	}
	/**
	* getLabel
	*
	* @return $label
	*/
	public function getLabel ()
	{
		return $this->label;
	}
	/**
	* getDataType
	*
	* @return $dataType
	*/
	public function getDataType ()
	{
		return $this->dataType;
	}
	/**
	* getDefaultValue
	*
	* @return $defaultValue
	*/
	public function getDefaultValue ()
	{
		return $this->defaultValue;
	}
	/**
	* getMaxLength
	*
	* @return $maxLength
	*/
	public function getMaxLength ()
	{
		return $this->maxLength;
	}
	/**
	* getNumericPrecision
	*
	* @return $numericPrecision
	*/
	public function getNumericPrecision ()
	{
		return $this->numericPrecision;
	}
	/**
	* getNumericScale
	*
	* @return $numericScale
	*/
	public function getNumericScale ()
	{
		return $this->numericScale;
	}
	/**
	* getCollation
	*
	* @return $collation
	*/
	public function getCollation ()
	{
		return $this->collation;
	}
	/**
	* getCharset
	*
	* @return $charset
	*/
	public function getCharset ()
	{
		return $this->charset;
	}
	/**
	* getIsPrimary
	*
	* @return $isPrimary
	*/
	public function getIsPrimary ()
	{
		return $this->isPrimary;
	}
	/**
	* getIsRequired
	*
	* @return $isRequired
	*/
	public function getIsRequired ()
	{
		return $this->isRequired;
	}
	/**
	* getIsBinary
	*
	* @return $isBinary
	*/
	public function getIsBinary ()
	{
		return $this->isBinary;
	}
	/**
	* getIsUnsigned
	*
	* @return $isUnsigned
	*/
	public function getIsUnsigned ()
	{
		return $this->isUnsigned;
	}
	/**
	* getIsUnique
	*
	* @return $isUnique
	*/
	public function getIsUnique ()
	{
		return $this->isUnique;
	}
	/**
	* getIsZerofilled
	*
	* @return $isZerofilled
	*/
	public function getIsZerofilled ()
	{
		return $this->isZerofilled;
	}
	/**
	* getIsAuto
	*
	* @return $isAuto
	*/
	public function getIsAuto ()
	{
		return $this->isAuto;
	}
	/**
	* getIsForeign
	*
	* @return $isForeign
	*/
	public function getIsForeign ()
	{
		return $this->isForeign;
	}
	/**
	 * setTable
	 *
	 * @param $table
	 */
	public function setTable ($table)
	{
		$this->table = $table;
		return $this;
	}
	/**
	 * setName
	 *
	 * @param $name
	 */
	public function setName ($name)
	{
		$this->name = $name;
		return $this;
	}
	/**
	 * setType
	 *
	 * @param $type
	 */
	public function setType ($type)
	{
		$this->type = $type;
		return $this;
	}
	/**
	 * setLabel
	 *
	 * @param $label
	 */
	public function setLabel ($label)
	{
		$this->label = $label;
		return $this;
	}
	/**
	 * setDataType
	 *
	 * @param $dataType
	 */
	public function setDataType ($dataType)
	{
		$this->dataType = $dataType;
		return $this;
	}
	/**
	 * setDefaultValue
	 *
	 * @param $defaultValue
	 */
	public function setDefaultValue ($defaultValue)
	{
		$this->defaultValue = $defaultValue;
		return $this;
	}
	/**
	 * setMaxLength
	 *
	 * @param $maxLength
	 */
	public function setMaxLength ($maxLength)
	{
		$this->maxLength = $maxLength;
		return $this;
	}
	/**
	 * setNumericPrecision
	 *
	 * @param $numericPrecision
	 */
	public function setNumericPrecision ($numericPrecision)
	{
		$this->numericPrecision = $numericPrecision;
		return $this;
	}
	/**
	 * setNumericScale
	 *
	 * @param $numericScale
	 */
	public function setNumericScale ($numericScale)
	{
		$this->numericScale = $numericScale;
		return $this;
	}
	/**
	 * setCollation
	 *
	 * @param $collation
	 */
	public function setCollation ($collation)
	{
		$this->collation = $collation;
		return $this;
	}
	/**
	 * setCharset
	 *
	 * @param $charset
	 */
	public function setCharset ($charset)
	{
		$this->charset = $charset;
		return $this;
	}
	/**
	 * setIsPrimary
	 *
	 * @param $isPrimary
	 */
	public function setIsPrimary ($isPrimary)
	{
		$this->isPrimary = $isPrimary;
		return $this;
	}
	/**
	 * setIsRequired
	 *
	 * @param $isRequired
	 */
	public function setIsRequired ($isRequired)
	{
		$this->isRequired = $isRequired;
		return $this;
	}
	/**
	 * setIsBinary
	 *
	 * @param $isBinary
	 */
	public function setIsBinary ($isBinary)
	{
		$this->isBinary = $isBinary;
		return $this;
	}
	/**
	 * setIsUnsigned
	 *
	 * @param $isUnsigned
	 */
	public function setIsUnsigned ($isUnsigned)
	{
		$this->isUnsigned = $isUnsigned;
		return $this;
	}
	/**
	 * setIsUnique
	 *
	 * @param $isUnique
	 */
	public function setIsUnique ($isUnique)
	{
		$this->isUnique = $isUnique;
		return $this;
	}
	/**
	 * setIsZerofilled
	 *
	 * @param $isZerofilled
	 */
	public function setIsZerofilled ($isZerofilled)
	{
		$this->isZerofilled = $isZerofilled;
		return $this;
	}
	/**
	 * setIsAuto
	 *
	 * @param $isAuto
	 */
	public function setIsAuto ($isAuto)
	{
		$this->isAuto = $isAuto;
		return $this;
	}
	/**
	 * setIsForeign
	 *
	 * @param $isForeign
	 */
	public function setIsForeign ($isForeign)
	{
		$this->isForeign = $isForeign;
		return $this;
	}
}