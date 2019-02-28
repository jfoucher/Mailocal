<?php

namespace App\Smtp;

/**
 * SMTP Message container.
 */
class Message {

  /**
   * Get an instance of Message.
   */
  public function __construct() {
    $this->delivered = FALSE;
  }

  /**
   * Username.
   *
   * @var string | null | boolean | string[]
   */
  public $username;

  /**
   * Summary of $password.
   *
   * @var mixed
   */
  public $password;

  /**
   * To addresses.
   *
   * @var string[]
   */
  public $to;

  /**
   * From addresses.
   *
   * @var string[]
   */
  public $from;

  /**
   * Message data.
   *
   * @var string
   */
  public $data;

  /**
   * If the message has been or not succesfully delivered.
   *
   * @var bool
   */
  public $delivered;

}
