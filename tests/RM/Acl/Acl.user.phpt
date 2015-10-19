<?php

/**
 * Test: Acl->isAllowed()
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

$database = 'test_' . Nette\Utils\Random::generate(10, 'a-zA-Z');
$connect($database);

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/test1.sql");

$relation = new RM\Acl\Subject\Relation($context);
$relation->addResource('book', ['author', 'customer'], function ($user, $relation, $id) use ($context) {
	$book = $context->table('book')->get($id);
	if ($book instanceof Nette\Database\Table\ActiveRow) {
		switch ($relation) {
			case 'author':
				return $book->author === $user->id;
			case 'customer':
				return (bool) $book->related('user_book')->where('user_id', $user->id)->fetch();
		}
	}
});

$acl = new RM\Acl\Acl;
$acl->addSubject(new RM\Acl\Subject\Role($context))
	->addSubject($relation)
	->addSubject(new RM\Acl\Subject\User($context));

/**
 * Role 'guest' has access to book:detail.
 * Role 'guest' has not access to book:detail 2001: A Space Odyssey.
 */

$user = Mockery::mock('Nette\Security\User')
	->shouldReceive('getId')->andReturn(NULL)
	->shouldReceive('getRoles')->andReturn(['guest'])
	->getMock();

Assert::same(FALSE, $acl->isAllowed($user, 'book'));
Assert::same(FALSE, $acl->isAllowed($user, 'book', NULL, 1)); // I, robot
Assert::same(FALSE, $acl->isAllowed($user, 'book', NULL, 2)); // 2001: A Space Odyssey

Assert::same(TRUE, $acl->isAllowed($user, 'book', 'detail'));
Assert::same(TRUE, $acl->isAllowed($user, 'book', 'detail', 1)); // I, robot
Assert::same(FALSE, $acl->isAllowed($user, 'book', 'detail', 2)); // 2001: A Space Odyssey

Assert::same(FALSE, $acl->isAllowed($user, 'book', 'publish'));
Assert::same(FALSE, $acl->isAllowed($user, 'book', 'publish', 1)); // I, robot
Assert::same(FALSE, $acl->isAllowed($user, 'book', 'publish', 2)); // 2001: A Space Odyssey

Assert::same(FALSE, $acl->isAllowed($user, 'book', 'comment'));
Assert::same(FALSE, $acl->isAllowed($user, 'book', 'comment', 1)); // I, robot
Assert::same(FALSE, $acl->isAllowed($user, 'book', 'comment', 2)); // 2001: A Space Odyssey

Assert::same(FALSE, $acl->isAllowed($user, 'book', 'undefined'));
Assert::same(FALSE, $acl->isAllowed($user, 'undefined', 'undefined'));

$disconnect($database);
