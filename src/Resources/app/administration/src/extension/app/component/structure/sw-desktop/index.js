import template from './sw-desktop.html.twig';

const { Service } = Shopware;

/**
 * @private
 */
export default {
    name: 'sw-desktop',
    template,

    data() {
        return {
            urlDiff: null,
        };
    },

    computed: {
        appUrlChangeService() {
            return Service('AppUrlChangeService');
        },
    },

    async created() {
        await this.updateShowUrlChangedModal();
    },

    methods: {
        async updateShowUrlChangedModal() {
            this.urlDiff = await this.appUrlChangeService.getUrlDiff();
        },

        closeModal() {
            this.urlDiff = null;
        },
    },
};
