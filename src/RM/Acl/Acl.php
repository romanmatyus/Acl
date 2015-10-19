<?php

namespace RM\Acl;

use Nette;

/**
 * Authorizator
 *
 * @author    Roman Mátyus
 * @copyright (c) Roman Mátyus 2015
 * @license   MIT
 */
class Acl implements Nette\Security\IAuthorizator
{
	/** @var bool */
	public $default = FALSE;

	/** @var [] of ISubject */
	private $subjects = [];

	private $cache = [];

	/**
	 * Has a user effective access to the Resource?
	 * @param  User   user
	 * @param  mixed  resource
	 * @param  mixed  privilege
	 * @param  mixed  id
	 * @return bool
	 */
	function isAllowed($user, $resource, $privilege = NULL, $id = NULL)
	{
		if (isset($this->cache[$user->getId()][$resource][$privilege][$id]))
			return $this->cache[$user->getId()][$resource][$privilege][$id];

		$allowed = $this->default;
		foreach ($this->subjects as $subject) {
			$tmp = $subject->isAllowed($user, $resource, $privilege, $id);
			if ($tmp !== NULL)
				$allowed = $tmp;
		}

		$this->cache[$user->getId()][$resource][$privilege][$id] = (bool) $allowed;

		return (bool) $allowed;
	}

	/**
	 * Add subject for checking access.
	 * @param ISubject $subject
	 * @return Acl
	 */
	public function addSubject(ISubject $subject)
	{
		$this->subjects[$subject->getType()] = $subject;
		return $this;
	}

	/**
	 * Add rule from other sources.
	 * @param  array  $data
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function addRule(array $data)
	{
		if (!isset($data['type']))
			throw new InvalidArgumentException('Argument "type" must be set.');
		elseif (!isset($this->subjects[$data['type']]))
			throw new InvalidArgumentException('Type "' . $data['type'] . '" is not registred.');

		$this->subjects[$data['type']]->import($data);
		return $this;
	}
}