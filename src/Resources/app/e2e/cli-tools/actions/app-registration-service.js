import {createHmac} from 'crypto';

/**
 * @module actions/AppRegistrationService
 */
/**
 * Creates an AppRegistrationService object
 * @param {string} secret
 * @param {string} confirmationUrl
 */
export default function AppRegistrationService(secret, confirmationUrl) {
    this.secret = secret;
    this.cliProxyUrl = confirmationUrl;
}

AppRegistrationService.prototype = {

    /**
     * Generates a
     * @param {string} name
     * @param {string} shop
     * @return {{confirmation_url: string, proof: PromiseLike<ArrayBuffer> | *, secret: string}}
     */
    registerApp(name, shop) {
        const hmac = createHmac('sha256', this.secret);
        hmac.update(shop + name);

        return {
            proof: hmac.digest('hex'),
            secret: 'dont_tell',
            confirmation_url: this.cliProxyUrl
        };
    }
};
