[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
![Build status](https://img.shields.io/travis/jfoucher/Mailocal.svg)

<img src="favicon.png" align="right" width="80">

# Mailocal

Safely test sending your emails by using this local SMTP server and viewing the results on the web interface.

By using this locally installed SMTP server you can be sure that your real customers will never see your test emails !

However you can see all of them by simply opening the provided interface in any browser.

## Screenshots

![All messages](https://mailocal.jfoucher.com/assets/img/img1.png "List of messages, can be filtered by recipient")
![single message](https://mailocal.jfoucher.com/assets/img/img2.png "Viewing a single message")

## Requirements
- [php 7.2](https://php.net)
- [composer](https://getcomposer.org)

If you don't have Composer yet, download it following the instructions on
[http://getcomposer.org/](http://getcomposer.org/) or just run the following command:

    curl -s http://getcomposer.org/installer | php

## Install

- `composer create-project jfoucher/mailocal`
- That's it

## Running

<img src="console.png" align="center" width="782">

- `cd mailocal` to enter the directory just created by composer
- Run `bin/mailocal` to run both the SMTP server and the web server

Alternatively you can:

- Run `yarn run build` to build the frontend assets
- `php bin/console email:server` to launch the SMTP server
- `php bin/console server:start` to start Symfony's built-in webserver

## Configuration

- Configure your other apps to use this new local SMTP server : 
  - host : `127.0.0.1`
  - port: `2525` (or the one you chose, see below)
  - You can configure an SMTP username and password by setting the `SMTP_SERVER_USER` and `SMTP_SERVER_PASSWORD`
 fields in you `.env` file. Make sure you update your email client's credentials accordingly.
 - [Mailocal](/) uses an SQLite database by default (in `var/data.db`) but you can choose to use any other database by setting the correct URL in the `.env` file
 
## Done
You can now view any emails you receive by opening http://localhost:8000 in your browser

The SMTP runs on port 2525 by default. Pass the `--port` option to use another one, like this: `php bin/console email:server --port=587`

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
  
## Warning

Never use this in production, only run it on your local machine.
