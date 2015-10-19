<?php

/**
 * Test: Subject\Role->import()
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

$database = 'test_' . Nette\Utils\Random::generate(10, 'a-zA-Z');
$connect($database);

$subject = new RM\Acl\Subject\Role($context);

$subject->import([
	'resource' => 'book',
	'privilege' => 'delete',
	'type' => 'role',
	'name' => 'admin',
	'access' => TRUE,
]);
$subject->import([
	'resource' => 'book',
	'id' => 14,
	'privilege' => 'read',
	'type' => 'role',
	'name' => 'admin',
	'access' => FALSE,
]);


Assert::same(
	[
		1 => [
			'id' => 1,
			'resource' => 'book',
			'privilege' => 'delete',
			'type' => 'role',
			'access' => 1,
		],
		[
			'id' => 2,
			'resource' => 'book',
			'privilege' => 'read',
			'type' => 'role',
			'access' => 0,
		],
	],
	array_map('toArray', $context->table('acl')->fetchAll())
);

Assert::same(
	[
		[
			'acl_id' => 1,
			'role_id' => 1,
		],
		[
			'acl_id' => 2,
			'role_id' => 1,
		],
	],
	array_map('toArray', $context->table('acl_role')->fetchAll())
);

Assert::same(
	[
		1 => [
			'id' => 1,
			'name' => 'admin',
			'parent' => NULL,
		],
	],
	array_map('toArray', $context->table('role')->fetchAll())
);

Assert::same(
	[
		[
			'acl_id' => 2,
			'resource_id' => 14,
		],
	],
	array_map('toArray', $context->table('book_resource')->fetchAll())
);

function toArray ($row) {
	return $row->toArray();
}

$disconnect($database);
