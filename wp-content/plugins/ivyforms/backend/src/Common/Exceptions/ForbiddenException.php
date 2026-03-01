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
 * Class ForbiddenException
 *
 * @package IvyForms\Common\Exceptions
 */
class ForbiddenException extends Exception
{
    /**
     * ForbiddenException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = 'forbidden', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        wp_die(
            esc_html__('You do not have permission to access this page.', 'ivyforms'),
            403
        );
    }
}
