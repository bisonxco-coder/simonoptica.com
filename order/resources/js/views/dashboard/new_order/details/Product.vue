<template>
  <div :class="'card pt-3 pb-4 px-5 mb-3 ' + (isComponentEnabled ? '' : 'component-disabled')" id="product">
    <h4 class="panel-title pt-4 pb-3">{{$translations.newOrder.details.product.title}}</h4>
    <div class="form-switch d-flex mb-3 pb-3">
      <input
        id="product_isSameLeftRight"
        v-model="isSameLeftRight"
        @change="onSameLeftRightChange"
        class="form-check-input panel-switch position-absolute"
        type="checkbox"
        :disabled="!isComponentEnabled"
      />
      <label class="form-check-label ms-4 ps-2" for="product_isSameLeftRight">{{$translations.common.sameLeftAndRight}}</label>
    </div>

    <div class="row">
      <!-- RIGHT LENS -->
      <div class="col-md-6 col-12 mb-md-0 mb-4">
        <div class="d-flex mb-4">
          <input
            id="product_right_hasLens"
            v-model="product.right.hasLens"
            class="form-check-input panel-checkbox"
            type="checkbox"
            :disabled="!product.left.hasLens || !isComponentEnabled"
          />
          <label class="form-check-label ms-4 px-3 position-absolute" for="product_right_hasLens">
            {{$translations.common.rightLens}}
          </label>
        </div>
        <div class="d-flex mb-4">
          <input
            id="product_right_isStock"
            v-model="product.right.isStock"
            class="form-check-input panel-checkbox"
            type="checkbox"
            :disabled="!isComponentEnabled || !product.right.hasLens"
          />
          <label class="form-check-label ms-4 px-3 position-absolute" for="product_right_isStock">
            {{$translations.newOrder.details.product.stock}}
          </label>
        </div>
        <panel-select
          :placeholder="$translations.newOrder.details.product.placeholders.lensType"
          :selection="product.right.lensTypeSelection"
          v-model="product.right.selectedLensType"
          titleParameter='LENSDESCRIPTION'
          :preserveOnDisable="!productProperties.right.selectedLensType.resetIfDisabled"
          :disabled="productProperties.right.selectedLensType.disabled"
          v-model:errorMessage="productProperties.right.selectedLensType.errorMessage"
        />
        <panel-select-multi
          v-if="showOtherField === 'YES'"
          :placeholder="$translations.newOrder.details.product.placeholders.other"
          :selection="product.right.otherSelection"
          v-model="product.right.selectedOtherList"
          titleParameter='DESCR'
          :disabled="!product.right.hasLens || !isComponentEnabled"
        />
      </div>

      <!-- LEFT LENS -->
      <div class="col-md-6 col-12 mb-md-0 mb-4">
        <div class="d-flex mb-4">
          <input
            id="product_left_hasLens"
            v-model="product.left.hasLens"
            class="form-check-input panel-checkbox"
            type="checkbox"
            :disabled="!product.right.hasLens || !isComponentEnabled"
          />
          <label class="form-check-label ms-4 px-3 position-absolute" for="product_left_hasLens">
            {{$translations.common.leftLens}}
          </label>
        </div>
        <div class="d-flex mb-4">
          <input
            id="product_left_isStock"
            v-model="product.left.isStock"
            class="form-check-input panel-checkbox"
            type="checkbox"
            :disabled="!isComponentEnabled || !product.left.hasLens || product.isSameLeftRight"
          />
          <label class="form-check-label ms-4 px-3 position-absolute" for="product_left_isStock">
            {{$translations.newOrder.details.product.stock}}
          </label>
        </div>
        <panel-select
          :placeholder="$translations.newOrder.details.product.placeholders.lensType"
          :selection="product.left.lensTypeSelection"
          v-model="product.left.selectedLensType"
          titleParameter='LENSDESCRIPTION'
          :isImmutable="product.isSameLeftRight"
          :preserveOnDisable="!productProperties.left.selectedLensType.resetIfDisabled"
          :disabled="productProperties.left.selectedLensType.disabled"
          v-model:errorMessage="productProperties.left.selectedLensType.errorMessage"
        />
        <panel-select-multi
          v-if="showOtherField === 'YES'"
          :placeholder="$translations.newOrder.details.product.placeholders.other"
          :selection="product.left.otherSelection"
          v-model="product.left.selectedOtherList"
          titleParameter='DESCR'
          :disabled="!product.left.hasLens || !isComponentEnabled"
        />
      </div>
    </div>

    <hr class="mb-4 mt-1">
    <div class="row">
      <div class="col-md-6 col-12">
        <panel-select
          :placeholder="$translations.newOrder.details.product.placeholders.tint"
          :selection="product.tintSelection"
          :notNull="product.tintMandatory"
          v-model="product.selectedTint"
          titleParameter='DESCR'
          :disabled="!isComponentEnabled"
        />
        <panel-select
          :placeholder="$translations.newOrder.details.product.placeholders.antireflectiveCoating"
          :selection="product.antireflectiveCoatingSelection"
          :notNull="(coatInitialEmpty === 'YES') ? false : product.antireflectiveCoatingMandatory"
          v-model="product.selectedAntireflectiveCoating"
          titleParameter='DESCR'
          :disabled="!isComponentEnabled"
        />
      </div>
      <div class="col-md-6 col-12">
        <panel-input
          :placeholder="$translations.newOrder.details.product.placeholders.tintRemark"
          v-model="product.tintRemark"
          :disabled="product.isTintRemarkDisabled  || !isComponentEnabled"
        />
        <div style="height: 0.4rem" />
        <panel-select v-if="showUv === 'YES'"
            :placeholder="$translations.newOrder.details.product.placeholders.uv"
            :selection="product.uvSelection"
            :notNull="false"
            v-model="product.selectedUv"
            titleParameter='DESCR'
            :disabled="!isComponentEnabled"
        />
      </div>
    </div>
  </div>
