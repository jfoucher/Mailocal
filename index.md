---
title: Mailocal
layout: default

---



Safely test sending your emails by using this local SMTP server and viewing the results on the web interface.

By using this locally installed SMTP server you can be sure that your real customers will never see your test emails !

However you can see all of them by simply opening the provided interface in any browser.

# Requirements
- [git](https://git-scm.com/downloads)
- [yarn](https://yarnpkg.com) or [npm](https://www.npmjs.com/)
- [composer](https://getcomposer.org)

# Installation

- `git clone git@github.com:jfoucher/mailocal.git && cd mailocal`
- `composer install`
- Copy `.env.example` to `.env` and set your database connection parameters.
- Run `php bin/console doctrine:database:create` to create the database
- Run `php bin/console doctrine:migrations:migrate` to create the database schema
- Run `yarn run start` to build and run everything

Alternatively you can:

- Run `yarn run build` to build the frontend assets
- `php bin/console email:server` to launch the SMTP server
- `php bin/console server:start` to start Symfony's built-in webserver

# Configuration

- Configure your other apps to use this new local SMTP server : 
  - host : `127.0.0.1`
  - port: `2525` (or the one you chose, see below)
  - You can configure an SMTP username and password by setting the `SMTP_SERVER_USER` and `SMTP_SERVER_PASSWORD`
 fields in you .env.local file. Make sure you update your email client's credentials accordingly.
 
# Done
You can now view any emails you receive by opening http://localhost:8000 in your browser

The SMTP runs on port 2525 by default. Pass the `--port` option to use another one, like this: `php bin/console email:server --port=587`
  
# Warning

Never use this in production, only run it on your local machine.
