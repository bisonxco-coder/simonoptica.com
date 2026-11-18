<template>
    <div class="mb-5 pt-5" v-if="canShowFooter">
        <div class="app-layout footer fixed-bottom ">
            <div :class="cardStyle">

                <!-- Calculation form -->
                <div :class="calculationStyle">
                    <calculation @onClose="onCalculationClose"/>
                </div>

                <!-- Footer -->
                <div class="d-flex justify-content-between">
                    <!-- Left side -->
                    <div>
                        <!-- Order basket -->
                        <div v-if="pageName == 'orderBasket'"></div>

                        <!-- Tracer configuration -->
                        <div v-else-if="pageName == 'tracerConfiguration'"></div>

                        <!-- New Order -->
                        <div v-else>
                            <div v-if="showCalculation && settingsShowCalculation === 'YES'" style="z-index:1;">
                                <div class="d-flex py-md-0 py-3 position-absolute" >
                                    <a :class="'pointer ' + arrowStyle" @click="onCalculationToggle"/>
                                    <label class="align-self-center px-4 pointer" @click="onCalculationToggle">{{ $translations.footer.calculation.title }}</label>

                                    <!-- Loading indicator -->
                                    <span v-if="calculation.isLoading && !calculation.isCalculationOpen" class="spinner-icon large opaque"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div>
                        <!-- Order basket -->
                        <div v-if="pageName == 'orderBasket'">
                            <button
                                class="btn btn-secondary panel-button border-0 px-5"
                                @click="onSendOrders"
                                :disabled="isSendOrdersButtonDisabled"
                            >
                                {{ $translations.footer.sendOrders }}
                            </button>
                        </div>

                        <!-- Tracer configuration -->
                        <div v-else-if="pageName == 'tracerConfiguration'">
                            <button
                                class="btn btn-secondary panel-button d-md-inline-block d-block blue border-0 px-5 mx-2 my-md-0 my-2"
                                @click="onSaveTracerConfiguration"
                                :disabled="!tracer.canSave"
                            >
                                {{$translations.footer.save}}
                            </button>
                        </div>

                        <!-- New Order -->
                        <div v-else>
                            <nav class="navbar navbar-expand-md navbar-light d-flex justify-content-end py-md-0 py-sm-2">
                                <button
                                    class="navbar-toggler"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#calculationNavbar"
                                    aria-controls="calculationNavbar"
                                    aria-expanded="false"
                                >
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="calculationNavbar">
                                    <div class="navbar-nav ">
                                        <button
                                            class="btn btn-secondary panel-button d-md-inline-block d-block border-0 px-4 mx-2 my-md-0 my-2"
                                            @click="onResetOrder"
                                        >
                                            {{$translations.footer.resetOrder}}
                                        </button>
                                        <button
                                            v-if="showSaveDraft"
                                            class="btn btn-secondary panel-button d-md-inline-block d-block blue border-0 px-5 mx-2 my-md-0 my-2"
                                            @click="onSaveDraft"
                                            :disabled="isSaveDraftButtonDisabled"
                                        >
                                            {{ $translations.footer.saveDraft }}
                                        </button>
                                        <button
                                            class="btn btn-secondary panel-button d-md-inline-block d-block blue border-0 px-5 mx-2 my-md-0 my-2"
                                            @click="onSaveOrder"
                                            :disabled="isSaveOrderButtonDisabled"
                                        >
                                            {{ $translations.footer.saveOrder }}
                                        </button>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Order from '@/utils/order.js'
import Calculation from './Calculation.vue'
import Tracer from '@/utils/tracer.js'
import {ref} from "vue";

