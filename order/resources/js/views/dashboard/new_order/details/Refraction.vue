<template>
    <div :class="'card pt-3 pb-4 px-5 mb-3 ' + (isComponentEnabled ? '' : 'component-disabled')" id="refraction">
        <h4 class="panel-title pt-4 pb-4">{{$translations.newOrder.details.refraction.title}}</h4>

        <div class="form-switch d-flex flex-column flex-sm-row mb-4 pb-3 justify-content-between ">
            <input
                id="refraction_isSameLeftRight"
                v-model="isSameLeftRight"
                @change="onSameLeftRightChange"
                class="form-check-input panel-switch position-absolute"
                type="checkbox"
                :disabled="!isComponentEnabled"
            />
            <label class="form-check-label ms-4 ps-2" for="refraction_isSameLeftRight">{{$translations.common.sameLeftAndRight}}</label>

            <div class="me-5 d-flex mt-sm-0 mt-4">
                <button class="d-flex expand-button border-0" @click="onPrismViewToggle" :disabled="!isComponentEnabled">
                    <span :class="'no-hover px-3 ' + toggleIconStyle"></span>
                    {{$translations.newOrder.details.refraction.prism}}
                </button>
            </div>
        </div>

        <div class="row">
            <!-- RIGHT LENS REFRACTION-->
            <div class="col-md-6 col-12 mb-md-0 mb-sm-5 ">
                <h4 class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.rightLens}}</h4>
                <panel-input
                    :placeholder="$translations.newOrder.details.refraction.placeholders.sphere"
                    v-model="refraction.right.sphere"
                    type="number"
                    :min="refraction.right.sphereMin"
                    :max="refraction.right.sphereMax"
                    :step="refraction.right.sphereStep"
                    :decimalSnap="3"
                    :decimals="3"
                    :preserveOnDisable="!refractionProperties.right.sphere.resetIfDisabled"
                    :disabled="refractionProperties.right.sphere.disabled"
                    :excludeZeroDisabling="true"
                    v-model:errorMessage="refractionProperties.right.sphere.errorMessage"
                />
                <!-- Normally the min for cylinder is 0 (zero) but because displaying negative
                values must be allow, invert the max and later inside
                ordersEvaluator.js-> cylinderSpherePowerEvaluation() evaluate it and also apply
                cylinder power -->
                <panel-input
                   :placeholder="$translations.newOrder.details.refraction.placeholders.cylinder"
                    v-model="refraction.right.cylinder"
                    type="number"
                   name="cyl"
                    :min="-refraction.right.cylinderMin"
                    :max="refraction.right.cylinderMax"
                    :step="refraction.right.cylinderStep"
                    :decimalSnap="3"
                    :decimals="3"
                    :preserveOnDisable="!refractionProperties.right.cylinder.resetIfDisabled"
                    :disabled="refractionProperties.right.cylinder.disabled"
                    v-model:errorMessage="refractionProperties.right.cylinder.errorMessage"
                />
                <panel-input
                    :placeholder="$translations.newOrder.details.refraction.placeholders.axis"
                    v-model="refraction.right.axis"
                    type="number"
                    :min="refraction.left.axisMin"
                    :max="refraction.right.axisMax"
                    :step="1"
                    :preserveOnDisable="!refractionProperties.right.axis.resetIfDisabled"
                    :disabled="refractionProperties.right.axis.disabled"
                    v-model:errorMessage="refractionProperties.right.axis.errorMessage"
                />
                <panel-input
                    :placeholder="(refraction.right.addDeg > 1) ?
                                    $translations.newOrder.details.refraction.placeholders.degression :
                                    $translations.newOrder.details.refraction.placeholders.addition"
                    v-model="refraction.right.addition"
                    type="number"
                    :min="refraction.right.additionMin"
                    :max="refraction.right.additionMax"
                    :step="refraction.right.additionStep"
                    :decimalSnap="3"
                    :decimals="3"
                    :preserveOnDisable="!refractionProperties.right.addition.resetIfDisabled"
                    :disabled="refractionProperties.right.addition.disabled"
                    v-model:errorMessage="refractionProperties.right.addition.errorMessage"
                />

                <!-- Prism -->
                <div v-if="refraction.isPrismExpanded">
                    <panel-input
                        :placeholder="$translations.newOrder.details.refraction.placeholders.prism1"
                        v-model="refraction.right.prism1"
                        type="number"
                        :min="refraction.right.prismMin"
                        :max="refraction.right.prismMax"
                        :step="0.01"
                        :preserveOnDisable="!refractionProperties.right.prism1.resetIfDisabled"
                        :disabled="refractionProperties.right.prism1.disabled"
                        v-model:errorMessage="refractionProperties.right.prism1.errorMessage"
                    />
                    <panel-input
                        :placeholder="$translations.newOrder.details.refraction.placeholders.base1"
                        v-model="refraction.right.base1"
                        type="number"
                        :step="1"
                        :disabled="!product.right.hasLens || !isComponentEnabled"
                        :list="refraction.right.base1Selection"
                        listLabel="label"
                        listValue="value"
                    />
                    <panel-input
                        :placeholder="$translations.newOrder.details.refraction.placeholders.prism2"
                        v-model="refraction.right.prism2"
                        type="number"
                        :min="refraction.right.prismMin"
                        :max="refraction.right.prismMax"
                        :step="0.01"
                        :preserveOnDisable="!refractionProperties.right.prism2.resetIfDisabled"
                        :disabled="refractionProperties.right.prism2.disabled"
                        v-model:errorMessage="refractionProperties.right.prism2.errorMessage"
                    />
                    <panel-input
                        :placeholder="$translations.newOrder.details.refraction.placeholders.base2"
                        v-model="refraction.right.base2"
                        type="number"
                        :step="1"
                        :disabled="!product.right.hasLens || !isComponentEnabled"
                        :list="refraction.right.base2Selection"
                        listLabel="label"
                        listValue="value"
                    />
                </div>
            </div>

            <!-- LEFT LENS REFRACTION-->
            <div class="col-md-6 col-12 mb-3">
                <h4 class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.leftLens}}</h4>
                <panel-input
                    :placeholder="$translations.newOrder.details.refraction.placeholders.sphere"
                    v-model="refraction.left.sphere"
                    type="number"
                    :min="refraction.left.sphereMin"
                    :max="refraction.left.sphereMax"
                    :step="refraction.left.sphereStep"
                    :decimalSnap="3"
                    :decimals="3"
                    :isImmutable="refraction.isSameLeftRight"
                    :preserveOnDisable="!refractionProperties.left.sphere.resetIfDisabled"
                    :disabled="refractionProperties.left.sphere.disabled"
                    :excludeZeroDisabling="true"
                    v-model:errorMessage="refractionProperties.left.sphere.errorMessage"
                />
                <!-- Normally the min for cylinder is 0 (zero) but because displaying negative
                values must be allow, invert the max and later inside
                ordersEvaluator.js-> cylinderSpherePowerEvaluation() evaluate it and also apply
                cylinder power -->
                <panel-input
                    :placeholder="$translations.newOrder.details.refraction.placeholders.cylinder"
                    v-model="refraction.left.cylinder"
                    type="number"
                    :min="-refraction.left.cylinderMin"
                    :max="refraction.left.cylinderMax"
                    :step="refraction.left.cylinderStep"
                    name="cyl"
                    :decimalSnap="3"
                    :decimals="3"
                    :isImmutable="refraction.isSameLeftRight"
                    :preserveOnDisable="!refractionProperties.left.cylinder.resetIfDisabled"
                    :disabled="refractionProperties.left.cylinder.disabled"
                    v-model:errorMessage="refractionProperties.left.cylinder.errorMessage"
                />
                <panel-input
                    :placeholder="$translations.newOrder.details.refraction.placeholders.axis"
                    v-model="refraction.left.axis"
                    type="number"
                    :min="refraction.left.axisMin"
                    :max="refraction.left.axisMax"
                    :step="1"
                    :isImmutable="refraction.isSameLeftRight"
                    :preserveOnDisable="!refractionProperties.left.axis.resetIfDisabled"
                    :disabled="refractionProperties.left.axis.disabled"
                    v-model:errorMessage="refractionProperties.left.axis.errorMessage"
                />
                <panel-input
                  :placeholder="(refraction.left.addDeg > 1) ?
                                    $translations.newOrder.details.refraction.placeholders.degression :
                                    $translations.newOrder.details.refraction.placeholders.addition"
                    v-model="refraction.left.addition"
                    type="number"
                    :min="refraction.left.additionMin"
                    :max="refraction.left.additionMax"
                    :step="refraction.left.additionStep"
                    :decimalSnap="3"
                    :decimals="3"
                    :isImmutable="refraction.isSameLeftRight"
                    :preserveOnDisable="!refractionProperties.left.addition.resetIfDisabled"
                    :disabled="refractionProperties.left.addition.disabled"
                    v-model:errorMessage="refractionProperties.left.addition.errorMessage"
                />

                <!-- Prism -->
                <div v-if="refraction.isPrismExpanded">
                    <panel-input
                        :placeholder="$translations.newOrder.details.refraction.placeholders.prism1"
                        v-model="refraction.left.prism1"
                        type="number"
                        :min="refraction.left.prismMin"
                        :max="refraction.left.prismMax"
                        :step="0.01"
                        :preserveOnDisable="!refractionProperties.left.prism1.resetIfDisabled"
                        :disabled="refractionProperties.left.prism1.disabled"
                        v-model:errorMessage="refractionProperties.left.prism1.errorMessage"
                    />
                    <panel-input
                        :placeholder="$translations.newOrder.details.refraction.placeholders.base1"
                        v-model="refraction.left.base1"
                        type="number"
                        :step="1"
                        :disabled="!product.left.hasLens || !isComponentEnabled"
                        :list="refraction.left.base1Selection"
                        listLabel="label"
                        listValue="value"
                    />
                    <panel-input
                        :placeholder="$translations.newOrder.details.refraction.placeholders.prism2"
                        v-model="refraction.left.prism2"
                        type="number"
                        :min="refraction.left.prismMin"
                        :max="refraction.left.prismMax"
                        :step="0.01"
                        :preserveOnDisable="!refractionProperties.left.prism2.resetIfDisabled"
                        :disabled="refractionProperties.left.prism2.disabled"
                        v-model:errorMessage="refractionProperties.left.prism2.errorMessage"
                    />
                    <panel-input
                        :placeholder="$translations.newOrder.details.refraction.placeholders.base2"
                        v-model="refraction.left.base2"
                        type="number"
                        :step="1"
                        :disabled="!product.left.hasLens || !isComponentEnabled"
                        :list="refraction.left.base2Selection"
                        listLabel="label"
                        listValue="value"
                    />
                </div>
            </div>

            <!-- RIGHT LENS FITTING-->
            <div class="col-md-6 col-12 mb-md-0 mb-sm-5 ">
                <panel-input
                    class="mt-5"
                    :placeholder="$translations.newOrder.details.refraction.placeholders.pd"
                    v-model="refraction.right.pd"
                    type="number"
                    :min="refraction.right.pdMin"
                    :max="refraction.right.pdMax"
                    :step="0.1"
                    :decimals="1"
                    :preserveOnDisable="!refractionProperties.right.pd.resetIfDisabled"
                    :disabled="refractionProperties.right.pd.disabled"
                    v-model:errorMessage="refractionProperties.right.pd.errorMessage"
                />
                <panel-input
                    :placeholder="$translations.newOrder.details.refraction.placeholders.height"
                    v-model="refraction.right.height"
                    type="number"
                    :min="refraction.right.heightMin"
                    :max="refraction.right.heightMax"
                    :step="0.1"
                    :decimals="1"
                    :preserveOnDisable="!refractionProperties.right.height.resetIfDisabled"
                    :disabled="refractionProperties.right.height.disabled"
                    v-model:errorMessage="refractionProperties.right.height.errorMessage"
                />
            </div>

            <!-- LEFT LENS FITTING-->
            <div class="col-md-6 col-12 mb-3">
                <panel-input
                    class="mt-5"
                    :placeholder="$translations.newOrder.details.refraction.placeholders.pd"
                    v-model="refraction.left.pd"
                    type="number"
                    :min="refraction.left.pdMin"
                    :max="refraction.left.pdMax"
                    :step="0.1"
                    :decimals="1"
                    :isImmutable="refraction.isSameLeftRight"
                    :preserveOnDisable="!refractionProperties.left.pd.resetIfDisabled"
                    :disabled="refractionProperties.left.pd.disabled"
                    v-model:errorMessage="refractionProperties.left.pd.errorMessage"
                />
                <panel-input
                    :placeholder="$translations.newOrder.details.refraction.placeholders.height"
                    v-model="refraction.left.height"
                    type="number"
                    :min="refraction.left.heightMin"
                    :max="refraction.left.heightMax"
                    :step="0.1"
                    :decimals="1"
                    :isImmutable="refraction.isSameLeftRight"
                    :preserveOnDisable="!refractionProperties.left.height.resetIfDisabled"
                    :disabled="refractionProperties.left.height.disabled"
                    v-model:errorMessage="refractionProperties.left.height.errorMessage"
                />
            </div>
        </div>
    </div>
