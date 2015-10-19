<?php

namespace RM\Acl\Subject;

use Nette;
use Nette\Security\IAuthorizator;
use RM\Acl\InvalidArgumentException;
use RM\Acl\IRelation;
use RM\Acl\ISubject;

/**
 * Class for check access by relation in Acl.
 *
 * @author    Roman Mátyus
 * @copyright (c) Roman Mátyus 2015
 * @license   MIT
 */
class Relation extends BaseDatabase implements ISubject
{
	/** @var [] of ISubject */
	private $resources = [];

	private $simpleCache = [];

	/**
	 * Has a user effective access to the Resource?
	 * @param  User   user
	 * @param  mixed  resource
	 * @param  mixed  privilege
	 * @param  mixed  id
	 * @return bool
	 */
	public function isAllowed($user, $resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL, $id = NULL)
	{
		$allowed = NULL;
		if (isset($this->resources[$resource])) {
			foreach ($this->resources[$resource]['relationships'] as $relation) {
				if (isset($this->simpleCache['resource'][$user->getId()][$resource][$id][$relation])) {
					if (!$this->simpleCache['resource'][$user->getId()][$resource][$id][$relation]) {
						continue;
					}
				} else {
					$this->simpleCache['resource'][$user->getId()][$resource][$id][$relation] = $this->resources[$resource]['cb']($user, $relation, $id);
				}

				if ($this->simpleCache['resource'][$user->getId()][$resource][$id][$relation]) {

					$acl = $this->getAcl($resource, $privilege, $id, [':acl_relation.name' => $relation]);
					
					try {
						foreach ($acl as $rule) {
							if ($rule->access !== NULL) {
								if ((bool) $rule->access === FALSE)
									return (bool) $rule->access;
								else
									$allowed = (bool) $rule->access;
							}
						}
					} catch (Nette\InvalidArgumentException $e) {
						if (strncmp($e->getMessage(), 'No reference found for', strlen('No reference found for')) !== 0)
							throw $e;
					}

				}
			}
		}
		return $allowed;
	}

	/**
	 * Get type of subject.
	 * @return string
	 */
	public function getType()
	{
		return ISubject::RELATION;
	}

	/**
	 * Add resource with relation.
	 * @param string   $name          Name of resource.
	 * @param array    $relationships Name of relations.
	 * @param callable $callback      Callback with checking relation.
	 * @return Relation
	 */
	public function addResource($name, array $relationships, callable $callback)
	{
		$this->resources[$name] = [
			'cb' => $callback,
			'relationships' => $relationships,
		];
		return $this;
	}

	/**
	 * Import structure from other sources.
	 * @param  array  $data
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function import(array $data)
	{
		$this->checkImportStructure($data);

		try {
			$aclRow = $this->existsAcl($data)->fetch();

			if (!$aclRow) {
				$this->db->beginTransaction();

				$aclRow = $this->saveAcl($data);

				/** Save Relation */
				$aclRow->related('acl_relation')->insert([
					'name' => $data['name'],
				]);

				$this->db->commit();
			}

			return $aclRow;
		} catch (\Exception $e) {
			if ($this->db->getConnection()->getPdo()->inTransaction())
				$this->db->rollBack();
			throw $e;
		}
	}

	/**
	 * Check if Acl rule exists in database.
	 * @param  array  $data Description of Acl
	 * @return Nette\Database\Table\Selection
	 */
	public function existsAcl(array $data)
	{
		$acl = parent::existsAcl($data);
		$acl->where(':acl_relation.name', $data['name']);
		return $acl;
	}

	/**
	 * Check input format.
	 * @param  array  $data
	 * @return Relation
	 */
	public function checkImportStructure(array $data)
	{
		parent::checkImportStructure($data);
		if (!isset($data['name']))
			throw new InvalidArgumentException('Argument "name" must be set.');
		elseif (empty($data['name']) OR !is_string($data['name']))
			throw new InvalidArgumentException('Argument "name" can not be empty and must be string.');
		return $this;
	}

}
