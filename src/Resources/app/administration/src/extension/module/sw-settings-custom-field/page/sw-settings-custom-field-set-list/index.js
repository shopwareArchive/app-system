const { Criteria } = Shopware.Data;

export default {
    'name': 'sw-settings-custom-field-set-list',

    computed: {
        listingCriteria() {
            const criteria = this.$super('listingCriteria');
            criteria.addFilter(Criteria.equals('appId', null));

            return criteria;
        },
    },
};
