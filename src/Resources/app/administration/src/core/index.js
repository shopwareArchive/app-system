import installServices from './service';
import extendAdministration from '../extension';
import { installComponents, addStateModules, initializeAppModules } from '../app';
import { installModules } from '../module';
import { installSnippets } from '../snippets';

function install(Shopware) {
    installServices(Shopware);
    addStateModules(Shopware);
    installSnippets(Shopware);

    installComponents(Shopware);
    installModules(Shopware);
    extendAdministration(Shopware);

    initializeAppModules();
}

export default {
    install,
};
