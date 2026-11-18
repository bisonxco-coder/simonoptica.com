<template>
    <!-- Main section -->
    <div class="card pt-3 pb-4 px-5 mb-3" id="order_type">
        <h4 class="panel-title pt-4 pb-3"><p v-html="$translations.newOrder.basicData.orderType.title"></p></h4>

        <div class="row">
            <div :class="orderTypeSelectionStyle()"
                v-for="orderType in basicData.orderTypeSelection" :value="orderType" :key="orderType.id"
            >
                <input
                    v-if="orderType.show === 'YES'"
                    v-model="selectedOrderType"
                    @change="onSelectedOrderTypeChange"
                    :value="orderType"
                    :id="orderType.id"
                    type="radio"
                    class="btn-check"
                    autocomplete="off"
                />
                <label v-if="orderType.show === 'YES'" :class="orderTypeGridButtonStyle()" :for="orderType.id">
                    <p v-html="orderType.title"></p>
                </label>
            </div>
        </div>

        <h4 class="panel-title pt-4 pb-2">{{$translations.newOrder.basicData.orderType.reference}}</h4>
        <panel-input
            v-model="basicData.referenceNumber"
            :placeholder="$translations.newOrder.basicData.orderType.placeholders.referenceNumber"
        />
      <panel-input
          v-if="showEntryUser === 'YES'"
          v-model="basicData.entryUser"
          :placeholder="$translations.newOrder.basicData.orderType.placeholders.entryUser"
      />
      <panel-select
          v-if="isManager && showShopConfiguration === 'YES'"
          :placeholder="$translations.newOrder.basicData.orderType.placeholders.customer"
          v-model="basicData.custNumbX"
          :selection="shopConfiguration.userTableData.rows"
          titleParameter="text"
          idParameter="id"
      />
        <panel-select
            v-if="hasAdditionalRemarkSelection"
            :placeholder="$translations.newOrder.basicData.orderType.placeholders.additionalRemark"
            v-model="basicData.selectedAdditionalRemark"
            :selection="basicData.additionalRemarkSelection"
            titleParameter="description"
        />
        <panel-input
            v-model="basicData.remark"
            type="textarea"
            :placeholder="$translations.newOrder.basicData.orderType.placeholders.remark"
        />

        <div class="d-flex justify-content-sm-between justify-content-center flex-wrap mt-5">
            <button v-if="showEndCustInfo === 'YES'"
                class="me-3 d-flex expand-button border-0 my-3"
                @click="onConsumerInfoExpand"
            >
                <span :class="'no-hover px-3 ' + consumerInfoToggleIconStyle"></span>
                {{$translations.newOrder.basicData.consumerInfo.title}}
            </button>
            <button
                class="btn btn-secondary panel-button blue d-md-inline-block d-block border-0 px-5 mx-2 my-3"
                @click="onContinue"
                :disabled="!$store.getters.canContinueToDetails"
            >
                {{$translations.newOrder.basicData.orderType.continueToDetails}}
            </button>
        </div>
    </div>


    <!-- Consumer info -->
    <div v-if="consumerInfo.isExpanded" class="card pt-3 pb-4 px-5 mb-3" id="consumer_info">
        <h4 class="panel-title pt-4 pb-3">{{$translations.newOrder.basicData.consumerInfo.title}}</h4>

        <panel-select
            :placeholder="$translations.newOrder.basicData.consumerInfo.placeholders.title"
            v-model="consumerInfo.selectedTitle"
            :selection="consumerInfo.titleSelection"
            titleParameter="description"
            idParameter="control"
        />
        <panel-input
            :placeholder="$translations.newOrder.basicData.consumerInfo.placeholders.firstName"
            v-model="consumerInfo.firstName"
        />
        <panel-input
            :placeholder="$translations.newOrder.basicData.consumerInfo.placeholders.name"
            v-model="consumerInfo.name"
        />
        <panel-input
            :placeholder="$translations.newOrder.basicData.consumerInfo.placeholders.dateOfBirth"
            v-model="consumerInfo.dateOfBirth"
            icon="calendar-icon"
            type="date"
        />
        <panel-input
            :placeholder="$translations.newOrder.basicData.consumerInfo.placeholders.phone"
            v-model="consumerInfo.phone"
        />
        <panel-select
            :placeholder="$translations.newOrder.basicData.consumerInfo.placeholders.language"
            v-model="consumerInfo.selectedLanguage"
            :selection="consumerInfo.languageSelection"
            titleParameter="description"
            idParameter="control"
        />
        <panel-input
            :placeholder="$translations.newOrder.basicData.consumerInfo.placeholders.street"
            v-model="consumerInfo.street"
        />
        <panel-input
            :placeholder="$translations.newOrder.basicData.consumerInfo.placeholders.zipCode"
            v-model="consumerInfo.zipCode"
        />
        <panel-input
            :placeholder="$translations.newOrder.basicData.consumerInfo.placeholders.city"
            v-model="consumerInfo.city"
        />
    </div>

</template>

<script>
import PanelInput from '@/components/PanelInput.vue'
import PanelSelect from '@/components/PanelSelect.vue'
import Initializator from '@/utils/initializator.js'
import Order from '@/utils/order.js'
import store from "../../../store";
import ShopConfiguration from '@/utils/shopConfiguration'

