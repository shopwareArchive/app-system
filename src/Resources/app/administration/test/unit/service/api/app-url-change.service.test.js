import apiResponses from '__fixtures__/app-system/app-url-change.fixtures';

describe('app-api.service', () => {
    beforeEach(() => {
        Shopware.Service('mockAdapter').reset();
    });

    test('constructor', () => {
        const appUrlChangeService = Shopware.Service('AppUrlChangeService');

        expect(appUrlChangeService.name).toBe('AppUrlChangeService');
    });

    test('It returns strategies in the right format', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');

        mockAdapter.onGet('app-system/app-url-change/strategies').reply(
            apiResponses.defaultStrategies.status,
            apiResponses.defaultStrategies.data,
        );

        const appUrlChangeService = Shopware.Service('AppUrlChangeService');
        const  strategies = await appUrlChangeService.fetchResolverStrategies();

        expect(Array.isArray(strategies)).toBe(true);
        expect(strategies.length).toBe(3);
        expect(strategies.map(({ name }) => name)).toEqual(Object.keys(apiResponses.defaultStrategies.data));
        strategies.forEach((strategy) => expect(strategy.description.length > 0));
    });

    test('It can fetch the url difference', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');

        mockAdapter.onGet('app-system/app-url-change/url-difference').reply(
            apiResponses.urlDifference.status,
            apiResponses.urlDifference.data,
        );

        const appUrlChangeService = Shopware.Service('AppUrlChangeService');
        let urlDiff = await appUrlChangeService.getUrlDiff();

        expect(urlDiff).toMatchObject(
            {
                oldUrl: 'https://old.com',
                newUrl: 'https://new.com',
            },
        );
    });

    test('It returns null if there is no url difference', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');

        mockAdapter.onGet('app-system/app-url-change/url-difference').reply(
            apiResponses.emptyUrlDifference.status,
            apiResponses.emptyUrlDifference.data,
        );

        const appUrlChangeService = Shopware.Service('AppUrlChangeService');
        let urlDiff = await appUrlChangeService.getUrlDiff();

        expect(urlDiff).toBeNull();
    });

    test('It correctly sends a resolver strategy', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');

        mockAdapter.onPost('app-system/app-url-change/resolve').reply(
            (req) => {
                expect(req.data).toEqual('{"strategy":"TestStrategy"}');
                return [204];
            });

        const appUrlChangeService = Shopware.Service('AppUrlChangeService');
        await appUrlChangeService.resolveUrlChange({ name: 'TestStrategy' });
    });
});
