import addAppManagementCommands from './commands/app-management-commands';
import addNavigationCommands from './commands/navigation';
import addSystemCommands from './commands/system-commands';
import addDataGridCommands from './commands/data-grid-commands';
import addActionButtonCommands from './commands/action-button-commands';
import addProductCommand from './commands/create-product-command';
import addThemeCommands from './commands/theme-commands';

Cypress.Cookies.defaults({
    whitelist: ['sw-admin-locale'],
});

require('@shopware-ag/e2e-testsuite-platform/cypress/support');

addSystemCommands(Cypress);
addAppManagementCommands(Cypress);
addNavigationCommands(Cypress);
addDataGridCommands(Cypress);
addActionButtonCommands(Cypress);
addProductCommand(Cypress);
addThemeCommands(Cypress);
