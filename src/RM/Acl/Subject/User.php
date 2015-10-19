<?php

namespace RM\Acl\Subject;

use Nette;
use Nette\Security\IAuthorizator;
use RM\Acl\InvalidArgumentException;
use RM\Acl\IRelation;
use RM\Acl\ISubject;

/**
 * Class for check access by user in Acl.
 *
 * @author    Roman Mátyus
 * @copyright (c) Roman Mátyus 2015
 * @license   MIT
 */
class User extends BaseDatabase implements ISubject
{
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
		if (isset($this->simpleCache[$user->getId()][$resource][$privilege][$id]))
			return $this->simpleCache[$user->getId()][$resource][$privilege][$id];

		$acl = $this->getAcl($resource, $privilege, $id, [':acl_user.user_id' => $user->getId()]);

		try {
			foreach ($acl as $rule) {
				$this->simpleCache[$user->getId()][$resource][$privilege][$id] = (bool) $rule->access;
				return (bool) $rule->access;
			}
		} catch (Nette\InvalidArgumentException $e) {
			if (strncmp($e->getMessage(), 'No reference found for', strlen('No reference found for')) !== 0)
				throw $e;
		}
	}

	/**
	 * Get type of subject
	 * @return string
	 */
	public function getType()
	{
		return ISubject::USER;
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
				$aclRow->related('acl_user')->insert([
					'user_id' => $data['user'],
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

	public function existsAcl(array $data)
	{
		$acl = parent::existsAcl($data);
		$acl->where(':acl_user.user_id', $data['user']);
		return $acl;
	}

	public function checkImportStructure(array $data)
	{
		parent::checkImportStructure($data);
		if (!isset($data['user']))
			throw new InvalidArgumentException('Argument "user" must be set.');
		elseif (empty($data['user']))
			throw new InvalidArgumentException('Argument "user" can not be empty.');
	}

}
