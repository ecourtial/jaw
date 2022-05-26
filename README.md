[![CircleCI](https://circleci.com/gh/ecourtial/jaw/tree/main.svg?style=svg)](https://circleci.com/gh/ecourtial/jaw/tree/main)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://gitHub.com/ecourtial/jaw/graphs/commit-activity)
[![Ask Me Anything !](https://img.shields.io/badge/Ask%20me-anything-1abc9c.svg)](https://gitHub.com/ecourtial/jaw)

# JAW: a headless CMS

## Description

Just a Word (JAW) is a free open-source headless CMS. At the beginning the idea was to make it private as a personal project: I wanted a stable, free and long-term oriented headless
CMS, so I could easily revamp the front application of my blog without having to re-create everything.

With JAW, you have now a CMS proving:
- An administration interface to manage your content.
- An API to get your content from your front-end application.
- Protection for the API is assured by a basic user token.
- Webhooks are provided if you want to be notified when some content is edited (for instance if you use cache).

There are still a lot of basic features to be developed, but the ambition of this project is to:
- Keep-up with the last version of PHP.
- Keep-up with the last version of Symfony.
- Trying (yes, trying) to keep-up with most of the good practices of Symfony (i.e the less deprecations notices as possible).

## Stack :light_rail:

- PHP 8.1
- Symfony 6.0
- Bootstrap 5

## Licence

This code is provided under the MIT licence.
Some parts of the codes come from the _Symfony Demo_ project and the licence of this project applies when mentioned.

## Changelog

See the changelog [here](CHANGELOG.md)

## Contributing

See the _project_ section of this repository to see the Todo list.

## Documentation :notebook:

* [Webhooks](doc/WEBHOOKS.md)
* [OpenAPI documentation](doc/api.yaml)
* [How to set up the project in your development environment](doc/DEV.md)
* [How to init the project in production](doc/PRODUCTION.md)
* [Procedure when releasing a new version of JAW (for maintainers)](doc/MAINTAINERS.md)
