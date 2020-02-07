export function installComponents(Shopware) {
    const { Component } = Shopware;
    const context = require.context('./component/', true, /\.js$/);
    return context.keys().forEach((item) => {
        const component = context(item).default;

        Component.register(component.name, component);
    });
}
