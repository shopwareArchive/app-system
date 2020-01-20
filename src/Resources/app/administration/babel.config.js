module.exports = {
    presets: [
        [
            '@babel/preset-env',
            {
                targets: {
                    node: 'current',
                },
            },
        ],
    ],
    env: {
        test: {
            presets: [
                '@babel/preset-env',
            ],
            plugins: [
                '@babel/plugin-syntax-dynamic-import',
                '@babel/plugin-proposal-class-properties',
                'require-context-hook',
                '@babel/plugin-transform-runtime',
            ],
        },
    },
};
