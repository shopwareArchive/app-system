const path = require('path');

const projectRoot = path.join(__dirname, '../../../../../../../');
const artifactsPath = path.join(projectRoot, '/build/artifacts');
const administrationCorePath = path.join(
    projectRoot,
    'vendor/shopware/platform/src/Administration/Resources/app/administration/src',
);

module.exports = {
    displayName: 'Testsuite for SaaS Connect',

    /*
     * mock.config
     */
    clearMocks: true,
    restoreMocks: true,

    /*
     * Coverage
     */
    coverageDirectory: artifactsPath,
    collectCoverage: true,
    coverageReporters: [
        'lcov',
        'text',
        'clover',
    ],
    reporters: [
        'default',
        ['jest-junit', {
            suiteName: 'Testsuite for SaaS Connect',
            outputDirectory: artifactsPath,
            outputName: 'administration.junit.xml',
        }],
    ],
    collectCoverageFrom: [
        '<rootDir>/src/core/**/*.js',
    ],
    coverageThreshold: {
        global: {
            functions: 100,
            statements: 100,
        },
    },

    watchPathIgnorePatterns: ['node_modules'],

    globals: {
        projectRoot,
        administrationCorePath,
    },

    /*
     * transforms
     */
    testMatch: [
        '<rootDir>/test/unit/**/*.test.js',
    ],

    setupFilesAfterEnv: [
        '<rootDir>/test/unit/jest-setup/setup-shopware.js',
    ],

    moduleNameMapper: {
        '^connect/(.*)$': '<rootDir>/src/$1',
        '\\.twig$': '<rootDir>/test/unit/__mocks__/template.mock.js',
        '\\.(css|scss|less)$': '<rootDir>/test/unit/__mocks__/style.mock.js',
        '^module$': '<rootDir>/test/unit/__mocks__/module.mock',
        '^src/app/component/component$': '<rootDir>/test/unit/__mocks__/components.mock',
        '^src/core/factory/http.factory': '<rootDir>/test/unit/__mocks__/http.factory.js',
        '^src/(.*)$': `${administrationCorePath}/$1`,
        '^__fixtures__/(.*)$': '<rootDir>/test/unit/__fixtures__/$1',
    },

    transformIgnorePatterns: ['node_modules'],

    transform: {
        '^.+\\.js$': 'babel-jest',
        '^.+\\.html$': 'html-loader-jest',
    },
};
