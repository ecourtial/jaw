# Setting up the project on your development environment

## Introduction

The project can be either run:
* using the _Symfony Server_;
* using Docker;
* using your local PHP setup.

We will focus on the second option. The project is almost ready to be used with docker
on the dev environment.

## Steps

* Clone the repository.
* Create a _.env.local_ file (or just a _.env_ one), containing the following variables:


| Variable name                   | Content                                                  |
|---------------------------------|----------------------------------------------------------|
| MYSQL_ROOT_PASSWORD             | The Mysql root user's password                           |
| MYSQL_USER                      | The Mysql user                                           |
| MYSQL_PASSWORD                  | The Mysql user's password                                |
| APP_NAME                        | Your application's name (the back-end, not the font-end) |
| GOOGLE_RECAPTCHA_V2_PUBLIC_KEY  | Your Google V2 captcha public key.                       |
| GOOGLE_RECAPTCHA_V2_PRIVATE_KEY | Your Google V2 captcha private key.                      |

Notes: use separates values for Google Captcha in your different environments.

* In the _docker/dev_ folder, copy the _.env.dist_ file to _.env_ and define all the required variables in it.
* Now, to start the project, just run _make start_.
* Connect to the _PHP_ container: _make php_.
* Run _composer install_ (it will also install the assets).
* Create the DB by running _make migrate_command_.
* Run the following command to create fixtures: _make load_fixtures_command_.
* You can now log in to the admin at the _/login_ URL, using one of the two created users:
  * A regular user: JohntheRegular / somePassword123456aa
  * An admin user: John / somePassword123456

You can also create a new user with the following command when inside the PHP container.

```
bin/console app:add-user YourPseudo YourPassword foor@bar.com "Marcellus Faust" --admin
```

# Some notes

* The _Makefile_ contains a lot of useful commands. Have a look at it. For instance:
  * _make phpunit_
  * _make phpcs_
  * _make phpstan_
  * _make stop_ (to stop the project properly).
* The nginx configuration file is defined to accept request to an _adminer.php_ file (not versioned).
