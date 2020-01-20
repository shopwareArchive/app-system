import AppApiService from './api/app-api.service';

function installServices(Shopware) {
    Shopware.Application.addServiceProvider(AppApiService.name, () => {
        const init = Shopware.Application.getContainer('init');
        return new AppApiService(init.httpClient, Shopware.Service('loginService'));
    });
}

export default installServices;
