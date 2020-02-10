import { spawn } from 'child_process';

/**
 * Resets the tests database. Internally runs psh.phar e2e:restore-db on the server
 * @module actions/resetDb
 * @param {string} rootDir 
 */
export default function resetDb(rootDir) {
    return new Promise((resolve, reject) => {
        const resetDbProcess = spawn(
            `${rootDir}/psh.phar`,
            [
                'e2e:restore-db',
                '--APP_ENV="prod"',
            ]
        );
        const outputBuffer = [];
        let output = null;

        resetDbProcess.stdout.on('data', (chunk) => {
            outputBuffer.push(chunk);
        });
        resetDbProcess.stdout.on('end', () => {
            output = Buffer.concat(outputBuffer).toString();
        });

        resetDbProcess.on('exit', (code) => {
            if (code === 0) {
                resolve({ code, message: output });
                return;
            }
            
            reject({ message: output, code });
        });
    });
};
