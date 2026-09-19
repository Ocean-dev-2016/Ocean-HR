<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenericMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $htmlContent;
    public $attachments;

    /**
     * @param string $subjectLine
     * @param string $htmlContent
     * @param array $attachments
     */
    public function __construct($subjectLine, $htmlContent, $attachments = [])
    {
        $this->subjectLine = $subjectLine;
        $this->htmlContent = $htmlContent;
        $this->attachments = $attachments;
    }

    public function build()
    {
        $fromAddress = config('mail.from.address', 'supportoceanhr@gmail.com');
        $fromName = config('mail.from.name', 'OceanHR');

        // Wrap fragment into valid full HTML5 email document
        $fullHtml = $this->wrapInHtmlDocument($this->htmlContent);

        // Generate clean plain-text version for 100% spam-free multipart delivery
        $plainText = $this->generatePlainText($this->htmlContent);

        $email = $this->from($fromAddress, $fromName)
            ->replyTo($fromAddress, $fromName)
            ->subject($this->subjectLine)
            ->html($fullHtml);

        if (!empty($plainText)) {
            $email->text('mail.plain_text', ['text' => $plainText]);
        }

        // Attach RFC compliance & anti-spam headers
        $this->withSymfonyMessage(function ($message) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('Auto-Submitted', 'auto-generated');
            $headers->addTextHeader('X-Auto-Response-Suppress', 'All');
            $headers->addTextHeader('Precedence', 'bulk');
        });

        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                if (is_array($file) && isset($file['file'])) {
                    $email->attach($file['file'], $file['options'] ?? []);
                } elseif (is_string($file)) {
                    $email->attach($file);
                }
            }
        }

        return $email;
    }

    private function wrapInHtmlDocument($content)
    {
        if (stripos($content, '<html') !== false) {
            return $content;
        }

        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>' . htmlspecialchars($this->subjectLine, ENT_QUOTES, 'UTF-8') . '</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: \'Segoe UI\', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; padding: 20px 0;">
        <tr>
            <td align="center">
                ' . $content . '
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    private function generatePlainText($html)
    {
        $text = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);
        $text = str_replace(["\r", "\n"], ' ', $text);
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<\/p>/i', "\n\n", $text);
        $text = preg_replace('/<\/tr>/i', "\n", $text);
        $text = preg_replace('/<\/td>/i', " | ", $text);
        $text = preg_replace('/<\/h[1-6]>/i', "\n\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s+\n/', "\n\n", $text);
        return trim($text);
    }
}
