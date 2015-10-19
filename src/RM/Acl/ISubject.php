<?php

namespace RM\Acl;

use Nette\Security\IAuthorizator;

interface ISubject
{
	const ROLE = 'role';
	const RELATION = 'relation';
	const USER = 'user';

	/**
	 * Has a user effective access to the Resource?
	 * @param  Nette\Security\User   user
	 * @param  mixed  resource
	 * @param  mixed  privilege
	 * @param  mixed  id
	 * @return bool
	 */
	function isAllowed($user, $resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL, $id);

	/**
	 * Get name of Subject
	 * @return string
	 */
	function getType();

	/**
	 * Import structure from other sources.
	 * @param  array  $data
	 * @return bool
	 */
	function import(array $data);
}