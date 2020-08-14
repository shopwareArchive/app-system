const defaultStrategies = {
    status: 200,
    statusText: 'OK',
    data: {
            MoveShopPermanently:
                'Use this URL for communicating with installed apps, this will disable communication to apps on the old' +
                ' URLs installation, but the app-data from the old installation will be available in this installation.',
            ReinstallApps:
                'Reinstall all apps anew for the new URL, so app communication on the old URLs installation keeps ' +
                'working like before. App-data from the old installation will not be available in this installation.',
            UninstallApps:
                'Uninstall all apps on this URL, so app communication on the old URLs installation keeps ' +
                'working like before.',
        },
};

const urlDifference = {
    status: 200,
    statusText: 'OK',
    data: {
        oldUrl: 'https://old.com',
        newUrl: 'https://new.com',
    },
};

const emptyUrlDifference = {
    status: 204,
    statusText: 'No Content',
    data: '',
};

export default {
    defaultStrategies,
    urlDifference,
    emptyUrlDifference,
};
