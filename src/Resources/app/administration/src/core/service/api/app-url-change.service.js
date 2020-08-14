const serviceName = 'AppUrlChangeService';

export default class AppUrlChangeService {
    static get name() {
        return serviceName;
    }

    constructor(httpClient, loginService) {
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.name = serviceName;
    }

    get basicHeaders() {
        return {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${this.loginService.getToken()}`,
        };
    }

    /**
     * @returns {Promise<Array<{key: string, description: string}>>}
     */
    fetchResolverStrategies() {
        return this.httpClient.get(
            'app-system/app-url-change/strategies',
            {
                headers: this.basicHeaders,
            },
        ).then(({ data }) => {
            return Object.entries(data).map(([key, description]) => {
                return { name: key, description };
            });
        });
    }

    /**
     * @param {{name: string}} strategy
     * @returns {*}
     */
    resolveUrlChange({ name }) {
        return this.httpClient
            .post(
                'app-system/app-url-change/resolve',
                { strategy: name },
                {
                    headers: this.basicHeaders,
                },
            ).then(({ data }) => {
                return data;
            });
    }

    /**
     * @returns {Promise<{newUrl: string, oldUrl: string} | null>}
     */
    getUrlDiff() {
        return this.httpClient.get(
            'app-system/app-url-change/url-difference',
            {
                headers: this.basicHeaders,
            },
        ).then((resp) => {
            if (resp.status === 204) {
                return null;
            }
            return resp.data;
        });
    }
}
