<?php
namespace Shahnewaz\PermissibleNg\Exceptions;

class FeatureNotAllowedException extends \Exception {
	public function __construct ($message = null, $code = 403) {
        parent::__construct($message, $code);
        $message = $message ?: 'You are not authorized to access this resource.';
		throw new FeatureNotAllowedException($message, 403);
	}
}