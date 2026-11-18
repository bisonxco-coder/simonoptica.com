<template></template>

<script>
import Helpers from '@/utils/helpers.js'
import Initializator from '@/utils/initializator.js'

export default {
  async mounted () {
    await Initializator.getProductCategoryInitial()
    Initializator.copyOrderState()
    Initializator.setInitialSelectedOrderType()
    Initializator.setInitialThicknessOption()
    Initializator.setInitialFrameAndBevel()
    await Initializator.getRemarkKeyData()
    //await Initializator.getIni()
    //await Initializator.assignIniValues()
    await Initializator.makeEmptyOrderCopy()
    await Initializator.getSalesFramesSupplierList()
    await Initializator.getLensFilter()
    await Initializator.getLensTypesOnly()
    this.startDelay()
    this.registerEventListeners()
    await Initializator.getDefaultMinThk()
    },
    computed: {
        evaluator () {return this.$store.state.moduleEvaluator},
        settings () {return this.$store.state.moduleSettings},
        orders () {return this.$store.state.moduleOrders},
        initializator () {return this.$store.state.moduleOrders.initializator},
        erp () {return this.$store.state.moduleOrders.erp},
        //ini () {return this.$store.state.moduleOrders.erp.getIni},
        basicData () { return this.orders.basicData},
        productCategory () { return this.orders.details.productCategory},
        formAndGeometry () { return this.orders.details.formAndGeometry},
        refraction () { return this.orders.details.refraction},
        refractionProperties () { return this.$store.state.moduleEvaluator.details.refraction},
        product () { return this.orders.details.product},
        form () { return this.orders.details.form},
        shape () { return this.orders.details.shape},
        frame () { return this.orders.details.frame},
    },
    methods: {
        registerEventListeners() {
            document.addEventListener('getLensTypesOnlyEvent', () => {
                this.initializator.selectProductCategoryThrottleTimer = Helpers.throttleDelay(this.initializator.selectProductCategoryThrottleTimer, () => Initializator.getLensTypesOnly())
            }),
            document.addEventListener('getRangesCombinationEvent', () => {
                this.initializator.selectLensThrottleTimer = Helpers.throttleDelay(this.initializator.selectLensThrottleTimer, () => Initializator.getRangesCombination())
            })
            document.addEventListener('getLensTypesOnly', () => {
                this.initializator.selectProductCategoryThrottleTimer = Helpers.throttleDelay(this.initializator.selectProductCategoryThrottleTimer, () => Initializator.getLensTypesOnly())
            })
        },
        startDelay(){
            this.initializator.hasStartDelayFinished = false
            setTimeout(() => this.initializator.hasStartDelayFinished = true, 100)
        },
    },
    watch: {
        'basicData.hasContinuedToDetails': function(val) {
            if (val) this.startDelay()
        },
        'basicData.selectedOrderType': function(val) {
            if (!val) Initializator.setInitialSelectedOrderType()
            Initializator.onOrderTypeChange(val)
            Initializator.evaluateComponents()
        },
        'product.right.selectedLensType': function(val) {
            this.initializator.selectLensThrottleTimer = Helpers.throttleDelay(this.initializator.selectLensThrottleTimer, () => Initializator.getRangesCombination())
            this.initializator.lensGeoThrottleTimerRight = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerRight, () => Initializator.getLensGeo('right'))
        },
        'product.left.selectedLensType': function(val) {
            this.initializator.selectLensThrottleTimer = Helpers.throttleDelay(this.initializator.selectLensThrottleTimer, () => Initializator.getRangesCombination())
            this.initializator.lensGeoThrottleTimerLeft = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerLeft, () => Initializator.getLensGeo('left'))
        },
        'product.right.isStock': function(val) {
            this.product.right.lensTypeSelection = Initializator.lensTypeStock(this.product.right.lensTypeSelection, val)
        },
        'product.left.isStock': function(val) {
            this.product.left.lensTypeSelection = Initializator.lensTypeStock(this.product.left.lensTypeSelection, val)
        },
        'productCategory.selectedMaterial': async function (val) {
            if (val !== null) {
                await Initializator.getProductCategoryUpdate({
                    material: val.Value,
                })
            }
          this.initializator.selectProductCategoryThrottleTimer = Helpers.throttleDelay(this.initializator.selectProductCategoryThrottleTimer, () => Initializator.getLensTypesOnly())
        },
        'productCategory.selectedSpecial': async function(val) {
            if (val !== null) {
                await Initializator.getProductCategoryUpdate({
                    material: this.productCategory.selectedMaterial.Value,
                    special: val.Value,
                })
            }
            this.initializator.selectProductCategoryThrottleTimer = Helpers.throttleDelay(this.initializator.selectProductCategoryThrottleTimer, () => Initializator.getLensTypesOnly())
        },
        'productCategory.selectedGroup': async function(val) {
           if (val !== null && this.productCategory.selectedSpecial !== null) {
               await Initializator.getProductCategoryUpdate({
                   material: this.productCategory.selectedMaterial.Value,
                   special: this.productCategory.selectedSpecial.Value,
                   group: val.Value
               })
           }
            this.initializator.selectProductCategoryThrottleTimer = Helpers.throttleDelay(this.initializator.selectProductCategoryThrottleTimer, () => Initializator.getLensTypesOnly())
            Initializator.lensFilterReload()
            Initializator.corridorCheck()
        },
        'productCategory.selectedLensFilter': function(val) {
            this.initializator.selectProductCategoryThrottleTimer = Helpers.throttleDelay(this.initializator.selectProductCategoryThrottleTimer, () => Initializator.getLensTypesOnly())
        },
        'productCategory.selectedIndex': function(val) {
            this.initializator.selectProductCategoryThrottleTimer = Helpers.throttleDelay(this.initializator.selectProductCategoryThrottleTimer, () => Initializator.getLensTypesOnly())
        },
        'productCategory.selectedCorridor': function(val) {
            this.initializator.selectProductCategoryThrottleTimer = Helpers.throttleDelay(this.initializator.selectProductCategoryThrottleTimer, () => Initializator.getLensTypesOnly())
        },
        'refraction.left.sphere': function(val) {
            Initializator.geometryRefractionEvaluation('left', this.erp.left.diamlist)
            this.evaluator.methods.cylinderSpherePowerEvaluation('left', this.erp.left.diamlist)
        },
        'refraction.left.cylinder': function(val) {
            Initializator.geometryRefractionEvaluation('left', this.erp.left.diamlist)
            this.evaluator.methods.cylinderSpherePowerEvaluation('left', this.erp.left.diamlist)
            Initializator.isAxisRequiredEvaluation('left')
        },
        'refraction.left.axis': function(val) {
            Initializator.isAxisRequiredEvaluation('left')
        },
        'refraction.left.addition': function(val) {
            Initializator.geometryRefractionEvaluation('left', this.erp.left.diamlist)
        },
        'refraction.left.pd': function(val) {
            this.initializator.lensGeoThrottleTimerLeft = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerLeft, () => Initializator.getLensGeo('left'), 400)
        },
        'refraction.left.height': function(val) {
            this.initializator.lensGeoThrottleTimerLeft = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerLeft, () => Initializator.getLensGeo('left'), 400)
        },
        'refraction.right.sphere': function(val) {
            Initializator.geometryRefractionEvaluation('right', this.erp.right.diamlist)
            this.evaluator.methods.cylinderSpherePowerEvaluation('right', this.erp.right.diamlist)
        },
        'refraction.right.cylinder': function(val) {
            Initializator.geometryRefractionEvaluation('right', this.erp.right.diamlist)
            this.evaluator.methods.cylinderSpherePowerEvaluation('right', this.erp.right.diamlist)
            Initializator.isAxisRequiredEvaluation('right')
        },
        'refraction.right.axis': function(val) {
            Initializator.isAxisRequiredEvaluation('right')
        },
        'refraction.right.addition': function(val) {
            Initializator.geometryRefractionEvaluation('right', this.erp.right.diamlist)
        },
        'refraction.right.pd': function(val) {
            this.initializator.lensGeoThrottleTimerRight = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerRight, () => Initializator.getLensGeo('right'), 400)
        },
        'refraction.right.height': function(val) {
            this.initializator.lensGeoThrottleTimerRight = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerRight, () => Initializator.getLensGeo('right'), 400)
        },
        'formAndGeometry.left.type': function(val) {
            Initializator.opticalDiameterEvaluation('left', this.erp.left.diamlist)
            Initializator.elipticalDiameterEvaluation('left', this.erp.left.diamlist)
        },
        'formAndGeometry.left.isOptimized': function(val) {
            if (this.evaluator.details.formAndGeometry.left.isOptimized.disabled) {
                this.formAndGeometry.left.isOptimized = false
                return
            }

            this.initializator.lensGeoThrottleTimerLeft = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerLeft, () => Initializator.getLensGeo('left'), 400)
            Initializator.setLensOptimizedDiameter('left')
            Initializator.opticalDiameterEvaluation('left', this.erp.left.diamlist)
            Initializator.elipticalDiameterEvaluation('left', this.erp.left.diamlist)
        },
        'formAndGeometry.left.lensDiameter': function(val) {
            Initializator.opticalDiameterEvaluation('left', this.erp.left.diamlist)
            Initializator.elipticalDiameterEvaluation('left', this.erp.left.diamlist)
        },
        'formAndGeometry.left.minimumDiameter': function(val) {
            Initializator.setLensOptimizedDiameter('left')
            Initializator.lensDiameterEvaluation('left')
        },
        'formAndGeometry.left.boxWidth': function(val) {
            this.initializator.scaleShapeTimer = Helpers.throttleDelay(this.initializator.scaleShapeTimer, () => Initializator.scaleShape())
        },
        'formAndGeometry.left.boxHeight': function(val) {
            this.initializator.scaleShapeTimer = Helpers.throttleDelay(this.initializator.scaleShapeTimer, () => Initializator.scaleShape())
        },
        'formAndGeometry.right.type': function(val) {
            Initializator.opticalDiameterEvaluation('right', this.erp.right.diamlist)
            Initializator.elipticalDiameterEvaluation('right', this.erp.right.diamlist)
        },
        'formAndGeometry.right.isOptimized': function(val) {
            if (this.evaluator.details.formAndGeometry.right.isOptimized.disabled) {
                this.formAndGeometry.right.isOptimized = false
                return
            }

            this.initializator.lensGeoThrottleTimerRight = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerRight, () => Initializator.getLensGeo('right'), 400)
            Initializator.setLensOptimizedDiameter('right')
            Initializator.opticalDiameterEvaluation('right', this.erp.right.diamlist)
            Initializator.elipticalDiameterEvaluation('right', this.erp.right.diamlist)
        },
        'formAndGeometry.right.lensDiameter': function(val) {
            Initializator.opticalDiameterEvaluation('right', this.erp.right.diamlist)
            Initializator.elipticalDiameterEvaluation('right', this.erp.right.diamlist)
        },
        'formAndGeometry.right.minimumDiameter': function(val) {
            Initializator.setLensOptimizedDiameter('right')
            Initializator.lensDiameterEvaluation('right')
        },
        'formAndGeometry.right.boxWidth': function(val) {
            this.initializator.scaleShapeTimer = Helpers.throttleDelay(this.initializator.scaleShapeTimer, () => Initializator.scaleShape())
        },
        'formAndGeometry.right.boxHeight': function(val) {
            this.initializator.scaleShapeTimer = Helpers.throttleDelay(this.initializator.scaleShapeTimer, () => Initializator.scaleShape())
        },
        'formAndGeometry.dbl': function(val) {
            this.initializator.lensGeoThrottleTimerRight = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerRight, () => Initializator.getLensGeo('right'), 400)
            this.initializator.lensGeoThrottleTimerLeft = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerLeft, () => Initializator.getLensGeo('left'), 400)
        },
        'frame.selectedFrame': async function(val) {
            if(this.orders.basicData.selectedOrderType.id === 'S' && this.orders.details.product.right.selectedLensType == null && this.orders.details.product.left.selectedLensType == null)
              Initializator.evaluateComponents()
            if (val) {
                // Allow skipping functionallity because otherwise, when editing an order, this overrites some edit order data
                if (this.initializator.skipOnceSelectedFrameFunctionallity){
                    this.initializator.skipOnceSelectedFrameFunctionallity = false
                    return
                }

                Initializator.clearFormAndGeometryDrawingFields()
                await Initializator.getFrameByID()
                this.initializator.lensGeoThrottleTimerRight = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerRight, () => Initializator.getLensGeo('right'))
                this.initializator.lensGeoThrottleTimerLeft = Helpers.throttleDelay(this.initializator.lensGeoThrottleTimerLeft, () => Initializator.getLensGeo('left'))
            }
        }
    }
}
</script>
