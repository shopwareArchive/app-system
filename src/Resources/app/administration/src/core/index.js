import installServices from './service';
import extendAdministration from '../extension';
import { installComponents } from '../app';
import { installModules } from '../module';
import { installSnippets } from '../snippets';

function install(Shopware) {
    installServices(Shopware);
    extendAdministration(Shopware);
    installComponents(Shopware);
    installSnippets(Shopware);
    installModules(Shopware);
}

export default {
    install,
};
