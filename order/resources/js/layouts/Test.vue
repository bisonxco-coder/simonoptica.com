<template>
    <div>
        <h4 class="text-center mb-0 mt-5" >DEBUG</h4>
        <hr class="mt-0 mb-5">
        
        <panel-select 
            placeholder="System language"
            v-model="selectedLanguageCode"
            :selection="languageCodeSelection"
        />
        <span>Is Editing: {{orders.isEditing}}</span><br>
        <button v-if="orders.isEditing" @click="onCancelEditing">Cancel editing</button><br>
        <span>{{orders.editingOrder}}</span><br>
        
    </div>
</template>

<script>
import Order from '@/utils/order.js'
import Frame from '@/utils/frame.js'
import Helpers from '@/utils/helpers.js'
import Tracer from '@/utils/tracer.js'
import Language from '@/utils/language.js'
import PanelSelect from '@/components/PanelSelect.vue'
import axios from 'axios'

var assert = require('assert');

function delay(duration) {
  return new Promise((resolve) => {
    setTimeout(() => resolve(), duration);
  });
}

export default {
    components: { PanelSelect },
    data() {
        return {
            languageCodeSelection: [],
            selectedLanguageCode: null
        }
    },
    computed: {
        evaluator () {return this.$store.state.moduleEvaluator},
        settings () {return this.$store.state.moduleSettings},
        orders () {return this.$store.state.moduleOrders},
        initializator () {return this.orders.initializator},
        erp () {return this.orders.erp},
        ini () {return this.erp.getIni},
        basicData () { return this.orders.basicData},
        calculation () { return this.orders.calculation},
        productCategory () { return this.orders.details.productCategory},
        formAndGeometry () { return this.orders.details.formAndGeometry},
        formAndGeometryProperties () { return this.evaluator.details.formAndGeometry},
        refraction () { return this.orders.details.refraction},
        refractionProperties () { return this.evaluator.details.refraction},
        product () { return this.orders.details.product},
        form () { return this.orders.details.form},
        shape () { return this.orders.details.shape},
        frame () { return this.orders.details.frame},
        consumerInfo () {return this.orders.consumerInfo},
        orderBasket () { return this.orders.orderBasket},
        individual () { return this.orders.details.individual},
        individualProperties () {return this.$store.state.moduleEvaluator.details.individual},
        bulkorder () { return this.orders.details.bulkorder},
    },
    watch: {
        async selectedLanguageCode(val) {
            this.$store.state.moduleSettings.translations = await Language.loadTranslations(this.selectedLanguageCode)
        }
    },
    mounted () {
        const getLanguageCodes = async () => {
            this.languageCodeSelection = await Language.getLanguageCodes()
        }
        getLanguageCodes()

        

        this.basicData.hasContinuedToDetails = true
        this.consumerInfo.isExpanded = true
        this.refraction.isPrismExpanded = true
        this.formAndGeometry.options.drillHoles = false
        this.formAndGeometry.options.shapeFromTracerOrDatabase = true
        this.calculation.isCalculationOpen = false

        // setTimeout(() => {
        //     Initializator.hasOrderBeenEdited(['selectedOrderType'])
        // }, 1000);
        Promise.resolve()
        // .then(() => delay(1000))
        // .then(() => this.createNotification()).then(() => delay(1000))
        // .then(() => this.createNotificationObject()).then(() => delay(1000))
        // .then(() => this.createNotification2Seconds()).then(() => delay(3000))
        // .then(() => this.createNotificationSuccessObject()).then(() => delay(3000))
        // .then(() => this.createDialogBox()).then(() => delay(1000))
        // .then(() => this.createMultipleDialogBox()).then(() => delay(1000))
        // .then(() => this.createWarningDialogBox()).then(() => delay(1000))
        // .then(() => this.handleBasicDetails()).then(() => delay(300))
        // .then(() => this.gotoOrderBasket()).then(() => delay(500))
        // .then(() => this.editOrder())
        // .then(() => this.saveOrder()).then(() => delay(1000))
        // .then(() => this.changeOrderTypeToSpectacles()).then(() => delay(100))
        // .then(() => Order.reset()).then(() => delay(200))
        // .then(() => this.searchFrame()).then(() => delay(300))
        // .then(() => this.selectFrame()).then(() => delay(300))
        // .then(() => this.handleProductCategory()).then(() => delay(300))
        // .then(() => this.setLensIsStock()).then(() => delay(300))
        // .then(() => this.handleProduct()).then(() => delay(300))
        // .then(() => this.saveOrder()).then(() => delay(1000))
        // .then(() => this.changeProduct()).then(() => delay(1000))
        // .then(() => this.handleRefraction()).then(() => delay(100))
        // // .then(() => this.handleFormAndGeometry()).then(() => delay(100))
        // // .then(() => this.changeFormAndGeometryDrillHoles()).then(() => delay(100))
        // // .then(() => this.selectFormAndGeometryFrameType()).then(() => delay(100))
        // // .then(() => this.selectFormAndGeometryBevelPosition()).then(() => delay(100))
        // .then(() => this.changeGrvValues()).then(() => delay(1000))
        // .then(() => this.changeFormAndGeometryFrameType()).then(() => delay(1000))
        // .then(() => this.selectFormAndGeometryFrameType()).then(() => delay(100))
        // .then(() => this.changeProductCategoryGroup()).then(() => delay(100))
        // .then(() => this.deselectProduct()).then(() => delay(1000))
        // .then(() => this.selectDifferentProducts()).then(() => delay(1000))
        // .then(() => this.saveOrder())
        // .then(() => this.testCopyRefractionData()).then(() => delay(300))
        // .then(() => this.testSelectDifferentFrames())
        // .then(() => this.testSelectFrame())
        // .then(() => this.testWritingRightMinimumDiameterToLensDiameterIfOptimizedIsChecked())
        // .then(() => this.testWritingRightMinimumDiameterToLensDiameterIfOptimizedIsChecked2())
        // .then(() => this.testFrameTypeDisableFunctionallity())
        // .then(() => this.testSetupData())
        // .then(() => this.testResetOrder1())
        // .then(() => this.testSaveOrderThenEditOrder1())
        // .then(() => this.testIsLensDiameterGreaterThanMinimum())
        // .then(() => this.testValidateMinimumLensDiameterAgainstSelectedLensDiameterValue())
        // .then(() => this.testDisplayIndividualFields())
        // .then(() => this.testDisplayIndividualFields2())
        // .then(() => this.testDisplayIndividualFields3())
        // .then(() => this.testDisplayIndividualFields4_initialEngraving())
        // .then(() => this.testDisplayIndividualFields5_differentLensTypes())
        // .then(() => this.testValidateIndividualFields())
        // .then(() => this.testLoadIndividualFields())
        // .then(() => this.testLoadingEveryFrameOfEveryBrandAndCheckIfFormDecodesProperly())
        // .then(() => this.testLoadingCarFrame44())
        // .then(() => this.testInterpolateFormen())
        // .then(() => this.testIsFrameAvailable())
        // .then(() => this.testIsShapeFromFrameAvailable())
        // .then(() => this.testIsShapeFromOptionsAvailable())
        // .then(() => this.testDBLAvailable())
        // .then(() => this.testRightBoxWidthAvailable())
        // .then(() => this.testRightBoxHeightAvailable())
        // .then(() => this.testGetAllFieldsWithDisableFunction())
        // .then(() => this.testIsDBLEmptyWhenOrderChangesFromStandardToRemoteEdgingAndBackToStandardAgain())
        // .then(() => this.testDoesRightBoxHeighKeepValueOnOrderChangeToRemoteEdgingFromStandardLens())
        // .then(() => this.testSaveOrderWithModifiedShapeData())
        // .then(() => this.testCopyFirstOrderFromOverview())
        // .then(() => this.testGetLanguages())
        // .then(() => this.testRunCalculation2Success())
        // .then(() => this.testRunCalculation2Failure())
        // .then(() => this.testTrace())
        // .then(() => this.testGetTranslations())
        // .then(() => this.testSelectIndividualLensType())
        // .then(() => this.testRunCalculation())
        // .then(() => this.testRunCalculationWithShape())
        // .then(() => this.testLensTypeWithOther())
        // .then(() => this.testSavingAdditionalRemark())
        // .then(() => this.testInputDecimalParsing())
        // .then(() => this.testLensDiameterHas3Fields())
        // .then(() => this.testSaveBulkorder())
        // .then(() => this.testEditBulkorder())
        // .then(() => this.testOptimizedLensDiameter())
        // .then(() => this.testRunCalculationWithShape2())
        // .then(() => this.testForcedDecentration())
    },
    methods: {
        async testLoadShapeFromDatabase() {
            let interval = 1000
            console.log("=====testLoadShapeFromDatabase=====")

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.$store.getters.getFrameToComeOrderType
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(2000)

            this.$router.push('/details')
            await delay(interval)

            console.log("TEST - product category - set group 2")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[2]
            await delay(interval)

            console.log("TEST - product - select lenstype 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 2
            this.refraction.right.cylinder = -0.25
            this.refraction.right.axis = 104
            this.refraction.right.addition = 1
            this.refraction.right.pd = 32
            this.refraction.right.height = 22
            this.refraction.left.sphere = 2
            this.refraction.left.cylinder = -0.5   
            this.refraction.left.axis = 89
            this.refraction.left.addition = 1   
            this.refraction.left.pd = 32
            this.refraction.left.height = 28
            await delay(interval)

            console.log("TEST - Find Form and geometry")
            let elementFormAndGeometry = document.getElementById('formAndGeometry')
            if (!elementFormAndGeometry.__vueParentComponent) {
                elementFormAndGeometry.error("no __vueParentComponent attached to ", elementFormAndGeometry)
                return
            }
            const formAndGeometry = elementFormAndGeometry.__vueParentComponent.ctx

            console.log("TEST - Form and geometry - load shape")
            formAndGeometry.loadFromDatabase.selectedShape = {id: 4}
            await formAndGeometry.onLoadFromDatabaseConfirm()
            this.formAndGeometry.dbl = 12

            window.location.href = "#formAndGeometry"

            console.log("DONE")
        },
        async testForcedDecentration() {
            let interval = 500
            console.log("=====testForcedDecentration=====")

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval*2)

            this.$router.push('/details')
            await delay(interval)

            window.location.href = "#formAndGeometry"

            console.log("TEST - product category - set group 2")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[2]
            await delay(interval)

            console.log("TEST - product - select lenstype 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 2
            this.refraction.right.cylinder = -0.25
            this.refraction.right.axis = 104
            this.refraction.right.addition = 1
            this.refraction.right.pd = 32
            this.refraction.right.height = 22
            this.refraction.left.sphere = 2
            this.refraction.left.cylinder = -0.5   
            this.refraction.left.axis = 89
            this.refraction.left.addition = 1   
            this.refraction.left.pd = 32
            this.refraction.left.height = 28
            await delay(interval)

            console.log("TEST - form and geometry - set forced decentration to true")
            this.formAndGeometry.options.forcedDecentration = true
            window.location.href = "#formAndGeometry"
            await delay(interval)

            this.formAndGeometry.right.forcedDecentration.decentrationX = 1
            this.formAndGeometry.right.forcedDecentration.decentrationY = 2
            this.formAndGeometry.left.forcedDecentration.decentrationX = 3
            this.formAndGeometry.left.forcedDecentration.decentrationY = 4


        },
        async testRunCalculationWithShape2() {
            let interval = 2000
            console.log("=====testRunCalculationWithShape2=====")

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.$store.getters.getFrameToComeOrderType
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            this.$router.push('/details')
            await delay(interval)

            console.log("TEST - product category - set group 2")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[2]
            await delay(interval)

            console.log("TEST - product - select lenstype 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 2
            this.refraction.right.cylinder = -0.25
            this.refraction.right.axis = 104
            this.refraction.right.addition = 1
            this.refraction.right.pd = 32
            this.refraction.right.height = 22
            this.refraction.left.sphere = 2
            this.refraction.left.cylinder = -0.5   
            this.refraction.left.axis = 89
            this.refraction.left.addition = 1   
            this.refraction.left.pd = 32
            this.refraction.left.height = 28
            await delay(interval)

            console.log("TEST - Find Form and geometry")
            let elementFormAndGeometry = document.getElementById('formAndGeometry')
            if (!elementFormAndGeometry.__vueParentComponent) {
                elementFormAndGeometry.error("no __vueParentComponent attached to ", elementFormAndGeometry)
                return
            }
            const formAndGeometry = elementFormAndGeometry.__vueParentComponent.ctx

            console.log("TEST - Form and geometry - load shape")
            formAndGeometry.loadFromDatabase.selectedShape = {id: 4}
            await formAndGeometry.onLoadFromDatabaseConfirm()
            this.formAndGeometry.dbl = 12

            return


            // console.log("TEST - set individual variables")
            // const corrlen = this.individual.definedFields.find(x => x.param == 'corrlen')
            // if (!corrlen || corrlen === undefined){
            //     console.error('NO PROGRESSION LENGTH found')
            //     return
            // }
            // corrlen.value = corrlen.list[1]
            // await delay(interval)

            console.log("TEST - Find footer component")
            let element = document.querySelector('.calculation')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const footer = element.__vueParentComponent.ctx

            console.log("TEST - Open footer")
            footer.onCalculationOpen()
            await delay(500)

            console.log("TEST - Find calculation component")
            element = document.querySelector('.row.d-flex.align-items-end.h-100')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const calculation = element.__vueParentComponent.ctx

            console.log("TEST - Trigger onCalculate()")
            await calculation.onCalculate()


            console.log("DONE")
        },
        async testOptimizedLensDiameter() {
            let interval = 500
            console.log("=====testLensDiameterHas3Fields=====")

            this.$router.push('/')
            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            this.$router.push('details')
            await delay(interval)

            console.log("TEST - frames - search frames")
            this.$router.push('details')
            Frame.onSearch()
            await delay(interval*2)

            console.log("TEST - product - select lenstype 2")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[2]
            await delay(interval*3)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = -2
            this.refraction.right.cylinder = -2
            this.refraction.right.axis = 2
            this.refraction.right.pd = 22
            this.refraction.right.height = 22
            this.refraction.left.sphere = -2
            this.refraction.left.cylinder = -2   
            this.refraction.left.axis = 3
            this.refraction.left.pd = 32
            this.refraction.left.height = 32
            
            window.location.href = "#formAndGeometry"

            console.log("TEST - frames - select frame")
            this.frame.selectedFrame = this.frame.frameTableData.rows[0]
            await delay(interval)

            console.log("TEST - form and geometry - set right isOptimized")
            this.formAndGeometry.right.isOptimized = true


            console.log("DONE")
        },
        async testEditBulkorder() {
            let interval = 1200
            console.log("=====testEditBulkorder=====")

            await delay(interval)
            console.log("TEST - Load orders from orderbakset")
            let params = {
                count: 10,
                page: 1
            }
            let orders = []
            await this.$store.dispatch('getOrderbasketOrders', params).then((response) => {
                orders = response.data
            }, e => {
                this.$store.dispatch('notify', {
                    message: e.data.message,
                })
                console.error(e.data)
            })

            if (orders.length > 0){
                console.log("TEST - edit last saved order")
                await Order.edit(orders[0])
                this.$router.push('details')

            }

            console.log("DONE")
        },
        async testSaveBulkorder() {
            let interval = 1200
            console.log("=====testSaveBulkorder=====")

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.$store.getters.getBulkorderOrderType

            this.$router.push('/')
            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            this.$router.push('details')
            await delay(interval)

            console.log("TEST - bulkorder - set lens type 1")
            this.bulkorder.product.selectedLensType = this.bulkorder.product.lensTypeSelection[1]
            await delay(100)

            console.log("TEST - bulkorder - set lens diameter 1")
            this.bulkorder.product.selectedLensDiameter = this.bulkorder.product.lensDiameterSelection[1]
            await delay(interval)

            this.bulkorder.grid.rows[0][0].value = 1
            this.bulkorder.grid.rows[1][0].value = -2
            this.bulkorder.grid.rows[2][0].value = 3
            this.bulkorder.grid.rows[3][0].value = 4
            this.bulkorder.grid.rows[1][1].value = 1
            this.bulkorder.grid.rows[2][1].value = 2
            this.bulkorder.grid.rows[3][1].value = 3
            this.bulkorder.grid.rows[2][2].value = 1
            this.bulkorder.grid.rows[3][2].value = 2
            this.bulkorder.grid.rows[3][3].value = 1

            document.dispatchEvent(new Event('saveOrderEvent'))

            console.log("TEST - frames - search frames")
            this.$router.push('details')
            Frame.onSearch()
            await delay(interval*2)

            console.log("TEST - product - select lenstype 2")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[2]
            await delay(interval*3)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = -2
            this.refraction.right.cylinder = -2
            this.refraction.right.axis = 2
            this.refraction.right.pd = 22
            this.refraction.right.height = 22
            this.refraction.left.sphere = -2
            this.refraction.left.cylinder = -2   
            this.refraction.left.axis = 3
            this.refraction.left.pd = 32
            this.refraction.left.height = 32
            
            window.location.href = "#formAndGeometry"


            console.log("TEST - frames - select frame")
            this.frame.selectedFrame = this.frame.frameTableData.rows[0]
            await delay(interval)

            console.log("TEST - form and geometry - set right isOptimized")
            this.formAndGeometry.right.isOptimized = true


            console.log("DONE")
        },
        async testLensDiameterHas3Fields() {
            let interval = 500
            console.log("=====testLensDiameterHas3Fields=====")

            // this.orders.details.components.find(x => x.name == 'formAndGeometry').sequence = 12
            // this.orders.details.components.find(x => x.name == 'refraction').sequence = 14
            
            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            this.$router.push('details')

            console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[2]
            await delay(interval*3)

            console.log("TEST - product - select lenstype 2")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval*3)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 0
            this.refraction.right.cylinder = 0.25
            this.refraction.right.axis = 1
            this.refraction.right.addition = 1
            this.refraction.left.sphere = 0
            this.refraction.left.cylinder = 0.25
            this.refraction.left.axis = 1
            this.refraction.left.addition = 1
            await delay(interval)

            console.log("TEST - form and geometry - set variables")
            // this.formAndGeometry.right.opticalDiameter = 22
            // this.formAndGeometry.right.elipticalDiameter = 21
            // this.formAndGeometry.left.opticalDiameter = 22
            // this.formAndGeometry.left.elipticalDiameter = 21
            // this.formAndGeometry.right.lensDiameter = 75
            // this.formAndGeometry.left.lensDiameter = 75
            await delay(interval)

            window.location.href = "#formAndGeometry"
        },
        async testZeroValues() {
            let interval = 1000
            console.log("=====testRunCalculationWithShape=====")

            this.$router.push('/details')
            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
            await delay(interval)

            console.log("TEST - product - select lenstype 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval*2)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 0
            this.refraction.right.cylinder = 0
            this.refraction.right.axis = 0
            this.refraction.right.pd = 22
            this.refraction.right.height = 22
            this.refraction.left.sphere = 0
            this.refraction.left.cylinder = 0   
            this.refraction.left.axis = 0
            this.refraction.left.pd = 22
            this.refraction.left.height = 22
            await delay(interval)

            console.log("DONE")
        },
        async testInputDecimalParsing() {
            let interval = 500
            console.log("=====testInputDecimalParsing=====")

            this.$router.push('details')

            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

             console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
            await delay(interval)

            console.log("TEST - product - select lenstype 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval*2)

            console.log("TEST - refraction - setting variables with dot")
            this.refraction.right.sphere = 1.5
            this.refraction.right.cylinder = 1
            await delay(interval)

            console.log("TEST - refraction - setting variables with comma")
            this.refraction.right.sphere = '1,5'
            await delay(interval)


            console.log("DONE")
        },
        async testSavingAdditionalRemark() {
            let interval = 500
            console.log("=====testSavingAdditionalRemark=====")

            // this.$router.push('/details')
            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.basicData.selectedAdditionalRemark = this.basicData.additionalRemarkSelection[2]
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            await document.dispatchEvent(new Event('saveDraftEvent'))

            console.log("DONE")
        },
        async testLensTypeWithOther() {
            let interval = 500
            console.log("=====testLensTypeWithOther=====")

            this.$router.push('/details')
            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
            await delay(interval)

            console.log("TEST - product - select lenstype 59")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[59]
            await delay(interval * 2)

            console.log("DONE")
        },
        async testRunCalculationWithShape() {
            let interval = 500
            console.log("=====testRunCalculationWithShape=====")

            this.$router.push('/details')
            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
            await delay(interval)

            console.log("TEST - product - select lenstype 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 1
            this.refraction.right.cylinder = 1
            this.refraction.right.axis = 1
            this.refraction.right.pd = 22
            this.refraction.right.height = 22
            this.refraction.left.sphere = 1
            this.refraction.left.cylinder = 1   
            this.refraction.left.axis = 1
            this.refraction.left.pd = 22
            this.refraction.left.height = 22
            await delay(interval)

            console.log("TEST - Find Form and geometry")
            let elementFormAndGeometry = document.getElementById('formAndGeometry')
            if (!elementFormAndGeometry.__vueParentComponent) {
                elementFormAndGeometry.error("no __vueParentComponent attached to ", elementFormAndGeometry)
                return
            }
            const formAndGeometry = elementFormAndGeometry.__vueParentComponent.ctx

            console.log("TEST - Form and geometry - load shape")
            formAndGeometry.loadFromDatabase.selectedShape = {id: 2}
            await formAndGeometry.onLoadFromDatabaseConfirm()


            // console.log("TEST - set individual variables")
            // const corrlen = this.individual.definedFields.find(x => x.param == 'corrlen')
            // if (!corrlen || corrlen === undefined){
            //     console.error('NO PROGRESSION LENGTH found')
            //     return
            // }
            // corrlen.value = corrlen.list[1]
            // await delay(interval)

            console.log("TEST - Find footer component")
            let element = document.querySelector('.calculation')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const footer = element.__vueParentComponent.ctx

            console.log("TEST - Open footer")
            footer.onCalculationOpen()
            await delay(interval)

            console.log("TEST - Find calculation component")
            element = document.querySelector('.row.d-flex.align-items-end.h-100')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const calculation = element.__vueParentComponent.ctx

            console.log("TEST - Trigger onCalculate()")
            await calculation.onCalculate()


            console.log("DONE")
        },
        async testRunCalculation() {
            let interval = 500
            console.log("=====testRunCalculation=====")

            this.$router.push('/details')
            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
            await delay(interval)

            console.log("TEST - product - select lenstype 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 1
            this.refraction.right.cylinder = 1
            this.refraction.right.axis = 1
            // this.refraction.right.pd = 22
            // this.refraction.right.height = 22
            this.refraction.left.sphere = 1
            this.refraction.left.cylinder = 1   
            this.refraction.left.axis = 1
            // this.refraction.left.pd = 22
            // this.refraction.left.height = 22
            await delay(interval)

            console.log("TEST - Find footer component")
            let element = document.querySelector('.calculation')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const footer = element.__vueParentComponent.ctx

            console.log("TEST - Open footer")
            footer.onCalculationOpen()
            await delay(interval)

            console.log("TEST - Find calculation component")
            element = document.querySelector('.row.d-flex.align-items-end.h-100')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const calculation = element.__vueParentComponent.ctx

            console.log("TEST - Trigger onCalculate()")
            await calculation.onCalculate()


            console.log("DONE")
        },
        async testSelectIndividualLensType () {
            let interval = 500
            console.log("=====testSelectIndividualLensType=====")

            this.$router.push('/details')
            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
            await delay(interval)

            console.log("TEST - product - select lenstype 152")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[152]
            await delay(interval)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 2
            this.refraction.right.pd = 25
            this.refraction.right.height = 17
            this.refraction.left.sphere = 3
            this.refraction.left.pd = 24
            this.refraction.left.height = 30
            await delay(interval)

            console.log("DONE")
        },
        async testGetLanguages() {
            this.$store.dispatch('getLanguages', {langCode: 'en', uid: "asd"}).then(response => {
                console.log(response)
            })
        },
        async testTrace() {
            /**
             * 1. Nrdi request na backend, da zbildaš url za LensSoapTracer
             * 2. Nrdi request na LensSoapTracer za prejet podatke trejcerja
             * 3. Nrdi request na LensSoapServer za dekriptat podatke iz LensSoapTracer 
             * 
             */

            // Url to build the waiting page (not important because there is a different waiting page in the design doc)
            const startTraceUrl =      "http://localhost:33333/startTrace?tracer=HOYNAT&port=COM3&baud=9600&flow=0&parity=E&data=8&stop=2&url=http://192.168.254.160:82&vca=1&trctp=&trcser=&custNumb=999999&custPW=4F546B354F546B35&ip=localhost:33333&proxyHost=&proxyPort=&proxyUser=&proxyPass=&supplier=LWR&ipLS=https://lensware.dyndns.biz:35001&ipLSDecrypt=https://lensware.dyndns.biz:35001&vcaFileName=upload_vca/999999_1677137934.vca"
            
            // Url endpoint to receive tracer data
            const getTracerDataUrl =   "http://localhost:33333/getTraceData?tracer=HOYNAT&port=COM3&baud=9600&flow=0&parity=E&data=8&stop=2&url=http://192.168.254.160:82&vca=1&trctp=&trcser=&custNumb=999999&custPW=4F546B354F546B35&ip=localhost:33333&proxyHost=&proxyPort=&proxyUser=&proxyPass=&supplier=LWR&ipLS=https://lensware.dyndns.biz:35001&ipLSDecrypt=https://lensware.dyndns.biz:35001&vcaFileName=upload_vca/999999_1677138809.vca&supplier=LWR"
            const getTracerDataUrl2 =  "http://localhost:33333/getTraceData?tracer=Hoya G&port=COM3&baud=9600&flow=0&parity=E&data=8&stop=2&url=http://192.168.254.160:83&vca=1&trctp=&trcser=&custNumb=EDITOR&custPW=5255524A56453953&ip=localhost:33333&proxyHost=&proxyPort=&proxyUser=&proxyPass=&supplier=LWR&ipLS=http://192.168.254.126:35000/    &ipLSDecrypt=http://192.168.254.126:35000/    &vcaFileName=upload_vca/EDITOR_1677591962.vca"

            const config = {headers: {common: []}, timeout: 10000}
            console.log("RUN TEST TRACE")
            axios.get(getTracerDataUrl, config).then(response => {

                // Parse html string 
                const html = response.data 
                var tracerHtml = document.createElement('html')
                tracerHtml.innerHTML = html

                // Get value of the html node or return ""
                const getValueOfNode = (node) => node !== undefined && node && node.value !== undefined && node.value ? node.value : ""

                // Get concatenated string of all <input> elements, whose ids are "tracerData{number}"
                const getTracerData = (result = "", id = 0) => {
                    const tracerDataNode = tracerHtml.querySelector("#tracerData" + id)
                    return tracerDataNode ? getTracerData(result += getValueOfNode(tracerDataNode), ++id) : result
                }

                // Get all variables 
                const tracerData = getTracerData()
                const supplier = getValueOfNode(tracerHtml.querySelector("#supplier"))
                const ipLSDecrypt = getValueOfNode(tracerHtml.querySelector("#ipLSDecrypt"))
                const vcaFileName = getValueOfNode(tracerHtml.querySelector("#vcaFileName"))
                const version = getValueOfNode(tracerHtml.querySelector("#version"))

                console.log([tracerData, supplier, ipLSDecrypt, vcaFileName, version])

                // Decrypt
                console.log("DECRYPT")
                this.$store.dispatch('decryptTracedata', {'cryptTrace': tracerData}).then(respnose => {
                    // This must be interpolated with the same tracer type as in the settings 
                    console.log(respnose)
                })
            })
        },
        async testGetTranslations() {
            this.$store.dispatch('getTranslations', {langCode: 'en', uid: "asd"}).then(response => {
                console.log(response)
            })
        },
        async testRunCalculation2Failure() {
            let interval = 300
            console.log("=====testRunCalculation2Failure=====")
            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            // console.log("TEST - product category - set group 2")
            // this.productCategory.selectedGroup = this.productCategory.groupSelection[2]
            // await delay(interval)

            console.log("TEST - product - select lenstype 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 4
            this.refraction.right.pd = 22
            this.refraction.right.height = 22
            this.refraction.left.sphere = 4
            this.refraction.left.pd = 22
            this.refraction.left.height = 22
            // await delay(interval)

            // console.log("TEST - set individual variables")
            // const corrlen = this.individual.definedFields.find(x => x.param == 'corrlen')
            // if (!corrlen || corrlen === undefined){
            //     console.error('NO PROGRESSION LENGTH found')
            //     return
            // }
            // corrlen.value = corrlen.list[1]
            // await delay(interval)

            console.log("TEST - Find footer component")
            let element = document.querySelector('.calculation')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const footer = element.__vueParentComponent.ctx

            console.log("TEST - Open footer")
            footer.onCalculationOpen()
            await delay(interval)

            console.log("TEST - Find calculation component")
            element = document.querySelector('.row.d-flex.align-items-end.h-100')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const calculation = element.__vueParentComponent.ctx

            console.log("TEST - Trigger onCalculate()")
            await calculation.onCalculate()


            console.log("DONE")
        },
        async testRunCalculation2Success() {
            let interval = 300
            console.log("=====testRunCalculation2Success=====")
            await delay(interval)

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval)

            // console.log("TEST - product category - set group 2")
            // this.productCategory.selectedGroup = this.productCategory.groupSelection[2]
            // await delay(interval)

            console.log("TEST - product - select lenstype 2")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[2]
            await delay(interval)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 4
            this.refraction.right.pd = 22
            this.refraction.right.height = 22
            this.refraction.left.sphere = 4
            this.refraction.left.pd = 22
            this.refraction.left.height = 22
            // await delay(interval)

            // console.log("TEST - set individual variables")
            // const corrlen = this.individual.definedFields.find(x => x.param == 'corrlen')
            // if (!corrlen || corrlen === undefined){
            //     console.error('NO PROGRESSION LENGTH found')
            //     return
            // }
            // corrlen.value = corrlen.list[1]
            // await delay(interval)

            console.log("TEST - Find footer component")
            let element = document.querySelector('.calculation')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const footer = element.__vueParentComponent.ctx

            console.log("TEST - Open footer")
            footer.onCalculationOpen()
            await delay(interval)

            console.log("TEST - Find calculation component")
            element = document.querySelector('.row.d-flex.align-items-end.h-100')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const calculation = element.__vueParentComponent.ctx

            console.log("TEST - Trigger onCalculate()")
            await calculation.onCalculate()


            console.log("DONE")
        },
        async testCopyFirstOrderFromOverview() {
            let interval = 500
            console.log("=====testCopyFirstOrderFromOverview=====")
            await delay(interval)

            console.log("TEST - goto overview")
            this.$router.push('/overview')
            await delay(interval)

            console.log("TEST - Find overview component")
            const element = document.querySelector('.card.pt-3.pb-4.px-5.mb-3')
            if (!element.__vueParentComponent) {
                console.error("no __vueParentComponent attached to ", element)
                return
            }
            const overview = element.__vueParentComponent.ctx
            await delay(interval)

            console.log("TEST - Load overview orders")
            await overview.getOrders()

            console.log("TEST - Copy the first order")
            const firstTableRow = overview.overviewTableData.rows[0]
            await overview.onOrderCopy(firstTableRow)
            await delay(interval)


            console.log("DONE")
        },
        async testSaveOrderWithModifiedShapeData() {
            let interval = 1000
            console.log("=====testDoesRightBoxHeighKeepValueOnOrderChangeToRemoteEdgingFromStandardLens=====")

            console.log("TEST - basic data - set basic")
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"

            console.log("TEST - frames - search frames of brand: CAR")
            const brand = this.frame.search.brandSelection.find(x => x.FSUPPCODE == 'CAR')
            this.frame.search.selectedBrand = brand
            await Frame.onSearch()
            await delay(interval)

            // console.log("TEST - frames - select frame")
            // this.frame.selectedFrame = this.frame.frameTableData.rows[0]
            // await delay(interval)

            console.log("TEST - product - select lenstype")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = -5
            this.refraction.right.pd = 25
            this.refraction.right.height = 17
            this.refraction.left.sphere = -4
            this.refraction.left.pd = 24
            this.refraction.left.height = 30
            await delay(interval/2)

            console.log("TEST - form and geometry - set variables")
            // this.formAndGeometry.right.boxWidth = 10
            // this.formAndGeometry.right.boxHeight = 30
            // this.formAndGeometry.left.boxWidth = 40
            // this.formAndGeometry.left.boxHeight = 10
            await delay(interval/2)

            // console.log("TEST - save order")
            // await Order.save()
            // await delay(interval/2)

            // console.log("TEST - goto order basket")
            // await this.gotoOrderBasket()
            // await delay(interval)

            console.log("DONE")
        },
        async testDoesRightBoxHeighKeepValueOnOrderChangeToRemoteEdgingFromStandardLens() {
            let interval = 1000
            console.log("=====testDoesRightBoxHeighKeepValueOnOrderChangeToRemoteEdgingFromStandardLens=====")

            const brand = this.frame.search.brandSelection.find(x => x.FSUPPCODE == 'CAR')

            console.log("TEST - frames - search frames of brand: " + brand.FSUPPCODE)
            this.frame.search.selectedBrand = brand
            await Frame.onSearch()
            await delay(interval)

            console.log("TEST - frames - select frame")
            this.frame.selectedFrame = this.frame.frameTableData.rows[0]
            await delay(interval)

            console.log("TEST - refraction - set right pd and height")
            this.refraction.right.pd = 22
            this.refraction.right.height = 23
            await delay(interval)

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            await delay(interval)
            assert.notEqual(this.formAndGeometry.right.boxHeight, null)
            assert.notEqual(this.formAndGeometry.right.boxHeight, undefined)

            console.log("TEST - Basic data - Select remote edging")
            this.basicData.selectedOrderType = this.$store.getters.getRemoteEdgingOrderType
            await delay(interval)
            // assert.notEqual(this.formAndGeometry.right.boxHeight, null)
            // assert.notEqual(this.formAndGeometry.right.boxHeight, undefined)
            console.log(this.formAndGeometry.right.boxHeight)

            console.log("DONE")
        },
        async testIsDBLEmptyWhenOrderChangesFromStandardToRemoteEdgingAndBackToStandardAgain() {
            let interval = 1000
            console.log("=====testIsDBLEmptyWhenOrderChangesFromStandardToRemoteEdgingAndBackToStandardAgain=====")

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            this.formAndGeometry.dbl = 15.01
            await delay(interval)
            console.log(this.formAndGeometry.dbl)
            assert.notEqual(this.formAndGeometry.dbl, null)
            assert.notEqual(this.formAndGeometry.dbl, undefined)

            console.log("TEST - Basic data - Select remote edging")
            this.basicData.selectedOrderType = this.$store.getters.getRemoteEdgingOrderType
            await delay(interval)
            console.log(this.formAndGeometry.dbl)
            assert.equal(this.formAndGeometry.dbl, null)

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            await delay(interval)
            console.log(this.formAndGeometry.dbl)
            assert.equal(this.formAndGeometry.dbl, null)

            console.log("DONE")
        },
        async testGetAllFieldsWithDisableFunction() {
            let interval = 200
            console.log("=====testGetAllFieldsWithDisableFunction=====")

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            console.log(this.$store.getters.getAllFieldsWithDisabledFunction)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging")
            this.basicData.selectedOrderType = this.$store.getters.getRemoteEdgingOrderType
            console.log(this.$store.getters.getAllFieldsWithDisabledFunction)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come")
            this.basicData.selectedOrderType = this.$store.getters.getFrameToComeOrderType
            console.log(this.$store.getters.getAllFieldsWithDisabledFunction)
            await delay(interval)

            console.log("TEST - Basic data - Select spectacles")
            this.basicData.selectedOrderType = this.$store.getters.getSpectaclesOrderType
            console.log(this.$store.getters.getAllFieldsWithDisabledFunction)
            await delay(interval)
            
            console.log("TEST - Basic data - Bulk order")
            this.basicData.selectedOrderType = this.$store.getters.getBulkorderOrderType
            console.log(this.$store.getters.getAllFieldsWithDisabledFunction)
            await delay(interval)
            

            console.log("DONE")
        },
        async testRightBoxHeightAvailable() {
            let interval = 200
            console.log("=====testRightBoxHeightAvailable=====")

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, false)
            await delay(interval)

            ////////////Remote edging
            console.log("TEST - Basic data - Select remote edging - Frame type = Metal")
            this.basicData.selectedOrderType = this.$store.getters.getRemoteEdgingOrderType
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeMetal
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging - Frame type = Cell")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeCell
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging - Frame type = Rimless Grooved")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessGrooved
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging - Frame type = Rimless grooved drilled")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessGroovedDrilled
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging - Frame type = Rimless drilled")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessDrilled
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging - Frame type = Rimless (Flat edge)")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessFlatEdge
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, false)
            await delay(interval)


            ////////////Frame to come
            console.log("TEST - Basic data - Select frame to come - Frame type = Metal")
            this.basicData.selectedOrderType = this.$store.getters.getFrameToComeOrderType
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeMetal
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come - Frame type = Cell")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeCell
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come - Frame type = Rimless Grooved")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessGrooved
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come - Frame type = Rimless grooved drilled")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessGroovedDrilled
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come - Frame type = Rimless drilled")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessDrilled
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come - Frame type = Rimless (Flat edge)")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessFlatEdge
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, false)
            await delay(interval)


            console.log("TEST - Basic data - Select spectacles")
            this.basicData.selectedOrderType = this.$store.getters.getSpectaclesOrderType
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, true)
            await delay(interval)
            
            console.log("TEST - Basic data - Bulk order")
            this.basicData.selectedOrderType = this.$store.getters.getBulkorderOrderType
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(this.formAndGeometryProperties.right.boxHeight.disabled, false)
            await delay(interval)

            console.log("Done")
        },
        async testRightBoxWidthAvailable() {
            let interval = 200
            console.log("=====testRightBoxWidthAvailable=====")

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, false)
            await delay(interval)

            ////////////Remote edging
            console.log("TEST - Basic data - Select remote edging - Frame type = Metal")
            this.basicData.selectedOrderType = this.$store.getters.getRemoteEdgingOrderType
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeMetal
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging - Frame type = Cell")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeCell
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging - Frame type = Rimless Grooved")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessGrooved
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging - Frame type = Rimless grooved drilled")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessGroovedDrilled
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging - Frame type = Rimless drilled")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessDrilled
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging - Frame type = Rimless (Flat edge)")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessFlatEdge
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, false)
            await delay(interval)


            ////////////Frame to come
            console.log("TEST - Basic data - Select frame to come - Frame type = Metal")
            this.basicData.selectedOrderType = this.$store.getters.getFrameToComeOrderType
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeMetal
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come - Frame type = Cell")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeCell
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come - Frame type = Rimless Grooved")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessGrooved
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come - Frame type = Rimless grooved drilled")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessGroovedDrilled
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come - Frame type = Rimless drilled")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessDrilled
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come - Frame type = Rimless (Flat edge)")
            this.formAndGeometry.selectedFrameType = this.$store.getters.getFrameTypeRimlessFlatEdge
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, false)
            await delay(interval)


            console.log("TEST - Basic data - Select spectacles")
            this.basicData.selectedOrderType = this.$store.getters.getSpectaclesOrderType
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, true)
            await delay(interval)
            
            console.log("TEST - Basic data - Bulk order")
            this.basicData.selectedOrderType = this.$store.getters.getBulkorderOrderType
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(this.formAndGeometryProperties.right.boxWidth.disabled, false)
            await delay(interval)

            console.log("Done")
        },
        async testDBLAvailable() {
            let interval = 200
            console.log("=====testDBLAvailable=====")

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(this.formAndGeometryProperties.dbl.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging")
            this.basicData.selectedOrderType = this.$store.getters.getRemoteEdgingOrderType
            assert.equal(this.formAndGeometryProperties.dbl.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come")
            this.basicData.selectedOrderType = this.$store.getters.getFrameToComeOrderType
            assert.equal(this.formAndGeometryProperties.dbl.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select spectacles")
            this.basicData.selectedOrderType = this.$store.getters.getSpectaclesOrderType
            assert.equal(this.formAndGeometryProperties.dbl.disabled, true)
            await delay(interval)
            
            console.log("TEST - Basic data - Bulk order")
            this.basicData.selectedOrderType = this.$store.getters.getBulkorderOrderType
            assert.equal(this.formAndGeometryProperties.dbl.disabled, true)
            await delay(interval)

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(this.formAndGeometryProperties.dbl.disabled, false)
            await delay(interval)

            console.log("Done")
        },
        async testIsShapeFromOptionsAvailable() {
            let interval = 200
            console.log("=====testIsShapeFromOptionsAvailable=====")

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(this.formAndGeometryProperties.options.shapeFromTracerOrDatabase.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging")
            this.basicData.selectedOrderType = this.$store.getters.getRemoteEdgingOrderType
            assert.equal(this.formAndGeometryProperties.options.shapeFromTracerOrDatabase.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come")
            this.basicData.selectedOrderType = this.$store.getters.getFrameToComeOrderType
            assert.equal(this.formAndGeometryProperties.options.shapeFromTracerOrDatabase.disabled, false)
            await delay(interval)

            console.log("TEST - Basic data - Select spectacles")
            this.basicData.selectedOrderType = this.$store.getters.getSpectaclesOrderType
            assert.equal(this.formAndGeometryProperties.options.shapeFromTracerOrDatabase.disabled, true)
            await delay(interval)
            
            console.log("TEST - Basic data - Bulk order")
            this.basicData.selectedOrderType = this.$store.getters.getBulkorderOrderType
            assert.equal(this.formAndGeometryProperties.options.shapeFromTracerOrDatabase.disabled, true)
            await delay(interval)

            console.log("Done")
        },
        async testIsShapeFromFrameAvailable() {
            let interval = 200
            console.log("=====testIsShapeFromFrameAvailable=====")

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(Frame.isShapeFromFrameAvailable(), true)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging")
            this.basicData.selectedOrderType = this.$store.getters.getRemoteEdgingOrderType
            assert.equal(Frame.isShapeFromFrameAvailable(), true)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come")
            this.basicData.selectedOrderType = this.$store.getters.getFrameToComeOrderType
            assert.equal(Frame.isShapeFromFrameAvailable(), true)
            await delay(interval)

            console.log("TEST - Basic data - Select spectacles")
            this.basicData.selectedOrderType = this.$store.getters.getSpectaclesOrderType
            assert.equal(Frame.isShapeFromFrameAvailable(), false)
            await delay(interval)
            
            console.log("TEST - Basic data - Bulk order")
            this.basicData.selectedOrderType = this.$store.getters.getBulkorderOrderType
            assert.equal(Frame.isShapeFromFrameAvailable(), false)
            await delay(interval)

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(Frame.isShapeFromFrameAvailable(), true)
            await delay(interval)

            console.log("Done")
        },
        async testIsFrameAvailable() {
            let interval = 200
            console.log("=====testIsFrameAvailable=====")

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(Frame.isFrameAvailable(), false)
            await delay(interval)

            console.log("TEST - Basic data - Select remote edging")
            this.basicData.selectedOrderType = this.$store.getters.getRemoteEdgingOrderType
            assert.equal(Frame.isFrameAvailable(), false)
            await delay(interval)

            console.log("TEST - Basic data - Select frame to come")
            this.basicData.selectedOrderType = this.$store.getters.getFrameToComeOrderType
            assert.equal(Frame.isFrameAvailable(), false)
            await delay(interval)

            console.log("TEST - Basic data - Select spectacles")
            this.basicData.selectedOrderType = this.$store.getters.getSpectaclesOrderType
            assert.equal(Frame.isFrameAvailable(), true)
            await delay(interval)
            
            console.log("TEST - Basic data - Bulk order")
            this.basicData.selectedOrderType = this.$store.getters.getBulkorderOrderType
            assert.equal(Frame.isFrameAvailable(), false)
            await delay(interval)

            console.log("TEST - Basic data - Select standard lens")
            this.basicData.selectedOrderType = this.$store.getters.getStandardLensOrderType
            assert.equal(Frame.isFrameAvailable(), false)
            await delay(interval)

            console.log("Done")
        },
        async testInterpolateFormen() {
            let type = 'TBLNAT'
            let form1 = "25302553257525982612261726202620261726122606260025842567255025272494246024272385234022952254222621982170214521262107209120972103210921142120212621402165219022152257230323482394244224902538257526092643267326892705271027052688267226552640262626122600260326062609261226152618262626432660267726892700271227202708268626642633258825432498244823962345230022692238220721842164214421302128212721252124212221212128214221562170219622222247227022902309232923362342234823482346234323402347235523642374239124082424244824762504"
            this.$store.dispatch('interpolate', {
                type: type,
                vca: form1
            })
            .then(response => {
                this.orders.isLoading = false

                console.log(response)

                // Extract all variables
                let decoded = Tracer.decodeTracerFormData(response, false)
                console.log(decoded)
            }, e => {
                this.orders.isLoading = false
                
                this.$store.dispatch('notify', {
                    message: e.data.message,
                })
                console.error(e)
            })
        },
        async testLoadingCarFrame44() {
            // Car frame id 44 has missing right lens box width information
            let interval = 1000
            console.log("=====testLoadingCarFrame44=====")

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]

            console.log("TEST - product - select right lens type 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - refraction - set sphere")
            this.refraction.right.sphere = -1
            this.refraction.left.sphere = -1
            await delay(interval)
            
            console.log("TEST - frame - select brand CAR")
            this.frame.search.selectedBrand = this.frame.search.brandSelection.find(x => x.FSUPPCODE == 'CAR');
            await Frame.onSearch()
            await delay(interval)

            console.log("TEST - frame - select frame 44")
            this.frame.selectedFrame = this.frame.frameTableData.rows[44]
            await delay(interval)

            console.log("DONE")
        },
        async testLoadingEveryFrameOfEveryBrandAndCheckIfFormDecodesProperly() {
            let interval = 1000
            console.log("=====testLoadingEveryFrameOfEveryBrandAndCheckIfFormDecodesProperly=====")

            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]

            console.log("TEST - product - select right lens type 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - refraction - set sphere")
            this.refraction.right.sphere = -1
            this.refraction.left.sphere = -1
            await delay(interval)
            
            for (let j = 0; j < this.frame.search.brandSelection.length; j++) {
                const brand = this.frame.search.brandSelection[j];
                let brandName = brand.FSUPPCODE

                console.log("TEST - frames - search frames of brand: " + brandName)
                this.frame.search.selectedBrand = brand
                await Frame.onSearch()
                await delay(interval)
                
                for (let i = 0; i < this.frame.frameTableData.rows.length; i++) {
                    console.log("TEST - frames - brand: " + brandName + "  select frame: " + i)
                    const frame = this.frame.frameTableData.rows[i]
                    this.frame.selectedFrame = frame
                    await delay(interval)
                    assert.notStrictEqual(this.formAndGeometry.graphics.decodedData, undefined, this.frame.selectedFrame)
                    assert.notStrictEqual(this.formAndGeometry.graphics.decodedData.form, undefined, this.frame.selectedFrame)

                    let errorCaseLogObject = {
                        message: "'R=' was not found in decodedData", 
                        selectedFrame: this.frame.selectedFrame, 
                        decodedData: this.formAndGeometry.graphics.decodedData
                    }
                    assert(this.formAndGeometry.graphics.decodedData.form.includes('R='), errorCaseLogObject)

                    console.log("TEST - frames - brand: " + brandName + "  select frame: " + i + " is OK!")
                }
                console.log("TEST - frames - finished brand: " + brandName)
            }

            console.log("DONE")
        },
        async testLoadIndividualFields() {
            let interval = 1000
            console.log("=====testLoadIndividualFields=====")

            console.log("TEST - Load orders from orderbakset")
            let params = {
                count: 10,
                page: 1
            }
            let orders = []
            await this.$store.dispatch('getOrderbasketOrders', params).then((response) => {
                orders = response.data
            }, e => {
                this.$store.dispatch('notify', {
                    message: e.data.message,
                })
                console.error(e.data)
            })

            if (orders.length > 0){
                console.log("TEST - edit last saved order")
                await Order.edit(orders[0])
            }



            console.log("DONE")
        },
        async testValidateIndividualFields() {
            let interval = 1000
            console.log("=====testValidateIndividualFields=====")
            console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
            await delay(interval)

            // console.log("TEST - product - select right lens type 282")
            // this.product.right.selectedLensType = this.product.right.lensTypeSelection[282]
            // await delay(interval)

            console.log("TEST - product - select right lens type 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            assert.equal(this.$store.getters.areIndividualFieldsEvaluated, true)


            console.log("DONE")
        },
        async testDisplayIndividualFields5_differentLensTypes() {
            let interval = 800
            console.log("=====testDisplayIndividualFields5_differentLensTypes=====")
            console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
            await delay(interval)

            console.log("TEST - product - set 'same left and right' to false")
            this.product.isSameLeftRight = false
            await delay(interval)

            console.log("TEST - product - select right lens type 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - product - select left lens type 3")
            this.product.left.selectedLensType = this.product.left.lensTypeSelection[3]
            await delay(interval)

            console.log("DONE")
        },
        async testDisplayIndividualFields4_initialEngraving() {
            let interval = 1000
            console.log("=====testDisplayIndividualFields4_initialEngraving=====")

            console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
            await delay(interval)

            console.log("TEST - product - select right lens type 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - individual - set initial engraving")
            if (this.individual.definedFields || this.individual.definedFields !== undefined){
                let initial = this.individual.definedFields.find(x => x.name == 'INITIAL')
                initial.value = 'Matjaž Brilj'
            }
            await delay(interval)

            console.log("DONE")
        },
        async testDisplayIndividualFields3() {
            let interval = 800
            console.log("=====testDisplayIndividualFields3=====")
            console.log("TEST - product category - set group 2")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[2]
            await delay(interval)

            console.log("TEST - product - select right lens type 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - product category - set group 1")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[1]
            await delay(interval)

            console.log("TEST - product - select right lens type 17")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[17]
            await delay(interval)

            console.log("DONE")
        },
        async testDisplayIndividualFields2() {
            let interval = 800
            console.log("=====testDisplayIndividualFields2=====")
            console.log("TEST - product category - set group 2")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[2]
            await delay(interval)

            console.log("TEST - product - select right lens type 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - product category - set group 0")
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
            await delay(interval)

            console.log("TEST - product - select right lens type 1")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("DONE")
        },
        async testDisplayIndividualFields() {
            let interval = 800
            console.log("=====testDisplayIndividualFields=====")
            console.log("TEST - product - select right lens type")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - product - select another right lens type")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[282]
            await delay(interval)

            console.log("TEST - product - select previous right lens type")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("DONE")
        },
        async testValidateMinimumLensDiameterAgainstSelectedLensDiameterValue() {
            let interval = 500

            await this.testSetupData2()
            await delay(interval)

            console.log("TEST - frames - searching frames")
            await Frame.onSearch()
            await delay(interval)

            console.log("TEST - frames - select frame 0")
            this.frame.selectedFrame = this.frame.frameTableData.rows[0]
            await delay(1500)

            console.log("DONE")
        },
        async testIsLensDiameterGreaterThanMinimum() {
            let interval = 500

            await this.testSetupData2()
            await delay(interval)

            console.log("TEST - frames - searching frames")
            await Frame.onSearch()
            await delay(interval)

            console.log("TEST - frames - select frame 3")
            this.frame.selectedFrame = this.frame.frameTableData.rows[3]
            await delay(1600)

            assert.equal(this.$store.getters.isLensDiameterGreaterThanMinimum, false)
            console.log("DONE")
        },
        async testSaveOrderThenEditOrder1() {
            let interval = 400

            await this.testSetupData()
            await delay(interval)
            
            console.log("TEST - frames - searching frames")
            await Frame.onSearch()
            await delay(interval)

            console.log("TEST - frames - select frame 0")
            this.frame.selectedFrame = this.frame.frameTableData.rows[0]
            await delay(2000)

            console.log("TEST - save order")
            await Order.save()
return
            console.log("TEST - goto order basket")
            await this.gotoOrderBasket()
            await delay(interval*2)

            console.log("TEST - edit last order")
            let order = this.orderBasket.orderBasketTableData.rows[0].rawData
            await Order.edit(order)
            await delay(interval)

            console.log("TEST - goto basic data")
            this.$router.push('/')
            await delay(interval)

            console.log("DONE")
        },
        async testResetOrder1() {
            let interval = 200
            await this.testSetupData()
            await delay(interval*2)

            console.log("TEST -- reset order 1")
            await Order.reset()
            await delay(interval)

            console.log("TEST - basic data")
            assert.equal(this.basicData.selectedOrderType.id, 'N')
            assert.equal(this.basicData.referenceNumber, null)
            assert.equal(this.basicData.remark, null)
            assert.equal(this.basicData.hasContinuedToDetails, false, 'hasContinuedToDetails')
            assert.equal(this.consumerInfo.selectedTitle, null)
            assert.equal(this.consumerInfo.firstName, null)
            assert.equal(this.consumerInfo.name, null)
            assert.equal(this.consumerInfo.street, null)
            assert.equal(this.consumerInfo.zipCode, null)
            assert.equal(this.consumerInfo.city, null)
            assert.equal(this.consumerInfo.phone, null)
            assert.equal(this.consumerInfo.selectedLanguage, null)
            await delay(interval)

            console.log("TEST - product category")
            assert.equal(this.productCategory.selectedMaterial, this.productCategory.materialSelection[0])
            assert.equal(this.productCategory.selectedIndex, this.productCategory.indexSelection[0])
            await delay(interval)

            console.log("TEST - product - lense type")
            assert.equal(this.product.right.selectedLensType, null)
            await delay(interval)

            console.log("TEST - product - other")
            // assert.equal(this.product.right.selectedOther, null)
            assert.equal(this.product.tintRemark, null)
            assert.equal(this.product.selectedTint, null)
            assert.equal(this.product.selectedAntireflectiveCoating, null)
            await delay(interval)

            console.log("TEST - refraction")
            assert.equal(this.refraction.right.sphere, null)
            assert.equal(this.refraction.right.cylinder, null)
            assert.equal(this.refraction.right.axis, null)
            assert.equal(this.refraction.right.prism1, null)
            assert.equal(this.refraction.right.prism2, null)
            assert.equal(this.refraction.right.pd, null)
            assert.equal(this.refraction.right.height, null)
            assert.equal(this.refraction.right.isAxisRequired, false, 'isAxisRequired')
            assert.equal(this.refraction.right.isPdRequired, false, 'isPdRequired')
            assert.equal(this.refraction.right.isHeightRequired, false, 'isHeightRequired')
            // assert.equal(this.refraction.right.range, null, 'right.range')
            assert.equal(this.refraction.left.sphere, null)
            assert.equal(this.refraction.left.cylinder, null)
            assert.equal(this.refraction.left.axis, null)
            assert.equal(this.refraction.left.prism1, null)
            assert.equal(this.refraction.left.prism2, null)
            assert.equal(this.refraction.left.pd, null)
            assert.equal(this.refraction.left.height, null)
            assert.equal(this.refraction.left.isAxisRequired, false)
            assert.equal(this.refraction.left.isPdRequired, false)
            assert.equal(this.refraction.left.isHeightRequired, false)
            // assert.equal(this.refraction.left.range, null)
            await delay(interval)

            console.log("TEST - form and geometry")
            assert.equal(this.formAndGeometry.dbl, null, 'dbl')
            assert.equal(this.formAndGeometry.selectedFrameType.control, this.formAndGeometry.frameTypeSelection[0].control, 'frame type')
            assert.equal(this.formAndGeometry.selectedBevelPosition.control, this.formAndGeometry.bevelPositionSelection[0].control, 'bevel position')
            assert.equal(this.formAndGeometry.isPolish, false, 'polish')
            assert.equal(this.formAndGeometry.isEdgePinbevel, false, 'edge pinbevel')
            assert.equal(this.formAndGeometry.grvWidthInput, 0.6, 'grv width')
            assert.equal(this.formAndGeometry.grvDepthInput, 0.4, 'grv depth')
            assert.equal(this.formAndGeometry.bevelPositionValue, null, 'bevel position')
            assert.equal(this.formAndGeometry.right.boxWidth, null, 'right.box width')
            assert.equal(this.formAndGeometry.right.boxHeight, null, 'right.box height')
            assert.equal(this.formAndGeometry.right.lensDiameter, 0, 'right.lensdiameter')
            assert.equal(this.formAndGeometry.right.selectedThicknessOption.id, this.formAndGeometry.right.thicknessRadioInputData[0].id, 'right.thickness id')
            assert.equal(this.formAndGeometry.right.thicknessRadioInputData[0].value, null, 'right.thickness 0')
            assert.equal(this.formAndGeometry.right.thicknessRadioInputData[1].value, null, 'right.thickness 1')
            assert.equal(this.formAndGeometry.right.basecurve, null, 'right.basecurve')
            assert.equal(this.formAndGeometry.right.isOptimized, false, 'right optimized')
            assert.equal(this.formAndGeometry.left.boxWidth, null, 'left.box width')
            assert.equal(this.formAndGeometry.left.boxHeight, null, 'left.box height')
            assert.equal(this.formAndGeometry.left.lensDiameter, 0, 'left.lensdiameter')
            assert.equal(this.formAndGeometry.left.selectedThicknessOption.id, this.formAndGeometry.right.thicknessRadioInputData[0].id, 'left.thickness id')
            assert.equal(this.formAndGeometry.left.thicknessRadioInputData[0].value, null, 'left.thickness 0')
            assert.equal(this.formAndGeometry.left.thicknessRadioInputData[1].value, null, 'left.thickness 1')
            assert.equal(this.formAndGeometry.left.basecurve, null, 'left.basecurve')
            assert.equal(this.formAndGeometry.left.isOptimized, false, 'left optimized')
            await delay(interval)

            console.log("DONE")
        },
        async testSetupData2() {
            console.log("TEST -- SETTING UP TEST DATA")

            let interval = 500
            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval/4)

            console.log("TEST - product category - select organisch")
            this.productCategory.selectedMaterial = this.productCategory.materialSelection[0]
            this.productCategory.selectedIndex = this.productCategory.indexSelection[0]
            await delay(interval)

            console.log("TEST - product - select freform lens types")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            this.product.left.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(interval)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 5
            this.refraction.right.cylinder = 2
            this.refraction.right.axis = 3
            this.refraction.right.prism1 = 4
            this.refraction.right.prism2 = 3
            this.refraction.right.pd = 25
            this.refraction.right.height = 17
            this.refraction.left.axis = 4
            this.refraction.left.pd = 25
            this.refraction.left.height = 31
            await delay(interval/4)

            console.log("TEST - form and geometry - set variables")
            this.formAndGeometry.dbl = 15
            this.formAndGeometry.selectedFrameType = this.formAndGeometry.frameTypeSelection[3]
            this.formAndGeometry.selectedBevelPosition = this.formAndGeometry.bevelPositionSelection[3]
            this.formAndGeometry.isPolish = true
            setTimeout(() => {
                this.formAndGeometry.grvWidthInput = 2.2
                this.formAndGeometry.grvDepthInput = 3.2
            }, 1000);
            this.formAndGeometry.bevelPositionValue = 1.2
            this.formAndGeometry.right.isOptimized = false
            this.formAndGeometry.right.boxWidth = 40
            this.formAndGeometry.right.boxHeight = 30
            this.formAndGeometry.right.lensDiameter = 66
            this.formAndGeometry.right.selectedThicknessOption = this.formAndGeometry.right.thicknessRadioInputData[1]
            this.formAndGeometry.right.selectedThicknessOption.value = 3
            this.formAndGeometry.right.basecurve = 15
            this.formAndGeometry.left.isOptimized = true
            this.formAndGeometry.left.boxWidth = 40
            this.formAndGeometry.left.boxHeight = 30
            this.formAndGeometry.left.selectedThicknessOption.value = 4
            this.formAndGeometry.left.basecurve = 16
            await delay(interval/4)

            console.log("TEST -- FINISHED SETTING UP TEST DATA")
        },
        async testSetupData() {
            console.log("TEST -- SETTING UP TEST DATA")

            let interval = 500
            console.log("TEST - basic data - prepare data")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[1]
            this.basicData.remark = "+asd65f4s6a87f98as3f06wq4de06a"
            this.basicData.referenceNumber = new Date().toLocaleString() + " Matjaž"
            this.consumerInfo.selectedTitle = this.consumerInfo.titleSelection[1]
            this.consumerInfo.firstName = "matjaž"
            this.consumerInfo.name = "brilj"
            this.consumerInfo.street = "cesta 1000"
            this.consumerInfo.zipCode = "9999"
            this.consumerInfo.city = "Solkan"
            this.consumerInfo.phone = "000 111 222 333"
            this.consumerInfo.selectedLanguage = this.consumerInfo.languageSelection[1]
            await delay(interval/4)

            console.log("TEST - product category - select Mineralisch, Einstaerken, 1.60")
            this.productCategory.selectedMaterial = this.productCategory.materialSelection[3]
            this.productCategory.selectedIndex = this.productCategory.indexSelection[5]
            await delay(interval)

            console.log("TEST - product - select right lens type Sv Spheric Mineral 1.60")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            this.product.left.selectedLensType = this.product.right.lensTypeSelection[2]
            await delay(interval)

            console.log("TEST - product - select right other Optimized")
            // this.product.right.selectedOther = this.product.right.otherSelection[1]
            // this.product.left.selectedOther = this.product.right.otherSelection[2]
            await delay(interval/2)

            console.log("TEST - product - Tint, tint remark and AR")
            this.product.tintRemark = "nude-vision 100%"
            this.product.selectedTint = this.product.tintSelection[1]
            this.product.selectedAntireflectiveCoating = this.product.antireflectiveCoatingSelection[1]
            await delay(interval/2)

            console.log("TEST - refraction - set variables")
            this.refraction.right.sphere = 5
            this.refraction.right.cylinder = 2
            this.refraction.right.axis = 3
            this.refraction.right.prism1 = 4
            this.refraction.right.prism2 = 3
            this.refraction.right.pd = 22
            this.refraction.right.height = 30
            this.refraction.left.axis = 4
            this.refraction.left.pd = 25
            this.refraction.left.height = 31
            await delay(interval/4)

            console.log("TEST - form and geometry - set variables")
            this.formAndGeometry.dbl = 15
            this.formAndGeometry.selectedFrameType = this.formAndGeometry.frameTypeSelection[3]
            this.formAndGeometry.selectedBevelPosition = this.formAndGeometry.bevelPositionSelection[3]
            this.formAndGeometry.isPolish = true
            setTimeout(() => {
                this.formAndGeometry.grvWidthInput = 2.2
                this.formAndGeometry.grvDepthInput = 3.2
            }, 1000);
            this.formAndGeometry.bevelPositionValue = 1.2
            this.formAndGeometry.right.isOptimized = false
            this.formAndGeometry.right.boxWidth = 40
            this.formAndGeometry.right.boxHeight = 30
            this.formAndGeometry.right.lensDiameter = 66
            this.formAndGeometry.right.selectedThicknessOption = this.formAndGeometry.right.thicknessRadioInputData[1]
            this.formAndGeometry.right.selectedThicknessOption.value = 3
            this.formAndGeometry.right.basecurve = 15
            this.formAndGeometry.left.isOptimized = true
            this.formAndGeometry.left.boxWidth = 40
            this.formAndGeometry.left.boxHeight = 30
            this.formAndGeometry.left.selectedThicknessOption.value = 4
            this.formAndGeometry.left.basecurve = 16
            await delay(interval/4)

            console.log("TEST -- FINISHED SETTING UP TEST DATA")
        },
        async testFrameTypeDisableFunctionallity() {
            let interval = 2000
            console.log("TEST - basic data - select standard lens")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[0]
            await delay(interval)

            console.log("TEST - basic data - select spectacles")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[3]
            await delay(interval)

            console.log("TEST - frames - searching frames")
            await Frame.onSearch()
            await delay(interval)

            console.log("TEST - frames - select frame 0")
            this.frame.selectedFrame = this.frame.frameTableData.rows[0]
            await delay(interval)

            console.log("TEST - frames - select frame 1")
            this.frame.selectedFrame = this.frame.frameTableData.rows[1]
            await delay(interval)

            console.log("TEST - frames - select frame 3")
            this.frame.selectedFrame = this.frame.frameTableData.rows[3]
            await delay(interval)

            console.log("TEST - basic data - select standard lens")
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[0]
            await delay(interval)

            console.log("DONE")
        },
        async testWritingRightMinimumDiameterToLensDiameterIfOptimizedIsChecked2() {
            let interval = 1000
            console.log("TEST - frames - searching frames")
            await Frame.onSearch()
            await delay(interval)

            console.log("TEST - frames - select frame 1")
            this.frame.selectedFrame = this.frame.frameTableData.rows[1]
            await delay(interval)

            console.log("TEST - refraction - set right pd and height")
            this.refraction.right.pd = 17
            this.refraction.right.height = 24
            await delay(interval)

            console.log("TEST - product - select right lens")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[2]
            await delay(interval)

            console.log("TEST - form and geometry - check right optimized")
            this.formAndGeometry.right.isOptimized = true
            await delay(interval)

            console.log("TEST - product - deselect right lens")
            this.product.right.selectedLensType = null
            await delay(interval)

            console.log("TEST - form and geometry - uncheck right optimized")
            this.formAndGeometry.right.isOptimized = false
            await delay(interval)

            console.log("DONE")
        },
        async testWritingRightMinimumDiameterToLensDiameterIfOptimizedIsChecked() {
            let interval = 500
            console.log("TEST - frames - searching frames")
            await Frame.onSearch()
            await delay(interval)

            console.log("TEST - frames - select frame 1")
            this.frame.selectedFrame = this.frame.frameTableData.rows[1]
            await delay(interval)

            console.log("TEST - refraction - set right pd and height")
            this.refraction.right.pd = 17
            this.refraction.right.height = 24
            await delay(interval)

            console.log("TEST - product - select right lens")
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[2]
            await delay(interval)

            console.log("TEST - form and geometry - check right optimized")
            this.formAndGeometry.right.isOptimized = true
            await delay(interval + 1000)

            if (this.formAndGeometry.right.minimumDiameter == this.formAndGeometry.right.lensDiameter)
                console.log("SUCCESS",
                    "minimumDiameter:", this.formAndGeometry.right.minimumDiameter, 
                    "lensDiameter:", this.formAndGeometry.right.lensDiameter
                )
            else {
                console.error("FAIL", 
                    "minimumDiameter:", this.formAndGeometry.right.minimumDiameter, 
                    "lensDiameter:", this.formAndGeometry.right.lensDiameter
                )
            }
        },
        async testSelectFrame() {
            console.log("TEST - frames - searching frames")
            await Frame.onSearch()
            await delay(500)

            console.log("TEST - frames - select frame 3")
            this.frame.selectedFrame = this.frame.frameTableData.rows[3]
            await delay(1000)

            console.log("DONE")
        },
        async testSelectDifferentFrames() {
            console.log("TEST - frames - searching frames")
            await Frame.onSearch()
            await delay(500)

            console.log("TEST - frames - select frame 0")
            this.frame.selectedFrame = this.frame.frameTableData.rows[0]
            await delay(1000)

            console.log("TEST - frames - select frame 1")
            this.frame.selectedFrame = this.frame.frameTableData.rows[1]
            await delay(1000)

            console.log("TEST - frames - select frame 2")
            this.frame.selectedFrame = this.frame.frameTableData.rows[2]
            await delay(1000)

            console.log("TEST - frames - select frame 3")
            this.frame.selectedFrame = this.frame.frameTableData.rows[3]
            await delay(1000)

            console.log("TEST - frames - select frame 4")
            this.frame.selectedFrame = this.frame.frameTableData.rows[4]
            await delay(1000)

            console.log("DONE")
        },
        async testCopyRefractionData() {
            console.log("TEST - refraction - set variables 1")
            this.refraction.right.pd = 17
            this.refraction.right.height = 24
            this.refraction.right.axis = 5
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
            await delay(500)

            console.log("TEST - refraction - set variables 2")
            this.refraction.right.prism1 = 2
            await delay(500)

            console.log("TEST - refraction - set variables 3")
            this.product.left.selectedLensType = this.product.left.lensTypeSelection[1]
            await delay(500)

            console.log("TEST - product - disable left lens")
            this.product.left.hasLens = false
            this.refraction.right.prism1 = 4
            await delay(500)

            console.log("TEST - refraction - enable same left right")
            this.refraction.isSameLeftRight = true
            await delay(500)

            console.log("TEST - product - enable left lens")
            this.product.left.hasLens = true
            this.product.left.selectedLensType = this.product.left.lensTypeSelection[1]
            await delay(500)

            console.log("DONE")
        },



        onCancelEditing(){
            this.orders.isEditing = false
            this.orders.editingOrder = null
        },
        gotoOrderBasket() {
            this.$router.push('/')
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    this.$router.push('/orderBasket')
                    resolve()
                }, 500);                
            })
        },
        createDialogBox() {
            let dialogParams = {
                title: "Your order is saved in theEE order basket. This is a test title test test TEST.",
                description: 'Do you want to go to the Order basket or create a new order? TEST test',
                falseButtonText: 'Order basket',
                trueButtonText: 'New Order',
                allowClosing: true,
            }
            this.$store.dispatch('dialog', dialogParams).then(value => {
                if (value) console.log("YES")
                else if (value === undefined) console.log("CLOSE")
                else console.log("NO")
            })
        },
        createMultipleDialogBox() {
            let dialogParams = {
                title: "Your order is saved in theEE order basket. This is a test title test test TEST.",
                description: 'Do you want to go to the Order basket or create a new order? TEST test',
                falseButtonText: 'Order basket',
                trueButtonText: 'New Order',
                allowClosing: true,
                extraButtons: [
                    {
                        text: 'Option 1',
                        style: 'blue',
                        click: () => {
                            console.log("jajajaajjaajajjajajjajja")
                        }
                    },
                    {
                        text: 'Option 2',
                        style: 'blue',
                        click: () => {
                            console.log("nenennenenenenenne")
                        }
                    }
                ]
            }
            this.$store.dispatch('dialog', dialogParams).then(value => {
                if (value) console.log("YES")
                else console.log("NO")
            })
        },
        createWarningDialogBox() {
            let dialogParams = {
                title: "Warning",
                description: 'Changing the order type will result in losing all the data you have inserted. Do you wish to continue?',
                falseButtonText: 'Cancel',
                trueButtonText: 'Continue',
                type: 'warning',
            }
            this.$store.dispatch('dialog', dialogParams).then(value => {
                if (value) console.log("YES")
                else if (value === undefined) console.log("CLOSE")
                else console.log("NO")
            })
        },
        createNotification(){
            this.$store.dispatch('notify', "Error test message aaaaaa")
        },
        createNotificationObject(){
            this.$store.dispatch('notify', {message: 'Error with object'})
        },
        createNotification2Seconds(){
            this.$store.dispatch('notify', {message: 'TESTAAA', duration: 2})
        },
        createNotificationSuccessObject(){
            this.$store.dispatch('notify', {message: 'Error with object', type: 'success'})
        },
        saveOrder() {
            document.dispatchEvent(new Event('saveOrderEvent'))
        },
        editOrder() {
            let order = this.orderBasket.orderBasketTableData.rows[0].rawData
            Order.edit(order)
        },
        handleBasicDetails() {
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[0]
            this.basicData.referenceNumber = "TEST " + new Date().toLocaleString('sl')
        },
        changeOrderTypeToSpectacles() {
            this.basicData.selectedOrderType = this.basicData.orderTypeSelection[3]
        },
        handleProductCategory() {
            this.productCategory.selectedMaterial = this.productCategory.materialSelection[0]
            this.productCategory.selectedSpecial = this.productCategory.specialSelection[0]
            this.productCategory.selectedGroup = this.productCategory.groupSelection[0]
        },
        setLensIsStock() {
            this.product.left.isStock = true
        },
        handleProduct() {
            this.product.left.selectedLensType = this.product.left.lensTypeSelection[2]
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[2]
        },
        changeProduct() {
            this.product.left.selectedLensType = this.product.left.lensTypeSelection[2]
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
        },
        handleRefraction() {
            this.refraction.left.sphere = 5
            this.refraction.left.pd = 22
            this.refraction.left.height = 22
            this.refraction.left.cylinder = 3
            this.refraction.left.axis = 3

            this.refraction.right.sphere = 4
            this.refraction.right.pd = 22
            this.refraction.right.height = 22
        },
        handleFormAndGeometry() {
            this.formAndGeometry.left.lensDiameter = 70
            this.formAndGeometry.right.lensDiameter = 70            
        },
        changeFormAndGeometryDrillHoles() {
            this.formAndGeometry.drillHoles[0].right.endX = 8
            this.formAndGeometry.drillHoles[0].right.endY = 12
        },
        searchFrame() {
            Frame.onSearch()
        },
        selectFrame() {
            this.frame.selectedFrame = this.frame.frameTableData.rows[0]
        },
        changeProductCategoryGroup() {
            this.productCategory.selectedGroup = this.productCategory.groupSelection[3]
        },
        deselectProduct() {
            this.product.left.selectedLensType = null
            this.product.right.selectedLensType = null
        },
        selectDifferentProducts() {
            this.product.left.selectedLensType = this.product.left.lensTypeSelection[1]
            this.product.right.selectedLensType = this.product.right.lensTypeSelection[1]
        },
        selectFormAndGeometryFrameType() {
            this.formAndGeometry.selectedFrameType = this.formAndGeometry.frameTypeSelection[3]
        },
        selectFormAndGeometryBevelPosition() {
            this.formAndGeometry.selectedBevelPosition = this.formAndGeometry.bevelPositionSelection[0]
        },
        changeGrvValues() {
            this.formAndGeometry.grvWidthInput = 10
            this.formAndGeometry.grvDepthInput = 12
        },
        changeFormAndGeometryFrameType() {
            this.formAndGeometry.selectedFrameType = this.formAndGeometry.frameTypeSelection[0]
        }
    }
}

</script>