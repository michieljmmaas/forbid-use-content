<?php

namespace Moxio\PsalmPlugin\Issue;


use Psalm\Issue\CodeIssue;

final class UnsafeGetContents extends CodeIssue {
	public const ERROR_LEVEL = -1;
	public const SHORTCODE = 1; // Choose a unique number for your plugin
}