import AppActionButtonService from 'connect/core/service/api/app-action-button.service';
import apiResponses from '__fixtures__/app-system/action-buttons.fixtures';

describe('app-api.service', () => {
    beforeEach(() => {
        Shopware.Service('mockAdapter').reset();
    });

    test('constructor', () => {
        const appActionButtonService = Shopware.Service('AppActionButtonService');

        expect(AppActionButtonService.name).toBe('AppActionButtonService');
        expect(appActionButtonService.name).toBe('AppActionButtonService');
    });

    test('fetch available actions', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');

        mockAdapter.onGet('app-system/action-button/product/list').reply(
            apiResponses.actionButtons.status,
            apiResponses.actionButtons.data,
        );

        const appActionButtonService = Shopware.Service('AppActionButtonService');
        const actions = await appActionButtonService.getActionButtonsPerView('product', 'list');

        expect(Array.isArray(actions)).toBe(true);
        expect(actions.length).toBe(1);
        expect(actions).toEqual(apiResponses.actionButtons.data.actions);
    });

    test('fetch undefined action', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');

        mockAdapter.onGet('app-system/action-button/product/list').reply(
            apiResponses.emptyActionButtonList.status,
            apiResponses.emptyActionButtonList.data,
        );

        const appActionButtonService = Shopware.Service('AppActionButtonService');
        const actions = await appActionButtonService.getActionButtonsPerView('product', 'list');

        expect(Array.isArray(actions)).toBe(true);
        expect(actions.length).toBe(0);
    });

    test('does not return top level array', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');

        mockAdapter.onGet('app-system/action-button/product/list').reply(
            apiResponses.malformedList.status,
            apiResponses.malformedList.data,
        );

        const appActionButtonService = Shopware.Service('AppActionButtonService');
        const actions = await appActionButtonService.getActionButtonsPerView('product', 'list');

        expect(Array.isArray(actions)).toBe(true);
        expect(actions.length).toBe(0);
    });

    test('run action has no response', async () => {
        const mockAdapter = Shopware.Service('mockAdapter');
        const actionId = Shopware.Utils.createId();

        mockAdapter.onPost(`app-system/action-button/run/${actionId}`).reply(
            apiResponses.emptyResponse.status,
            apiResponses.emptyResponse.data,
        );

        const appActionButtonService = Shopware.Service('AppActionButtonService');
        const response = await appActionButtonService.runAction(actionId);

        expect(response).toEqual([]);
    });
});
