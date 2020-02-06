export function installModules(Shopware) {
    const { Module, Component } = Shopware;
    const modules = [];

    modules.forEach((moduleName) => {
        const module = require(`./${moduleName}`).default;

        if (module.components) {
            Object.keys(module.components).forEach((componentName) => {
                Component.register(componentName, module.components[componentName]);
            });
        }

        Module.register(module.name, module);
    });
}
