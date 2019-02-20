<?php

namespace App\Smtp;

use Evenement\EventEmitterInterface;

use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use React\EventLoop\LoopInterface;

/**
 * SMTP session interface.
 */
interface SessionInterface extends EventEmitterInterface {

  const EVENT_SMTP_RECEIVED = 'smtp.received';

  /**
   * Bind to events and start communicating.
   */
  public function run();

  /**
   * Dispose.
   */
  public function dispose();

  /**
   * Constructor.
   *
   * @param ConnectionInterface $socket
   *   Socket connection.
   * @param SmtpSettings $settings
   *   SMTP Settings.
   * @param LoggerInterface $log
   *   Log service.
   * @param LoopInterface $loop
   *   Loop service.
   */
  public function __construct(ConnectionInterface $socket, SmtpSettings $settings, LoggerInterface $log, LoopInterface $loop);

}
