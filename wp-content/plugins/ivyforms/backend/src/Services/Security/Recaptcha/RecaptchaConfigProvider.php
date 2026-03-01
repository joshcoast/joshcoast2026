<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Security\Recaptcha;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Security\Common\CaptchaServiceInterface;

/**
 * Provides reCAPTCHA frontend configuration
 *
 * @package IvyForms\Services\Security\Recaptcha
 */
class RecaptchaConfigProvider
{
    /**
     * Get reCAPTCHA configuration for frontend
     *
     * @param CaptchaServiceInterface|null $service
     * @return array<string, mixed>
     */
    public function getConfig(?CaptchaServiceInterface $service): array
    {
        $config = [
            'configured' => false,
            'type' => 'v2',
            'siteKey' => '',
            'scriptUrl' => '',
            'size' => 'normal'
        ];

        if ($service && $service->isConfigured()) {
            $recaptchaConfig = $service->getFrontendConfig(
                $service->getType(),
                $service->getSiteKey()
            );
            $recaptchaConfig['configured'] = true;
            return $recaptchaConfig;
        }

        return $config;
    }
}
