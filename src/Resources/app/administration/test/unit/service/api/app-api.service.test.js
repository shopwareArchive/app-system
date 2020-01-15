describe('app-api.service', () => {
    test('constructor', () => {
        const httpClient = Shopware.Application.getContainer('init').httpClient;
        const loginService = Shopware.Service('loginService');

        const AppApiService = require('connect/core/service/api/app-api.service.js').default;

        const apiService = new AppApiService(
            httpClient,
            loginService
        );

        expect(AppApiService.name).toBe('AppApiService');
        expect(apiService.name).toBe('AppApiService');
    });
});
