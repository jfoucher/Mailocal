<?php

namespace App\Smtp;

use React\EventLoop\LoopInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Symfony compatible Server.
 *
 * Extends the Server implementation to add support
 * for Symfony's event dispatcher.
 */
class ServerSymfony extends Server {

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Constructor, no required parameters if binding to the localhost interface.
   *
   * @param string $host
   *   IP address to bind the server to, defaults to 127.0.0.1.
   * @param int $port
   *   Port to user on the specified host address, defaults to 25.
   * @param LoggerInterface $log
   *   Logger service.
   * @param \React\EventLoop\LoopInterface $loop
   *   Loop service.
   */
  public function __construct(string $host, int $port, LoggerInterface $log, LoopInterface $loop, string $sessionClass, EventDispatcherInterface $dispatcher) {
    parent::__construct($host, $port, $log, $loop, $sessionClass);
    $this->dispatcher = $dispatcher;
    $this->on(SessionInterface::EVENT_SMTP_RECEIVED, [$this, 'onMessageReceived']);
    $this->on(CustomSession::EVENT_SMTP_AUTH_FAILED, [$this, 'onAuthFailed']);
  }

  /**
   * Listener for message received.
   *
   * @param Message $message
   *   Mail message.
   */
  public function onMessageReceived(Message $message) {
    // It will also be fun to dispatch this as a symfony event.
    $event = new MessageReceivedEvent($message);
    $this->dispatcher->dispatch(SessionInterface::EVENT_SMTP_RECEIVED, $event);
  }

  /**
   * Listener for auth failed.
   *
   * @param Message $message
   *   Mail message.
   */
  public function onAuthFailed(Message $message, $username, $password) {
    $event = new AuthFailedEvent($message, $username, $password);
    $this->dispatcher->dispatch(CustomSession::EVENT_SMTP_AUTH_FAILED, $event);
  }

}
