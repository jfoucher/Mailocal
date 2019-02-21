<?php

namespace App\Smtp;

use Symfony\Component\EventDispatcher\Event;

/**
 * authFailedEvent short summary.
 */
class AuthFailedEvent extends Event {

  /**
   * The e.mail message.
   *
   * @var Message
   */
  public $message;
  public $username;
  public $password;

  /**
   * Get an instance of AuthFailedEvent.
   * @param Message $message
   */
  public function __construct(Message $message, $username, $password) {
    $this->message = $message;
    $this->username = $username;
    $this->password = $password;

  }

}
