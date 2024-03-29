<?php

/*
 * This file is part of the Maillocal package.
 *
 * Copyright 2019 Jonathan Foucher
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package Mailocal
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Html2Text\Html2Text;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmailRepository")
 */
class Email
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true, name="text_content")
     */
    private $text;

    /**
     * @ORM\Column(type="text", nullable=true, name="raw_content")
     */
    private $raw;

    /**
     * @ORM\Column(type="array", nullable=true, name="attachments")
     */
    private $attachments;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $subject;

    /**
     * @ORM\Column(type="string", nullable=true, name="from_address")
     */
    private $from;

    /**
     * @ORM\Column(type="string", nullable=true, name="from_name")
     */
    private $fromName;

    /**
     * @ORM\Column(type="string", nullable=true, name="to_address")
     */
    private $to;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $readAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $html;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $headers;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return quoted_printable_decode($this->text);
    }

    public function setText(?string $message): self
    {
        $this->text = $message;

        return $this;
    }

    public function getHeaders(): ?string
    {
        return $this->headers;
    }

    public function setHeaders(?string $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHtml(): ?string
    {
        return quoted_printable_decode($this->html);
    }

    /**
     * @param string|null $html
     * @return self
     */
    public function setHtml(?string $html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param mixed $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $date
     */
    public function setCreatedAt($date)
    {
        $this->created_at = $date;
    }

    /**
     * @return mixed
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param mixed $fromName
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
    }

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param mixed $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return mixed
     */
    public function getRaw()
    {
        return quoted_printable_decode($this->raw);
    }

    /**
     * @param mixed $raw
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
    }

    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param mixed $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * @return mixed
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * @param mixed $readAt
     */
    public function setReadAt($readAt)
    {
        $this->readAt = $readAt;
    }

    public function getFirstLine()
    {
        $text = trim($this->getText(), " \t\n\r".urldecode("%C2%A0"));
        if (!$text) {
            $text = trim((new Html2Text($this->getHtml()))->getText(), " \t\n\r".urldecode("%C2%A0"));
        }

        $firstLine = trim(explode("\n", $text)[0]);
        return $firstLine === '--' ? '' : $firstLine;
    }
}
