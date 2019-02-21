# SMS

SMS is survey management software based on Laravel Framework


## Acknowledgement

This project start in 2014 with election related survey in Myanmar funded by [National Democratic Institute (NDI)](https://www.ndi.org/) with an agreement to release as opensource MIT license.
 Since then development is going on peroidically in some of my freetime. Rewrite from scratch more than 3 times. To continue development [People's Alliance for Credible Election (PACE)](https://www.pacemyanmar.org) funded further development since 2015.
 [TI Cambodia](https://www.ticambodia.org/) also funded to extend software to support SMS data reporting to use in Cambodia's 2017 Election.


## Installation

clone or download from github

```
cp .env.example .env
composer install
npm install yarn -g
yarn install
yarn run production
php artisan migrate
```
open http://localhost/ or your domain.

register first admin user account.

import sample data list.

create project.

to import survey, use xls template in docs directory.

## Version
This software is still in alpha stage. There are many breaking changes between releases.

## Laravel Documentation

Read Laravel documentation for web server setup.

Documentation for the framework can be found on the [Laravel website](http://laravel.com/docs).

## Contributing

Thank you for considering contributing to theSMS!

## Security Vulnerabilities

If you discover a security vulnerability within SMS, please send an e-mail to Sithu Thwin at sithu@thwin.net. All security vulnerabilities will be promptly addressed.

## License

The SMS is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
