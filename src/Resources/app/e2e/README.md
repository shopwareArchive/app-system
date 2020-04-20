# Connect e2e tests

## Installing

All dependencies that need to be installed to run connect's e2e test suite can be installed by executing `make e2e-init`.

## cypress.env.json

To set up configuration for e2e tests we use a `cypress.env.json` file.
We use it to build cypress' `baseUrl` and to tell cypress how the cli proxy server (see below) is available.

A sample implementation can be found at `<ConnectRoot>/dev-ops/gitlab/cypress.env.json`.

```
{
    "schema": "http",
    "host": "localhost",
    "port": 8000,
    "locale": "en-GB",
    "cliProxy": {
        "port": 8005
    }
}
```

Schema host and port schould point to the same entries as your `APP_URL`.

To use the administration in watch mode you only need to set the port to your dev-port (Remember that storefront tests will fail if you use the administration in watch mode).

```
{
    "schema": "http",
    "host": "localhost",
    "port": 8080,
    "locale": "en-GB",
    "cliProxy": {
        "port": 8005
    }
}
```

## The cli proxy

To execute the e2e tests successfully it is mandatory to run a small express server next to your shopware installation.
That server is able to run commands on your shopware server like resetting the database between tests and installing apps for e2e tests.
Additionally it acts as an external app server to test app functionality.

The cli proxy can be started by executing the command:

`make e2e-cli-proxy`

## Run tests

To open the cypress runner always use the command outside from your container.

`make e2e-open`

This is because it changes the database to `shopware_e2e`.
While the runner is open you should not use any other psh command since it could reset the database connection to use your dev database.  
