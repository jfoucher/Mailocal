# Mailocal

Safely test sending your emails by using this local SMTP server and viewing the results on the web interface.

By using this locally installed SMTP server you can be sure that your real customers will never see your test emails !

However you can see all of them by simply opening the provided interface in any browser.

# Installation

- `git clone git@github.com:jfoucher/mailocal.git && cd mailocal`
- `composer install`
- `php bin/console email:server`
- Configure your other apps to use this new local SMTP server : 
  - host : 127.0.0.1
  - port: 2525
  - No user and no password
  
# Warning

Never use this in production, only run it on your local machine.
