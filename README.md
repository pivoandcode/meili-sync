# Acorn WordpressMeilisearch Package

This repo can be used to scaffold an Acorn package. See the [Acorn Package Development](https://roots.io/acorn/docs/package-development/) docs for further information.

## Installation

You can install this package with Composer:

```bash
composer require vendor-name/wordpress-meilisearch-1-package
```

You can publish the config file with:

```shell
$ wp acorn vendor:publish --provider="PivoAndCode\WordpressMeilisearchPackage\Providers\WordpressMeilisearchServiceProvider"
```

## Usage

From a Blade template:

```blade
@include('WordpressMeilisearch::wordpress-meilisearch')
```

From WP-CLI:

```shell
$ wp acorn wordpress-meilisearch-1
```
# meili-sync
