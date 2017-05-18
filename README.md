# silex-secure-controllers

[![Build Status](https://travis-ci.org/emartech/silex-secure-controllers.svg?branch=master)](https://travis-ci.org/emartech/silex-secure-controllers)
Copyright EMARSYS 2017 All rights reserved.


Install dependencies with docker: 

```
$ docker run --rm --interactive --tty --volume $PWD:/app composer install --ignore-platform-reqs --no-scripts
```

Update dependencies with docker: 

```
$ docker run --rm --interactive --tty --volume $PWD:/app composer update --ignore-platform-reqs --no-scripts
```

Run tests:

```
$ docker run -v $(pwd):/app --rm phpunit/phpunit test/ --bootstrap vendor/autoload.php
```
