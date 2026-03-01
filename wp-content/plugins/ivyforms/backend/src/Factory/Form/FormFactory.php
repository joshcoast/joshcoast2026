<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Factory\Form;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Helpers\Helper;
use IvyForms\Entity\Form\Form as FormEntity;
use IvyForms\ValueObjects\Form\Form;
use IvyForms\ValueObjects\Form\IntegrationSettings;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class FormFactory
 *
 * @package IvyForms\Factory\Form
 */
class FormFactory
{
    /**
     * @param array<string, mixed> $data
     *
     * @return  FormEntity
     *
     * @throws ValidationException
     */
    public static function create(array $data): FormEntity
    {
        $settings = isset($data['settings']) ? json_decode($data['settings'], true) : [];
        $integrationSettings = Helper::parseToArray($data['integrationSettings']);

        if (!empty($settings)) {
            $data['showTitle']       = $settings['showTitle'] ?? true;
            $data['showDescription'] = $settings['showDescription'] ?? false;
            $data['storeEntries']    = !isset($settings['storeEntries']) || (bool)$settings['storeEntries'];
            $data['formActionButtons'] = $settings['formActionButtons'] ?? [
                'submitButtonSettings' => [
                    'label'    => BackendStrings::getCommonStrings()['submit'],
                    'position' => 'default'
                ]
            ];
        }

        $formObject = new Form(
            $data['id'] ?? 0,
            $data['name'] ?? '',
            $data['author'] ?? '',
            $data['starred'] ?? false,
            $data['published'] ?? true,
            $data['description'] ?? '',
            $data['showTitle'] ?? true,
            $data['showDescription'] ?? false,
            isset($data['storeEntries']) ? (bool)$data['storeEntries'] : true,
            $data['fields'] ?? [],
            new IntegrationSettings($integrationSettings),
            $data['formActionButtons']
        );

        $form = new FormEntity($formObject);

        if (isset($data['id'])) {
            $form->setId($data['id']);
        }

        if (isset($data['name'])) {
            $form->setName($data['name']);
        }

        if (isset($data['author'])) {
            $form->setAuthor($data['author']);
        }

        if (isset($data['starred'])) {
            $form->setStarred($data['starred']);
        }

        if (isset($data['published'])) {
            $form->setPublished($data['published']);
        }

        if (isset($data['description'])) {
            $form->setDescription($data['description']);
        }

        if (isset($data['fields'])) {
            $form->setFields($data['fields']);
        }

        if (isset($data['showTitle'])) {
            $form->setTitleVisible($data['showTitle']);
        }

        if (isset($data['showDescription'])) {
            $form->setDescriptionVisible($data['showDescription']);
        }

        if (isset($data['storeEntries'])) {
            $form->setStoreEntries($data['storeEntries']);
        }

        return $form;
    }
}
