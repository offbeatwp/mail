<?php

namespace OffbeatWP\Mail;

use BadMethodCallException;
use InvalidArgumentException;
use OffbeatWP\Contracts\View;
use OffbeatWP\Views\ViewableTrait;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

final class Mail
{
    use ViewableTrait;

    /** @var string|string[] */
    private string|array $to = '';
    private string $template = '';
    private string $subject = '';
    private string $content = '';
    private string $from = '';
    private string $fromName = '';

    public function __construct(string $template = '')
    {
        if (!$template) {
            $template = config('app.mail.default_template') ?: '';
        }

        if (!is_string($template)) {
            throw new InvalidArgumentException('Template configured in in app.mail.default_template must be a string.');
        }

        $this->template = $template;
        $this->view = offbeat(View::class);

        $this->setRecursiveViewsPath(dirname(__DIR__));
    }

    public function send(): bool
    {
        if ($this->template) {
            add_filter('wp_mail_content_type', [$this, 'getHtmlMailContentType']);
            $sent = wp_mail($this->to, $this->subject, $this->getHtml(), $this->getHeaders());
            remove_filter('wp_mail_content_type', [$this, 'getHtmlMailContentType']);
        } else {
            $sent = wp_mail($this->to, $this->subject, $this->content, $this->getHeaders());
        }

        return $sent;
    }

    public function getHtmlMailContentType(): string
    {
        return 'text/html';
    }

    public function getHtml(): string
    {
        $html = $this->view($this->template, [
            'subject' => $this->subject,
            'content' => $this->content
        ]);

        return (new CssToInlineStyles())->convert($html);
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setContentFromTemplate(string $template, array $args = []): void
    {
        /** @var View $view */
        $view = offbeat(View::class);

        if (method_exists($view, 'createTemplate')) {
            $this->content = $view->createTemplate($template)->render($args);
        } else {
            throw new BadMethodCallException('The ' . $view::class . ' class does not hae a createTemplate method.');
        }
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /** @param string|string[] $to */
    public function setTo(string|array $to): void
    {
        $this->to = $to;
    }

    public function setFrom(string $email): void
    {
        $this->from = $email;
    }
    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFromName(string $fromName): void
    {
        $this->fromName = $fromName;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /** @return string|string[] */
    public function getTo(): string|array
    {
        return $this->to;
    }

    /** @return string[] */
    public function getHeaders(): array
    {
        $headers = [];

        if ($this->from) {
            if ($this->fromName) {
                $headers[] = "From: {$this->fromName} <{$this->from}>";
            } else {
                $headers[] = $this->from;
            }
        }

        return $headers;
    }
}
