import Helpers from '@/utils/helpers.js'
import ApiService from '@/utils/api';
import Order from '@/utils/order';
import Initializator from '@/utils/initializator';
import store from '@/store'

const self = {
    get erp() { return store.state.moduleOrders.erp},
    get calculation() { return store.state.moduleOrders.calculation},
    get product() { return store.state.moduleOrders.details.product},
    get refraction() { return store.state.moduleOrders.details.refraction},
    get formAndGeometry() { return store.state.moduleOrders.details.formAndGeometry},
    get individual() { return store.state.moduleOrders.details.individual},
    get initializator() { return store.state.moduleOrders.initializator},
    get form() {return store.state.moduleOrders.details.form},
    get orders () {return store.state.moduleOrders},
}
export default {
    async getCalculation() {
        // Check if order is evaluated one more time in case this function wasn't called from Calculation.vue
        if (!Order.isOrderEvaluated()) return
        
        // Run get lens geo
        self.initializator.lensGeoThrottleTimerRight = await Helpers.throttleDelay(self.initializator.lensGeoThrottleTimerRight, () => Initializator.getLensGeo('right'))
        self.initializator.lensGeoThrottleTimerLeft = await Helpers.throttleDelay(self.initializator.lensGeoThrottleTimerLeft, () => Initializator.getLensGeo('left'))

        // Set is optimized
        self.calculation.right.isOptimized = self.formAndGeometry.right.isOptimized
        self.calculation.left.isOptimized = self.formAndGeometry.left.isOptimized

        // Initialize params
        let params = Order.getSaveOrderData().params
        // let params = {
        //     data: this.getCalculationData(),
        // }

        // Add form r values
        const decodedData = self.form.shape.current.decodedData
        if (decodedData && decodedData !== undefined){
            if (decodedData.right && decodedData.right !== undefined)
                params.formRight = self.form.shape.current.decodedData.right.rValues
            if (decodedData.left && decodedData.left !== undefined)
                params.formLeft = self.form.shape.current.decodedData.left.rValues
        }

        self.orders.isLoading = true
        self.calculation.isLoading = true
        return store.dispatch('getCalculation', params)
        .then(response => {
            self.orders.isLoading = false
            self.calculation.isLoading = false

            // Save response
            self.erp.getCalculation = response

            // Assign calculation data to fields
            this.setCalculationData(response)

            // Set translated error messages
            this.setErrorMessages(response)

            return response
        }, e => {
            self.orders.isLoading = false
            self.calculation.isLoading = false

            // Reset calculation data
            this.clearCalculationData()
            
            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)

            return null
        }) 
    },
    getCalculationData() {
        // Get accn
        const authData = ApiService.addAuthParameters({});
        let accn = authData.uid

        // Get lens product code
        const lensCodeR = Helpers.getObjProp(self.product.right.selectedLensType, 'LENSCODE')
        const lensCodeL = Helpers.getObjProp(self.product.left.selectedLensType, 'LENSCODE')

        // Get side
        let side = 'B'
        if (lensCodeR == '') side = 'L'
        if (lensCodeL == '') side = 'R'

        // Get sphere
        const sphereR = self.refraction.right.sphere || ''
        const sphereL = self.refraction.left.sphere || ''

        // Get cylinder
        const cylinderR = self.refraction.right.cylinder || ''
        const cylinderL = self.refraction.left.cylinder || ''

        // Get axis
        const axisR = self.refraction.right.axis || ''
        const axisL = self.refraction.left.axis || ''

        // Get addition
        const additionR = self.refraction.right.addition || ''
        const additionL = self.refraction.left.addition || ''

        // Get pd
        const pdR = self.refraction.right.pd || ''
        const pdL = self.refraction.left.pd || ''

        // Get height
        const heightR = self.refraction.right.height || ''
        const heightL = self.refraction.left.height || ''

        // Get dev. center
        const devCenterL = self.formAndGeometry.left.thicknessRadioInputData.find(x => x.id == 1).value || ''

        // Get Minimal edge
        const minEdgeR = self.formAndGeometry.right.thicknessRadioInputData.find(x => x.id == 0).value || ''
        const minEdgeL = self.formAndGeometry.left.thicknessRadioInputData.find(x => x.id == 0).value || ''

        // Get basecurve
        const basecurveR = self.formAndGeometry.right.basecurve || ''
        const basecurveL = self.formAndGeometry.left.basecurve || ''

        // Get dbl
        const dbl = self.formAndGeometry.dbl || ''

        // Get is optimized flag
        const isOptimizedR = self.formAndGeometry.right.isOptimized
        const isOptimizedL = self.formAndGeometry.left.isOptimized

        // Get lens diameter
        const lensDiameterR = self.formAndGeometry.right.lensDiameter || ''
        const lensDiameterL = self.formAndGeometry.left.lensDiameter || ''

        // Get corrlen
        let corrlen = ''
        if (self.individual.definedFields && self.individual.definedFields.length > 0){
            const selectedCorrlen = self.individual.definedFields.find(x => x.param == 'corrlen')

            if (selectedCorrlen && selectedCorrlen !== undefined)
                corrlen = selectedCorrlen.value || ''
        }

        // Get shape form
        const decodedData = self.form.shape.current.decodedData
        let form = ""
        if (decodedData && decodedData !== undefined){
            form = decodedData.form
        }

        // Form data string
        let data = `
            DO=${side}
            JOB=
            ACCN=${accn}
            CLIENT=
            LNAM=${lensCodeR};${lensCodeL}
            SPH=${sphereR};${sphereL}
            CYL=${cylinderR};${cylinderL}
            AX=${axisR};${axisL}
            ADD=${additionR};${additionL}
            IPD=${pdR};${pdL}
            SEGHT=${heightR};${heightL}
            MINCTR=${devCenterL};${devCenterL}
            MINEDG=${minEdgeR};${minEdgeL}
            MBASE=${basecurveR};${basecurveL}
            DBL=${dbl}
            _CTO=${isOptimizedR};${isOptimizedL}
            PRVM=0;0
            PRVA=0;0
            CRIB=${lensDiameterR};${lensDiameterL}
            ELLH=;
            CORRLEN=${corrlen};${corrlen}
            ${form}
        `

        // Clear spaces
        data = data.split('\n').filter(x => x).map(x => x.trim()).join('\n')

        // Return encoded in base64
        return Helpers.stringToBase64(data)
    },
    setCalculationData(response) {
        if (!response || response === undefined || response == []) return 

        let calcDiamR = Helpers.getObjProp(response, 'calcDiamR')
        let calcDiamL = Helpers.getObjProp(response, 'calcDiamL')
        let calcEllhR = Helpers.getObjProp(response, 'calcEllhR')
        let calcEllhL = Helpers.getObjProp(response, 'calcEllhL')

        self.calculation.right.lensType = Helpers.getObjProp(response, 'calcLenstypeR')
        self.calculation.right.diameter = calcEllhR != '' ? calcDiamR + '/' + calcEllhR : calcDiamR
        self.calculation.right.geometrical = parseFloat(Helpers.getObjProp(response, 'calcCentThicknGeoR')).toFixed(1)
        self.calculation.right.optical = parseFloat(Helpers.getObjProp(response, 'calcCentThicknOptR')).toFixed(1)
        self.calculation.right.basecurve = Helpers.getObjProp(response, 'calcBasecR')
        self.calculation.right.weight = Helpers.getObjProp(response, 'calcWeightR')
        
        self.calculation.left.lensType = Helpers.getObjProp(response, 'calcLenstypeL')
        self.calculation.left.diameter = calcEllhL != '' ? calcDiamL + '/' + calcEllhL : calcDiamL
        self.calculation.left.geometrical = parseFloat(Helpers.getObjProp(response, 'calcCentThicknGeoL')).toFixed(1)
        self.calculation.left.optical = parseFloat(Helpers.getObjProp(response, 'calcCentThicknOptL')).toFixed(1)
        self.calculation.left.basecurve = Helpers.getObjProp(response, 'calcBasecL')
        self.calculation.left.weight = Helpers.getObjProp(response, 'calcWeightL')
    },
    setErrorMessages(response) {
        Object.defineProperty(self.calculation.right, 'error', {get() { return ''}})
        Object.defineProperty(self.calculation.left, 'error', {get() { return ''}})
        Object.defineProperty(self.calculation.right, 'warning', {get() { return ''}})
        Object.defineProperty(self.calculation.left, 'warning', {get() { return ''}})

        if (!response || response == '' || response === undefined ) return

        const errorL = Helpers.getObjProp(response, 'errorL')
        const errorR = Helpers.getObjProp(response, 'errorR')


        if ((!errorL || errorL === undefined || errorL == '') &&
            (!errorR || errorR === undefined || errorR == '')
        )return

        // Split 'string' with 'delimiter' and take string at position 'pos'
        const getStringPos = (string, pos, delimiter = ';') => string.split(delimiter)[pos]

        // Returns error message translation (Example input: "XSTATUS=L;-1083;No blank found: Diameter/Deactivation;E")
        const parseErrorMessageTranslation = (input, warning = false) => {
            let defaultMessage = store.getters.translations.footer.calculation.xstatus.lensCannotBeCalculated
            if(warning) defaultMessage = ''
            const errorCode = getStringPos(input, 1)
            let message = `${defaultMessage} (${input})`
            switch (errorCode) {
                case '2500': message = store.getters.translations.footer.calculation.xstatus.resultIsApproximation; break;
                case '1218': message = store.getters.translations.footer.calculation.xstatus.thicknessNotSufficient; break;
            }
            return message
        }

        if (errorR) {
            const errorWaning = getStringPos(parseErrorMessageTranslation(errorR), 3)
            if(errorWaning.charAt(0) === 'W') {
                Object.defineProperty(self.calculation.right, 'warning', {
                    get() {
                        const side = store.getters.translations.common.right
                        const type = store.getters.translations.common.warning
                        const message = parseErrorMessageTranslation(errorR, true)
                        return `${side}: ${type}: ${message}`
                    }
                })
            }
            else {
                Object.defineProperty(self.calculation.right, 'error', {
                    get() {
                        const side = store.getters.translations.common.right
                        const type = store.getters.translations.common.fault
                        const message = parseErrorMessageTranslation(errorR)
                        return `${side}: ${type}: ${message}`
                    }
                })
            }

        }
        if (errorL) {
            const errorWaning = getStringPos(parseErrorMessageTranslation(errorL), 3)
            if(errorWaning.charAt(0) === 'W') {
                Object.defineProperty(self.calculation.left, 'warning', {
                    get() {
                        const side = store.getters.translations.common.left
                        const type = store.getters.translations.common.warning
                        const message = parseErrorMessageTranslation(errorL, true)
                        return `${side}: ${type}: ${message}`
                    }
                })
            }
            else {
                Object.defineProperty(self.calculation.left, 'error', {
                    get() {
                        const side = store.getters.translations.common.left
                        const type = store.getters.translations.common.fault
                        const message = parseErrorMessageTranslation(errorL)
                        return `${side}: ${type}: ${message}`
                    }
                })
            }
        }
    },
    clearCalculationData() {
        self.erp.getCalculation = null
        self.calculation.isLoading = false
        self.calculation.areGraphicsSuccessfullyRendered = false
        
        let sides = ['right','left'].forEach(side => {
            self.calculation[side].lensType = null
            self.calculation[side].diameter = null
            self.calculation[side].isOptimized = null
            self.calculation[side].geometrical = null
            self.calculation[side].optical = null
            self.calculation[side].minimal = null
            self.calculation[side].maximal = null
            self.calculation[side].top = null
            self.calculation[side].down = null
            self.calculation[side].nasal = null
            self.calculation[side].temporal = null
            self.calculation[side].basecurve = null
            self.calculation[side].weight = null

            Object.defineProperty(self.calculation[side], 'error', {get() { return ''}})
        })
    },
}