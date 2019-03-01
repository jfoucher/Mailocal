---
title: Mailocal
layout: home
excerpt: Debug your emails while making sure real customers never see them.
intro: Safely test your application's emails by using this local SMTP server and viewing the results in the web interface.
intro2: By using this locally installed SMTP server you can be sure that your real customers will never see your test emails! However you can see all of them by simply opening the provided interface in any browser.
images:
  - title: Main interface view
    image: assets/img/img1.png
    link: assets/img/img1.png
  - title: Viewing messages details
    image: assets/img/img2.png
    link: assets/img/img2.png
---


## Installation

### Requirements

- [php 7.2](https://php.net)
- [composer](https://getcomposer.org)

If you don't have Composer yet, download it following the instructions on [http://getcomposer.org/](http://getcomposer.org/) or just run the following command:

    curl -s http://getcomposer.org/installer | php


### Install

- `composer create-project jfoucher/mailocal`
- Done!

### Running

<img src="assets/img/console.png" alt="drawing" width="782" align="center" />


- `cd mailocal` to enter the directory just created by composer
- Run `bin/mailocal to run both the SMTP server and the web server


Alternatively you can:

- Run `yarn run build` to build the frontend assets (they are included in the distribution but maybe you want to change the look of the web interface)
- `php bin/console email:server` to launch the SMTP server
- `php bin/console server:start` to start Symfony's built-in webserver

### Configuration

- Configure your other apps to use this new local SMTP server : 
  - host : `127.0.0.1`
  - port: `2525` (or the one you chose, see below)
  
- You can configure an SMTP username and password by setting the `SMTP_SERVER_USER` and `SMTP_SERVER_PASSWORD`
 fields in your `.env` file. Make sure you update your email client's credentials accordingly.
- [Mailocal](/) uses an SQLite database by default (in `var/data.db`) but you can choose to use any other database by setting the correct URL in the `.env` file
- The SMTP runs on port 2525 by default. Pass the `--port` option to use another one, like this: `php bin/console email:server --port=587`. You can also set the `SMTP_SERVER_PORT` variable in the `.env` file (this is useful if you run `bin/mailocal` directly).
 
### Done

You can now view any emails you receive by opening http://localhost:8000 in your browser

The SMTP runs on port 2525 by default. Pass the `--port` option to use another one, like this: `php bin/console email:server --port=587`
  
### Warning

Never use this in production, only run it on your local machine.
