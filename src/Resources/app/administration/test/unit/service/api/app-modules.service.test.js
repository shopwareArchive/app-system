import AppModulesService from 'connect/core/service/api/app-modules.service';
import apiResponses from '__fixtures__/app-system/app-modules.fixtures';

let httpClient = null;

describe('app-api.service', () => {
    beforeEach(() => {
        if (httpClient) {
            httpClient.reset();
        }
    });

    test('constructor', () => {
        const httpClient = Shopware.Application.getContainer('init').httpClient;
        const loginService = Shopware.Service('loginService');

        const appModulesService = new AppModulesService(
            httpClient,
            loginService,
        );

        expect(AppModulesService.name).toBe('AppModulesService');
        expect(appModulesService.name).toBe('AppModulesService');
    });

    test('It can fetch modules', (done) => {
        httpClient = Shopware.Application.getContainer('init').httpClient;
        const appModulesService = new AppModulesService(httpClient, Shopware.Service('loginService'));

        appModulesService.fetchAppModules()
            .then((modules) => {
                expect(Array.isArray(modules)).toBe(true);
                expect(modules.length).toBe(2);
                expect(modules).toEqual(apiResponses.appsAndModules.data.modules);
                
                done();
            });

        httpClient.mockResponse(apiResponses.appsAndModules);
        expect(httpClient.get).toBeCalledWith(
            'app-system/modules',
            {
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: 'Bearer false',
                },
            },
        );
    });

    test('with empty list', (done) => {
        httpClient = Shopware.Application.getContainer('init').httpClient;
        const appModulesService = new AppModulesService(httpClient, Shopware.Service('loginService'));

        appModulesService.fetchAppModules()
            .then((modules) => {
                expect(Array.isArray(modules)).toBe(true);
                expect(modules.length).toBe(0);
                
                done();
            });

        httpClient.mockResponse(apiResponses.emptyModuleList);
        expect(httpClient.get).toBeCalledWith(
            'app-system/modules',
            {
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: 'Bearer false',
                },
            },
        );
    });

    test('It does not return top level array', (done) => {
        httpClient = Shopware.Application.getContainer('init').httpClient;
        const appModulesService = new AppModulesService(httpClient, Shopware.Service('loginService'));

        appModulesService.fetchAppModules()
            .then((modules) => {
                expect(Array.isArray(modules)).toBe(true);
                expect(modules.length).toBe(0);
                
                done();
            });

        httpClient.mockResponse(apiResponses.malformedModulesList);
        expect(httpClient.get).toBeCalledWith(
            'app-system/modules',
            {
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: 'Bearer false',
                },
            },
        );
    });
});
