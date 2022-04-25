# jaw
Just A Word: a headless CMS

Stack

Symfo 6
Bootstrap 5
Lister le composants Symfo (Client, Doctrine, Security, Translation)
FosCKeditor...
VOIR LE fichier composer.json

Conf :

docker pour dev only

APP_NAME
Passer en revue toutes les var d'env.


bin/console app:add-user YourPseudo YourPassword foor@bar.com "Marcellus Faust" --admin


Do not hesitate to contribute (see the ticket tab to select a task)


APP_NAME != Blog title
back != front

Admin fixture:

John
somePassword123456


JohntheRegular
somePassword123456aa

Quelque soit l'env:

bin/console assets:install public


Reference au Symfony Demo (licence)

Todo new version: change the app version in the conf file

CHANGELOG

Parler du makefile pour le dev

Pour installer en prod:
make init

posssible de mettre un adminer

Example command pour migration depuis ma vieille DB.

