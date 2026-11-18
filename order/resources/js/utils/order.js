import store from '@/store'
import Frame from '@/utils/frame.js'
import Helpers from '@/utils/helpers.js'
import Initializator from '@/utils/initializator.js'
import Tracer from '@/utils/tracer.js'
import ApiService from '@/utils/api';
import Bulkorder from '@/utils/bulkorder.js'
import EditResponse from '@/utils/editResponse.js'
import ShopConfiguration from "./shopConfiguration";

const self = {
    get orders() {return store.state.moduleOrders},
    get evaluator () {return store.state.moduleEvaluator.methods},
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
    get shopConfiguration() {return store.state.moduleOrders.shopConfiguration}
}
export default {
    async edit(order, then = () => {}) {
        const editResponse = new EditResponse()

        try {
            await this.reset()
            let response = await this.editBasicData(order)
            editResponse.addMessages(response.messages)

            if (this.isOrderBulkorder(order))
                response = await this.editBulkorder(order)
            else response = await this.editOrder(order)
            editResponse.addMessages(response.messages)

            // Set editing flag to true
            self.orders.isEditing = true
            self.orders.editingOrder = order

        } catch (error) {
            console.error(error)
            editResponse.addMessage(error)
        }

        return then(editResponse)
    },
    async editBasicData(order) {
        const editResponse = new EditResponse()

        try {
            const remarkData = this.getRemarkData(order.Remark)
            let remark = remarkData.remark
            let additionalRemarkCode = remarkData.additionalRemarkCode
            let entryUser = order.EntryUser
            if(entryUser === 'WEB') entryUser = ''

            let custNumbX = order.CustNumbX
            if(custNumbX !== null && custNumbX !== ''){
                await ShopConfiguration.getUsers()
                custNumbX = self.shopConfiguration.userTableData.rows.find(x => x.id == custNumbX)
            }


            // Set basic data fields
            let orderType = self.basicData.orderTypeSelection.find(x => x.id == order.Rectype)
            if (!orderType) orderType = store.getters.getDefaultOrderType
            self.basicData.selectedOrderType = orderType
            self.basicData.remark = remark
            self.basicData.referenceNumber = order.Reference
            self.basicData.entryUser = entryUser
            self.basicData.custNumbX = custNumbX
            self.basicData.hasContinuedToDetails = true

            // Set additional remark selection
            if (additionalRemarkCode && additionalRemarkCode !== undefined && additionalRemarkCode != '')
                self.basicData.selectedAdditionalRemark = self.basicData.additionalRemarkSelection.find(x => x && x !== undefined && x.code !== undefined && x.code == additionalRemarkCode)

            // Set consumer info fields
            if (order.order_consumer.length > 0){
                let consumer = order.order_consumer[0]
                let consTitle = Helpers.getObjProp(consumer, 'Title', null)
                if (consTitle) self.consumerInfo.selectedTitle = self.consumerInfo.titleSelection.find(x => x.control == consTitle)

                let consLastName = Helpers.getObjProp(consumer, 'Name', null)
                if (consLastName) self.consumerInfo.name = consLastName

                let consFirstName = Helpers.getObjProp(consumer, 'FirstName', null)
                if (consFirstName) self.consumerInfo.firstName = consFirstName

                let consDateOfBirth = Helpers.getObjProp(consumer, 'DateOfBirth', null)
                if (consDateOfBirth) self.consumerInfo.dateOfBirth = consDateOfBirth

                let consPhone = Helpers.getObjProp(consumer, 'Phone', null)
                if (consPhone) self.consumerInfo.phone = consPhone

                let consLang = Helpers.getObjProp(consumer, 'Language', null)
                if (consLang) self.consumerInfo.selectedLanguage = self.consumerInfo.languageSelection.find(x => x.control == consLang)

                let consStreet = Helpers.getObjProp(consumer, 'Street', null)
                if (consStreet) self.consumerInfo.street = consStreet

                let consZip = Helpers.getObjProp(consumer, 'ZipCode', null)
                if (consZip) self.consumerInfo.zipCode = consZip

                let consCity = Helpers.getObjProp(consumer, 'City', null)
                if (consCity) self.consumerInfo.city = consCity
            }

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
    async editOrder(order) {
        const editResponse = new EditResponse()

        try {
            // Set product category options
            self.productCategory.selectedMaterial = self.productCategory.materialSelection.find(x => x.Value == order.order_lens_properties[0].Mat_Ext)
            self.productCategory.selectedGroup = self.productCategory.groupSelection.find(x => x.Value == order.order_lens_properties[0].Group_Ext)
            self.productCategory.selectedSpecial = self.productCategory.specialSelection[0]
            self.productCategory.selectedCorridor = null

            // Set hasLens in product
            self.product.left.hasLens = false
            self.product.right.hasLens = false
            self.product.isSameLeftRight = false
            order.order_lens_properties.forEach(orderLensProperties => {
                let side = orderLensProperties.Side == 'R' ? 'right' : 'left'
                self.product[side].hasLens = true
            })

            const hasVal = val => {
                try {
                    const parsed = parseFloat(val)
                    if (parsed === undefined) return false
                    if (parsed === null) return false
                    if (isNaN(parsed)) return false
                    return true
                } catch (error) {
                    return false
                }
            }
            const hasForcedDecentration = order.order_lens_properties.some(x => hasVal(x.DecentX) || x.DecentY > 0)

            self.initializator.hasStartDelayFinished = true
            await Initializator.getLensTypesOnly()

            order.order_component.forEach(component => {
                let lens = self.erp.getLensTypesOnly.Lens.filter(x => x).find(x => x.LENSCODE == component.Compcode)
                let side = component.Side == 'R' ? 'right' : 'left'

                // Set lens type, stock flag and lenstype selection
                if (lens !== undefined && lens !== null) {
                    self.product[side].selectedLensType = lens
                    self.product[side].isStock = !!+lens.STOCK
                    self.product[side].lensTypeSelection = self.erp.getLensTypesOnly.Lens.filter(x => x.STOCK == lens.STOCK)
                }
            })

            // If no lens type was found, this means there was a missmatch between the data from the order and the data from the LSS.
            const hasLenstype = (lensType) => lensType !== undefined && lensType !== null && lensType !== ''
            if (!hasLenstype(self.product.left.selectedLensType) && !hasLenstype(self.product.right.selectedLensType)){
                editResponse.addMessage(store.getters.translations.notifications.lensTypeDataMissmatch)
            }

            order.order_lens_properties.forEach(orderLensProperties => {
                let side = orderLensProperties.Side == 'R' ? 'right' : 'left'

                // Set geometry thickness
                if (orderLensProperties.Edgethick) { // Edgethick = Minimal edge
                    self.formAndGeometry[side].selectedThicknessOption = self.formAndGeometry[side].thicknessRadioInputData.find(x => x.id == 0)
                    self.formAndGeometry[side].selectedThicknessOption.value = orderLensProperties.Edgethick
                }
                if (orderLensProperties.Centthick) { // Centthick = Dev. center
                    self.formAndGeometry[side].selectedThicknessOption = self.formAndGeometry[side].thicknessRadioInputData.find(x => x.id == 1)
                    self.formAndGeometry[side].selectedThicknessOption.value = orderLensProperties.Centthick
                }

                // Set basecurve, geometry type
                self.formAndGeometry[side].basecurve = orderLensProperties.BaseCurve
                self.formAndGeometry[side].type = orderLensProperties.Cribtype
            })

            self.initializator.hasStartDelayFinished = true
            await Initializator.getRangesCombination()

            // Reset other component lists
            let otherValuesRight = []
            let otherValuesLeft = []

            order.order_component.forEach(component => {
                if (!self.erp.getRangesCombination || self.erp.getRangesCombination === undefined || Object.entries(self.erp.getRangesCombination).length <= 0) return

                // Set tint
                if (self.erp.getRangesCombination.TintList !== undefined && Object.entries(self.erp.getRangesCombination.TintList).length > 0 && !self.product.selectedTint){
                    self.product.selectedTint = self.erp.getRangesCombination.TintList.filter(x => x).find(x => x.CODE == component.Compcode)
                }

                // Set antireflective coating
                if (self.erp.getRangesCombination.CoatList !== undefined && Object.entries(self.erp.getRangesCombination.CoatList).length > 0 && !self.product.selectedAntireflectiveCoating){
                    self.product.selectedAntireflectiveCoating = self.erp.getRangesCombination.CoatList.filter(x => x).find(x => x.CODE == component.Compcode)
                }

                // Set UV
                if (self.erp.getRangesCombination.UVList !== undefined && Object.entries(self.erp.getRangesCombination.UVList).length > 0 && !self.product.selectedUv){
                    self.product.selectedUv = self.erp.getRangesCombination.UVList.filter(x => x).find(x => x.CODE == component.Compcode)
                }

                // Get other
                if (self.erp.getRangesCombination.OtherList !== undefined && Object.entries(self.erp.getRangesCombination.OtherList).length > 0 && component.Comptype == '??'){
                    const val = self.erp.getRangesCombination.OtherList.find(x => x.CODE == component.Compcode)
                    if (val && val !== undefined){
                        if (component.Side == 'R') otherValuesRight.push(val)
                        else otherValuesLeft.push(val)
                    }
                }
            })

            // Set other
            self.product.right.selectedOtherList = otherValuesRight
            self.product.left.selectedOtherList = otherValuesLeft

            order.order_lens_properties.forEach(orderLensProperties => {
                let side = orderLensProperties.Side == 'R' ? 'right' : 'left'

                // Set refraction fields
                self.refraction[side].sphere = orderLensProperties.Sphere
                self.refraction[side].cylinder = orderLensProperties.Cylinder
                self.refraction[side].axis = orderLensProperties.Axis
                self.refraction[side].addition = orderLensProperties.Addition
                self.refraction[side].pd = orderLensProperties.PD
                self.refraction[side].height = orderLensProperties.Height
                self.refraction[side].prism1 = orderLensProperties.Prism1
                self.refraction[side].prism2 = orderLensProperties.Prism2
                self.refraction[side].base1 = orderLensProperties.PrBase1
                self.refraction[side].base2 = orderLensProperties.PrBase2

                // Set individual fields
                let right = '';
                if(self.product?.right?.selectedLensType?.LENSCODE)
                  right = self.product.right.selectedLensType.LENSCODE;
              let left = '';
              if(self.product?.left?.selectedLensType?.LENSCODE)
                left = self.product.left.selectedLensType.LENSCODE;
                this.setIndividualFields(
                    side,
                    Helpers.getObjProp(orderLensProperties, 'IndType'),
                    Helpers.getObjProp(orderLensProperties, 'Individual'),
                  right,
                  left,
                )

                // Set lens diameters
                self.formAndGeometry[side].lensDiameter = orderLensProperties.Diam1
                self.formAndGeometry[side].opticalDiameter = orderLensProperties.Diam2
                self.formAndGeometry[side].elipticalDiameter = orderLensProperties.Diam3
            })

            let indexes = []
            let lensFilters = []
            let sides = ['left','right'].forEach(side => {
                let range = self.refraction[side].range

                // Push only unique index values
                if (!indexes.filter(x => x).find(x => x == range.INDEX_SF6_11))
                    indexes.push(range.INDEX_SF6_11)

                // Push only lens filter values
                if (!lensFilters.filter(x => x).find(x => x == range.LENSFILTER))
                    lensFilters.push(range.LENSFILTER)
            })

            // Set index
            let selectedIndex = null
            if (indexes.length == 1)
                selectedIndex = self.productCategory.indexSelection.filter(x => x).find(x => x.Value == indexes[0])
            self.productCategory.selectedIndex = selectedIndex

            // Set lens filter
            let selectedLensFilter = null
            if (lensFilters.length == 1)
                selectedLensFilter = self.productCategory.lensFilterSelection.filter(x => x).find(x => x.Value == lensFilters[0])
            self.productCategory.selectedLensFilter = selectedLensFilter

            // Set tint remark field
            const remarkData = this.getRemarkData(order.Remark)
            self.product.tintRemark = remarkData.tintRemark

            // Select frame
            if (order.FrameNo && !hasForcedDecentration){
                self.frame.search.frameCode = order.FrameNo
                Frame.onSearch(response => {
                    if (self.frame.frameTableData.rows.length > 0){
                        self.frame.hasFrameBeenSelected = true
                        self.frame.selectedFrame = self.frame.frameTableData.rows[0]
                    }
                })

                // Run functionallity for selecting a frame, like it is in Initializator.vue
                self.initializator.skipOnceSelectedFrameFunctionallity = true
                Initializator.clearFormAndGeometryDrawingFields()
                await Initializator.getFrameByID()
                self.initializator.lensGeoThrottleTimerRight = Helpers.throttleDelay(self.initializator.lensGeoThrottleTimerRight, () => Initializator.getLensGeo('right'))
                self.initializator.lensGeoThrottleTimerLeft = Helpers.throttleDelay(self.initializator.lensGeoThrottleTimerLeft, () => Initializator.getLensGeo('left'))
            }

            // Set form and geometry fields
            let orderShape = order.order_shape.length > 0 ? order.order_shape[0] : null
            if (orderShape && !hasForcedDecentration){
                self.formAndGeometry.fPinbevel = orderShape.FPinB
                self.formAndGeometry.pinbevel = orderShape.PinB
                self.formAndGeometry.isPolish = !!+orderShape.Polish
                self.formAndGeometry.grvWidthInput = orderShape.GWidth
                self.formAndGeometry.grvDepthInput = orderShape.GDepth
                self.formAndGeometry.dbl = orderShape.DBL

              self.formAndGeometry.panto = orderShape.Panto
              self.formAndGeometry.frcv = orderShape.Frcv
              self.formAndGeometry.ztilt = orderShape.FaceFormA
                // self.formAndGeometry.isEdgePinbevel = ?

                // Set frame type
                let frameType = store.getters.getFrameTypeSelection
                    .filter(x => x != undefined)
                    .find(x => x.control == `${orderShape.FTyp};${orderShape.Etyp}`)
                self.formAndGeometry.selectedFrameType = frameType

                // Set bevel position and value
                const bevelSelection = self.formAndGeometry.bevelPositionSelection.filter(x => x && x !== undefined)
                const bevelResult = this.getBevelPositionAndValue(bevelSelection, orderShape.BevP, orderShape.BevM, store.getters.getBevelPositionAutomatic)
                self.formAndGeometry.bevelPositionValue = null
                self.formAndGeometry.selectedBevelPosition = null
                if (bevelResult){
                    self.formAndGeometry.bevelPositionValue = bevelResult.bevelValue
                    self.formAndGeometry.selectedBevelPosition = bevelResult.bevelPosition
                }

                // If shape form was saved
                if (orderShape.Form){

                    // Interpolate form
                    await Initializator.interpolate(orderShape.Trctype, orderShape.Form)
                    Initializator.setFrameByIdFormData(self.form.shape.current.decodedData)
                    Initializator.getLensGeo('left')
                    Initializator.getLensGeo('right')
                }
                order.order_shape.forEach(async orderShape => {
                    let side = orderShape.Side == 'R' ? 'right' : 'left'

                    // Set form and geometry fields
                    self.formAndGeometry[side].boxWidth = orderShape.HBox
                    self.formAndGeometry[side].boxHeight = orderShape.VBox

                    // Set drill holes
                    await store.dispatch('convertDrillHoles', {
                        'drille': Helpers.stringToHex(orderShape.Drill),
                        'trcstr': orderShape.Form,
                        'type': orderShape.Trctype,
                        'side': orderShape.Side,
                    }).then(response => this.setDrillHolesData(response, side))

                    // Set min edge thickness. Value is stored inside drill holes string data
                    if (!self.formAndGeometry.minEdgeThickness || self.formAndGeometry.minEdgeThickness === undefined){
                        let minEdgeThicknessValue = null
                        try {
                            if (Helpers.getObjProp(orderShape, 'Drill', false) && orderShape.Drill.includes('_DRHTHK=')){
                                const drhthk = orderShape.Drill.split(';').find(x => x.includes('_DRHTHK='))
                                const split = drhthk.split('_DRHTHK=')
                                if (split.length > 1)
                                    minEdgeThicknessValue = split[1]
                            }
                        } catch (error) {
                            console.error(error)
                        }
                        self.formAndGeometry.minEdgeThickness = minEdgeThicknessValue
                    }
                })
            }

            order.order_lens_properties.forEach(orderLensProperties => {
                let side = orderLensProperties.Side == 'R' ? 'right' : 'left'

                // Set isOptimized
                if (!hasForcedDecentration) {
                    self.formAndGeometry[side].isOptimized = Helpers.getObjProp(orderLensProperties, 'CTO', false) == 1? true : false
                }

                // Forced Decentration Mode
                if (hasForcedDecentration) {
                    self.formAndGeometry[side].forcedDecentration.decentrationX = Helpers.getObjProp(orderLensProperties, 'DecentX', null)
                    self.formAndGeometry[side].forcedDecentration.decentrationY = Helpers.getObjProp(orderLensProperties, 'DecentY', null)
                }
            })

            // Set Forced Decentration Mode to enabled
            self.formAndGeometry.options.forcedDecentration = hasForcedDecentration
        } catch (error) {
            console.error(error)
            editResponse.addMessage(error)
        }

        return editResponse
    },
    async editBulkorder(order) {
        const editResponse = new EditResponse()

        try {
            // Set product group
            const group = order.order_lens_properties[0].Group_Ext
            self.bulkorder.product.selectedGroup = Bulkorder.getGroupSelection().find(x => x.Value == group)
            await Bulkorder.getLenses()

            // Set lens type
            const compcode = order.order_component[0].Compcode
            self.bulkorder.product.selectedLensType = Bulkorder.getLensTypeFromCode(compcode)
            Bulkorder.resetGrid()
            Bulkorder.setLensDiameterValues()

            // Set lens diameter
            const diam1 = order.order_component[0].Diam1
            if (Bulkorder.isLensDiameterValueInSelection(diam1)){
                self.bulkorder.product.selectedLensDiameter = Bulkorder.getLensDiameterByValue(diam1)

                // Load grid
                await Bulkorder.getGrid()

                // Fill grid
                const allCells = Bulkorder.getAllCells()
                order.order_component.filter(x => x.Side === 'B' && x.ItemNumb !== null && x.ItemNumb !== '')
                .forEach(component => {

                    // Find cell by matching item numbers
                    const foundCell = allCells.find(cell => cell.itemNumber == component.ItemNumb)
                    if (foundCell !== undefined && foundCell !== null){
                        foundCell.value = component.Quantity
                    }
                })
            }
        } catch (error) {
            console.error(error)
            editResponse.addMessages(error)
        }

        return editResponse
    },
    getRemarkData(remarkString) {
        const getStringBetween = (str, start, end) => str.substring(str.indexOf(start) + 1, str.lastIndexOf(end))

        // Remark, tint remark and additional remark are all stored in the remark field
        // Get tint remark - stored as Tint:
        let tintRemarkSplit = remarkString ? remarkString.split(' Tint: ') : ''
        let tintRemark = tintRemarkSplit[1] || ''

        // Get additional remark code - is stored between two # symbols
        let remarkSplit = tintRemarkSplit[0] || ''
        let additionalRemarkCode = getStringBetween(remarkSplit, '#', '#')
        if (additionalRemarkCode.length === 0 && remarkSplit.indexOf('#REMARKKEY#') > -1) {
            additionalRemarkCode = remarkSplit.substring(remarkSplit.indexOf('#REMARKKEY#') + ('#REMARKKEY#').length, 3)
        }

        // Get base remark
        remarkSplit = remarkSplit.replace('#REMARKKEY#' + additionalRemarkCode, '')
        remarkSplit = remarkSplit.replace('#' + additionalRemarkCode + '#', '')
        let remark = remarkSplit

        let additionalRemarkDescr = '';
        if (additionalRemarkCode.length !== 0) {
            const el = self.basicData.additionalRemarkSelection.find( (el) => el.code === additionalRemarkCode);
            if(el)
                additionalRemarkDescr = el.description;
        }
        return {
            remark: remark,
            tintRemark: tintRemark,
            additionalRemarkCode: additionalRemarkCode,
            additionalRemarkDescr: additionalRemarkDescr
        }
    } ,
    setDrillHolesData(drillHolesString, side){
        if (!drillHolesString || drillHolesString === undefined || drillHolesString == '' || drillHolesString == [] || drillHolesString  == {}) return
        try {
            drillHolesString.split(';').forEach((drillHole, index) => {
                const drillHoleData = drillHole.split(',')
                self.formAndGeometry.drillHoles[index][side] = {
                    'startX': drillHoleData[0],
                    'startY': drillHoleData[1],
                    'endX': drillHoleData[2],
                    'endY': drillHoleData[3],
                    'diam': drillHoleData[4],
                    'depth': drillHoleData[5],
                    'direction': drillHoleData[7],
                }
            })
        } catch (error) {console.error(error)}
    },
    getBevelPositionAndValue(selection, bevP, bevM, defaultItem) {
        if (!selection || selection == undefined || selection.length == 0) {
            console.warn("Missing selection: " + selection)
            return null
        }
        if (!bevP || bevP === undefined) {
            console.warn("Missing bevP: " + bevP)
            return null
        }
        if (!bevM || bevM === undefined) {
            console.warn("Missing bevM: " + bevM)
            return null
        }
        if (isNaN(bevP)) {
            console.warn("bevP is not a number: " + bevP)
            return null
        }
        if (isNaN(bevM)) {
            console.warn("bevM is not a number: " + bevM)
            return null
        }
        if (!defaultItem || defaultItem === undefined) {
            console.warn("Missing defaultItem: " + defaultItem)
            return null
        }

        let _bevelPosition = defaultItem
        let _bevelValue = bevM
        let _bevP = parseFloat(bevP)
        let _bevM = parseFloat(bevM)

        try {
            for (let i = 0; i < selection.length; i++) {
                const element = selection[i];

                // Validate element control property
                if (!element || element === undefined ||
                    !element.control || element.control == undefined
                ) {
                    console.warn("element does not include control property - " + element)
                    continue
                }

                // Validate control format
                const control = element.control.toString()
                if (!control.includes(';')){
                    console.warn("element control property is not in correct form - " + element)
                    continue
                }

                // Slice control property
                const split = control.split(';')
                const first = split[0]
                const second = split[1]

                // follow front & follow back & frame curve
                if (((_bevP == 1 && first == 1) || (_bevP == 3 && first == 3)  || (_bevP == 5 && first == 5)) && second == 1){
                    _bevelPosition = element
                    _bevelValue = _bevM
                    break;
                }

                // values with 2 control numbers
                if (split.length == 2 && first == _bevP && second == _bevM){
                    _bevelPosition = element
                    _bevelValue = null
                    break
                }

                // values with 3 control numbers
                if (split.length == 3){
                    let third = split[2]

                    if (first == _bevP && third == _bevM){
                        _bevelPosition = element
                        _bevelValue = null
                        break
                    }
                }
            }
        } catch (error) {
            // on error return default values
            _bevelPosition = defaultItem
            _bevelValue = _bevM
            console.error(error)
        }

        return {
            bevelPosition: _bevelPosition,
            bevelValue: _bevelValue
        }
    },
    setIndividualFields(side, individualType, individualString, lenscoder = '', lenscodel = '') {
        self.orders.isLoading = true
        store.dispatch('translateIndividual', {
            side: side,
            indtype: individualType,
            individualstring: individualString,
           lenscoder: lenscoder,
          lenscodel: lenscodel
        }).then((response) => {
            self.orders.isLoading = false

            try {
              if(response['indi'].Ranges[0]) {
                if (response['indi'].Ranges[0]['INDIVIDUALDETAIL']) {
                  const indMap = response['indi'].Ranges[0]['INDIVIDUALDETAIL']
                  let right = []
                  let left = []
                  let common = []
                  for (let key in indMap['PARAMETER']) {
                    let indKey = indMap['PARAMETER'][key]
                    if (indMap[indKey]) {
                      let s = {
                        name: indMap['PARAMETER'][key],
                        get label() {
                            if(indMap[indKey]['DESCRIPTION'] && indMap[indKey]['DESCRIPTION'] !== '')
                                return indMap[indKey]['DESCRIPTION']
                            return store.getters.translations.newOrder.details.individual.placeholders[indMap['PARAMETER'][key]]
                        },
                        pos: indMap[indKey]['POS'],
                        required: indMap[indKey]['REQUIRED'],
                      }
                      if (indMap[indKey]['MIN']) {
                        s['min'] = indMap[indKey]['MIN']
                        s['max'] = indMap[indKey]['MAX']
                        s['step'] = 0.1
                        s['decimals'] = 2
                      } else {
                        s['validChars'] = indMap[indKey]['VALIDCHARS']
                      }
                      if (indMap[indKey]['RELATION'] === 'S') {
                        right.push(s)
                        left.push(s)
                      } else {
                        common.push(s)
                      }
                    }
                    self.orders.details.individual.fieldMaps.right = right
                    self.orders.details.individual.fieldMaps.left = left
                    self.orders.details.individual.fieldMaps.common = common
                  }
                }
              }

                // Key-pair data where the key is the param of the individual field
                let individualData = response['translated']
                if (!individualData || individualData === undefined) return
                Object.entries(individualData).forEach(individualEntry => {
                    const fieldName = individualEntry[0]
                    const fieldValue = individualEntry[1]
                    // Individual component common fields
                    if (self.individual.definedFields &&
                        self.individual.definedFields !== undefined &&
                        self.individual.definedFields.length > 0
                    ){
                        self.individual.definedFields.map(definedField => {
                            if ((definedField.name && definedField.name !== undefined && definedField.name == fieldName) &&
                                (!definedField.value || definedField.value === undefined))
                                definedField.value = fieldValue
                            return definedField
                        })
                    }

                    // Individual component side fields
                    if (self.individual[side].definedFields &&
                        self.individual[side].definedFields !== undefined &&
                        self.individual[side].definedFields.length > 0
                    ){
                        self.individual[side].definedFields.map(definedField => {
                            if ((definedField.name && definedField.name !== undefined && definedField.name == fieldName) &&
                                (!definedField.value || definedField.value === undefined)){
                                    definedField.value = fieldValue
                                }

                            return definedField
                        })
                    }
                })
            } catch (error) {
                console.error(error)
            }
        }, e => {
            self.orders.isLoading = false
            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        });
    },
    save(then = (response) => {}, error = (error) => {}){
        let saveOrderData = store.getters.isOrderTypeBulkorder ? this.getBulkorderSaveData() : this.getSaveOrderData()

        // Send to backend
        self.orders.isLoading = true
        return store.dispatch(saveOrderData.dispatchAction, saveOrderData.params).then((response) => {
            self.orders.isLoading = false
            self.orders.isEditing = false
            self.orders.isDraft = false
            self.orders.editingOrder = null
            then(response)
        }, e => {
            self.orders.isLoading = false
            self.orders.isDraft = false
            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e.data)
            error(e)
        });
    },
    getSaveOrderData() {
        const authData = ApiService.addAuthParameters({});

        let custNumbX = ''
        if(self.basicData.custNumbX !== null){
            custNumbX = self.basicData.custNumbX.id
        }
        // Other
        let params = {
            customer: authData.uid,
            custNumbX: custNumbX,
            v_consRes1: '',
            v_consRes2: '',
            v_consRes3: '',
            v_consRes4: '',
            v_consRes5: '',
            v_consRes6: '',
            v_consRes7: '',
            v_consRes8: '',
            v_consRes9: '',
            v_consRes10: '',
        }

        // Basic data
        params.reference = self.basicData.referenceNumber
        params.entryUser = self.basicData.entryUser
        params.remark = self.basicData.remark
        params.ordertype = self.basicData.selectedOrderType.id
        let additionalRemark = Helpers.getObjProp(self.basicData.selectedAdditionalRemark, 'code', null)
        if (additionalRemark) params.additionalRemark = additionalRemark

        // Consumer info
        let consTitle = Helpers.getObjProp(self.consumerInfo.selectedTitle, 'control', null)
        let consLastName = Helpers.getObjProp(self.consumerInfo, 'name', null)
        let consFirstName = Helpers.getObjProp(self.consumerInfo, 'firstName', null)
        let consDateOfBirth = Helpers.getObjProp(self.consumerInfo, 'dateOfBirth', null)
        let consPhone = Helpers.getObjProp(self.consumerInfo, 'phone', null)
        let consLang = Helpers.getObjProp(self.consumerInfo.selectedLanguage, 'control', null)
        let consStreet = Helpers.getObjProp(self.consumerInfo, 'street', null)
        let consZip = Helpers.getObjProp(self.consumerInfo, 'zipCode', null)
        let consCity = Helpers.getObjProp(self.consumerInfo, 'city', null)
        if (consTitle) params.consTitle = consTitle
        if (consLastName) params.consLastName = consLastName
        if (consFirstName) params.consFirstName = consFirstName
        if (consDateOfBirth) params.consDateOfBirth = consDateOfBirth
        if (consPhone) params.consPhone = consPhone
        if (consLang) params.consLang = consLang
        if (consStreet) params.consStreet = consStreet
        if (consZip) params.consZip = consZip
        if (consCity) params.consCity = consCity

        // Product category
        params.material = Helpers.getObjProp(self.productCategory.selectedMaterial, 'Value', 2)
        params.group = Helpers.getObjProp(self.productCategory.selectedGroup, 'Value', 0)
        params.index = Helpers.getObjProp(self.productCategory.selectedIndex, 'Value', 'X')
        params.properties = Helpers.getObjProp(self.productCategory.selectedSpecial, 'Value', 'X')
        params.filter = Helpers.getObjProp(self.productCategory.selectedLensFilter, 'Value', 'X')

        // Product
        params.L = + self.product.left.hasLens
        params.R = + self.product.right.hasLens
        params.tint = Helpers.getObjProp(self.product.selectedTint,'CODE', 0)
        params.tintdescrr = Helpers.getObjProp(self.product.selectedTint,'DESCR')
        params.tintdescrl = Helpers.getObjProp(self.product.selectedTint,'DESCR')
        params.tintremark = self.product.tintRemark
        params.coating = Helpers.getObjProp(self.product.selectedAntireflectiveCoating,'CODE', 0)
        params.coatdescrr = Helpers.getObjProp(self.product.selectedAntireflectiveCoating,'DESCR')
        params.coatdescrl = Helpers.getObjProp(self.product.selectedAntireflectiveCoating,'DESCR')
        params.otherListr = self.product.right.selectedOtherList.map(x => {
            return {code: Helpers.getObjProp(x, 'CODE'), description: Helpers.getObjProp(x, 'DESCR')}
        })
        params.otherListl = self.product.left.selectedOtherList.map(x => {
            return {code: Helpers.getObjProp(x, 'CODE'), description: Helpers.getObjProp(x, 'DESCR')}
        })
        params.uv = Helpers.getObjProp(self.product.selectedUv,'CODE', 0)
        params.uvdescrr = Helpers.getObjProp(self.product.selectedUv,'DESCR')
        params.uvdescrl = Helpers.getObjProp(self.product.selectedUv,'DESCR')

        // Frame
        params.frameNo = ''
        params.v_fReference = ''
        if (self.frame.selectedFrame && store.getters.isFrameAvailable){
            params.frameNo = self.frame.selectedFrame.rawData.FRAMECODE.trim()
            params.v_fReference = self.frame.selectedFrame.rawData.FREFERENCE
        }

        // Shape
        params.tracedata = self.form.shape.original.data
        params.trctype = self.form.shape.type
        params.interpolateForm = self.form.shape.current.data

        params['individual'] = { common: [], right: [], left: [] }
        // Individual - common fields
        if (self.individual.definedFields){
          self.individual.definedFields.forEach(individualField => {
              params['individual']['common'].push({
                name:  individualField.name,
                position: individualField['data']['POS'],
                value: Helpers.getObjProp(individualField, 'value', null)
              })
            })
        }

        // Individual - left fields
        params.indtypel = Helpers.getObjProp(self.individual.left, 'type')
        if (self.individual.left.definedFields){
            self.individual.left.definedFields.forEach(individualField => {
              params['individual']['left'].push({
                name:  individualField.name,
                position: individualField['data']['POS'],
                value: Helpers.getObjProp(individualField, 'value', null)
              })
            })
        }

        // Individual - right fields
        params.indtyper = Helpers.getObjProp(self.individual.right, 'type')
        if (self.individual.right.definedFields){
            self.individual.right.definedFields.forEach(individualField => {
              params['individual']['right'].push({
                name:  individualField.name,
                position: individualField['data']['POS'],
                value: Helpers.getObjProp(individualField, 'value', null)
              })
            })
        }

        // Form and Geometry
        params.v_pinbevel = + self.formAndGeometry.isEdgePinbevel || false
        params.fpinb_def = self.formAndGeometry.fPinbevel
        params.pinb_def = self.formAndGeometry.pinbevel
        params.v_polish = + self.formAndGeometry.isPolish || false
        params.v_grvWidth = self.formAndGeometry.grvWidthInput
        params.v_grvDepth = self.formAndGeometry.grvDepthInput
        params.v_dbl = self.formAndGeometry.dbl
        params.v_drillThk = self.formAndGeometry.minEdgeThickness
        params.v_frametype = Helpers.getObjProp(self.formAndGeometry.selectedFrameType, 'type', store.getters.getFrameTypeMetal.type)
        params.v_bevelpos = Helpers.getObjProp(self.formAndGeometry.selectedBevelPosition, 'type', store.getters.getBevelPositionAutomatic.type)
        params.v_bevm = self.formAndGeometry.bevelPositionValue
        params.has_forcedDecentration = self.formAndGeometry.options.forcedDecentration
        params.panto = self.formAndGeometry.panto
        params.frcv = self.formAndGeometry.frcv
        params.ztilt= self.formAndGeometry.ztilt
        // Form and Geometry - left
        params.basecurvel = self.product.left.hasLens ? self.formAndGeometry.left.basecurve : null
        params.cribtypel = self.product.left.hasLens ? self.formAndGeometry.left.type : null
        params.v_hboxl = self.product.left.hasLens ? self.formAndGeometry.left.boxWidth : null
        params.v_vboxl = self.product.left.hasLens ? self.formAndGeometry.left.boxHeight : null
        params.v_ctol = self.product.left.hasLens ? + self.formAndGeometry.left.isOptimized : null
        params.v_fdecxl = self.product.left.hasLens ? self.formAndGeometry.left.decentrationX : null
        params.v_fdecyl = self.product.left.hasLens ? self.formAndGeometry.left.decentrationY : null
        params.forcedDecentration_decentrationxl = self.formAndGeometry.left.forcedDecentration.decentrationX
        params.forcedDecentration_decentrationyl = self.formAndGeometry.left.forcedDecentration.decentrationY

        // Form and Geometry - right
        params.basecurver = self.product.right.hasLens ? self.formAndGeometry.right.basecurve : null
        params.cribtyper = self.product.right.hasLens ? self.formAndGeometry.right.type : null
        params.v_hboxr = self.product.right.hasLens ? self.formAndGeometry.right.boxWidth : null
        params.v_vboxr = self.product.right.hasLens ? self.formAndGeometry.right.boxHeight : null
        params.v_ctor = self.product.right.hasLens ? + self.formAndGeometry.right.isOptimized : null
        params.v_fdecxr = self.product.right.hasLens ? self.formAndGeometry.right.decentrationX : null
        params.v_fdecyr = self.product.right.hasLens ? self.formAndGeometry.right.decentrationY : null
        params.forcedDecentration_decentrationxr = self.formAndGeometry.right.forcedDecentration.decentrationX
        params.forcedDecentration_decentrationyr = self.formAndGeometry.right.forcedDecentration.decentrationY

        // Form And Geometry - Drill holes
        let sides = ['R', 'L'].forEach(side => {
            self.formAndGeometry.drillHoles.forEach((drillHole, index) => {
                let number = index + 1
                let newSide = side == 'R' ? 'right' : 'left'
                params['v_drillsX' + number + side] = drillHole[newSide].startX
                params['v_drillsY' + number + side] = drillHole[newSide].startY
                params['v_drilleX' + number + side] = drillHole[newSide].endX
                params['v_drilleY' + number + side] = drillHole[newSide].endY
                params['v_drilldi' + number + side] = drillHole[newSide].diam
                params['v_drillde' + number + side] = drillHole[newSide].depth || 0
                params['v_direction' + number + side] = drillHole[newSide].direction || 'T'
            })
        })

        // LEFT LENS
        if (self.product.left.hasLens){
            params = {
                ...params,

                // Product
                lenstypel: Helpers.getObjProp(self.product.left.selectedLensType,'LENSCODE'),
                lensdescrl: Helpers.getObjProp(self.product.left.selectedLensType,'LENSDESCRIPTION'),
                stockl: self.product.left.isStock ? true : null,

                // Refraction
                addDegl: self.refraction.left.addDeg,
                spherel: self.refraction.left.sphere,
                cylinderl: self.refraction.left.cylinder,
                axisl: self.refraction.left.axis,
                additionl: self.refraction.left.addition,
                prism1l: self.refraction.left.prism1,
                prism2l: self.refraction.left.prism2,
                pdl: self.refraction.left.pd,
                heightl: self.refraction.left.height,
                base1l: self.refraction.left.base1,
                base2l: self.refraction.left.base2,
            }

            // Form And Geometry
            let lensDiameter = self.formAndGeometry.left.lensDiameter
            let opticalDiameter = self.formAndGeometry.left.opticalDiameter
            let elipticalDiameter = self.formAndGeometry.left.elipticalDiameter
            params.diam1l = lensDiameter
            params.diam2l = opticalDiameter

            params.rundlList = lensDiameter
            params.v_diamlCTO = lensDiameter
            if (self.formAndGeometry.left.type == 'O'){
                params.selectDiamL = 3 //Liste Oval
                params.ellipselList = lensDiameter,
                params.ovall = 'on'
                params.diam3l = elipticalDiameter
            }else {
                params.selectDiamL = 2 // Manuelle Eingabe Rund
                params.rundl = lensDiameter,
                params.ovall = 'off'
            }
            let thicknessId = Helpers.getObjProp(self.formAndGeometry.left.selectedThicknessOption,'id', null)
            if (thicknessId || thicknessId == 0){
                params.selectThicknessL = thicknessId == 0 ? 1 : 2

                if (params.selectThicknessL == 1) {// 1 = minedgel
                    params.minedgel = Helpers.getObjProp(self.formAndGeometry.left.selectedThicknessOption, 'value')
                    params.devcenterl = null
                }
                if (params.selectThicknessL == 2) {// 2 = devcenterl
                    params.minedgel = null
                    params.devcenterl = Helpers.getObjProp(self.formAndGeometry.left.selectedThicknessOption, 'value')
                }
            }
        }

        // RIGHT LENS
        if (self.product.right.hasLens){
            params = {
                ...params,

                // Product
                lenstyper: Helpers.getObjProp(self.product.right.selectedLensType,'LENSCODE'),
                lensdescrr: Helpers.getObjProp(self.product.right.selectedLensType,'LENSDESCRIPTION'),
                stockr: self.product.right.isStock ? true : null,

                // Refraction
                addDegr: self.refraction.right.addDeg,
                spherer: self.refraction.right.sphere,
                cylinderr: self.refraction.right.cylinder,
                axisr: self.refraction.right.axis,
                additionr: self.refraction.right.addition,
                prism1r: self.refraction.right.prism1,
                prism2r: self.refraction.right.prism2,
                pdr: self.refraction.right.pd,
                heightr: self.refraction.right.height,
                base1r: self.refraction.right.base1,
                base2r: self.refraction.right.base2,
            }

            // Form And Geometry
            let lensDiameter = self.formAndGeometry.right.lensDiameter
            let opticalDiameter = self.formAndGeometry.right.opticalDiameter
            let elipticalDiameter = self.formAndGeometry.right.elipticalDiameter
            params.diam1r = lensDiameter
            params.diam2r = opticalDiameter

            params.rundrList = lensDiameter
            params.v_diamrCTO = lensDiameter
            if (self.formAndGeometry.right.type == 'O'){
                params.selectDiamR = 3 //Liste Oval
                params.ellipserList = lensDiameter,
                params.ovalr = 'on'
                params.diam3r = elipticalDiameter
            }else {
                params.selectDiamR = 2 // Manuelle Eingabe Rund
                params.rundr = lensDiameter,
                params.ovalr = 'off'
            }
            let thicknessId = Helpers.getObjProp(self.formAndGeometry.right.selectedThicknessOption,'id', null)
            if (thicknessId || thicknessId == 0){
                params.selectThicknessR = thicknessId == 0 ? 1 : 2

                if (params.selectThicknessR == 1) {// 1 = minedger
                    params.minedger = Helpers.getObjProp(self.formAndGeometry.right.selectedThicknessOption, 'value')
                    params.devcenterr = null
                }
                if (params.selectThicknessR == 2) {// 2 = devcenterr
                    params.minedger = null
                    params.devcenterr = Helpers.getObjProp(self.formAndGeometry.right.selectedThicknessOption, 'value')
                }
            }
        }

        // If the order is being edited, pass the original order in the parameters and change dispatch action
        let dispatchAction = 'saveOrder'
        params.action = 'new'
        params.orderbuttonSave = 1

        if (self.orders.isEditing) {
            dispatchAction = 'editOrder'
            params.action = 'edit'
            params.order = self.orders.editingOrder
        }

        if(self.orders.isDraft){
            params.action = 'draft'
        }

        return {
            params: params,
            dispatchAction: dispatchAction
        }
    },
    getBulkorderSaveData() {
        const authData = ApiService.addAuthParameters({});

        const tommorow = new Date()
        tommorow.setDate(tommorow.getDate() + 1)
        const date = tommorow.getDay() + "." + tommorow.getMonth() + "." + tommorow.getFullYear()

        let custNumbX = ''
        if(self.basicData.custNumbX !== null){
            custNumbX = self.basicData.custNumbX.id
        }
        let params = {
            ...Bulkorder.getParsedCellValuesForSaving(),
            action: self.orders.isEditing ? 'edit' : 'new',
            h_customer: authData.uid,
            h_branch: "",
            h_reference: self.basicData.referenceNumber,
            h_requDelvDate: date,
            h_entryUser: self.basicData.entryUser,
            custNumbX: custNumbX,
            h_comment: self.basicData.remark,
            h_reference1: "",
            h_reference2: "",
            h_test: "",
            h_lensgroup: self.bulkorder.product.selectedGroup.Value,
            v_lenscode: Bulkorder.getLensCode(),
            v_diameter: Bulkorder.getLensDiameter(),
            v_side: self.bulkorder.product.side,
            lensDescr: Bulkorder.getLensType(),
            ordnumb: self.orders.isEditing ? self.orders.editingOrder.OrdNumb : ''
        }

        let dispatchAction = self.orders.isEditing ?  'editBulkorder' : 'saveBulkorder'
        return {
            params: params,
            dispatchAction: dispatchAction
        }
    },
    print(order) {
        let orderNumber = Helpers.getObjProp(order, 'OrdNumb')

        self.orders.isLoading = true
        store.dispatch('getOrderbasketOrderSheet', {
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
    async reset(excludeResettingProperties = [], options = {}) {
        // ==Reset fields==

        // Using a copy of the original state, copy all variables to the current state
        // while keeping all current variables inside of 'exclude'
        let exclude = ['inTesting', 'stateCopy', 'erp.getIni',
            'details.isComponentEnabled', 'details.components',
            'isEditing','editingOrder',

            // selection fields
            'shopConfiguration.userTableData',
            'basicData.orderTypeSelection',
            'consumerInfo.languageSelection',
            'consumerInfo.titleSelection',
            'details.formAndGeometry.frameTypeSelection',
            'details.formAndGeometry.bevelPositionSelection',
            'details.formAndGeometry.defaultMinThk',
            'details.frame.brandSelection',
            'details.productCategory.lensFilterSelection',
            'details.productCategory.sameIndexBothSides'
        ]
        exclude = exclude.concat(excludeResettingProperties)
        var state = JSON.parse(JSON.stringify(self.orders.stateCopy))
        exclude.forEach(x => {
            try {
                let value = eval(`state.${x}`)
                let originalValue = eval(`self.orders.${x}`)

                if (value === undefined && originalValue === undefined) return

                // Parsing with JSON.stringify won't include functions. For this,
                // copy any functions from the original state.
                eval(`state.${x} = originalValue`)
            } catch (error) {
                console.error(error)
            }
        })

        // Pass the new state to current
        store.state.moduleOrders = state

        // ===Set default values===
        const is = (prop) => prop || prop === undefined ? true : false
        if (is(options.setInitialSelectedOrderType)) Initializator.setInitialSelectedOrderType()
        if (is(options.setInitialThicknessOption)) Initializator.setInitialThicknessOption()
        if (is(options.setInitialFrameAndBevel)) Initializator.setInitialFrameAndBevel()
        store.commit('setDashboardTitle', {
            title: () => store.getters.translations.newOrder.title,
            subTitle: () => store.getters.getOrderTypeTitle
        })
        await Initializator.getIniProperties()
        await Initializator.getRemarkKeyData()
        //await Initializator.getIni()
        //await Initializator.assignIniValues()
        if (is(options.makeEmptyOrderCopy)) Initializator.makeEmptyOrderCopy()
        await Initializator.getSalesFramesSupplierList()
        await Initializator.getLensFilter()
        await Initializator.getLensTypesOnly()
        self.initializator.hasStartDelayFinished = true
    },
    isOrderEvaluated() {
        // The main evaluation function for saving an order.

        store.getters.resetAllErrorMessages

        // Bulkorder evaluation
        if (store.getters.isOrderTypeBulkorder){
            Bulkorder.resetAllErrorMessages()
            if (!this.areDraftFieldsEvaluated()) return false
            if (!this.isRemarkEvaluated()) return false
            if (!this.hasContinuedToDetails()) return false
            if (!this.hasGridData()) return false
            if (!this.areAllGridValuesPositive()) return false
            return true
        }

        // Check the cylinder-sphere power values. If there is an error, it will get
        // picked up inside the this.isErrorPresent() function.
        self.evaluator.cylinderSpherePowerEvaluation('left', self.erp.left.diamlist)
        self.evaluator.cylinderSpherePowerEvaluation('right', self.erp.right.diamlist)

        if (!this.areDraftFieldsEvaluated()) return false
        if (!this.isRemarkEvaluated()) return false
        if (!this.hasContinuedToDetails()) return false
        if (!this.isFrameSelected()) return false
        if (!this.areRequiredFieldsEvaluated()) return false
        if (!this.areFieldsInRange()) return false
        if (!this.areIndividualFieldsEvaluated()) return false
        if (this.isErrorPresent()) return false
        return true
    },
    areAllGridValuesPositive() {
        if (Bulkorder.areAllCellValuesPositive()) return true

        // Notify error
        store.dispatch('notify', {
            message: store.getters.translations.notifications.insertPositiveValue,
            duration: 5
        })
        return false
    },
    hasGridData() {
        if (Bulkorder.doesGridHaveValues()) return true

        // Notify error
        store.dispatch('notify', {
            message: store.getters.translations.notifications.noLensesOrdered,
            duration: 5
        })
        return false
    },
    hasContinuedToDetails() {
        if (self.basicData.hasContinuedToDetails) return true

        // Notify on error
        store.dispatch('notify', {
            message: store.getters.translations.notifications.continueToDetailSection,
            duration: 5
        })
        return false
    },
    isRemarkEvaluated() {
        if (store.getters.isRemarkEvaluated) return true

        // Notify on error
        store.dispatch('notify', {
            message: store.getters.translations.notifications.writeRemark,
            duration: 5
        })
        return false
    },
    areFieldsInRange() {
        if (store.getters.areFieldsInRange) return true

        // Notify on error
        store.dispatch('notify', {
            message: store.getters.translations.notifications.outOfDelivery,
            duration: 5
        })
        return false
    },
    areRequiredFieldsEvaluated() {
        if (store.getters.areRequiredFieldsEvaluated) return true

        // Notify on error
        store.dispatch('notify', {
            message: store.getters.translations.notifications.fillAllFields,
            duration: 5
        })
        return false
    },
    areIndividualFieldsEvaluated() {
        if (store.getters.areIndividualFieldsEvaluated) return true

        // Notify on error
        store.dispatch('notify', {
            message: store.getters.translations.notifications.checkIndividualFields,
            duration: 5
        })
        return false
    },
    areDraftFieldsEvaluated() {
        if (store.getters.areDraftFieldsEvaluated) return true

        // Notify error
        store.dispatch('notify', {
            message: store.getters.translations.notifications.enterReferenceNumber,
            duration: 5
        })
        return false
    },
    isFrameSelected() {
        if (store.getters.isFrameSelected) return true

        // Notify error
        store.dispatch('notify', {
            message: store.getters.translations.notifications.selectAFrame,
            duration: 5
        })
        return false
    },
    isErrorPresent() {
        if (!store.getters.isErrorPresent) return false

        // Notify error
        store.dispatch('notify', {
            message: store.getters.translations.notifications.correctExistingErrors,
            duration: 5
        })
        return true
    },
    isOrderBulkorder(order) {
        const orderType = this.getPropertyInHeader(order, 'Rectype')
        if (orderType === null) return false

        return orderType === 'W'
    },
    getPropertyInHeader(order, property) {
        if (property === undefined || property === null)
            return null
        if (order === undefined || order === null || order === '' || Object.entries(order).length === 0)
            return null

        return Helpers.getObjProp(order, property, null)
    }
}
