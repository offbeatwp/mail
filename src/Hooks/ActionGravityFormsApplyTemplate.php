<?php

namespace OffbeatWP\Mail\Hooks;

use OffbeatWP\Mail\Repositories\MailRepository;
use OffbeatWP\Hooks\AbstractAction;

final class ActionGravityFormsApplyTemplate extends AbstractAction
{
    public function action()
    {
        add_filter('gform_pre_send_email', [$this, 'gfNotificationApplyTemplate'], 10, 3);
        add_filter('gform_html_message_template_pre_send_email', [$this, 'gfNotificationSimplifyTemplate']);
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

        $mail = MailRepository::getInstance()->make($template);
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
