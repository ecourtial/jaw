# Changelog

## 1.2.0
* Bump the Symfony version from 6.2 to 6.4.
* Dropped the use of the php security checker. We use _composer audit_ instead.

## 1.1.0

* Improve project installation doc and configuration for the dev environment.
* Fix bug: the API search endpoint was not looking into the content of the posts (only in the title and the summary).
* Update Twig to remove a CVE.
* Update the Makefile with the new _docker compose_ command.
* Added a "Remember me" feature when logging-in.
* Bump the Symfony version from 6.0 to 6.2:
  * Remove deprecations.
  * Simplify the CSRF configuration for the logout action.
  * Remove the use of the "composer/package-versions-deprecated" plugin.
  * Simplified user password handling in the form.

## 1.0

* First release of the project.
