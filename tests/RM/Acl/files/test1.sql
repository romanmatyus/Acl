/**
 * Role 'guest' has access to book:detail.
 * Role 'guest' has not access to book:detail 2001: A Space Odyssey.
 * Role 'registred' has access to book:detail
 * Role 'registred' has access to book:comment
 * Role 'author' has access to book:publish and inherit access from 'registred'.
 * Role 'superadmin' has access to book:*.
 *
 * Author Isaac Asimov has access to book:* I, robot
 * Author Arthur C. Clarke has access to book:* 2001: A Space Odyssey
 * Customer John Doe has access to book:comment I, robot and 2001: A Space Odyssey
 * Customer John Wick has access to book:comment I, robot
 *
 * User Arthur C. Clarke has not access to book:comment 2001: I, robot
 * User John Wick has access to book:comment 2001: A Space Odyssey
 *
 * Other accesses are undefined (NULL).
 */

SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `acl` (`id`, `resource`, `privilege`, `type`, `access`) VALUES
(1,	'book',	'detail',	'role',	1),
(2,	'book',	'detail',	'role',	0),
(3,	'book',	'detail',	'role',	1),
(4,	'book',	'comment',	'role',	1),
(5,	'book',	'publish',	'role',	1),
(6,	'book',	NULL,	'role',	1),
(7,	'book',	NULL,	'relation', 1),
(8,	'book',	'comment',	'relation',	1),
(9,	'book',	'comment',	'user',	0),
(10,	'book',	'comment',	'user',	1);

INSERT INTO `acl_user` (`acl_id`, `user_id`) VALUES
(9,	4),
(10,	2);

INSERT INTO `acl_role` (`acl_id`, `role_id`) VALUES
(1,	1),
(2,	1),
(3,	2),
(4,	2),
(5,	3),
(6,	4);

INSERT INTO `role` (`id`, `name`, `parent`) VALUES
(1,	'guest',	NULL),
(2,	'registred',	NULL),
(3,	'author',	2),
(4,	'superadmin',	NULL);

INSERT INTO `acl_relation` (`acl_id`, `name`) VALUES
(7, 'author'),
(8, 'customer');

INSERT INTO `user` (`id`, `name`) VALUES
(1, 'John Doe'),
(2, 'John Wick'),
(3, 'Isaac Asimov'),
(4, 'Arthur C. Clarke'),
(5, 'Clark Kent');

INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
(1, 2),
(2, 2),
(3, 3),
(4, 3),
(5, 4);

INSERT INTO `book` (`id`, `name`, `author`) VALUES
(1,	'I, robot', 3),
(2,	'2001: A Space Odyssey', 4);

INSERT INTO `book_resource` (`acl_id`, `resource_id`) VALUES
(2,	2),
(9, 1),
(10, 2);

INSERT INTO `user_book` (`user_id`, `book_id`) VALUES
(1, 1),
(1, 2),
(2, 1);
