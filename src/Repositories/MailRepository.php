<?php

namespace OffbeatWP\Mail\Repositories;

final class MailRepository
{
    private static ?MailRepository $instance = null;
    private array $templates = [];

    public static function getInstance(): MailRepository
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {

    }

    public function registerTemplate(string $name, string $template)
    {
        $this->templates[$template] = $name;
    }

    /** @return array<string, string> */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function getTemplatePath(string $name): ?string
    {
        return $this->templates[$name] ?? null;
    }
}
