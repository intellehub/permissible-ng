<?php
namespace Shahnewaz\Permissible\Exceptions;

class FeatureNotAllowedException extends \Exception {
	public function __construct ($message = null, $code = 401) {
    $message = $message ?: 'You are not authorized to access this feature.';
		throw new \Exception($message, 401);
	}
}