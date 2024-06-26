<?php

namespace OffbeatWP\Mail;

use OffbeatWP\Mail\Repositories\MailRepository;
use OffbeatWP\Mail\SiteSettings\MailSettings;
use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Services\AbstractService;

final class MailService extends AbstractService
{
    public function register(SiteSettings $settings): void
    {
        add_action('after_setup_theme', function () {
            add_filter('gform_notification_settings_fields', [$this, 'gfNotificationTemplateSetting']);
        });

        add_action('init', function () {
            add_filter('gform_pre_send_email', [$this, 'gfNotificationApplyTemplate'], 10, 3);
            add_filter('gform_html_message_template_pre_send_email', [$this, 'gfNotificationSimplifyTemplate']);
        });

        $settings->addPage(MailSettings::class);
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

    /**
     * @param array $email
     * @param string $messageFormat
     * @param array $notification
     * @return array
     */
    public function gfNotificationApplyTemplate($email, $messageFormat, $notification)
    {
        $notificationTemplate = $notification['mail_template'];

        if ($notificationTemplate) {
            $template = $notificationTemplate;
        } elseif (setting('gravityforms_default_template')) {
            $template = setting('gravityforms_default_template');
        } else {
            $template = 'mails/clean';
        }

        $mail = new Mail($template);
        $mail->setSubject($email['subject']);
        $mail->setContent($email['message']);

        $email['message'] = $mail->getHtml();

        return $email;
    }

    /**
     * @param string $template
     * @return string
     */
    public function gfNotificationSimplifyTemplate($template): string
    {
        return '{message}';
    }
}
