<?php

namespace App\Smtp;

/**
 * Class to manage SMTP Sever settings.
 */
class SmtpSettings {

  /**
   * Get an instance of SmtpSettings.
   */
  private function __construct() {}

  /**
   * Get an instance of SmtpSettings.
   */
  public static function instance() {
    static $instance;
    if (!isset($instance)) {
      $instance = new SmtpSettings();
    }
    return $instance;
  }

  /**
   * Default SMTP host.
   *
   * @var string
   */
  public $smtpHostDefault = '127.0.0.1';

  /**
   * Default SMTP port.
   *
   * @var int
   */
  public $smtpPortDefault = 25;

  /**
   * Default max message size of 2MB.
   *
   * @var int
   */
  public $smtpMaxSize = 2097152;

  /**
   * Read from sockets in 1K chunks.
   *
   * @var int
   */
  public $smtpChunkSize = 1024;

  /**
   * SMTP response codes.
   */

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp211 = '211 System status, or system help reply';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp214 = '214 Help message';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp220 = '220 Service ready';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp221 = '221 Service closing transmission channel';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp235 = '235 Authentication successful';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp250 = '250 Requested mail action okay, completed';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp251 = '251 User not local';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp354 = '354 Start mail input; end with <CRLF>.<CRLF>';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp421 = '421 Service not available';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp450 = '450 Requested mail action not taken: mailbox unavailable';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp451 = '451 Requested action aborted: error in processing';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp452 = '452 Requested action not taken: insufficient system storage';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp500 = '500 Syntax error, command unrecognized';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp501 = '501 Syntax error in parameters or arguments';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp502 = '502 Command not implemented';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp503 = '503 Bad sequence of commands';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp504 = '504 Command parameter not implemented';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp535 = '535 Authentication failed';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp550 = '550 Requested action not taken: mailbox unavailable';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp551 = '551 User not local';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp552 = '552 Requested mail action aborted: exceeded storage allocation';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp553 = '553 Requested action not taken: mailbox name not allowed';

  /**
   * SMTP message.
   *
   * @var string
   */
  public $smtp554 = '554 Transaction failed';

}
