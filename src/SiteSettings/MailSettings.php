<?php

namespace OffbeatWP\Mail\SiteSettings;

use OffbeatWP\Mail\Repositories\MailRepository;
use OffbeatWP\Form\Fields\Select;
use OffbeatWP\Form\Form;

final class MailSettings
{
    public const ID = 'settings-mail';
    public const PRIORITY = 25;

    public function title(): string
    {
        return __('Mail');
    }

    public function form(): Form
    {
        $form = new Form();

        $form->addField(
            Select::make('gravityforms_default_template', 'Default gravity forms template')
                ->addOption('', 'Theme default')
                ->addOptions(MailRepository::getInstance()->getTemplates())
        );

        return $form;
    }
}
