import path from 'path';
import AxiosMockAdapter from 'axios-mock-adapter';

const shopwareHttpFactory = require(path.join(administrationCorePath, 'core/factory/http.factory')).default;

export default function createHTTPClient(context) {
    const client = shopwareHttpFactory(context);
    Shopware.Application.getContainer('service').mockAdapter = new AxiosMockAdapter(client);

    return client;
}
