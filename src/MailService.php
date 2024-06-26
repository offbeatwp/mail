<?php

namespace OffbeatWP\Mail;

use OffbeatWP\Mail\SiteSettings\MailSettings;
use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Services\AbstractService;

class MailService extends AbstractService
{
    public array $bindings = [
        'mail' => Repositories\MailRepository::class
    ];

    public function register(SiteSettings $settings): void
    {
        container('hooks')->addAction('after_setup_theme', Hooks\ActionGravityFormsTemplateField::class);
        container('hooks')->addAction('init', Hooks\ActionGravityFormsApplyTemplate::class);
        container('hooks')->addAction('phpmailer_init', Hooks\DeliveryMailsOnlyToWhitelistAction::class, 100);

        $settings->addPage(MailSettings::class);
    }
}
