export function installModules(Shopware) {
    const { Module, Component } = Shopware;
    const modules = ['sw-my-apps'];

    modules.forEach((moduleName) => {
        const module = require(`./${moduleName}`).default;

        if (module.components) {
            Object.keys(module.components).forEach((componentName) => {
                const component = module.components[componentName];

                if (component.extendsFrom) {
                    Component.extend(componentName, component.extendsFrom, component);
                    return;
                }

                Component.register(componentName, component);
            });
        }

        Module.register(module.name, module);
    });
}