export default {
    components: {Calculation},
    data () {
        return {
            isCalculationOpen: false,
            saveOrderDialogParams: null,
            isSending: false,
            isSaving: false,
          warningMinimumDiameter: this.$store.getters.getIniProperties['SETTINGS_WARN_MIN_DIAMETER'],
          settingsShowCalculation: this.$store.getters.getIniProperties['SETTINGS_SHOW_CALCULATION'],
        }
    },
    computed: {
        tracer () {return this.$store.state.moduleTracer},
        evaluator () {return this.$store.state.moduleEvaluator.methods},
        erp () {return this.$store.state.moduleOrders.erp},
        orders () {return this.$store.state.moduleOrders},
        basicData () { return this.orders.basicData},
        calculation () { return this.orders.calculation},
        productCategory () { return this.orders.details.productCategory},
        formAndGeometry () { return this.orders.details.formAndGeometry},
        refraction () { return this.orders.details.refraction},
        product () { return this.orders.details.product},
        form () { return this.orders.details.form},
        shape () { return this.orders.details.shape},
        frame () { return this.orders.details.frame},
        orderBasket () { return this.orders.orderBasket},
        pageName () {
            let isIn = ['overview','orderBasket','basicData']
            .some(x => x.includes(this.$route.name))

            if (isIn)
                this.calculation.isCalculationOpen = false

            return this.$route.name
        },
        arrowStyle () {
            if (this.calculation.isCalculationOpen) return 'arrow-icon rotated'
            return 'arrow-icon'
        },
        calculationStyle() {
            let style = 'calculation'
            if (this.calculation.isCalculationOpen)
                style += ' open'

            return style
        },
        cardStyle() {
            let style = "card footer-card shadow-md bottom"
            if (this.pageName != 'orderBasket')
                style += " py-md-3 py-0"
            if (this.calculation.isCalculationOpen)
                style += " pt-3"
            return style
        },
        isSendOrdersButtonDisabled() {
            if (this.isSending) return true
            if (this.orderBasket.selectedOrderBaskets.length > 0) return false
            return true
        },
        canShowFooter(){
            let show = false
            if (this.pageName != 'overview')
                show = true
            if (this.pageName == 'contact')
                show = false
            if (this.pageName == 'shopConfiguration')
                show = false
            if (this.pageName == 'changePassword')
                show = false
            if (this.pageName == 'privacy')
                show = false
            if (this.pageName == 'imprint')
                show = false
                return show
        },
        isSaveDraftButtonDisabled() {
            return !this.$store.getters.canContinueToDetails || this.isSaving
        },
        isSaveOrderButtonDisabled() {
            return !this.$store.getters.canContinueToDetails || this.isSaving
        },
        showSaveDraft() {
            return !this.$store.getters.isOrderTypeBulkorder
        },
        showCalculation() {
            return !this.$store.getters.isOrderTypeBulkorder
        }
    },
    mounted () {
        this.saveOrderEventListener()
    },
    methods: {
        saveOrderEventListener () {
            document.addEventListener('saveOrderEvent', () => {
                if (!Order.isOrderEvaluated()) return

                this.saveOrderDialogParams = null
                this.saveOrder()
            })
            document.addEventListener('saveDraftEvent', () => {
                if (!Order.areDraftFieldsEvaluated()) return

                this.orders.isDraft = true

                this.saveOrderDialogParams = null
                this.saveOrder()
            })
        },
        onSaveTracerConfiguration() {
            Tracer.saveTracerConfiguration()
        },
        onCalculationToggle () {
            this.calculation.isCalculationOpen = !this.calculation.isCalculationOpen
        },
        onCalculationClose() {
            this.calculation.isCalculationOpen = false
        },
        onCalculationOpen() {
            this.calculation.isCalculationOpen = true
        },
        onSaveDraft(){
            if (!Order.areDraftFieldsEvaluated()) return

            this.saveOrderDialogParams = {
                title: this.$translations.dialog.orderDraftSavedToBasket,
                description: this.$translations.dialog.messages.orderBasketOrNewOrder,
                falseButtonText: this.$translations.dialog.orderBasket,
                trueButtonText: this.$translations.dialog.newOrder,
                allowClosing: true,
                wasOrderSavedFromFooter: true,
            }

            this.orders.isDraft = true

            this.saveOrder()
        },
        onSaveOrder () {
            if (!Order.isOrderEvaluated()) return

            const advance = () => {
                this.saveOrderDialogParams = {
                    title: this.$translations.dialog.orderSavedToBasket,
                    description: this.$translations.dialog.messages.orderBasketOrNewOrder,
                    falseButtonText: this.$translations.dialog.orderBasket,
                    trueButtonText: this.$translations.dialog.newOrder,
                    allowClosing: true,
                    wasOrderSavedFromFooter: true,
                }
                this.saveOrder()
            }

            if (this.$store.getters.isLensDiameterGreaterThanMinimum){
                advance()
                return
            }

            let dialogParams = {
                title: this.$translations.dialog.warning,
                description: this.$translations.dialog.messages.minimumGreaterThanSelectedLensDiameter,
                falseButtonText: this.$translations.dialog.cancel,
                trueButtonText: this.$translations.dialog.continue,
                type: 'warning',
                allowClosing: true,
            }
            this.$store.dispatch('dialog', dialogParams).then(hasConfirmed => {
                if (hasConfirmed) advance()
                return
            })

        },
        onResetOrder () {
            this.$store.dispatch('dialog', {
                title: this.$translations.dialog.warning,
                description: this.$translations.dialog.messages.resetAllFileds,
                falseButtonText: this.$translations.dialog.cancel,
                trueButtonText: this.$translations.dialog.continue,
                type: 'warning',
                allowClosing: true
            }).then(async value => {
                if (!value) return
                
                await Order.reset()

                // Redirect to home route after reset
                this.$router.push('/newOrder')
            })
        },
        onSendOrders () {
            this.sendOrders()
        },
        sendOrders() {
            if (this.isSending) return
            this.isSending = true

            let params = {
                orders: this.orderBasket.selectedOrderBaskets.map(x => x.rawData.OrdNumb)
            }
            
            this.orders.isLoading = true
            this.$store.dispatch('sendOrders', params).then((response) => {
                this.orders.isLoading = false
                this.isSending = false;
                this.orderBasket.selectedOrderBaskets = []

                this.$store.dispatch('notify', {
                    message: this.$translations.notifications.orderSent,
                    duration: 5,
                    type: 'success'
                })
                document.dispatchEvent(new Event('orderSentEvent'))
            }, e => {
                this.orders.isLoading = false
                this.isSending = false
                this.$store.dispatch('notify', {
                    message: e.data.message,
                })
                console.error(e.data)
            });
        },
        async saveOrder(){
          if(this.warningMinimumDiameter === 'YES') {
            const dia = await this.checkDiameter();
            if(!dia && dia !== undefined) return
          }

            if (this.isSaving) return
            this.isSaving = true

            Order.save(
                response => {
                    this.$store.dispatch('notify', {
                        message: this.$translations.notifications.orderSavedToOrderBasket,
                        duration: 5,
                        type: 'success'
                    })
                    this.isSaving = false

                    if (!this.saveOrderDialogParams) return
                    this.$store.dispatch('dialog', this.saveOrderDialogParams).then(value => {
                        if (value === undefined) return
                        // Reset order
                        Order.reset()

                        // Go to home route
                        if (value){
                            this.$router.push('/newOrder')
                            return
                        }
                        // Go to order basket
                        this.$router.push('orderBasket')
                    })
                    this.saveOrderDialogParams = null
                },
                error => {
                    this.saveOrderDialogParams = null
                    this.isSaving = false
                }
            )
        },
      checkDiameter() {
        if(this.orders.basicData.selectedOrderType.id === 'W')
          return true
        const l = Number(this.formAndGeometry.left.lensDiameter)
        const r = Number(this.formAndGeometry.right.lensDiameter)
        let minimumR = true
        let minimumL = true
        if((l !== Number(this.formAndGeometry.left.lensDiameterValues[0]) || this.formAndGeometry.left.isOptimized) && l > 0)
          minimumL = false
        if((r !== Number(this.formAndGeometry.right.lensDiameterValues[0]) || this.formAndGeometry.right.isOptimized) && r > 0)
          minimumR = false
        if(minimumR && minimumL) {
          const minimumDialogParams = {
            title: this.$translations.dialog.messages.MinDiameter,
            description: this.$translations.dialog.messages.MinDiameterSelected,
            falseButtonText: this.$translations.dialog.no,
            trueButtonText: this.$translations.dialog.yes,
            allowClosing: false,
            wasOrderSavedFromFooter: true,
          }
          return this.$store.dispatch('dialog', minimumDialogParams).then(value => {
            return !(value === undefined || !value);
          })
        }
      }
    }
}
</script>

<style scoped lang="scss">
.footer {
    z-index: 9000 !important;
    margin-bottom: 25px;
}
.app-layout {
    margin-left: -3%;
    margin-right: -3%;
}
.arrow-icon {
    opacity: 1;
    width: 30px;
    height: 30px;
}
.btn-group{
    position: absolute;
    display: contents;
}
.calculation{
    // overflow: auto;
    height: 0px;
    transition: .3s;

    &.open {
        height: 55vh;
        overflow: auto;
        overflow-x: hidden;

        @media only screen and (max-height: 800px) {
            height: 75vh;
        }

        @media only screen and (max-height: 650px) {
            height: 65vh;
        }
    }
}
</style>

