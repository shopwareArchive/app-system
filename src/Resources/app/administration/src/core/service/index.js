import AppActionButtonService from './api/app-action-button.service';

function installServices(Shopware) {
    Shopware.Application.addServiceProvider(AppActionButtonService.name, () => {
        const init = Shopware.Application.getContainer('init');
        return new AppActionButtonService(init.httpClient, Shopware.Service('loginService'));
    });
}

export default installServices;
