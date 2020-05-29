# Connect

App system for Shopware 6 Cloud & OnPrem.

## Getting started

To get started with the theme system create a `custom/apps` folder in your shopware installation.
Create a folder for your app and provide a manifest file in that folder:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<manifest xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:noNamespaceSchemaLocation="/src/Core/Content/App/Manifest/Schema/manifest-1.0.xsd">
    <meta>
        <name>YourTechnicalThemeName</name>
        <label>Label</label>
        <label lang="de-DE">Name</label>
        <description>A description</description>
        <description lang="de-DE">Eine Beschreibung</description>
        <author>Your Company Ltd.</author>
        <copyright>(c) by Your Company Ltd.</copyright>
        <version>1.0.0</version>
    </meta>
</manifest>
```

After that you should be able to install your app via `bin/console app:refresh`.

## Developing Themes

The app system was designed to make it easy to migrate your existing themes to the app system.
The app system is based on the current theme system, that way you can reuse most of your existing themes code. 

### Manifest file
As the app system does not depend on the plugin system you don't need to provide a `composer.json` and plugin base class. Instead you have to provide the metadata of your theme in a `manifest.xml` file in your themes root folder.
A minimal manifest can be found in the getting started section.

The modifications you want to make in your theme have to be stored in the `/Resources` folder, just like in the current plugin theme system.
 
Please note that it is absolutely possibly to provide a `manifest.xml` and a `composer.json` and plugin base class in one theme, that way your theme is compatible with both the plugin system and the app system.

### Limitations
If you use the app system to publish your theme this comes with some limitations:

1. You can't extend the shopware php backend, all php files you may include in your theme won't be executed.
    * this currently leads to the limitation that it is not possible to add custom snippets to your theme, but this will be possible in the future.
1. You can't extend the shopware adminstration, all js files provided in the /administration namespace will be ignored.

### Installation
Once you have installed your theme via `bin/console app:refresh` your theme should show up in the theme manager and you should be able to use the theme commands, like `bin/console theme:compile` or `bin/console theme:refresh` with your theme.
