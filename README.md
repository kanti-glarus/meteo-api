# Meteo API

This ist the API to our [Meteostation](https://meteo.kanti-glarus.ch) on the rooftop of the Kantonsschule Glarus.

## Online

The current version of the API is here: [meteo.kanti-informatik.ch/](https://meteo.kanti-informatik.ch/).

## Running local

1) clone this git-repository
2) run `composer install` in the root-folder
3) setup an apache-server
4) point your server to the `<root>/public` folder
5) copy `<root>/.env.example` to `<root>/.env` and add the correct values to connect with the database
6) your website is ready