</template>

<script>
import PanelInput from '@/components/PanelInput.vue'
import PanelSelect from '@/components/PanelSelect.vue'
import Helpers from '@/utils/helpers.js'

export default {
    data() {
        return{
            isPrismExpanded: false,
            isSameLeftRight: false,
        }
    },
    components: {
        PanelInput, PanelSelect
    },
    computed: {
        isComponentEnabled() {return this.$store.state.moduleOrders.details.isComponentEnabled('refraction')},
        refraction() {return this.$store.state.moduleOrders.details.refraction},
        refractionProperties() {return this.$store.state.moduleEvaluator.details.refraction},
        product() {return this.$store.state.moduleOrders.details.product},
        toggleIconStyle () {
            if (this.refraction.isPrismExpanded) return 'minimize-icon'
            return 'add-icon'
        },
    },
    watch: {
        'product.left.selectedLensType': function(val) {
            this.setSameLeftAndRight()
        },
        'product.right.selectedLensType': function(val) {
            this.setSameLeftAndRight()
        },
        'product.left.hasLens': function(val) {
            this.setSameLeftAndRight()
        },
        'product.right.hasLens': function(val) {
            this.setSameLeftAndRight()
        },
        'refraction.isSameLeftRight': function(val) {
            this.isSameLeftRight = val
            this.setSameLeftAndRight()
        },
        'refraction.right': {
            handler() { this.setSameLeftAndRight() },
            deep: true
        },
    },
    mounted () {
        this.isSameLeftRight = this.refraction.isSameLeftRight
    },
    methods: {
        onPrismViewToggle() {
            this.refraction.isPrismExpanded = !this.refraction.isPrismExpanded
        },
        onSameLeftRightChange() {
            let newVal = !this.refraction.isSameLeftRight
            if (!newVal) {
                this.refraction.isSameLeftRight = false
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
                    this.refraction.isSameLeftRight = false
                    this.isSameLeftRight = false
                    return
                }
                this.refraction.isSameLeftRight = newVal
                this.isSameLeftRight = newVal
            })
        },
        setSameLeftAndRight() {
            if (!this.refraction.isSameLeftRight || !this.product.right.hasLens || !this.product.left.hasLens) return

            let only = [
                'sphere', 'cylinder', 'axis', 'addition', 'pd', 'height'
            ]
            let result = Helpers.copyDataFromRightToLeftSide(this.refraction, only)
            if (result !== true) {
                console.error(result)
                return
            }
        }
    }
}
</script>
