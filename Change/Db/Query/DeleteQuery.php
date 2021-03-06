<?php
namespace Change\Db\Query;

/**
 * @api
 * @name \Change\Db\Query\DeleteQuery
 */
class DeleteQuery extends AbstractQuery
{
	/**
	 * @var Clauses\DeleteClause
	 */
	protected $deleteClause;

	/**
	 * @var Clauses\FromClause
	 */
	protected $fromClause;

	/**
	 * @var Clauses\WhereClause
	 */
	protected $whereClause;

	/**
	 * @api
	 * @return Clauses\DeleteClause|null
	 */
	public function getDeleteClause()
	{
		return $this->deleteClause;
	}

	/**
	 * @api
	 * @param Clauses\DeleteClause $deleteClause
	 */
	public function setDeleteClause(Clauses\DeleteClause $deleteClause)
	{
		$this->deleteClause = $deleteClause;
	}

	/**
	 * @api
	 * @return Clauses\FromClause|null
	 */
	public function getFromClause()
	{
		return $this->fromClause;
	}

	/**
	 * @api
	 * @param Clauses\FromClause $fromClause
	 */
	public function setFromClause(Clauses\FromClause $fromClause)
	{
		$this->fromClause = $fromClause;
	}

	/**
	 * @api
	 * @return Clauses\WhereClause|null
	 */
	public function getWhereClause()
	{
		return $this->whereClause;
	}

	/**
	 * @api
	 * @param Clauses\WhereClause $whereClause
	 */
	public function setWhereClause(Clauses\WhereClause $whereClause)
	{
		$this->whereClause = $whereClause;
	}

	/**
	 * @api
	 * @throws \RuntimeException
	 */
	public function checkCompile()
	{
		if ($this->deleteClause === null)
		{
			throw new \RuntimeException('DeleteClause can not be null', 42006);
		}
		if ($this->fromClause === null)
		{
			throw new \RuntimeException('FromClause can not be null', 42007);
		}
	}

	/**
	 * @api
	 * @throws \RuntimeException
	 * @return string
	 */
	public function toSQL92String()
	{
		$this->checkCompile();

		$parts = array($this->deleteClause->toSQL92String(), $this->fromClause->toSQL92String());
		if ($this->whereClause !== null)
		{
			$parts[] = $this->whereClause->toSQL92String();
		}
		return implode(' ', $parts);
	}

	/**
	 * @api
	 * @return integer
	 */
	public function execute()
	{
		return $this->getDbProvider()->executeQuery($this);
	}
}
