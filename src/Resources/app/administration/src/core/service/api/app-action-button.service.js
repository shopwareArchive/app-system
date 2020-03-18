const serviceName = 'AppActionButtonService';

export default class AppActionButtonService {
    static get name() {
        return serviceName;
    }

    /**
     * @param {AxiosInstance} httpClient
     * @param {LoginService} loginService
     */
    constructor(httpClient, loginService) {
        this.httpClient = httpClient;
        this.loginService = loginService;

        this.name = serviceName;
    }

    get basicHeaders() {
        return {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'sw-language-id': Shopware.Context.api.languageId,
            Authorization: `Bearer ${this.loginService.getToken()}`,
        };
    }

    /**
     * Fetches available actions for a page
     *
     * @param {string} entity
     * @param {string} view
     */
    getActionButtonsPerView(entity, view) {
        return this.httpClient
            .get(`app-system/action-button/${entity}/${view}`,
                {
                    headers: this.basicHeaders,
                },
            ).then(({ data }) => {
                return this.getActionbuttonsFromRequest(data);
            });
    }

    getActionbuttonsFromRequest(data) {
        if (!!data && !!data.actions) {
            return data.actions;
        }

        return [];
    }

    /**
     * Run an action on the server
     *
     * @param {string} id
     * @param {Object} params
     */
    runAction(id, params = {}) {
        return this.httpClient
            .post(
                `app-system/action-button/run/${id}`,
                params,
                {
                    headers: this.basicHeaders,
                },
            ).then(({ data }) => {
                return data;
            });
    }
}
