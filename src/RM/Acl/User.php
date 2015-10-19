<?php

namespace RM\Acl;

use Nette;
use Nette\Security\IAuthorizator;

/**
 * User authentication and authorization.
 */
class User extends Nette\Security\User
{
	/**
	 * Has a user effective access to the Resource?
	 * @param  mixed  resource
	 * @param  mixed  privilege
	 * @param  mixed  id
	 * @return bool
	 */
	public function isAllowed($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL, $id = NULL)
	{
		return $this->getAuthorizator()->isAllowed($this, $resource, $privilege, $id);
	}

}
