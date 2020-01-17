export function installSnippets(Shopware) {
    Shopware.Locale.extend('de-DE', require('./snippets/de-DE'));
    Shopware.Locale.extend('en-GB', require('./snippets/en-GB'));
}

export function installComponents(Shopware) {
    const { Component } = Shopware;
    const context = require.context('./component/', true, /\.js$/);
    return context.keys().forEach((item) => {
        const component = context(item).default;

        Component.register(component.name, component);
    });
}
