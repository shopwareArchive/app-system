module.exports = {
    plugins: ['import'],

    rules: {
        'use-isnan': ['error'],
        'curly': ['error', 'all'],
        'array-callback-return': ['error'],
        'arrow-parens': ['error', 'always'],
        'quotes': ['error', 'single'],
        'no-unused-vars': ['error'],
        'no-console': ['error', { allow: ["error"]}],
        'comma-dangle': ['error', 'always-multiline'],

        'import/no-useless-path-segments': ['warn', { noUselessIndex: true }],
        'max-len': [ 'warn', 125, { 'ignoreRegExpLiterals': true } ],
        'consistent-return': ['warn'],
        'eol-last': ['warn'],
        'array-bracket-spacing': ['warn', 'never'],
        'object-curly-spacing': ['warn', 'always'],
        'comma-spacing': ['warn', {"before": false, "after": true}],
    }
};
