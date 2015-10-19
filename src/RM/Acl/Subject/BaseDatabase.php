<?php

namespace RM\Acl\Subject;

use Nette;
use Nette\Database\Context;
use Nette\Object;
use Nette\Security\IAuthorizator;
use RM\Acl\InvalidArgumentException;
use RM\Acl\ISubject;

/**
 * Bace class for Nette Database subjects.
 *
 * @author    Roman Mátyus
 * @copyright (c) Roman Mátyus 2015
 * @license   MIT
 */
abstract class BaseDatabase extends Object
{
	/** @var Context */
	protected $db;

	public function __construct(Context $db)
	{
		$this->db = $db;
	}

	public function checkImportStructure(array $data)
	{
		if (!isset($data['type']))
			throw new InvalidArgumentException('Argument "type" must be set.');
		elseif ($data['type'] !== $this->getType())
			throw new InvalidArgumentException('Type "' . $data['type'] . '" is not usable in ' . get_class($this) . '. Try "' . $this->getType() . '".');
		elseif (!isset($data['resource']))
			throw new InvalidArgumentException('Argument "resource" must be set.');
		elseif (empty($data['resource']) OR !is_string($data['resource']))
			throw new InvalidArgumentException('Argument "resource" can not be empty and must be string.');
		elseif (!isset($data['access']))
			throw new InvalidArgumentException('Argument "access" must be set.');
		elseif (!is_bool($data['access']))
			throw new InvalidArgumentException('Argument "access" must be boolean.');
	}

	public function getAcl($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL, $id = NULL, array $conditions = array())
	{

		$acl = $this->db->table('acl')
			->where('type', $this->getType())
			->where('resource', $resource)
			->order('access');

		foreach ($conditions as $attribute => $value)
			$acl->where($attribute, $value);

		if ($privilege !== IAuthorizator::ALL) {
			$aclTmp = clone $acl;
			$acl->where('privilege', $privilege);
			$acl = ($acl->count() > 0)
				? $acl
				: $aclTmp->where('privilege', NULL);
		} else {
			$acl->where('privilege', NULL);
		}

		if ($id !== NULL) {
			$aclTmp = clone $acl;
			$acl->where(':' . $resource . '_resource.resource_id', $id);
			$acl = ($acl->count() > 0)
				? $acl
				: $aclTmp->group('acl.id')
					->having('COUNT(:' . $resource . '_resource.resource_id) = 0');
		} else {
			$acl->group('acl.id')
				->having('COUNT(:' . $resource . '_resource.resource_id) = 0');
		}

		return $acl;
	}

	public function saveAcl(array $data)
	{
		/** Save Acl */
		$aclRow = $this->db->table('acl')->insert([
			'type' => $this->getType(),
			'resource' => $data['resource'],
			'privilege' => (isset($data['privilege'])) ? $data['privilege'] : NULL,
			'access' => $data['access'],
		]);

		/** Connect Acl and Resource ID */
		if (isset($data['id'])) {
			$aclRow->related($data['resource'] . '_resource')->insert([
				'resource_id' => $data['id'],
			]);
		}

		return $aclRow;
	}

	public function existsAcl(array $data)
	{
		$acl = $this->db->table('acl')
			->where('type', $this->getType())
			->where('resource', $data['resource'])
			->where('privilege', (isset($data['privilege'])) ? $data['privilege'] : NULL);

		if (isset($data['id']))
			$acl->where(':' . $data['resource'] . '_resource.resource_id', $data['id']);

		return $acl;
	}

}
