<?php

namespace Moxio\PsalmPlugin\Hook;

use Moxio\PsalmPlugin\Issue\UnsafeGetContents;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;

final class AfterMethodCallHandler implements AfterMethodCallAnalysisInterface {

	#[\Override]
	public static function afterMethodCallAnalysis(AfterMethodCallAnalysisEvent $event): void {
		$expr = $event->getExpr();
		$source = $event->getStatementsSource();

		// Check if the method being called is 'getContents'
		if (!$expr->name instanceof \PhpParser\Node\Identifier ||
			$expr->name->name !== 'getContents') {
			return;
		}

		$codebase = $event->getCodebase();

		// Get the type of the variable/expression on which getContents() is called
		$type = $source->getNodeTypeProvider()->getType($expr->var);

		if (!$type) {
			return;
		}

		// Check if any of the possible types implement StreamInterface
		foreach ($type->getAtomicTypes() as $atomic) {
			if ($atomic instanceof \Psalm\Type\Atomic\TNamedObject) {
				$className = $atomic->value;

				// Check if this type IS StreamInterface or implements it
				if ($className === 'Psr\\Http\\Message\\StreamInterface' ||
					($codebase->classOrInterfaceExists($className) &&
						$codebase->classImplements($className, 'Psr\\Http\\Message\\StreamInterface'))
				) {
					/** @psalm-suppress UndefinedClass */
					IssueBuffer::accepts(
						new UnsafeGetContents(
							'Calling getContents() on a PSR-7 StreamInterface is unsafe.',
							new CodeLocation($source, $expr)
						),
						$source->getSuppressedIssues()
					);
					return;
				}
			}
		}
	}
}