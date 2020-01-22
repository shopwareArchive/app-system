import installServices from './service';
import extendAdministration from '../extension';
import { installComponents, installSnippets }from '../app';

function install(Shopware) {
    installServices(Shopware);
    extendAdministration(Shopware);
    installComponents(Shopware);
    installSnippets(Shopware);
}

export default {
    install,
};
