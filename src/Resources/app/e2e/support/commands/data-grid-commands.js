/**
 * Queries a data grid and adds its column information
 * @param {string} selector
 */
function inspectDataGrid(selector = '.sw-data-grid') {
    let columns = [];
    return cy.get(selector).within((grid) => {
        cy.get('.sw-data-grid__skeleton').should('not.exist');
        return cy.wrap(grid)
            .find('thead.sw-data-grid__header')
            .find('th.sw-data-grid__cell--header')
            .each((element, index) => {
                if (index === 0 && element.hasClass('sw-data-grid__cell--selection')) {
                   columns.push('selection');
                   return;
                }

                if (element.hasClass('sw-data-grid__cell-spacer')) {
                    return;
                }

                columns.push(element.text().trim());
            }).then(() => {
                grid.columns = columns;
                return grid;
            });
    });
}

/**
 * @callback predicate
 * @param {HtmlElement} row
 * @param {number} index
 * @return {boolean}
 */

/**
 * Query rows within a datagrid where a column value fullfills a given predicate
 * @param {JQuery} prevSubject
 * @param {string} columnName
 * @param {string|predicate} predicate
 */
function findRowsWith(prevSubject, columnName, predicate) {
    const columnIndex = getColumnIndex(prevSubject.columns, columnName);
    const rows = prevSubject.find('tbody tr.sw-data-grid__row')
        .filter(function (index) {
            if (typeof predicate === 'function') {
                return predicate(this, index);
            }

            const cell = this.querySelector(`td:nth-child(${columnIndex + 1})`);
            return cell.innerText.match(predicate);
        });

    rows.columns = prevSubject.columns;
    return rows;
}

/**
 * Finds a single row which column value fullfills a given predicate
 * @param {JQuery} prevSubject
 * @param {string} columnName
 * @param {string|predicate} predicate
 */
function findRowWith(prevSubject, columnName, predicate) {
    const row = findRowsWith(prevSubject, columnName, predicate);
    expect(row).to.have.length(1);
    return row;
}

/**
 * Executes a callback in a cell of a given data-grid row
 * @param {JQuery} prevSubject
 * @param {string} name
 * @param {function} callback
 */
function inColumn(prevSubject, name, callback) {
    const columnIndex = getColumnIndex(prevSubject.columns, name);
    const cell = prevSubject.find(`td:nth-child(${columnIndex + 1})`);

    callback(cell);
    return prevSubject;
}

/**
 * Sets the selected state of a data-grid row
 * @param {JQuery} prevSubject 
 * @param {boolean} [select]
 */
function setRowSelected(prevSubject, select = true) {
    return cy.wrap(prevSubject)
        .find('td.sw-data-grid__cell--selection')
        .find('input[type=checkbox]')
        .then((checkbox) => {
            if (select) {
                cy.wrap(checkbox).check();
                return;
            }
            cy.wrap(checkbox).uncheck();
        }).then(() => {
            return prevSubject;
        });
}

/**
 * Returns the index of a column name in an array of column names
 * @param {Array<string>} columns 
 * @param {string} columnName
 * @returns {number}
 */
function getColumnIndex(columns, columnName) {
    const index = columns.findIndex((column) => {
        return column === columnName;
    });
    expect(index).to.be.above(-1);
    return index;
}

export default function addDataGridCommands(Cypress) {
    Cypress.Commands.add('inspectDataGrid', { prevSubject: false }, inspectDataGrid);
    Cypress.Commands.add('findRowsWith', { prevSubject: true }, findRowsWith);
    Cypress.Commands.add('findRowWith', { prevSubject: true }, findRowWith);
    Cypress.Commands.add('setRowSelected', { prevSubject: true }, setRowSelected);
    Cypress.Commands.add('inColumn', { prevSubject: true }, inColumn);
}
