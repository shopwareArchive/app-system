import express from 'express';
import jsonBody from 'body/json.js';
import { dirname, join, resolve } from 'path';
import AppService from './actions/app-service.js';
import AppRegistrationService from "./actions/app-registration-service.js";
import resetDB from './actions/reset-db.js';
import clearCache from './actions/clear-cache.js';
import cypressEnv from '../cypress.env.json';

const e2eRoot = join(process.cwd());
const projectRoot = join(e2eRoot, '../../../../../../../');

const proxyPort = cypressEnv.cliProxy.port;
const cliProxyUrl = `${cypressEnv.schema}://${cypressEnv.host}:${proxyPort}`;

const confirmationPath = '/confirm';

const appService = new AppService(
    projectRoot,
    e2eRoot,
    cliProxyUrl,
);
const appRegistrationService = new AppRegistrationService(
    's3cr3t',
    cliProxyUrl + confirmationPath,
);

const server = express();

server.post('/install-e2e-apps', function (req, res) {
    jsonBody(req, res, function(err, body) {
        if (err) {
            res.statusCode = 400;
            res.send(JSON.stringify(err));
            return;
        }

        appService.installApps(body.apps).then(() => {
            res.statusCode = 204;
            res.send();
        }).catch((err) => {
            res.statusCode = 400;
            res.send(JSON.stringify(err));
        });
    });
});

server.delete('/remove-e2e-apps', function (req, res) {
    appService.removeApps().then(() => {
        res.statusCode = 204;
        res.send();
    }).catch((cause) => {
        res.statusCode = 400;
        res.send(JSON.stringify(cause));
    });
});

server.delete('/cleanup', function (req, res) {
    resetDB(projectRoot)
        .then((stdout) => {
            clearCache(projectRoot);
            return stdout;
        }).then((stdout) => {
            res.statusCode = 200;
            res.send(JSON.stringify(stdout));
        }).catch((cause) => {
            res.statusCode = 400;
            res.send(JSON.stringify(cause));
        });
});

server.get('/show-app-action', function(req, res) {
    res.sendFile(resolve(`${e2eRoot}/cli-tools/view/show-product-app.html`));
});

server.get('/:name/registration', function(req, res) {
    const body = appRegistrationService.registerApp(req.params.name, req.query.shop);

    res.statusCode = 200;
    res.send(JSON.stringify(body));
});

server.post(confirmationPath, function(req, res) {
    res.statusCode = 204;
    res.send()
});

server.listen(proxyPort, () => {
    // eslint-disable-next-line
    console.log(`
CLI Proxy server for e2e system commands started. ${dirname('.')}
Listening at port: ${proxyPort}
`);
});
