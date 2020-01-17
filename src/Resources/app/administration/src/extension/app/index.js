export function overrideComponents(Shopware) {
    const { Component } = Shopware;
    const context = require.context('./component/', true, /\.js$/);

    return context.keys().forEach((item) => {
        const component = context(item).default;
        Component.override(component.name, component, 0);
    });
}
