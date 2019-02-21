<?php
/**
 * CustomSession.php
 *
 * Created By: jonathan
 * Date: 20/02/2019
 * Time: 19:21
 */

namespace App\Smtp;


class CustomSession extends Session
{
    const EVENT_SMTP_AUTH_FAILED = 'smtp.auth_failed';
    /**
     * Make sure we call the authentication
     * @param string $arg
     */
    protected function mailCommand(string $arg) {
        $arg = trim($arg, 'FfRrOoMm: <>');
        $arr = explode('@', $arg);

        $this->from['user'] = $arr[0];
        $this->from['domain'] = $arr[1];
        if ($this->doAuth($this->message->username, $this->message->password)) {
            return $this->socketWriteLine($this->settings->smtp250);
        }
        $this->socketWriteLine($this->settings->smtp535);
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticate(string $auth_id, string $user_id, string $password) : bool {
        // If the smtp server user is defined, check that it is correct
        return $this->doAuth($user_id, $password);
    }

    protected function doAuth($username, $password) {
        $user = getenv('SMTP_SERVER_USER');
        $pwd = getenv('SMTP_SERVER_PASSWORD');
        if ($user) {
            $res = ($user === $username && $pwd === $password);
            if ($res === false) {
                $this->emit(self::EVENT_SMTP_AUTH_FAILED, [$this->message, $username, $password]);
                $this->log->info('Authentication error');
            }

            return $res;
        }
        // Server user is not defined, let through
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function dataFinishedHandler() : string {
        if ($this->doAuth($this->message->username, $this->message->password)) {
            $this->message->delivered = true;
            $this->log->info('Message delivered');
            return '';
        }

        return $this->settings->smtp535;
    }
}