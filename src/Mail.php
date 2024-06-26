<?php

namespace OffbeatWP\Mail;

use OffbeatWP\Contracts\View;
use OffbeatWP\Views\ViewableTrait;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Mail
{
    use ViewableTrait;

    protected $template;
    protected $args = [];
    protected $to = null;
    protected $from = null;
    protected $fromName = null;

    public function __construct($template = null)
    {
        if (is_null($template)) {
            $template = config('app.mail.default_template');
        }

        $this->setTemplate($template);

        $this->view = offbeat(View::class);

        $this->setMailTemplatePath();
    }

    public function setMailTemplatePath()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $calledFromFile = $backtrace[1]['file'];

        if (preg_match('#Services/Mail/Repositories/MailRepository.php$#', $calledFromFile)) {
            $calledFromFile = $backtrace[2]['file'];
        }

        $this->setRecursiveViewsPath(dirname($calledFromFile), 5);
    }

    public function send(): mixed
    {
        add_filter('wp_mail_content_type', [$this, 'setHtmlMailContentType']);

        $body = $this->getHtml();

        $mailer = wp_mail($this->getTo(), $this->getSubject(), $body, $this->getHeaders());

        remove_filter('wp_mail_content_type', [$this, 'setHtmlMailContentType']);
        return $mailer;
    }


    public function setHtmlMailContentType()
    {
        return 'text/html';
    }

    public function getHtml()
    {
        $html = $this->view($this->getTemplate(), $this->args);

        $cssToInlineStyles = new CssToInlineStyles();
        $html = $cssToInlineStyles->convert($html);

        return $html;
    }

    public function setSubject($subject)
    {
        $this->args['subject'] = $subject;
    }

    public function getSubject()
    {
        return $this->args['subject'];
    }

    public function setContent($content)
    {
        $this->args['content'] = $content;
    }

    public function setContentFromTemplate($template, $args = [])
    {
        $template = offbeat(View::class)->createTemplate($template);

        $this->setContent($template->render($args));
    }

    public function getContent()
    {
        return $this->args['content'];
    }

    public function setTo($to)
    {
        $this->to = $to;
    }

    public function setFrom($email)
    {
        $this->from = $email;
    }
    public function getFrom()
    {
        return $this->from;
    }

    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
    }

    public function getFromName()
    {
        return $this->fromName;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getHeaders()
    {
        $headers = [];

        if (!empty($this->getFrom())) {
            if (!empty($this->getFromName())) {
                $headers[] = "From: {$this->getFromName()} <{$this->getFrom()}>";
            } else {
                $headers[] = $this->getFrom();
            }
        }

        // $headers[] = 'Cc: John Q Codex <jqc@wordpress.org>';
        // $headers[] = 'Cc: iluvwp@wordpress.org'; // note you can just use a simple email address

        return $headers;
    }
}
