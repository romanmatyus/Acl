<?php

namespace RM\Acl\Subject;

use Nette;
use Nette\Security\IAuthorizator;
use RM\Acl\InvalidArgumentException;
use RM\Acl\ISubject;

/**
 * Class for check access by Role in Acl.
 *
 * @author    Roman Mátyus
 * @copyright (c) Roman Mátyus 2015
 * @license   MIT
 */
class Role extends BaseDatabase implements ISubject
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
		foreach ($user->getRoles() as $role) {
			$allowed = $this->checkByRole($role, $resource, $privilege, $id);
			if ($allowed !== NULL)
				return (bool) $allowed;
		}
	}

	/**
	 * Get type of subject
	 * @return string
	 */
	public function getType()
	{
		return ISubject::ROLE;
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

				/** Save Role */
				$roleRow = $this->db->table('role')
					->where('name', $data['name'])
					->fetch();
				if (!$roleRow)
					$roleRow = $this->db->table('role')->insert([
						'name' => $data['name'],
					]);

				/** Connect Acl and Role */
				$aclRow->related('acl_role')->insert([
					'role_id' => $roleRow->id,
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
		$acl->where(':acl_role.role.name', $data['name']);
		return $acl;
	}

	public function checkImportStructure(array $data)
	{
		parent::checkImportStructure($data);
		if (!isset($data['name']))
			throw new InvalidArgumentException('Argument "name" must be set.');
		elseif (empty($data['name']) OR !is_string($data['name']))
			throw new InvalidArgumentException('Argument "name" can not be empty and must be string.');
	}

	private function checkByRole($role, $resource, $privilege, $id)
	{
		if (isset($this->simpleCache[$role][$resource][$privilege][$id]))
			return $this->simpleCache[$role][$resource][$privilege][$id];
		$allowed = NULL;

		$acl = $this->getAcl($resource, $privilege, $id, [':acl_role.role.name' => $role]);

		try {
			foreach ($acl as $rule) {
				$this->simpleCache[$role][$resource][$privilege][$id] = $rule->access;
				return $rule->access;
			}
		} catch (Nette\InvalidArgumentException $e) {
			if (strncmp($e->getMessage(), 'No reference found for', strlen('No reference found for')) !== 0)
				throw $e;
		}

		$roleRow = $this->db->table('role')->where('name', $role)->fetch();
		if ($roleRow && $roleRow->parent !== NULL) {
			$allowed = $this->checkByRole($roleRow->ref('role', 'parent')->name, $resource, $privilege, $id);
			if ($allowed !== NULL)
				return $allowed;
		}
	}

}
