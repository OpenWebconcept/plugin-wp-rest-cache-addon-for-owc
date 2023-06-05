# WP REST Cache - AddOn for OpenWebConcept

Adds caching of the OpenWebConcept endpoints to the WP REST Cache.

## Installation

1. Unzip and/or move all files to the /wp-contents/plugins/wp-rest-cache-addon-for-owc directory.
2. Log into the WordPress admin and make sure the [WP REST Cache plugin](https://wordpress.org/plugins/wp-rest-cache/) is installed and activated.
3. Activate the 'WP REST Cache - AddOn for OpenWebConcept' plugin through the 'Plugins' menu.

or install using Composer:

1. `composer config repositories.openwebconcept/wp-rest-cache-addon-for-owc git git@github.com:OpenWebconcept/plugin-wp-rest-cache-addon-for-owc.git`
2. `composer require openwebconcept/wp-rest-cache-addon-for-owc`

## Supported plugins/endpoints

The following plugins/endpoints are supported by this add-on and are automatically cached if this plugin is activated (and the corresponding plugin is activated as well).

### [OpenConvenanten plugin](https://github.com/OpenWebconcept/plugin-openconvenanten)

* owc/openconvenanten/v1/items

### [OpenPub Base plugin](https://github.com/OpenWebconcept/plugin-openpub-base)

* owc/openpub/v1/items
* owc/openpub/v1/themes

### [Persberichten plugin](https://github.com/OpenWebconcept/plugin-openpub-persberichten)

* owc/openpub/v1/persberichten

### [Ruimtelijke plannen plugin](https://github.com/OpenWebconcept/plugin-ruimtelijke-plannen)

* owc/openpub/v1/ruimtelijke-plannen

### [B&W Besluiten plugin](https://github.com/OpenWebconcept/plugin-b-w-besluiten)

* owc/openpub/v1/besluiten

### [OpenWOB plugin](https://github.com/OpenWebconcept/plugin-openwob)

* owc/openwob/v1/items

### [OpenWOO plugin](https://github.com/OpenWebconcept/plugin-openwoo)

* owc/openwoo/v1/items

### [PDC Base plugin](https://github.com/OpenWebconcept/plugin-pdc-base)

* owc/pdc/v1/items
* owc/pdc/v1/themes
* owc/pdc/v1/themas
* owc/pdc/v1/subthemes
* owc/pdc/v1/subthemas
* owc/pdc/v1/groups
* owc/pdc/v1/sdg
* owc/pdc/v1/sdg-kiss

### [PDC Locations](https://github.com/OpenWebconcept/plugin-pdc-locations)

* owc/pdc/v1/locations

### [PDC Internal Products](https://github.com/OpenWebconcept/plugin-pdc-internal-products)

The following endpoint provided by the PDC Internal Products plugin is explicitly *NOT* cached (since it requires authentication):
* owc/pdc/v1/items/internal

## Questions

Are you missing any endpoints or have other technical questions? Please ask them at the [GitHub issues](https://github.com/OpenWebconcept/plugin-wp-rest-cache-addon-for-owc/issues). For general questions about the Open Webconcept we ask you get in touch with us via the [Open Webconcept website](https://openwebconcept.nl/contact/).