const { ApiService } = Shopware.Classes;

const serviceName = 'AppApiService';

export default class AppApiService extends ApiService {
    static get name() {
        return serviceName;
    }

    /**
     * @param {axios} httpClient 
     * @param {*} loginService 
     */
    constructor(httpClient, loginService) {
        super(httpClient, loginService, 'app-system', 'aplication/json');
        this.name = serviceName;
    }

    get basicHeaders() {
        const basicHeader = this.getBasicHeaders();

        // ToDo fetch language id of admin locale

        return basicHeader;
    }

    /**
     * Fetches available actions for a page
     * 
     * @param {string} entity 
     * @param {string} view
     */
    getActionButtonsPerView(entity, view) {
        return this.httpClient
            .get(`${this.apiEndpoint}/action-button/${entity}/${view}`,
                {
                    headers: this.basicHeaders,
                }
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    /**
     * Run an action on the server
     * 
     * @param {string} id
     * @param {Object} requirements
     */
    runAction(id, requirements) {
        return this.httpClient
            .post(
                `${this.apiEndpoint}/action-button/run/${id}`,
                {
                    headers: this.basicHeaders,
                    data: requirements,
                }
            ).then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}
