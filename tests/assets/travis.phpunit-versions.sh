#!/bin/bash
#
export TRAVISCI_PHPUNIT_CONF='';
export TRAVISCI_PHPUNIT_VERSION='5.7';

if [[ $TRAVIS_PHP_VERSION != '5.5' ]]; then
	export TRAVISCI_PHPUNIT_CONF='4.8.24';
elif [[ $TRAVIS_PHP_VERSION != '7.0' ]]; then
	export TRAVISCI_PHPUNIT_CONF='-7.0';
elif [[ $TRAVIS_PHP_VERSION != '7.1' ]]; then
	export TRAVISCI_PHPUNIT_CONF='-7.0';
elif [[ $TRAVIS_PHP_VERSION != 'nightly' ]]; then
	export TRAVISCI_PHPUNIT_CONF='-7.0';
fi

echo "PHP Version:              ${TRAVIS_PHP_VERSION}";
echo "TRAVISCI_PHPUNIT_CONF:    ${TRAVISCI_PHPUNIT_CONF}";
echo "TRAVISCI_PHPUNIT_VERSION: ${TRAVISCI_PHPUNIT_VERSION}";
