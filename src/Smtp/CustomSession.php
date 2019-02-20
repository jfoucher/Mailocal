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
    /**
     * {@inheritdoc}
     */
    protected function authenticate(string $auth_id, string $user_id, string $password) : bool {
        // You can also "not" check auth here, and fail on
        // dataFinishedHandler() during delivery if credentials
        // are wrong. Credentials are stored in the message itself.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function dataFinishedHandler() : string {
        $this->log->info(json_encode($this->message));
        return '';
    }
}