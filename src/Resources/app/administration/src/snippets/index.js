export function installSnippets(Shopware) {
    Shopware.Locale.extend('de-DE', require('./de-DE'));
    Shopware.Locale.extend('en-GB', require('./en-GB'));
}
