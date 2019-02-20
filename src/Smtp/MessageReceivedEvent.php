<?php

namespace App\Smtp;

use Symfony\Component\EventDispatcher\Event;

/**
 * MessageReceivedEvent short summary.
 */
class MessageReceivedEvent extends Event {

  /**
   * The e.mail message.
   *
   * @var Message
   */
  public $message;

  /**
   * Get an instance of MessageReceivedEvent.
   * @param Message $message
   */
  public function __construct(Message $message) {
    $this->message = $message;

  }

}
