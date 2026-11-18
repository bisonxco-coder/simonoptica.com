import store from '@/store'
import Frame from '@/utils/frame.js'
import Helpers from '@/utils/helpers.js'
import Initializator from '@/utils/initializator.js'
import Order from '@/utils/order.js'
import Tracer from '@/utils/tracer.js'
import Bulkorder from '@/utils/bulkorder.js'
import EditResponse from '@/utils/editResponse.js'

const self = {
    get orders() {return store.state.moduleOrders},
    get erp() { return store.state.moduleOrders.erp},
    get basicData() { return store.state.moduleOrders.basicData},
    get consumerInfo() { return store.state.moduleOrders.consumerInfo},
    get productCategory() { return store.state.moduleOrders.details.productCategory},
    get formAndGeometry() { return store.state.moduleOrders.details.formAndGeometry},
    get refraction() { return store.state.moduleOrders.details.refraction},
    get product() { return store.state.moduleOrders.details.product},
    get frame() { return store.state.moduleOrders.details.frame},
    get individual() { return store.state.moduleOrders.details.individual},
    get initializator() { return store.state.moduleOrders.initializator},
    get form() { return store.state.moduleOrders.details.form},
    get bulkorder() {return store.state.moduleOrders.details.bulkorder},
}
class CopyResponse {
    constructor(_hasError = false, _message = ""){
        this.hasError = _hasError
        this.message = _message
    }
}
export default {
    async copy(order) {
        const editResponse = new EditResponse()

        try {
            await Order.reset()
            let response = await this.copyBasicData(order)
            editResponse.addMessages(response.messages)

            if (this.isOrderBulkorder(order))
                response = await this.copyBulkorder(order)
            else response = await this.copyOrder(order)
            editResponse.addMessages(response.messages)

        } catch (error) {
            console.error(error)
            editResponse.addMessage(error)
        }

        return editResponse
    },
    async copyBasicData(order) {
        const editResponse = new EditResponse()

        try {
            const remarkData = Order.getRemarkData(Helpers.getObjProp(order, 'Remark'))
            let remark = remarkData.remark
            let additionalRemarkCode = remarkData.additionalRemarkCode

            // Set basic data
            self.basicData.referenceNumber = Helpers.getObjProp(order, 'Reference')
            self.basicData.remark = remark
            self.basicData.hasContinuedToDetails = true

            // Set additional remark selection
            if (additionalRemarkCode && additionalRemarkCode !== undefined && additionalRemarkCode != '')
                self.basicData.selectedAdditionalRemark = self.basicData.additionalRemarkSelection.find(x => x && x !== undefined && x.code !== undefined && x.code == additionalRemarkCode)

            // Set basic data - selected order type
            const orderTypeId = Helpers.getObjProp(order, 'RecType', store.getters.getDefaultOrderType.id)
            self.basicData.selectedOrderType = self.basicData.orderTypeSelection.find(x => x.id == orderTypeId)

            // Set consumer info data
            self.consumerInfo.selectedTitle = self.consumerInfo.titleSelection.find(x => x.control == Helpers.getObjProp(order, 'ConsTitle', null))
            self.consumerInfo.selectedLanguage = self.consumerInfo.languageSelection.find(x => x.control == Helpers.getObjProp(order, 'ConsLang', null))
            self.consumerInfo.name = Helpers.getObjProp(order, 'ConsLastName', null)
            self.consumerInfo.firstName = Helpers.getObjProp(order, 'ConsFirstName', null)
            self.consumerInfo.street = Helpers.getObjProp(order, 'ConsStreet', null)
            self.consumerInfo.zipCode = Helpers.getObjProp(order, 'ConsZip', null)
            self.consumerInfo.city = Helpers.getObjProp(order, 'ConsCity', null)
            self.consumerInfo.phone = Helpers.getObjProp(order, 'ConsPhone', null)
            self.consumerInfo.dateOfBirth = Helpers.getObjProp(order, 'ConsDateOfBirth', null)
            self.consumerInfo.email = Helpers.getObjProp(order, 'ConsEMail', null)

            // Set the title of the order
            store.commit('setDashboardTitle', {
                title: () => store.getters.translations.newOrder.title,
                subTitle: () => store.getters.getOrderTypeTitle
            })
        } catch (error) {
            console.error(error)
            editResponse.addMessages(error)
        }

        return editResponse
    },
    async copyOrder(order) {
        const editResponse = new EditResponse()
        try {
            // Set product category options
            self.productCategory.selectedMaterial = self.productCategory.materialSelection.find(x => x.Value == Helpers.getObjProp(order, 'Mat_ExtR', null))
            self.productCategory.selectedGroup = self.productCategory.groupSelection.find(x => x.Value == Helpers.getObjProp(order, 'Group_ExtR', null))

            self.initializator.hasStartDelayFinished = true
            await Initializator.getLensTypesOnly()

            // Set lens type stock (R = false, S = true)
            self.product.right.isStock = Helpers.getObjProp(order, 'StockR', false) == 'S'
            self.product.left.isStock = Helpers.getObjProp(order, 'StockL', false) == 'S'

            // Set lens types and has lens
            const leftLens = Helpers.getObjProp(order, 'LenscodeL', null)
            const rightLens = Helpers.getObjProp(order, 'LenscodeR', null)
            self.product.left.hasLens = leftLens && leftLens !== ''
            self.product.right.hasLens = rightLens && rightLens !== ''
            self.product.isSameLeftRight = false
            self.product.left.selectedLensType = self.erp.getLensTypesOnly.Lens.filter(x => x).find(x => x.LENSCODE == leftLens)
            self.product.right.selectedLensType = self.erp.getLensTypesOnly.Lens.filter(x => x).find(x => x.LENSCODE == rightLens)

            // If no lens type was found, this means there was a missmatch between the data from the order and the data from the LSS.
            const hasLenstype = (lensType) => lensType !== undefined && lensType !== null && lensType !== ''
            if (!hasLenstype(self.product.left.selectedLensType) && !hasLenstype(self.product.right.selectedLensType)){
                editResponse.addMessage(store.getters.translations.notifications.lensTypeDataMissmatch)
            }

            await Initializator.getRangesCombination()

            // Set geometry Edgethick = Minimal edge
            const edgeThicknessLeft = Helpers.getObjPropNumber(order, 'EdgeL', null)
            const edgeThicknessRight = Helpers.getObjPropNumber(order, 'EdgeR', null)
            if (edgeThicknessLeft) {
                self.formAndGeometry.left.selectedThicknessOption = self.formAndGeometry.left.thicknessRadioInputData.find(x => x.id == 0)
                self.formAndGeometry.left.selectedThicknessOption.value = edgeThicknessLeft
            }
            if (edgeThicknessRight) {
                self.formAndGeometry.right.selectedThicknessOption = self.formAndGeometry.right.thicknessRadioInputData.find(x => x.id == 0)
                self.formAndGeometry.right.selectedThicknessOption.value = edgeThicknessRight
            }

            // Set geometry Centthick = Dev. center
            const centThicknessLeft = Helpers.getObjPropNumber(order, 'CentL', null)
            const centThicknessRight = Helpers.getObjPropNumber(order, 'CentR', null)
            if (centThicknessLeft) {
                self.formAndGeometry.left.selectedThicknessOption = self.formAndGeometry.left.thicknessRadioInputData.find(x => x.id == 1)
                self.formAndGeometry.left.selectedThicknessOption.value = centThicknessLeft
            }
            if (centThicknessRight) {
                self.formAndGeometry.right.selectedThicknessOption = self.formAndGeometry.right.thicknessRadioInputData.find(x => x.id == 1)
                self.formAndGeometry.right.selectedThicknessOption.value = centThicknessRight
            }

            // Set geometry basecurve
            self.formAndGeometry.left.basecurve = Helpers.getObjPropNumber(order, 'BasecL', null)
            self.formAndGeometry.right.basecurve = Helpers.getObjPropNumber(order, 'BasecR', null)

            // Set geometry is optimized
            self.formAndGeometry.left.isOptimized =  Helpers.getObjProp(order, 'CtoL', null) == '1'
            self.formAndGeometry.right.isOptimized =  Helpers.getObjProp(order, 'CtoR', null) == '1'

            // Set geometry lens diameter
            // const getLensDiameter = (_diam1, _diam2, _diam3) => {
            //     try {
            //         const diamCombination = []
            //         const diam1 = Helpers.getObjProp(order, _diam1).trim()
            //         const diam2 = Helpers.getObjProp(order, _diam2).trim()
            //         const diam3 = Helpers.getObjProp(order, _diam3).trim()
            //         if (diam1 && diam1 !== '' && !isNaN(diam1)) diamCombination.push(parseFloat(diam1))
            //         if (diam2 && diam2 !== '' && !isNaN(diam2)) diamCombination.push(parseFloat(diam2))
            //         if (diam3 && diam3 !== '' && !isNaN(diam3)) diamCombination.push(parseFloat(diam3))
            //         return Math.max(...diamCombination)
            //     } catch (error) { console.error(error)}
            //     return null
            // }
            // self.formAndGeometry.left.lensDiameter = getLensDiameter('Diam1L', 'Diam2L', 'Diam3L')
            // self.formAndGeometry.right.lensDiameter = getLensDiameter('Diam1R', 'Diam2R', 'Diam3R')
            self.formAndGeometry.left.lensDiameter = Helpers.getObjProp(order, 'Diam1L').trim()
            self.formAndGeometry.left.opticalDiameter = Helpers.getObjProp(order, 'Diam2L').trim()
            self.formAndGeometry.left.elipticalDiameter = Helpers.getObjProp(order, 'Diam3L').trim()
            self.formAndGeometry.right.lensDiameter = Helpers.getObjProp(order, 'Diam1R').trim()
            self.formAndGeometry.right.opticalDiameter = Helpers.getObjProp(order, 'Diam2R').trim()
            self.formAndGeometry.right.elipticalDiameter = Helpers.getObjProp(order, 'Diam3R').trim()
            self.initializator.hasStartDelayFinished = true

            if(self.formAndGeometry.left.elipticalDiameter > 0) self.formAndGeometry.left.type = 'O'
            if(self.formAndGeometry.right.elipticalDiameter > 0) self.formAndGeometry.right.type = 'O'

            // Set selected tint
            const tintCode = Helpers.getObjProp(order, 'TintcodeR', null)
            if (self.erp.getRangesCombination.TintList.length > 0 && !self.product.selectedTint && tintCode && tintCode !== ''){
                self.product.selectedTint = self.erp.getRangesCombination.TintList.filter(x => x).find(x => x.CODE == tintCode)
            }

            // Set tint remark field
            const remarkData = Order.getRemarkData(Helpers.getObjProp(order, 'Remark'))
            self.product.tintRemark = remarkData.tintRemark

            // Set selected antireflective coating
            const coatCode = Helpers.getObjProp(order, 'CoatcodeR', null)
            if (self.erp.getRangesCombination.CoatList.length > 0 && !self.product.selectedAntireflectiveCoating && coatCode && coatCode !== ''){
                self.product.selectedAntireflectiveCoating = self.erp.getRangesCombination.CoatList.filter(x => x).find(x => x.CODE == coatCode)
            }

            // Set selected UV
            const uvCode = Helpers.getObjProp(order, 'UVcodeR', null)
            if (self.erp.getRangesCombination.UVList.length > 0 && !self.product.selectedUv && uvCode && uvCode !== ''){
                self.product.selectedUv = self.erp.getRangesCombination.UVList.filter(x => x).find(x => x.CODE == uvCode)
            }

            // Set sphere
            self.refraction.right.sphere = Helpers.getObjPropNumber(order, 'SphR', null)
            self.refraction.left.sphere = Helpers.getObjPropNumber(order, 'SphL', null)

            // Set cylinder
            self.refraction.right.cylinder = Helpers.getObjPropNumber(order, 'CylR', null)
            self.refraction.left.cylinder = Helpers.getObjPropNumber(order, 'CylL', null)

            // Set axis
            self.refraction.right.axis = Helpers.getObjPropNumber(order, 'AxisR', null)
            self.refraction.left.axis = Helpers.getObjPropNumber(order, 'AxisL', null)

            // Set addition
            self.refraction.right.addition = Helpers.getObjPropNumber(order, 'AddR', null)
            self.refraction.left.addition = Helpers.getObjPropNumber(order, 'AddL', null)

            // Set pd
            self.refraction.right.pd = Helpers.getObjPropNumber(order, 'PdR', null)
            self.refraction.left.pd = Helpers.getObjPropNumber(order, 'PdL', null)

            // Set height
            self.refraction.right.height = Helpers.getObjPropNumber(order, 'HeightR', null)
            self.refraction.left.height = Helpers.getObjPropNumber(order, 'HeightL', null)

            // Set prism1
            self.refraction.right.prism1 = Helpers.getObjPropNumber(order, 'Prism1R', null)
            self.refraction.left.prism1 = Helpers.getObjPropNumber(order, 'Prism1L', null)

            // Set prism2
            self.refraction.right.prism2 = Helpers.getObjPropNumber(order, 'Prism2R', null)
            self.refraction.left.prism2 = Helpers.getObjPropNumber(order, 'Prism2L', null)

            // Set base1 and base2
            self.refraction.right.base1 = Helpers.getObjProp(order, 'Base1R', null)
            self.refraction.left.base1 = Helpers.getObjProp(order, 'Base1L', null)
            self.refraction.right.base2 = Helpers.getObjProp(order, 'Base2R', null)
            self.refraction.left.base2 = Helpers.getObjProp(order, 'Base2L', null)


            // Set individual fields
            Order.setIndividualFields(
                'right',
                Helpers.getObjProp(order, 'IndTypeR'),
                Helpers.getObjProp(order, 'IndR'),
              self.product.right.selectedLensType.LENSCODE,
              self.product.left.selectedLensType.LENSCODE,
            )
            Order.setIndividualFields(
                'left',
                Helpers.getObjProp(order, 'IndTypeL'),
                Helpers.getObjProp(order, 'IndL')
            )

            // Set selected frame type
            const ftyp = Helpers.getObjProp(order, 'FTyp', null)
            const etyp = Helpers.getObjProp(order, 'ETyp', null)
            let frameType = null
            if ((ftyp && ftyp !== '') && (etyp && etyp !== '')){
                frameType = store.getters.getFrameTypeSelection
                    .filter(x => x != undefined)
                    .find(x => x.control == `${ftyp};${etyp}`)
            }
            self.formAndGeometry.selectedFrameType = frameType

            // Set dbl
            self.formAndGeometry.dbl = Helpers.getObjPropNumber(order, 'DBL', null)

            // Set bevel position and value
            const bevelSelection = self.formAndGeometry.bevelPositionSelection.filter(x => x && x !== undefined)
            const bevP = Helpers.getObjPropNumber(order, 'BevP', null)
            const bevM = Helpers.getObjPropNumber(order, 'BevM', null)
            const bevelResult = Order.getBevelPositionAndValue(bevelSelection, bevP, bevM, store.getters.getBevelPositionAutomatic)
            self.formAndGeometry.bevelPositionValue = null
            self.formAndGeometry.selectedBevelPosition = null
            if (bevelResult){
                self.formAndGeometry.bevelPositionValue = bevelResult.bevelValue
                self.formAndGeometry.selectedBevelPosition = bevelResult.bevelPosition
            }
            self.formAndGeometry.frcv = order.Fcrv
            self.formAndGeometry.ztilt = order.FaceFormA
            // Set min edge thicknes
            const drillThicknessRight = Helpers.getObjPropNumber(order, 'DrhThkR', null)
            const drillThicknessLeft = Helpers.getObjPropNumber(order, 'DrhThkL', null)
            self.formAndGeometry.minEdgeThickness = drillThicknessLeft || drillThicknessRight

            // Set shape form (FormR and FormL are encoded in base64)
            const leftForm = Helpers.getObjProp(order, 'FormR', null)
            const rightForm = Helpers.getObjProp(order, 'FormL', null)
            if ((leftForm && leftForm !== '') && (rightForm && rightForm !== '')){

                // Concatenate left and right form with an 'a' character otherwise the decoding is wrong.
                const concatenatedFormBase64 = leftForm + 'a' + rightForm

                // Pass a hex converted string to shape.original.data because when sending
                // the soap request for "scaleShape", the form parameter must be encoded in hex
                const concatenatedFormHex = Helpers.stringToHex(Helpers.base64ToString(concatenatedFormBase64))

                self.form.shape.type = 'OMAVCA'
                self.form.shape.original.data = concatenatedFormHex
                self.form.shape.original.decodedData = Tracer.decodeTracerFormData(concatenatedFormHex)
                self.form.shape.current.data = self.form.shape.original.data
                self.form.shape.current.decodedData = self.form.shape.original.decodedData

                Initializator.setFrameByIdFormData(self.form.shape.current.decodedData)
                Initializator.getLensGeo('left')
                Initializator.getLensGeo('right')
            }

            // Set drill holes
            const drillHolesRight = Helpers.getObjProp(order, 'DrillR', null)
            const drillHolesLeft = Helpers.getObjProp(order, 'DrillL', null)
            Order.setDrillHolesData(drillHolesRight, 'right')
            Order.setDrillHolesData(drillHolesLeft, 'left')
        } catch (error) {
            console.error(error)
            editResponse.addMessages(error)
        }

        return editResponse
    },
    async copyBulkorder(order) {
        const editResponse = new EditResponse()

        try {
            const itemList = Helpers.getObjProp(order, 'ItemListR', null)
            if (itemList === undefined || itemList === null || itemList === '') return
            const translatedItemList = await this.getTranslatedItemList(itemList)

            if (translatedItemList === undefined || translatedItemList === null) return
            order.LenscodeR = translatedItemList[0]['compCode']
            order.Diam1R = translatedItemList[0]['diameter']

            // Set product group
            const group = Helpers.getObjProp(order, 'Group_ExtR', null)
            if (group === undefined || group === null || group === '') return
            self.bulkorder.product.selectedGroup = Bulkorder.getGroupSelection().find(x => x.Value == group)
            await Bulkorder.getLenses()

            // Set lens type
            const compcode = Helpers.getObjProp(order, 'LenscodeR', null)
            if (compcode === undefined || compcode === null || compcode === '') return
            self.bulkorder.product.selectedLensType = Bulkorder.getLensTypeFromCode(compcode)
            Bulkorder.resetGrid()
            Bulkorder.setLensDiameterValues()

            // Set lens diameter
            const diam1 = parseFloat(Helpers.getObjProp(order, 'Diam1R', null))
            if (diam1 === undefined || diam1 === null || diam1 === '' || isNaN(diam1)) return
            if (Bulkorder.isLensDiameterValueInSelection(diam1)){
                self.bulkorder.product.selectedLensDiameter = Bulkorder.getLensDiameterByValue(diam1)

                // Load grid
                await Bulkorder.getGrid()

                // Fill grid
                const allCells = Bulkorder.getAllCells()
                translatedItemList.forEach(component => {

                    // Find cell by matching sphere and cylinder values
                    const foundCell = allCells.find(cell => cell.sphere == component.sphere && cell.cylinder == component.cylinder)
                    if (foundCell !== undefined && foundCell !== null){
                        foundCell.value = component.quantity
                    }
                })
            }
        } catch (error) {
            console.error(error)
            editResponse.addMessages(error)
        }

        return editResponse
    },
    async getTranslatedItemList(itemList) {
        self.orders.isLoading = true
        return store.dispatch('translateBulkorderItemList', {
            itemList: itemList,
        }).then((response) => {
            self.orders.isLoading = false

            return response
        }, e => {
            self.orders.isLoading = false
            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        });
    },
    print(order) {
        let orderNumber = Helpers.getObjProp(order, 'OrdNumbH')

        self.orders.isLoading = true
        store.dispatch('getOverviewOrderSheet', {
            orderNumber: orderNumber
        })
        .then((response) => {
            self.orders.isLoading = false
            if (!response) return
            try {
                Helpers.printBase64File(response)
            } catch (error) {
                store.dispatch('notify', {message: error, duration: 5})
                console.error(error)
            }
        }, e => {
            self.orders.isLoading = false
            store.dispatch('notify', {message: e.data.message})
            console.error(e)
        });
    },
    isOrderBulkorder(order) {
        const orderType = Order.getPropertyInHeader(order, 'RecType')
        if (orderType === null) return false

        return orderType === 'W'
    },
}
