import AppModulesService from 'connect/core/service/api/app-modules.service';
import apiResponses from '__fixtures__/app-system/app-modules.fixtures';

describe('app-api.service', () => {
    beforeEach(() => {
        Shopware.Service('mockAdapter').reset();
    });

    test('constructor', () => {
        const appModulesService = Shopware.Service('AppModulesService');

        expect(AppModulesService.name).toBe('AppModulesService');
        expect(appModulesService.name).toBe('AppModulesService');
    });

    test('It can fetch modules', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');

        mockAdapter.onGet('app-system/modules').reply(
            apiResponses.appsAndModules.status,
            apiResponses.appsAndModules.data,
        );

        const appModulesService = Shopware.Service('AppModulesService');
        const modules = await appModulesService.fetchAppModules();

        expect(Array.isArray(modules)).toBe(true);
        expect(modules.length).toBe(2);
        expect(modules).toEqual(apiResponses.appsAndModules.data.modules);
    });

    test('with empty list', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');

        mockAdapter.onGet('app-system/modules').reply(
            apiResponses.emptyModuleList.status,
            apiResponses.emptyModuleList.data,
        );

        const appModulesService = Shopware.Service('AppModulesService');
        const modules = await appModulesService.fetchAppModules();
        
        expect(Array.isArray(modules)).toBe(true);
        expect(modules.length).toBe(0);;
    });

    test('It does not return top level array', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');

        mockAdapter.onGet('app-system/modules').reply(
            apiResponses.malformedModulesList.status,
            apiResponses.malformedModulesList.data,
        );

        const appModulesService = Shopware.Service('AppModulesService');
        const modules = await appModulesService.fetchAppModules();

        expect(Array.isArray(modules)).toBe(true);
        expect(modules.length).toBe(0);
    });
});