export default {
  components: { PanelInput, PanelSelect },
  data() {
    return {
      selectedOrderType: null,
      hasChanges: false,
      showEndCustInfo: store.getters.getIniProperties['SETTINGS_SHOW_END_CUSTOMER_INFO'],
      showEntryUser: store.getters.getIniProperties['LENS_ORDER_CONFIGURATION_ENTRYUSER'],
      showShopConfiguration: store.getters.getIniProperties['SETTINGS_SHOW_SHOP_CONFIG'],
      isManager : store.getters.isManager
    }
  },
    computed: {
        orders() {return this.$store.state.moduleOrders},
        basicData() {return this.$store.state.moduleOrders.basicData},
        consumerInfo() {return this.$store.state.moduleOrders.consumerInfo},
        consumerInfoToggleIconStyle () {
            if (this.consumerInfo.isExpanded) return 'minimize-icon'
            return 'add-icon'
        },
        hasAdditionalRemarkSelection() {
            return (
                this.basicData.additionalRemarkSelection &&
                this.basicData.additionalRemarkSelection !== undefined &&
                Object.entries(this.basicData.additionalRemarkSelection).length > 1
            )
        },
      shopConfiguration() {return this.$store.state.moduleOrders.shopConfiguration},
    },
    mounted () {
        this.selectedOrderType = this.basicData.selectedOrderType
        this.setDashboardTitle()
      if(this.showShopConfiguration === 'YES' && this.isManager)
        this.getUsers()
    },
    methods: {
        onSelectedOrderTypeChange() {
            let newVal = this.selectedOrderType

            // Quickly set local value back to the original radio option,
            // to make it look as if the same option is still selected
            this.selectedOrderType = this.basicData.selectedOrderType

            // Skip showing confirmation dialog, if the newly selected order type belongs to the same group as the previous one
            let orderTypeGroupIds = [
                [
                    this.$store.getters.getStandardLensOrderType.id,
                    this.$store.getters.getRemoteEdgingOrderType.id,
                    this.$store.getters.getFrameToComeOrderType.id
                ],
                [this.$store.getters.getSpectaclesOrderType.id],
                [this.$store.getters.getBulkorderOrderType.id]
            ]
            let isOrderTypeGroupped = this.isOrderTypeGroupped(newVal, orderTypeGroupIds)

            // Skip showing confirmation dialog, if no order data changed
            let excludeChecking = ['selectedOrderType']
            let hasOrderBeenEdited = Initializator.hasOrderBeenEdited(excludeChecking)

            if (!hasOrderBeenEdited || isOrderTypeGroupped){
                this.basicData.selectedOrderType = newVal
                this.selectedOrderType = newVal
                this.setDashboardTitle()
                return
            }

            // Create a confirmation dialog window when the user wants to change the order type
            let dialogParams = {
                title: this.$translations.dialog.warning,
                description: this.$translations.dialog.messages.orderChangeConfirmation,
                falseButtonText: this.$translations.dialog.cancel,
                trueButtonText: this.$translations.dialog.continue,
                type: 'warning',
                allowClosing: true,
            }
            this.$store.dispatch('dialog', dialogParams).then(hasConfirmed => {
                if (hasConfirmed) {
                    this.basicData.selectedOrderType = newVal
                    this.selectedOrderType = newVal
                    this.setDashboardTitle()
                    Order.reset(['basicData', 'consumerInfo', 'emptyOrderCopy'], {
                        setInitialSelectedOrderType: false,
                        makeEmptyOrderCopy: false
                    })
                    return
                }
            })
        },
        onConsumerInfoExpand() {
            this.consumerInfo.isExpanded = !this.consumerInfo.isExpanded
        },
        onContinue() {
          const el = document.getElementById('dashboard-title').offsetTop
          let top = 0;
          if(el) {
            top = el
          }
          window.scrollTo({ top: top, left:0, behavior: "instant"})
            this.$router.push('details')
        },
        setDashboardTitle() {
            this.$store.commit('setDashboardTitle', {
                title: () => this.$translations.newOrder.title,
                subTitle: () => this.$store.getters.getOrderTypeTitle
            })
        },
        isOrderTypeGroupped(orderType, orderTypeGroupIds) {
            try {
                return orderTypeGroupIds.some(orderTypeGroup =>
                    orderTypeGroup.some(orderTypeId => orderTypeId == this.selectedOrderType.id) &&
                    orderTypeGroup.some(orderTypeId => orderTypeId == orderType.id))
            } catch (error) {
                console.error(error)
            }
            return false
        },
        orderTypeSelectionStyle() {
            return "col-xl col-lg-4 col-sm-4 col-xs-6 col mb-4"
        },
        orderTypeGridButtonStyle() {
            return "btn btn-outline-primary grid-button order-type-button-style border border-1 border-radius-xl p-3"
        },
      async getUsers() {
        this.isLoading = true
        await ShopConfiguration.getUsers()
        this.isLoading = false
      }
    }
}
</script>
<style>
.order-type-button-style{
    height: 80px;
    width: 16%;
    min-width: 8em;

    font-weight: 500;
    font-size: 16px;
}
</style>
