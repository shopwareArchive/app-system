import { overrideComponents } from './app';
import { installModuleExtensions } from './module';

function extendAdministration(Shopware) {
    overrideComponents(Shopware);
    installModuleExtensions(Shopware);
}

export default extendAdministration;
