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

namespace App\Smtp;

class CustomSession extends Session
{
    const EVENT_SMTP_AUTH_FAILED = 'smtp.auth_failed';
    /**
     * Make sure we call the authentication
     * @param string $arg
     */
    protected function mailCommand(string $arg)
    {
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
    protected function authenticate(string $auth_id, string $user_id, string $password) : bool
    {
        // If the smtp server user is defined, check that it is correct
        return $this->doAuth($user_id, $password);
    }

    protected function doAuth($username, $password)
    {
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
    protected function dataFinishedHandler() : string
    {
        if ($this->doAuth($this->message->username, $this->message->password)) {
            $this->message->delivered = true;
            $this->log->info('Message delivered');
            return '';
        }

        return $this->settings->smtp535;
    }
}
