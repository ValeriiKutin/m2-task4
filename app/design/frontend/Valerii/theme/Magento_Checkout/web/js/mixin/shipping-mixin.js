define([
    'jquery',
], function ($) {
    'use strict';

    return function (originalShipping) {
        return originalShipping.extend({
            initialize: function () {
                this._super();
                this.rates.subscribe(this.filterRates.bind(this));
            },
            filterRates: function (rates) {

                console.log(rates)

                const filteredRates = rates.filter(rate => rate.amount <= 1000);

                console.log(filteredRates)

                if (JSON.stringify(rates) !== JSON.stringify(filteredRates)) {
                    this.rates(filteredRates);
                }
                console.log('smth')
            }
        });
    };
});
