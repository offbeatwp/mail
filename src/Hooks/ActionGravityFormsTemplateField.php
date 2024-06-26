<?php

namespace OffbeatWP\Mail\Hooks;

use OffbeatWP\Mail\Repositories\MailRepository;
use OffbeatWP\Hooks\AbstractAction;

final class ActionGravityFormsTemplateField extends AbstractAction
{
    public function action(): void
    {
        add_filter('gform_notification_settings_fields', [$this, 'gfNotificationTemplateSetting']);
    }

    public function gfNotificationTemplateSetting(array $fields): array
    {
        $templates = [
            [
                'label' => __('Use default template', 'lynx'),
                'value' => '',
            ]
        ];

        foreach (MailRepository::getInstance()->getTemplates() as $value => $label) {
            $templates[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        $fields[] = [
            'title' => 'Templates',
            'fields' => [
                [
                    'type' => 'select',
                    'label' => __('Template', 'lynx'),
                    'choices' => $templates,
                    'name' => 'mail_template'
                ]
            ]
        ];

        return $fields;
    }
}
