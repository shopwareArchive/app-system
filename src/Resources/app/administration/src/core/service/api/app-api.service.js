const serviceName = 'AppApiService';

export default class AppApiService {
    static get name() {
        return serviceName;
    }

    /**
     * @param {AxiosInstance} httpClient 
     * @param {LoginService} loginService 
     */
    constructor(httpClient, loginService) {
        this.httpClient = httpClient;
        this.loginSerice = loginService;

        this.name = serviceName;
    }

    get basicHeaders() {
        return {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${this.loginSerice.getToken()}`,
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
                }
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
                }
            ).then(({ data }) => {
                return data;
            });
    }
}
