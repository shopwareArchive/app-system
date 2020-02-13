export function installComponents(Shopware) {
    const { Component } = Shopware;
    const context = require.context('./component/', true, /\.js$/);

    context.keys().forEach((item) => {
        const component = context(item).default;

        Component.register(component.name, component);
    });
}

export function addStateModules(Shopware) {
    const { State } = Shopware;
    const states = ['connect-apps'];

    states.forEach((moduleName) => {
        const state = require(`./state/${moduleName}.state`).default;
        State.registerModule(moduleName, state);
    });
}

export function initializeAppModules() {
    return Shopware.State.dispatch('connect-apps/fetchAppModules');
}