</template>

<script>
import PanelSelect from '@/components/PanelSelect.vue'
import PanelSelectMulti from '@/components/PanelSelectMulti.vue'
import PanelInput from '@/components/PanelInput.vue'
import Helpers from '@/utils/helpers.js'

export default {
  components: {
    PanelSelect, PanelSelectMulti, PanelInput,
  },
  data() {
    return {
      isSameLeftRight: false,
      showOtherField: this.$store.getters.getIniProperties['SETTINGS_SHOW_OTHER_FIELD'],
      coatInitialEmpty: this.$store.getters.getIniProperties['SETTINGS_COAT_INITIAL_EMPTY'],
      showUv: this.$store.getters.getIniProperties['SETTINGS_SHOW_UV_LIST'],
    }
  },
  computed: {
    isComponentEnabled() {return this.$store.state.moduleOrders.details.isComponentEnabled('product')},
    product() {return this.$store.state.moduleOrders.details.product},
    productProperties() {return this.$store.state.moduleEvaluator.details.product}
  },
  watch: {
    'product.left.hasLens': function(val) {
      this.setSameLeftAndRight()
    },
    'product.right.hasLens': function(val) {
      this.setSameLeftAndRight()
    },
    'product.isSameLeftRight': function(val) {
      this.isSameLeftRight = val
      this.setSameLeftAndRight()
    },
    'product.right': {
      handler() { this.setSameLeftAndRight() },
      deep: true
    },
  },
  mounted() {
    this.isSameLeftRight = this.product.isSameLeftRight
  },
  methods: {
    onSameLeftRightChange() {
      let newVal = !this.product.isSameLeftRight
      if (!newVal) {
        this.product.isSameLeftRight = false
        this.isSameLeftRight = false
        return
      }
      let dialogParams = {
        title: this.$translations.dialog.warning,
        description: this.$translations.dialog.messages.overwriteLeftLens,
        falseButtonText: this.$translations.dialog.cancel,
        trueButtonText: this.$translations.dialog.continue,
        type: 'warning',
        allowClosing: true
      }
      this.$store.dispatch('dialog', dialogParams).then(hasConfirmed => {
        if (!hasConfirmed) {
          this.product.isSameLeftRight = false
          this.isSameLeftRight = false
          return
        }
        this.product.isSameLeftRight = newVal
        this.isSameLeftRight = newVal
      })
    },
    setSameLeftAndRight() {
      if (!this.product.isSameLeftRight || !this.product.right.hasLens || !this.product.left.hasLens) return

      let only = ['selectedLensType', 'isStock', 'selectedOtherList']
      let result = Helpers.copyDataFromRightToLeftSide(this.product, only)
      if (result !== true) {
        console.error(result)
      }
    }
  }
}
</script>
