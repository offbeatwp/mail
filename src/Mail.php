<?php

namespace OffbeatWP\Mail;

use BadMethodCallException;
use OffbeatWP\Contracts\View;
use OffbeatWP\Views\ViewableTrait;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

final class Mail
{
    use ViewableTrait;

    /** @var string|string[] */
    private string|array $to = '';
    private string $template;
    private string $subject = '';
    private string $content = '';
    private string $from = '';
    private string $fromName = '';

    public function __construct(?string $template = null)
    {
        if ($template === null) {
            $template = config('app.mail.default_template');
        }

        $this->template = $template;
        $this->view = offbeat(View::class);

        $this->setMailTemplatePath();
    }

    // TODO: Surely there is a better way to do this?
    public function setMailTemplatePath(): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $calledFromFile = $backtrace[1]['file'];

        if (preg_match('#Services/Mail/Repositories/MailRepository.php$#', $calledFromFile)) {
            $calledFromFile = $backtrace[2]['file'];
        }

        $this->setRecursiveViewsPath(dirname($calledFromFile), 5);
    }

    public function send(): bool
    {
        add_filter('wp_mail_content_type', [$this, 'getHtmlMailContentType']);

        $sent = wp_mail($this->to, $this->subject, $this->getHtml(), $this->getHeaders());

        remove_filter('wp_mail_content_type', [$this, 'getHtmlMailContentType']);

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
