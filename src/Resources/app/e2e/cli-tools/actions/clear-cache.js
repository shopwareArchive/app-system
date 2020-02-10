
import { spawn } from 'child_process';

/**
 * Clears the shopware servers cache. Internally runs psh.phar cache on the server
 * @module actions/resetDb
 * @param {string} rootDir 
 */
export default function clearCache(rootDir) {
    return new Promise((resolve, reject) => {
        const clearCacheProcess = spawn(
            `${rootDir}/psh.phar`,
            [
                'cache',
                '--DB_NAME="shopware_e2e"',
                '--APP_ENV="prod"',
            ],
        );
        const outputBuffer = [];
        let output = null;

        clearCacheProcess.stdout.on('data', (chunk) => {
            outputBuffer.push(chunk);
        });
        clearCacheProcess.stdout.on('end', () => {
            output = Buffer.concat(outputBuffer).toString();
        });

        clearCacheProcess.on('exit', (code) => {
            if (code === 0) {
                resolve();
                return;
            }
            
            reject({ message: output, code });
        });
    });
};
