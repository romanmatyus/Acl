<?php

namespace RM\Acl\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use RM\Acl\MissingAnnotationException;

/**
 * Nette DI extension for Acl.
 *
 * @author    Roman Mátyus
 * @copyright (c) Roman Mátyus 2015
 * @license   MIT
 */
class AclExtension extends CompilerExtension
{
	/** @var [] */
	public $defaults = [
		'default' => FALSE,
		'subjects' => [],
		'rules' => [],
	];

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$config = $this->validateConfig(array_merge(
				$this->defaults,
				[
					'subjects' => [
						'@' . $this->prefix('subject.role'),
						'@' . $this->prefix('subject.relation'),
						'@' . $this->prefix('subject.user'),
					],
				]
			)
		);

		foreach ($builder->findByType('Nette\Security\User') as $name => $def) {
			$builder->removeDefinition($name);
		}

		$builder->addDefinition($this->prefix('user'))
			->setClass('RM\Acl\User');

		$builder->addDefinition($this->prefix('subject.role'))
			->setClass('RM\Acl\Subject\Role');

		$relation = $builder->addDefinition($this->prefix('subject.relation'))
			->setClass('RM\Acl\Subject\Relation');

		foreach ($builder->findByType('RM\Acl\IRelation') as $name => $def) {

			$reflectionClass = class_exists('Nette\Reflection\ClassType') ? 'Nette\Reflection\ClassType' : 'ReflectionClass';
			$reflection = new $reflectionClass($def->getClass());

			if (!$reflection->hasAnnotation('resourceName'))
				throw new MissingAnnotationException("Annotation 'resourceName' must be defined.");
			if (!$reflection->hasAnnotation('relationship'))
				throw new MissingAnnotationException("Annotation 'relationship' must be defined.");

			$relation->addSetup('addResource', [
				$reflection->getAnnotation('resourceName'),
				(array) $reflection->getAnnotation('relationship'),
				($reflection->getMethod('verifyRelation')->isStatic())
					? $def->getClass() . '::verifyRelation'
					: ['@' . $name, 'verifyRelation'],
			]);
		}

		$builder->addDefinition($this->prefix('subject.user'))
			->setClass('RM\Acl\Subject\User');

		$acl = $builder->addDefinition($this->prefix('acl'))
			->setClass('RM\Acl\Acl')
			->addSetup('$default', [$config['default']]);

		foreach ($config['subjects'] as $name)
			$acl->addSetup('addSubject', [$name]);

		foreach ($config['rules'] as $rule)
			$acl->addSetup('addRule', [$rule]);

	}

	/**
	 * Register extension to DI Container.
	 * @param  Configurator $config
	 */
	public static function register(Configurator $config)
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) {
			$compiler->addExtension('acl', new AclExtension());
		};
	}

}
