const serviceName = 'AppModulesService';

export default class AppModulesService{
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

    fetchAppModules() {
        return this.httpClient.get(
            'app-system/modules',
            {
                headers: this.basicHeaders,
            },
        ).then(({ data }) => {
            return data.modules || [];
        });
    }
};
