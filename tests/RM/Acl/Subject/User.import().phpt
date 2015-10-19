<?php

/**
 * Test: Subject\User->import()
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

$database = 'test_' . Nette\Utils\Random::generate(10, 'a-zA-Z');
$connect($database);

$subject = new RM\Acl\Subject\User($context);

/**
 * User Arthur C. Clarke has not access to book:comment 2001: I, robot
 * User John Wick has access to book:comment 2001: A Space Odyssey
 */

$subject->import([
	'resource' => 'book',
	'privilege' => 'comment',
	'type' => 'user',
	'id' => 1,
	'user' => 4,
	'access' => FALSE,
]);
$subject->import([
	'resource' => 'book',
	'privilege' => 'comment',
	'type' => 'user',
	'id' => 2,
	'user' => 2,
	'access' => TRUE,
]);

Assert::same(
	[
		1 => [
			'id' => 1,
			'resource' => 'book',
			'privilege' => 'comment',
			'type' => 'user',
			'access' => 0,
		],
		[
			'id' => 2,
			'resource' => 'book',
			'privilege' => 'comment',
			'type' => 'user',
			'access' => 1,
		],
	],
	array_map('toArray', $context->table('acl')->fetchAll())
);

Assert::same(
	[
		[
			'acl_id' => 1,
			'user_id' => 4,
		],
		[
			'acl_id' => 2,
			'user_id' => 2,
		],
	],
	array_map('toArray', $context->table('acl_user')->fetchAll())
);

Assert::same(
	[
		[
			'acl_id' => 1,
			'resource_id' => 1,
		],
		[
			'acl_id' => 2,
			'resource_id' => 2,
		],
	],
	array_map('toArray', $context->table('book_resource')->fetchAll())
);

function toArray ($row) {
	return $row->toArray();
}

$disconnect($database);
