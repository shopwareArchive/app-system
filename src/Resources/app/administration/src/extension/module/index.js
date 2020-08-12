export function installModuleExtensions(Shopware) {
    const swSettingsCustomFieldPage = require('./sw-settings-custom-field/page/sw-settings-custom-field-set-list').default;

    const { Component } = Shopware;

    Component.override(swSettingsCustomFieldPage.name, swSettingsCustomFieldPage);
};
