<?php

/**
 * Test: Subject\Role->asAllowed()
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

$database = 'test_' . Nette\Utils\Random::generate(10, 'a-zA-Z');
$connect($database);

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/test1.sql");

$subject = new RM\Acl\Subject\Role($context);

/**
 * Role 'guest' has access to book:detail.
 * Role 'guest' has not access to book:detail '2001: A Space Odyssey'.
 */

$user = Mockery::mock('Nette\Security\User')
	->shouldReceive('getRoles')->andReturn(['guest'])
	->getMock();

Assert::same(NULL, $subject->isAllowed($user, 'book'));

Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail', 1));
Assert::same(FALSE, $subject->isAllowed($user, 'book', 'detail', 2));

Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish', 1));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish', 2));

Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment', 1));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment', 2));

Assert::same(NULL, $subject->isAllowed($user, 'book', 'undefined'));
Assert::same(NULL, $subject->isAllowed($user, 'undefined', 'undefined'));

/**
 * Role 'registred' has access to book:detail
 * Role 'registred' has access to book:comment
 */
$user = Mockery::mock('Nette\Security\User')
	->shouldReceive('getRoles')->andReturn(['registred'])
	->getMock();

Assert::same(NULL, $subject->isAllowed($user, 'book'));

Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail', 1));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail', 2));

Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish', 1));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish', 2));

Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 1));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 2));

Assert::same(NULL, $subject->isAllowed($user, 'book', 'undefined'));
Assert::same(NULL, $subject->isAllowed($user, 'undefined', 'undefined'));

/**
 * Role 'author' has access to book:publish and inherit access from 'registred'.
 */
$user = Mockery::mock('Nette\Security\User')
	->shouldReceive('getRoles')->andReturn(['author'])
	->getMock();

Assert::same(NULL, $subject->isAllowed($user, 'book'));

Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail', 1));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail', 2));

Assert::same(TRUE, $subject->isAllowed($user, 'book', 'publish'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'publish', 1));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'publish', 2));

Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 1));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 2));

Assert::same(NULL, $subject->isAllowed($user, 'book', 'undefined'));
Assert::same(NULL, $subject->isAllowed($user, 'undefined', 'undefined'));

/**
 * Role 'superadmin' has access to book:*.
 */
$user = Mockery::mock('Nette\Security\User')
	->shouldReceive('getRoles')->andReturn(['superadmin'])
	->getMock();

Assert::same(TRUE, $subject->isAllowed($user, 'book'));

Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail', 1));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail', 2));

Assert::same(TRUE, $subject->isAllowed($user, 'book', 'publish'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'publish', 1));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'publish', 2));

Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 1));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 2));

Assert::same(TRUE, $subject->isAllowed($user, 'book', 'undefined'));
Assert::same(NULL, $subject->isAllowed($user, 'undefined', 'undefined'));


$disconnect($database);
