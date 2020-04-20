
import fs from 'fs-extra';
import { join } from 'path';
import { spawn } from 'child_process';

/**
 * @module actions/AppService
 */
const fixturesFolder = 'fixtures';
const appFolder = 'custom/apps/shopware-e2e';

/**
 * Creates an AppService object
 * @param {string} projectRoot 
 * @param {string} e2eRootDir 
 * @param {string} cliProxyUrl
 */
export default function AppService(projectRoot, e2eRootDir, cliProxyUrl) {
    this.projectRoot = projectRoot;
    this.e2eRootDir = e2eRootDir;
    this.cliProxyUrl = cliProxyUrl;
}

AppService.prototype = {
    /**
     * Copies fixture app folders to project dir and runs app:refresh command
     * @param {Array.<string>} appNames
     * @return {Promise}
     */
    installApps(appNames) {
        return Promise.all(appNames.map((appName) => {
            const source = join(this.e2eRootDir, `${fixturesFolder}/${appName}`);
            const destination = join(this.projectRoot, `${appFolder}/${appName}`);

            return this.installApp(source, destination);
        })).then(() => {
            return this.updateApps();
        });
    },

    installApp(source, destination) {
        return fs.copy(source, destination).then(() => {
            return fs.readFile(`${destination}/manifest.xml`);
        }).then((manifest) => {
            const content = manifest.toString()
                .replace(/__PROXY_URL__/g, this.cliProxyUrl);

            return fs.writeFile(`${destination}/manifest.xml`, content);
        });
    },

    /**
     * Removes the shopware-e2e folder in projects app dir and runs app:refresh command
     * @returns {Promise}
     */
    removeApps() {
        return fs.emptyDir(join(this.projectRoot, appFolder))
            .then(() => {
                fs.rmdir(join(this.projectRoot, appFolder));
            })
            .then(() => {
                return this.updateApps();
            });
    },

    /**
     * Runs app:refresh command on the server
     * @private
     * @returns {Promise}
     */
    updateApps() {
        return new Promise((resolve, reject) => {
            const updateProcess = spawn('php', [`${this.projectRoot}/bin/console`, 'app:refresh', '-f']);
            const outputBuffer = [];
            let output = null;

            updateProcess.stdout.on('data', (chunk) => {
                outputBuffer.push(chunk);
            });

            updateProcess.stdout.on('end', () => {
                output = Buffer.concat(outputBuffer).toString();
            });

            updateProcess.on('exit', (code) => {
                if (code === 0) {
                    resolve();
                    return;
                }
                reject({ message: output, code });
            });
        });
    },
};
