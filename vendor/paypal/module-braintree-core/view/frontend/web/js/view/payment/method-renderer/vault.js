/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
define([
    'ko',
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'PayPal_Braintree/js/view/payment/adapter',
    'Magento_Ui/js/model/messageList',
    'PayPal_Braintree/js/view/payment/validator-handler',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/full-screen-loader',
    'braintree',
    'braintreeHostedFields',
    'braintreeDataCollector',
    'mage/url'
], function (
    ko,
    $,
    VaultComponent,
    Braintree,
    globalMessageList,
    validatorManager,
    additionalValidators,
    fullScreenLoader,
    client,
    hostedFields,
    dataCollector,
    url
) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            active: false,
            hostedFieldsInstance: null,
            imports: {
                onActiveChange: 'active'
            },
            modules: {
                hostedFields: '${ $.parentName }.braintree'
            },
            template: 'PayPal_Braintree/payment/cc/vault',
            updatePaymentUrl: url.build('braintree/payment/updatepaymentmethod'),
            vaultedCVV: ko.observable(''),
            validatorManager: validatorManager,
            isValidCvv: false,
            deviceData: null,
            onInstanceReady: function (instance) {
                instance.on('validityChange', this.onValidityChange.bind(this));
            }
        },

        /**
         * Event fired by Braintree SDK whenever input value length matches the validation length.
         * In the case of a CVV, this is 3, or 4 for AMEX.
         *
         * @param event
         */
        onValidityChange: function (event) {
            if (event.emittedBy === 'cvv') {
                this.isValidCvv = event.fields.cvv.isValid;
            }
        },

        /**
         * @returns {exports}
         */
        initObservable: function () {
            this._super().observe(['active']);
            this.validatorManager.initialize();
            return this;
        },

        /**
         * Is payment option active?
         *
         * @returns {boolean}
         */
        isActive: function () {
            let active = this.getId() === this.isChecked();

            this.active(active);
            return active;
        },

        /**
         * Fired whenever a payment option is changed.
         *
         * @param isActive
         */
        onActiveChange: function (isActive) {
            let self = this;

            if (!isActive) {
                return;
            }

            if (self.showCvvVerify()) {
                if (self.hostedFieldsInstance) {
                    self.hostedFieldsInstance.teardown(function (teardownError) {
                        if (teardownError) {
                            globalMessageList.addErrorMessage({
                                message: teardownError.message
                            });
                        }
                        self.hostedFieldsInstance = null;
                        self.initHostedCvvField();
                    });
                    return;
                }
                self.initHostedCvvField();
            }
        },

        /**
         * Initialize the CVV input field with the Braintree Hosted Fields SDK.
         */
        initHostedCvvField: function () {
            let self = this;

            client.create({
                authorization: Braintree.getClientToken()
            }, function (clientError, clientInstance) {
                if (clientError) {
                    globalMessageList.addErrorMessage({
                        message: clientError.message
                    });
                }

                let options = {
                    client: clientInstance
                };

                if (typeof Braintree.config.dataCollector === 'object'
                    && typeof Braintree.config.dataCollector.paypal === 'boolean'
                ) {
                    options.paypal = true;
                }

                dataCollector.create(options, function (err, dataCollectorInstance) {
                    if (err) {
                        return console.log(err);
                    }
                    self.deviceData = dataCollectorInstance.deviceData;
                }.bind(this)); //eslint-disable-line no-extra-bind

                hostedFields.create({
                    client: clientInstance,
                    fields: {
                        cvv: {
                            selector: '#' + self.getId() + '_cid',
                            placeholder: '123'
                        }
                    }
                }, function (hostedError, hostedFieldsInstance) {
                    if (hostedError) {
                        globalMessageList.addErrorMessage({
                            message: hostedError.message
                        });
                        return;
                    }

                    self.hostedFieldsInstance = hostedFieldsInstance;
                    self.onInstanceReady(self.hostedFieldsInstance);
                });
            });
        },

        /**
         * Return the payment method code.
         *
         * @returns {string}
         */
        getCode: function () {
            return 'braintree_cc_vault';
        },

        /**
         * Get last 4 digits of card
         *
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details.maskedCC;
        },

        /**
         * Get expiration date
         *
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        /**
         * Get card type
         *
         * @returns {String}
         */
        getCardType: function () {
            return this.details.type;
        },

        /**
         * Get card icons
         *
         * @param {String} type
         * @returns {Boolean}
         */
        getIcons: function (type) {
            return window.checkoutConfig.payment.braintree.icons.hasOwnProperty(type) ?
                window.checkoutConfig.payment.braintree.icons[type]
                : false;
        },

        /**
         * Get show CVV Field
         *
         * @returns {Boolean}
         */
        showCvvVerify: function () {
            return window.checkoutConfig.payment[this.code].cvvVerify;
        },

        /**
         * Show or hide the error message.
         *
         * @param selector
         * @param state
         * @returns {boolean}
         */
        validateCvv: function (selector, state) {
            let $selector = $(selector),
                invalidClass = 'braintree-hosted-fields-invalid';

            if (state === true) {
                $selector.removeClass(invalidClass);
                return true;
            }

            $selector.addClass(invalidClass);
            return false;
        },

        /**
         * Place order
         */
        placeOrder: function () {
            let self = this;

            if (self.showCvvVerify()) {
                if (!self.validateCvv('#' + self.getId() + '_cid', self.isValidCvv)
                    || !additionalValidators.validate()) {
                    return;
                }
            } else if (!additionalValidators.validate()) {
                return;
            }

            fullScreenLoader.startLoader();

            if (self.showCvvVerify() && typeof self.hostedFieldsInstance !== 'undefined') {
                self.hostedFieldsInstance.tokenize({}, function (error, payload) {
                    if (error) {
                        fullScreenLoader.stopLoader();
                        globalMessageList.addErrorMessage({
                            message: error.message
                        });
                        return;
                    }
                    $.getJSON(self.updatePaymentUrl, {
                        'nonce': payload.nonce,
                        'public_hash': self.publicHash
                    }).done(function (response) {
                        if (response.success === false) {
                            fullScreenLoader.stopLoader();
                            globalMessageList.addErrorMessage({
                                message: 'CVV verification failed.'
                            });
                            return;
                        }
                        self.getPaymentMethodNonce();
                    });
                });
            } else {
                self.getPaymentMethodNonce();
            }
        },

        /**
         * Send request to get payment method nonce
         */
        getPaymentMethodNonce: function () {
            let self = this;

            fullScreenLoader.startLoader();
            $.getJSON(self.nonceUrl, {
                'public_hash': self.publicHash,
                'cvv': self.vaultedCVV()
            }).done(function (response) {
                fullScreenLoader.stopLoader();
                self.hostedFields(function (formComponent) {
                    formComponent.setPaymentMethodNonce(response.paymentMethodNonce);
                    formComponent.setCreditCardBin(response.details.bin);
                    formComponent.additionalData['public_hash'] = self.publicHash;
                    formComponent.additionalData['device_data'] = self.deviceData;
                    formComponent.code = self.code;
                    formComponent.messageContainer = self.messageContainer;
                    if (self.vaultedCVV()) {
                        formComponent.additionalData['cvv'] = self.vaultedCVV();
                    }

                    self.validatorManager.validate(formComponent, function () {
                        fullScreenLoader.stopLoader();
                        return formComponent.placeOrder('parent');
                    }, function () {
                        // No teardown actions required.
                        fullScreenLoader.stopLoader();
                        formComponent.setPaymentMethodNonce(null);
                        formComponent.setCreditCardBin(null);
                    });

                });
            }).fail(function (response) {
                let error = JSON.parse(response.responseText);

                fullScreenLoader.stopLoader();
                globalMessageList.addErrorMessage({
                    message: error.message
                });
            });
        }
    });
});
