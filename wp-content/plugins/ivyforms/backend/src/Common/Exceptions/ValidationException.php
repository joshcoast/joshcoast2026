<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Common\Exceptions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Exception;

/**
 * Class ValidationException
 *
 * @package IvyForms\Common\Exceptions
 */
class ValidationException extends Exception
{
    /**
     * ValidationException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = 'validation_failed', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
