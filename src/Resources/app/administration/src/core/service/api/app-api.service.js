const { ApiService } = Shopware.Classes;

const serviceName = 'AppApiService';

export default class AppApiService extends ApiService {
    static get name() {
        return serviceName;
    }

    constructor(httpClient, loginService) {
        super(httpClient, loginService, 'app');
        this.name = serviceName;
    }
}
