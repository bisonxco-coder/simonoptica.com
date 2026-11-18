import store from '@/store'
import Helpers from '@/utils/helpers.js'
import Tracer from '@/utils/tracer.js'
import Language from '@/utils/language.js'

const self = {
    get evaluator() {return store.state.moduleEvaluator},
    get settings() {return store.state.moduleSettings},
    get orders() {return store.state.moduleOrders},
    get details() {return store.state.moduleOrders.details},
    get consumerInfo() { return store.state.moduleOrders.consumerInfo},
    get initializator() { return store.state.moduleOrders.initializator},
    get erp() { return store.state.moduleOrders.erp},
    get basicData() { return store.state.moduleOrders.basicData},
    get productCategory() { return store.state.moduleOrders.details.productCategory},
    get formAndGeometry() { return store.state.moduleOrders.details.formAndGeometry},
    get formAndGeometryProperties() { return store.state.moduleEvaluator.details.formAndGeometry},
    get refraction() { return store.state.moduleOrders.details.refraction},
    get refractionProperties() { return store.state.moduleEvaluator.details.refraction},
    get product() { return store.state.moduleOrders.details.product},
    get frame() { return store.state.moduleOrders.details.frame},
    get form() { return store.state.moduleOrders.details.form},
    get individual() { return store.state.moduleOrders.details.individual},
    get individualProperties() { return store.state.moduleEvaluator.details.individual},
}
export default {
    getProductCategoryInitial(){
        return store.dispatch('getProductCategoryInitial', {
        }).then((response) => {
            self.productCategory.materialSelection = Language.parseRecTypeJsonTranslations(Helpers.reduceArr(response["Materials"].Materials, 'Value', 'Material', 'Value'), 'Material');
            self.productCategory.specialSelection = Language.parseRecTypeJsonTranslations(Helpers.reduceArr(response["Specials"].Specials, 'Value', 'Special', 'Value'), 'Special');
            self.productCategory.groupSelection = Language.parseRecTypeJsonTranslations(Helpers.reduceArr(response["Groups"].Groups, 'Value', 'Group', 'Value'), 'Group');
            self.productCategory.indexSelection = Helpers.reduceArr(response["Indices"].Indices, 'Value', 'Index', 'Value');
        }, e => {
            self.orders.isLoading = false
            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        });
    },
    getProductCategoryUpdate(data){
        return store.dispatch('getProductCategoryUpdate', data)
          .then((response) => {
              self.productCategory.specialSelection = Language.parseRecTypeJsonTranslations(Helpers.reduceArr(response["Specials"].Specials, 'Value', 'Special', 'Value'), 'Special');
              self.productCategory.groupSelection = Language.parseRecTypeJsonTranslations(Helpers.reduceArr(response["Groups"].Groups, 'Value', 'Group', 'Value'), 'Group');
              self.productCategory.indexSelection = Helpers.reduceArr(response["Indices"].Indices, 'Value', 'Index', 'Value');
          }, e => {
              self.orders.isLoading = false
              store.dispatch('notify', {
                  message: e.data.message,
              })
              console.error(e)
          });
    },
    getDefaultMinThk() {
        return store.dispatch('getDefaultMinThk', {})
          .then(async (response) => {
              self.formAndGeometry.defaultMinThk= response;
          }, e => {
              self.orders.isLoading = false
              store.dispatch('notify', {
                  message: e.data.message,
              })
              console.error(e)
          });
    },
    onOrderTypeChange(newOrderType) {
        // Plausability check. Some fields are disabled for some ordertypes.
        // Definitions for each field are found in ordersEvaluator.js
        const allFieldsWithDisableFunction = store.getters.getAllFieldsWithDisabledFunction
        allFieldsWithDisableFunction.forEach(field => {
            // If the field is disabled and flagged to reset on disabled, set it to null
            if (field.value) {
                if (field.stateField.resetIfDisabled !== undefined && field.stateField.resetIfDisabled){
                    field.setOriginalField(null)
                }
            }
        })
    },
    evaluateComponents() {
        const orderType = self.basicData.selectedOrderType

        // Component show/enable logic for each order type
        const components = self.orders.details.components
        const componentsCopy = self.orders.stateCopy.details.components
        components.map(component => {

            // Reset components
            const componentCopy = componentsCopy.find(x => x.name == component.name)
            component.show = componentCopy.show
            component.enabled = componentCopy.enabled

            if(componentCopy.name === 'frame'){
                if(store.getters.getIniProperties['SETTINGS_SHOW_FRAME_COMPONENT'] === 'NO')
                    component.show = false;
            }
            switch (orderType.id) {
                // Bulk order
                case store.getters.getBulkorderOrderType.id:
                    component.show = (component.name === 'bulkorder')
                    component.enabled = (component.name === 'bulkorder')
                    break;

                // Spectacles
                case store.getters.getSpectaclesOrderType.id:
                    if (component.name !== 'frame'){
                        component.enabled = self.frame.selectedFrame ? true : false
                    }
                    break;
            }
        })
    },
    setInitialFrameAndBevel() {
        self.formAndGeometry.selectedFrameType = store.getters.getFrameTypeMetal
        self.formAndGeometry.selectedBevelPosition = store.getters.getBevelPositionAutomatic
    },
    setInitialSelectedOrderType() {
        self.basicData.selectedOrderType = store.getters.getDefaultOrderType
    },
    setInitialThicknessOption() {
        self.formAndGeometry.right.selectedThicknessOption = self.formAndGeometry.right.thicknessRadioInputData[0]
        self.formAndGeometry.left.selectedThicknessOption = self.formAndGeometry.left.thicknessRadioInputData[0]
    },
    copyOrderState() {
        // Used as a reference when resetting the current order
        store.state.moduleOrders.stateCopy = JSON.parse(JSON.stringify(self.orders))
    },
    makeEmptyOrderCopy() {
        // Make copies of different order components for detecting differences
        self.orders.emptyOrderCopy.basicData = JSON.parse(JSON.stringify(self.orders.basicData))
        self.orders.emptyOrderCopy.consumerInfo = JSON.parse(JSON.stringify(self.orders.consumerInfo))
        self.orders.emptyOrderCopy.formAndGeometry = JSON.parse(JSON.stringify(self.formAndGeometry))
        self.orders.emptyOrderCopy.form = JSON.parse(JSON.stringify(self.form))
        self.orders.emptyOrderCopy.frame = JSON.parse(JSON.stringify(self.frame))
        self.orders.emptyOrderCopy.refraction = JSON.parse(JSON.stringify(self.refraction))
    },
    hasOrderBeenEdited(excludeChecking = []) {
        if (!self.orders.emptyOrderCopy.basicData || !self.orders.emptyOrderCopy.consumerInfo)
            return false

        // Return true if current order data is different from empty order data
        if (!Helpers.areObjectsEqual(self.orders.emptyOrderCopy.basicData, self.orders.basicData, excludeChecking)) return true
        if (!Helpers.areObjectsEqual(self.orders.emptyOrderCopy.consumerInfo, self.orders.consumerInfo, excludeChecking)) return true
        return false
    },
    clearFormAndGeometryDrawingFields() {
        self.formAndGeometry.dbl = null
        self.form.shape.type = null
        self.form.shape.original.data = null
        self.form.shape.original.decodedData = null
        self.form.shape.current.data = null
        self.form.shape.current.decodedData = null
        self.formAndGeometry.panto = null
        self.formAndGeometry.frcv = null
        self.formAndGeometry.ztilt = null
        let sides = ['left','right'].forEach(side => {
            self.formAndGeometry[side].boxWidth = null
            self.formAndGeometry[side].boxHeight = null
            self.formAndGeometry[side].decentrationX = null
            self.formAndGeometry[side].decentrationY = null
        })
    },
    getRemarkKeyData() {
        self.basicData.additionalRemarkSelection = null

        let params = {
            lang: store.getters.getLanguageCode
        }

        self.orders.isLoading = true
        return store.dispatch('getRemarkKey', params)
        .then(response => {
            self.orders.isLoading = false
            self.basicData.additionalRemarkSelection = response
        },e => {
            self.orders.isLoading = false
            self.basicData.additionalRemarkSelection = null

            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        })
    },
    getIniProperties() {
        self.orders.isLoading = true

        // self.settings.getIniProperties = null
        return store.dispatch('getIniProperties', {})
        .then(response => {
            self.orders.isLoading = false
            self.settings.getIniProperties = response

            let env = response

            // Create an object of grouped properties from the env.
            const envMapper = (mapping, data, mainId) => {
                let id = 1 // Start from 1 because in .env values also start from 1
                let values = []
                let entries = Object.entries(mapping)

                const getId = (str) => {
                    const reverseDescriptionSplit = str[0].split('').reverse()
                    const extractedId = reverseDescriptionSplit[1] + reverseDescriptionSplit[0]
                    return extractedId
                }

                // first create id
                entries.forEach((entry, index) => {
                    if (index > 0) return
                    // Use [mainId] for the id.
                    data.filter(x => x[0].includes(entry[1])).forEach(x => {
                        values.push({[mainId]: getId(x)})
                    })
                })
                entries.forEach(entry => {
                    // Add mapping properties to list object
                    data.filter(x => x[0].includes(entry[1])).forEach(x => {
                        values.find(y => y[mainId] == getId(x))[entry[0]] = x[1]
                    })
                })
                return values
            }

            // Frame bevel selection
            let frameBevelMapping = {
                control: 'CONTROL',
                lang: 'LANG',
                description: 'DESCRIPTION',
            }
            let frameBevelData = Object.entries(env).filter(x => x[0].includes('FRAME_BEVEL_'))
            const bevelPositionSelection = envMapper(frameBevelMapping, frameBevelData, 'type')
            self.formAndGeometry.bevelPositionSelection = Language.parseEnvTranslations(bevelPositionSelection, 'frameBevel')
            Object.defineProperty(self.formAndGeometry, 'bevelPositionSelection', {
                get() {return Language.parseEnvTranslations(bevelPositionSelection, 'frameBevel')}
            })

            // Frame type selection
            let frameTypeMapping = {
                control: 'CONTROL',
                lang: 'LANG',
                description: 'DESCRIPTION',
            }
            let frameTypeData = Object.entries(env).filter(x => x[0].includes('FRAME_MATERIAL_'))
            const frameTypeSelection = envMapper(frameTypeMapping, frameTypeData, 'type')
            self.formAndGeometry.frameTypeSelection = Language.parseEnvTranslations(frameTypeSelection, 'frameMaterial')
            Object.defineProperty(self.formAndGeometry, 'frameTypeSelection', {
                get() {return Language.parseEnvTranslations(frameTypeSelection, 'frameMaterial')}
            })

            // Default grv depth and width
            self.formAndGeometry.grvWidthInput = env.LENS_ORDER_DEFAULTS_GWIDTH
            self.formAndGeometry.grvDepthInput = env.LENS_ORDER_DEFAULTS_GDEPTH

            // Default pinbevel and fpinbevel
            self.formAndGeometry.fPinbevel = env.LENS_ORDER_DEFAULTS_FPINB
            self.formAndGeometry.pinbevel = env.LENS_ORDER_DEFAULTS_PINB

            // Consumer info - title
            let consumerInfoTitleData = Object.entries(env).filter(x => x[0].includes('TITLE_ITEM'))
            const consumerInfoTitleSelection = consumerInfoTitleData.map(titleData => {
                let split = titleData[1].split(';')
                return {
                    control: split[0],
                    description: split[1],
                    lang: split[2],
                }
            })
            try{
                self.consumerInfo.titleSelection = Language.parseEnvTranslations(consumerInfoTitleSelection, 'consumerTitle')
                Object.defineProperty(self.consumerInfo, 'titleSelection', {
                    get() {return Language.parseEnvTranslations(consumerInfoTitleSelection, 'consumerTitle')},
                    set() {return true}
                })
            }
            catch (error){
                console.log('Error: Initializator => getIniProperties => self.consumerInfo.titleSelection')
                console.log(self.consumerInfo.titleSelection)
            }

            // Consumer info - language
            let consumerInfoLanguageData = Object.entries(env).filter(x => x[0].includes('LANGUAGE_ITEM'))
            const consumerInfoLanguageSelection = consumerInfoLanguageData.map(languageData => {
                let split = languageData[1].split(';')
                return {
                    control: split[0],
                    description: split[1],
                    lang: split[2],
                }
            })
            try{
                self.consumerInfo.languageSelection = Language.parseEnvTranslations(consumerInfoLanguageSelection, 'consumerLanguage')
                Object.defineProperty(self.consumerInfo, 'languageSelection', {
                    get() {return Language.parseEnvTranslations(consumerInfoLanguageSelection, 'consumerLanguage')},
                    set() {return true}
                })
            }
            catch (error){
                console.log('Error: Initializator => getIniProperties => self.consumerInfo.languageSelection')
                console.log(self.consumerInfo.languageSelection)
            }
            const formAndGeometry = store.state.moduleSettings
            self.formAndGeometry.right.ovalMaxDiff = formAndGeometry.getIniProperties['OVALMAXDIFF']
            self.formAndGeometry.left.ovalMaxDiff = formAndGeometry.getIniProperties['OVALMAXDIFF']
            // Default forced decentration X/Y Min/Max right side
            self.formAndGeometry.right.forcedDecentration.decentrationXMin = env.FORCED_DECENTRATION_X_MIN
            self.formAndGeometry.right.forcedDecentration.decentrationXMax = env.FORCED_DECENTRATION_X_MAX
            self.formAndGeometry.right.forcedDecentration.decentrationYMin = env.FORCED_DECENTRATION_Y_MIN
            self.formAndGeometry.right.forcedDecentration.decentrationYMax = env.FORCED_DECENTRATION_Y_MAX

            // Default forced decentration X/Y Min/Max left side
            self.formAndGeometry.left.forcedDecentration.decentrationXMin = env.FORCED_DECENTRATION_X_MIN
            self.formAndGeometry.left.forcedDecentration.decentrationXMax = env.FORCED_DECENTRATION_X_MAX
            self.formAndGeometry.left.forcedDecentration.decentrationYMin = env.FORCED_DECENTRATION_Y_MIN
            self.formAndGeometry.left.forcedDecentration.decentrationYMax = env.FORCED_DECENTRATION_Y_MAX
            self.productCategory.sameIndexBothSides = store.getters.getIniProperties['SETTINGS_BOTH_SAME_IDX'];
        }, e => {
            self.orders.isLoading = false
            self.settings.getIniProperties = e

            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        })
    },
    /*getIni() {
        self.erp.getIni = null

        self.orders.isLoading = true
        return store.dispatch('getIndividualIni', {})
        .then(response => {
            self.orders.isLoading = false

            if (!response) return
            try {
                let parsedResponse = response
                let currentGroup = null
                let data = {}

                // Remove any '\n' or '\r' and filter out comments
                if (parsedResponse.includes('\n')){
                    parsedResponse = parsedResponse.split('\n')
                    .map(x => {
                        x = x.trim()
                        if (x.includes('\r'))
                            x = x.split('\r').filter(r => r)[0]
                        return x
                    })
                    .filter(x => {
                        if (!x) return

                        x = x.trim()
                        if (x.includes(';') && x.indexOf(';') === 0)
                            return
                        return x
                    })
                }

                // Parse ini string
                parsedResponse.forEach(x => {
                    // Get ini group title
                    let group = x.substring(x.indexOf("[") + 1, x.lastIndexOf("]"))
                    if (group) {
                        currentGroup = group
                        return
                    }

                    // Get variables
                    let prop = x.split('=')
                    let fieldName = prop[0]
                    let fieldValue = prop[1]

                    // Check if variable is a bool
                    if (fieldValue == 'NO')
                        fieldValue = false
                    else if (fieldValue == 'YES')
                        fieldValue = true

                    // Handle special variable properties
                    else if(fieldValue != null && fieldValue.includes(';')){
                        let fieldValueSplit = fieldValue.split(';')
                        let isRequired = null
                        let min = null
                        let max = null

                        // Check if variable is required (optional or mandatory)
                        fieldValueSplit.forEach(x => {
                            if (x == 'MAN') isRequired = true
                            if (x == 'OPT') isRequired = false
                        })
                        if (fieldValueSplit.length == 3){
                            min = fieldValueSplit[0]
                            max = fieldValueSplit[1]
                        }

                        // Create an object to add to the object list
                        fieldValue = {values: fieldValueSplit}
                        if (isRequired !== null)
                            fieldValue.isRequired = isRequired
                        if (min !== null)
                            fieldValue.min = min
                        if (max !== null)
                            fieldValue.max = max
                    }

                    // Add to object list
                    if (!data[currentGroup])
                        data[currentGroup] = {}
                    data[currentGroup][fieldName] = fieldValue
                })

                self.erp.getIni = data
            } catch (error) {
                store.dispatch('notify', {message: error, duration: 5})
                console.error(error)
            }
        }, e => {
            self.orders.isLoading = false
            self.erp.getIni = null

            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        })
    },*/
    /*assignIniValues() {
        if (!self.erp.getIni) return

        try {
            let mode = self.erp.defaultIniMode
            let ini = self.erp.getIni

            // Get subsection [FFUBAS, FFUALL...]
            let properties = Helpers.getObjProp(ini, mode, null)
            if (!properties) return

            // Get properties
            let parameters = Helpers.getObjProp(properties, 'PARAMETER', null)
            if (!parameters) return

            // Get meta data of fields
            let fieldsMeta = parameters.values.map(param => {
                let val = Helpers.getObjProp(properties, param, null)
                if (!val) return null

                let meta = {
                    'name': param.toLowerCase(),
                    ...val
                }

                // Map object property names
                if (Helpers.getObjProp(val, 'min', null))
                    meta['minName'] = meta.name + 'Min'
                if (Helpers.getObjProp(val, 'max', null))
                    meta['maxName'] = meta.name + 'Max'
                if (Helpers.getObjProp(val, 'isRequired', null))
                    meta['isRequiredName'] = "is" + Helpers.capitalizeFirstLetter(meta.name) + 'Required'

                return meta
            }).filter(x => x)

            // Assign refraction values to obtained fields
            fieldsMeta.forEach(meta => {
                let sides = ['left', 'right'].forEach(side => {

                    // If both 'baseObject' and 'metaObject' contain an object with the name of
                    // 'metaFieldName', write to 'baseObject.metaFieldName' the value of
                    // 'metaObject.metaFieldValue'
                    const setObject = (baseObject, metaObject, metaFieldName, metaFieldValue) => {
                        let name = Helpers.getObjProp(metaObject, metaFieldName, null)
                        if (!name) return

                        let obj = Helpers.getObjProp(baseObject, name, undefined)
                        if (obj == undefined) return

                        baseObject[name] = metaObject[metaFieldValue]
                    }

                    setObject(self.refraction[side], meta, 'minName', 'min')
                    setObject(self.refraction[side], meta, 'maxName', 'max')
                    setObject(self.refraction[side], meta, 'isRequiredName', 'isRequired')
                })
            })
        } catch (error) {
            store.dispatch('notify', {message: error, duration: 5})
            console.error(error)
        }
    },*/
    getFrameByID() {
        if (!self.frame.selectedFrame) return

        self.erp.getFrameByID = null

        self.orders.isLoading = true
        return store.dispatch('getFrameByID', {
            frameNo: self.frame.selectedFrame.rawData.ID.trim()
        })
        .then((response) => {
            self.orders.isLoading = false
            if (!response.Frame || response.Frame.length == 0) return
            self.erp.getFrameByID = response

            let frame = response.Frame[0]

            // Form and geometry
            self.formAndGeometry.minEdgeThickness = frame.DRHTHK
            self.formAndGeometry.isEdgePinbevel = frame.FPINB > 0 || frame.PINB > 0
            self.formAndGeometry.bevelPositionValue = frame.BEVM > 0 ? frame.BEVM : 0

            // Concatenate FTYP;ETYP to get correct frame type
            let frameType = store.getters.getFrameTypeSelection
            .filter(x => x != undefined)
            .find(x => x.control == `${frame.FTYP};${frame.ETYP}`)
            self.formAndGeometry.selectedFrameType = frameType

            // Assign drill holes data to each side in Form and geometry
            const splitUpDrilHoleslData = r => r.split(';').map(x => x.split(','))
            const assignDrillHolesData = (drillData, side) => {
                splitUpDrilHoleslData(drillData).forEach((x, index) => {
                    self.formAndGeometry.drillHoles[index][side].startX = x[0]
                    self.formAndGeometry.drillHoles[index][side].startY = x[1]
                    self.formAndGeometry.drillHoles[index][side].endX = x[2]
                    self.formAndGeometry.drillHoles[index][side].endY = x[3]
                    self.formAndGeometry.drillHoles[index][side].diam = x[4]
                    self.formAndGeometry.drillHoles[index][side].depth = x[5]
                    self.formAndGeometry.drillHoles[index][side].direction = x[7]
                })
            }
            assignDrillHolesData(frame.DRILLR, 'right')
            assignDrillHolesData(frame.DRILLL, 'left')

            // Make a deep copy of the drill data for editing
            self.formAndGeometry.drillHolesEditingData = JSON.parse(JSON.stringify(self.formAndGeometry.drillHoles))

            // Decode FORM from hex to ascii and extract all variables
            let parseHexToStr = frame.TRCTYPE == 'TBLNAT' ? false : true
            self.form.frame.type = frame.TRCTYPE
            self.form.frame.data = frame.FORM
            self.form.frame.decodedData = Tracer.decodeTracerFormData(frame.FORM, parseHexToStr)

            // Set DBL
            let formProperties = self.form.frame.decodedData.properties
            let dbl = Helpers.getObjProp(formProperties, 'DBL', null)
            self.formAndGeometry.dbl = frame.DBL
            if (dbl) self.formAndGeometry.dbl = dbl

            this.setFrameByIdFormData(self.form.frame.decodedData)
            return this.interpolate(self.form.frame.type, self.form.frame.data)
        }, e => {
            self.orders.isLoading = false

            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        });
    },
    setFrameByIdFormData(decodedData) {
        const resetBoxWidth = () => {
            self.formAndGeometry.right.boxWidth = null
            self.formAndGeometry.left.boxWidth = null
        }
        const resetBoxHeight = () => {
            self.formAndGeometry.left.boxHeight = null
            self.formAndGeometry.right.boxHeight = null
        }
        const resetPanto = () => {
            self.formAndGeometry.panto = null
        }
        const resetFrcv = () => {
          self.formAndGeometry.frcv = null
        }
        const resetZtilt = () => {
            self.formAndGeometry.ztilt = null
        }

        // Fill out Form and geometry fields from the extracted FORM properties
        let formProperties = Helpers.getObjProp(decodedData, 'properties', null)
        if (!formProperties) {
            resetBoxWidth()
            resetBoxHeight()
            resetPanto()
            resetFrcv()
            resetZtilt()
            return
        }

        let hbox = Helpers.getObjProp(formProperties, 'HBOX', null)
        if (hbox){
            try {
                hbox = hbox.split(';')

                let rightHbox = hbox[0]
                self.formAndGeometry.right.boxWidth = rightHbox

                let leftHBox = hbox[1]
                self.formAndGeometry.left.boxWidth = leftHBox
            } catch (error) {
                resetBoxWidth()
                console.warn(error)
            }
        }

        let vbox = Helpers.getObjProp(formProperties, 'VBOX', null)
        if (vbox){
            try {
                vbox = vbox.split(';')

                let rightVbox = vbox[0]
                self.formAndGeometry.right.boxHeight = rightVbox

                let leftVbox = vbox[1]
                self.formAndGeometry.left.boxHeight = leftVbox
            } catch (error) {
                resetBoxHeight()
                console.warn(error)
            }
        }
        let panto = Helpers.getObjProp(formProperties, 'PANTO', null)
        if (panto){
            try {
                panto = panto.split(';')
                self.formAndGeometry.panto = panto[0]
            } catch (error) {
                resetPanto()
                console.warn(error)
            }
        }
      let frcv = Helpers.getObjProp(formProperties, 'FCRV', null)
      if (frcv){
        try {
          frcv = frcv.split(';')
          self.formAndGeometry.frcv = frcv[0]
        } catch (error) {
          resetPanto()
          console.warn(error)
        }
      }
      let ztilt = Helpers.getObjProp(formProperties, 'ZTILT', null)
      if (ztilt){
        try {
          ztilt = ztilt.split(';')
          self.formAndGeometry.ztilt = ztilt[0]
        } catch (error) {
          resetZtilt()
          console.warn(error)
        }
      }

    },
    interpolate(_type = 'HOYVCA', _vca = '') {
        if (!_type && !_vca) return
        let trcType = ''
        let form = ''

        if (_type && !trcType) trcType = _type
        if (_vca) form = _vca

        if (trcType !== 'TBLNAT'){
            form = Tracer.prepareTracerForm(form, trcType)
        }

        self.erp.interpolate = null
        self.orders.isLoading = true
        return store.dispatch('interpolate', {
            type: trcType,
            vca: form
        })
        .then(response => {
            self.orders.isLoading = false
            self.erp.interpolate = response

            // Extract all variables
            self.form.shape.type = trcType
            self.form.shape.original.data = form
            self.form.shape.original.decodedData = Tracer.decodeTracerFormData(response, false)
            self.form.shape.current.data = self.form.shape.original.data
            self.form.shape.current.decodedData = self.form.shape.original.decodedData
        }, e => {
            self.orders.isLoading = false

            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        })
    },
    async interpolateBasic(type, form) {
        self.orders.isLoading = false
        return await store.dispatch('interpolate', {
            type: type,
            vca: form
        })
        .then(response => {
            self.orders.isLoading = false
            return response
        }, e => {
            self.orders.isLoading = false

            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
        })
    },
    getLensGeo(side, _type = 'HOYVCA', _trcstr = '') {
        // Reset all values and return
        if (self.refraction[side].pd <= 0 || self.refraction[side].height <= 0){
            self.formAndGeometry[side].minimumDiameter = null
            self.formAndGeometry[side].decentrationX = null
            self.formAndGeometry[side].decentrationY = null
            return
        }

        let type = ''
        let trcstr = ''
        if (self.form.shape.original.data && self.form.shape.original.data !== undefined){
            trcstr = self.form.shape.original.data
        }
        if (self.form.shape.type && self.form.shape.type !== undefined){
            type = self.form.shape.type
        }

        if (_type && !type) type = _type
        if (_trcstr) trcstr = _trcstr

        if (trcstr == '' || type == '') return

        if (type !== 'TBLNAT'){
            trcstr = Tracer.prepareTracerForm(trcstr, type)
        }

        self.erp[side].getLensGeo = null
        self.orders.isLoading = true
        return store.dispatch('getLensGeo', {
            lensCode: Helpers.getObjProp(self.product[side].selectedLensType, 'LENSCODE', ''),
            trcstr: trcstr,
            type: type,
            cto: +self.formAndGeometry[side].isOptimized,
            side: side == 'right' ? 'R' : 'L',
            pd: self.refraction[side].pd,
            height: self.refraction[side].height,
            dbl: Helpers.getObjProp(self.formAndGeometry, 'dbl' , ''),
        })
        .then((response) => {
            self.orders.isLoading = false
            if (!response || !response.LensGeo) return

            self.erp[side].getLensGeo = response
            let lensGeo = response.LensGeo[0]

            self.formAndGeometry[side].minimumDiameter = Helpers.getObjProp(lensGeo, 'MINDM', null)
            self.formAndGeometry[side].decentrationX = Helpers.getObjProp(lensGeo, 'DECX', null)
            self.formAndGeometry[side].decentrationY = Helpers.getObjProp(lensGeo, 'DECY', null)
        }, e => {
            self.orders.isLoading = false

            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        });
    },
    scaleShape(_type = 'HOYVCA', _trcstr = '') {
        if (!self.formAndGeometry.right.boxWidth ||
            !self.formAndGeometry.right.boxHeight ||
            !self.formAndGeometry.left.boxWidth ||
            !self.formAndGeometry.left.boxHeight
        ) return

        let type = ''
        let trcstr = ''
        if (self.form.shape.original.data && self.form.shape.original.data !== undefined){
            trcstr = self.form.shape.original.data
        }
        if (self.form.shape.type && self.form.shape.type !== undefined){
            type = self.form.shape.type
        }

        if (_type && !type) type = _type
        if (_trcstr) trcstr = _trcstr

        if (trcstr == '' || type == '') return

        if (type !== 'TBLNAT'){
            trcstr = Tracer.prepareTracerForm(trcstr, type)
        }

        self.erp.scaleShape = null
        self.orders.isLoading = true
        store.dispatch('scaleShape', {
            trcstr: trcstr,
            type: type,
            boxXR: self.formAndGeometry.right.boxWidth,
            boxYR: self.formAndGeometry.right.boxHeight,
            boxXL: self.formAndGeometry.left.boxWidth,
            boxYL: self.formAndGeometry.left.boxHeight,
        })
        .then((response) => {
            self.orders.isLoading = false
            self.erp.scaleShape = response

            if (!response.ShapeList || response.ShapeList.length <= 0)
                return

            let shapeList = response.ShapeList[0]

            // Assign the scaled shape to the current shape form
            self.form.shape.current.data = shapeList.VCA
            self.form.shape.current.decodedData = Tracer.decodeTracerFormData(shapeList.VCA)
        }, e => {
            self.orders.isLoading = false


            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        });
    },
    getSalesFramesSupplierList() {
        self.orders.isLoading = true
        return store.dispatch('getSalesFramesSupplierList', {})
        .then((response) => {
            self.orders.isLoading = false
            self.erp.getSalesFramesSupplierList = response
            self.frame.search.brandSelection = response.Supplier
        }, e => {
            self.orders.isLoading = false

            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        });
    },
    getRangesCombination() {
        if (!self.initializator.hasStartDelayFinished) return
        let lenscoder = Helpers.getObjProp(self.product.right.selectedLensType, 'LENSCODE', '0')
        let lenscodel = Helpers.getObjProp(self.product.left.selectedLensType, 'LENSCODE', '0')

        self.orders.isLoading = true
        return store.dispatch('getRangesCombination', {
            lenscoder: lenscoder,
            lenscodel: lenscodel,
        }).then((response) => {
            self.orders.isLoading = false
          if(response.Ranges[0]){
            if(response.Ranges[0]['INDIVIDUALDETAIL']){
              const indMap = response.Ranges[0]['INDIVIDUALDETAIL']
              let right = []
              let left = []
              let common = []
              for(let key in indMap['PARAMETER']) {
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


            if (!response || response === undefined || Object.entries(response).length <= 0) {
                this.resetRangesCombinationVariables()
                return
            }

            self.erp.getRangesCombination = response

            // Product
            self.product.right.otherSelection = response.OtherList
            self.product.left.otherSelection = response.OtherList
            self.product.tintSelection = response.TintList
            self.product.tintMandatory = (response.TintMandatory === 'YES')
            self.product.antireflectiveCoatingSelection = response.CoatList
            if(response.UVList && response.UVList.length > 0)
                self.product.uvSelection = response.UVList
            self.product.antireflectiveCoatingMandatory = (response.CoatMandatory === 'YES')
            if (response.TintList.length > 0)
                self.product.isTintRemarkDisabled = false
            else self.product.isTintRemarkDisabled = true

            // Set range dependent fields
            response.Ranges.forEach(range => {
                if (lenscoder == range.LENSCODE) this.rangeSetup('right', range)
                if (lenscodel == range.LENSCODE) this.rangeSetup('left', range)
            })

            // Reset range dependent fields
            if (lenscoder == '0') this.rangeReset('right')
            if (lenscodel == '0') this.rangeReset('left')

            // Individual component display logic
            let canShowIndividualComponent = this.canShowIndividualComponent()
            this.handleIndividualComponentDisplay(canShowIndividualComponent)
            if (canShowIndividualComponent) {
                this.defineIndividualComponentCommonFields()
                this.cleanIndividualComponentSideFields()
            }
            else this.resetIndividualComponentCommonFields()

            /*this.geometryRefractionEvaluation('right', self.erp['right'].diamlist)
            this.geometryRefractionEvaluation('left', self.erp['left'].diamlist)*/
        }, e => {
            self.orders.isLoading = false

            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)

            this.resetRangesCombinationVariables()
        });
    },
    resetRangesCombinationVariables() {
        this.rangeReset('left')
        this.rangeReset('right')
        this.handleIndividualComponentDisplay(false)
        self.erp.getRangesCombination = null
        self.product.right.otherSelection = null
        self.product.left.otherSelection = null
        self.product.tintSelection = null
        self.product.tintMandatory = false
        self.product.antireflectiveCoatingSelection = null
        self.product.antireflectiveCoatingMandatory = false
        self.product.isTintRemarkDisabled = true
    },
    rangeSetup(side, range) {
        this.refractionSetup(side, range)
        this.geometryRefractionEvaluation(side, self.erp[side].diamlist)
        self.evaluator.methods.cylinderSpherePowerEvaluation(side, self.erp[side].diamlist)

        let canShowIndividualComponent = this.canShowIndividualComponent()
        if (canShowIndividualComponent)
            this.defineIndividualComponentSideFields(side, range)
        else this.resetIndividualComponentSideFields(side)
    },
    rangeReset(side) {
        this.refractionReset(side)
        this.resetGeometryRefractionFields(side)
        this.resetIndividualComponentSideFields(side)
        this.resetIndividualComponentCommonFields()
    },
    refractionSetup(side, range) {
        self.erp[side].diamlist = Helpers.getObjProp(range, 'DIAMLIST', [])

        self.refraction[side].range = range
        self.refraction[side].additionMax = Helpers.getObjProp(range, 'ADDITION_MAX', 0)
        self.refraction[side].additionMin = Helpers.getObjProp(range, 'ADDITION_MIN', 0)
        self.refraction[side].cylinderMax = Helpers.getObjProp(range, 'CYLINDER_MAX', 0)
        self.refraction[side].cylinderMin = Helpers.getObjProp(range, 'CYLINDER_MIN', 0)
        self.refraction[side].sphereMax = Helpers.getObjProp(range, 'SPHERE_MAX', 0)
        self.refraction[side].sphereMin = Helpers.getObjProp(range, 'SPHERE_MIN', 0)
        self.refraction[side].prismMax = Helpers.getObjProp(range, 'PRISM_MAX', 0)
        self.refraction[side].addDeg = Helpers.getObjProp(range, 'ADDDEG', 0)

        let individualDetail = Helpers.getObjProp(range, 'INDIVIDUALDETAIL', false)
        let isHeightRequired = self.orders.stateCopy.details.refraction[side].isHeightRequired
        let heightMin = self.orders.stateCopy.details.refraction[side].heightMin
        let heightMax = self.orders.stateCopy.details.refraction[side].heightMax
        let isPdRequired = self.orders.stateCopy.details.refraction[side].isPdRequired
        let pdMin = self.orders.stateCopy.details.refraction[side].pdMin
        let pdMax = self.orders.stateCopy.details.refraction[side].pdMax

        if (individualDetail && (individualDetail !== undefined || individualDetail !== null && individualDetail !== '')) {
            // Height
            let height = Helpers.getObjProp(individualDetail, 'HEIGHT', false)
            if (height){
                heightMin = Helpers.getObjProp(height, 'MIN', heightMin)
                heightMax = Helpers.getObjProp(height, 'MAX', heightMax)
                isHeightRequired = Helpers.getObjProp(height, 'REQUIRED', 'NO') == 'YES' ? true : false
            }

            // PD
            let pd = Helpers.getObjProp(individualDetail, 'PD', false)
            if (pd){
                pdMin = Helpers.getObjProp(pd, 'MIN', pdMin)
                pdMax = Helpers.getObjProp(pd, 'MAX', pdMax)
                isPdRequired = Helpers.getObjProp(pd, 'REQUIRED', 'NO') == 'YES' ? true : false
            }
        }

        self.refraction[side].isHeightRequired = isHeightRequired
        self.refraction[side].heightMin = heightMin
        self.refraction[side].heightMax = heightMax
        self.refraction[side].isPdRequired = isPdRequired
        self.refraction[side].pdMin = pdMin
        self.refraction[side].pdMax = pdMax
    },
    refractionReset(side) {
        self.refraction[side].additionMax = self.orders.stateCopy.details.refraction[side].additionMax
        self.refraction[side].additionMin = self.orders.stateCopy.details.refraction[side].additionMin
        self.refraction[side].cylinderMax = self.orders.stateCopy.details.refraction[side].cylinderMax
        self.refraction[side].cylinderMin = self.orders.stateCopy.details.refraction[side].cylinderMin
        self.refraction[side].sphereMax = self.orders.stateCopy.details.refraction[side].sphereMax
        self.refraction[side].sphereMin = self.orders.stateCopy.details.refraction[side].sphereMin
        self.refraction[side].prismMax = self.orders.stateCopy.details.refraction[side].prismMax
        self.refraction[side].isHeightRequired = self.orders.stateCopy.details.refraction[side].isHeightRequired
        self.refraction[side].isPdRequired = self.orders.stateCopy.details.refraction[side].isPdRequired
        self.refraction[side].heightMin = self.orders.stateCopy.details.refraction[side].heightMin
        self.refraction[side].heightMax = self.orders.stateCopy.details.refraction[side].heightMax
        self.refraction[side].pdMin = self.orders.stateCopy.details.refraction[side].pdMin
        self.refraction[side].pdMax = self.orders.stateCopy.details.refraction[side].pdMax
        self.refraction[side].addDeg = self.orders.stateCopy.details.refraction[side].addDeg
        self.refraction[side].range = []

        self.erp[side].diamlist = []
    },
    canShowIndividualComponent() {
        let canShow = false
        try {
            let sides = ['left', 'right']
            canShow = sides.some(side => {
                let selectedLens = self.product[side].selectedLensType
                if (!selectedLens) return false

                let range = self.refraction[side].range
                if (!range || !range.INDIVIDUALTYPE) return false

                return true
            })
        } catch (error) {
            canShow = false
        }
        return canShow
    },
    handleIndividualComponentDisplay(canShow){
        let indivualComponent = self.orders.details.components.find(x => x.name == 'individual')
        indivualComponent.show = canShow
    },
    defineIndividualComponentSideFields(side, range) {
        // Define side specific fields that will be displayed in the individual component
        try {
            let fieldMaps = self.individual.fieldMaps
            let individualDetail = Helpers.getObjProp(range, 'INDIVIDUALDETAIL', false)
            let individualType = Helpers.getObjProp(range, 'INDIVIDUALTYPE', false)
            let parameter = Helpers.getObjProp(individualDetail, 'PARAMETER', false)
            if (!individualDetail || !individualType || !parameter) {
                this.resetIndividualComponentSideFields(side)
                this.resetIndividualComponentCommonFields()
                return
            }

            // Get available parameters
            let excludeParameters = ['PD', 'HEIGHT']
            let parameters = parameter.filter(x => excludeParameters.every(excludeParameter => excludeParameter != x))

            // Prepare side field data
            let sideFieldData = parameters.map(parameter => {
                // References
                let fieldMap = fieldMaps[side].find(x => x.name == parameter)
                if (!fieldMap || fieldMap == undefined)
                    fieldMap = fieldMaps.common.find(x => x.name == parameter)
                if (!fieldMap || fieldMap == undefined)
                    return null

                let existingParam = []
                if (self.individual[side].definedFields)
                    existingParam = self.individual[side].definedFields.find(x => x.name == parameter)
                let data = individualDetail[parameter]

                // Properties
                let name = parameter
                let hasSide = fieldMaps[side].some(x => x.name == parameter)
                let value = Helpers.getObjProp(existingParam, 'value', null)
                let label = Helpers.getObjProp(fieldMap, 'label', null)
                let param = Helpers.getObjProp(fieldMap, 'param', null)
                //let step = Helpers.getObjProp(fieldMap, 'step', null)
                //let decimals = Helpers.getObjProp(fieldMap, 'decimals', null)
                //let type = Helpers.getObjProp(fieldMap, 'type', 'number')
                let component = Helpers.getObjProp(data, 'LIST', false) ? 'select' : 'input'
                //let min = Helpers.getObjProp(data, 'MIN', type == 'number' ? -999999999 : null)
                //let max = Helpers.getObjProp(data, 'MAX', type == 'number' ? 999999999 : null)
                let min = 0;
                let max = 0;
                let step = 0.1
                let decimals = 0
                let type = 'number'
                let validChars = ''
                if(fieldMap['min']) {
                  min = fieldMap['min']
                  max = fieldMap['max']
                  step = fieldMap['step']
                  decimals = fieldMap['decimals']
                }
                else if (fieldMap['validChars']) {
                  type = 'text'
                  validChars = fieldMap['validChars']
                }
                let position = fieldMap['pos']
                let required = Helpers.getObjProp(data, 'REQUIRED', false) == "YES" ? true : false
                let list = Helpers.getObjProp(data, 'LIST', null)

                // Evaluation passed from ordersEvaluator.js
                let evaluation = self.individualProperties.evaluation

                // If list is empty but min and max are present, fill list with values from min to max
                if (list){
                    list = list.filter(x => x)
                    if (list.length == 0 && min && max){
                        for (let i = min; i <= max; i++) {
                            list.push(i)
                        }
                    }
                }

                return {
                    data: data,
                    name: name,
                    side: hasSide ? side : null,
                    value: value,
                    label: label,
                    param: param,
                    step: step,
                    decimals: decimals,
                    type: type,
                    component: component,
                    min: min,
                    max: max,
                    required: required,
                    list: list,
                    errorMessage: "",
                    evaluation: evaluation,
                    pos: position,
                    validChars: validChars
                }
            }).filter(x => x)

            // Add to individual component side
            self.individual[side].type = individualType
            self.individual[side].definedFields = sideFieldData
        } catch (error) {
            store.dispatch('notify', {
                message: error,
                duration: 3
            })
            console.error(error)
        }
    },
    defineIndividualComponentCommonFields() {
        // Define common fields that will be displayed in the individual component
        try {
            // Prepare field data
            let fieldData = []
            let sides = ['left','right'].forEach(side => {
                let vals = []
                try {
                    if (Helpers.getObjProp(self.individual[side].definedFields, 'length', false)){
                        vals = self.individual[side].definedFields.filter(x => {
                            let doesFieldExist = fieldData.some(y => y.name == x.name)
                            return x.side == null && !doesFieldExist
                        })
                    }
                } catch (error) {
                    console.error(error)
                }

                if (vals && vals.length > 0)
                    fieldData.push(...vals)
            })

            // Add to individual component
            self.individual.definedFields = fieldData
        } catch (error) {
            store.dispatch('notify', {
                message: error,
                duration: 3
            })
            console.error(error)
        }
    },
    cleanIndividualComponentSideFields() {
        try {
            let sides = ['left','right'].forEach(side => {
                let definedFields = self.individual[side].definedFields
                if (!definedFields || definedFields === undefined || definedFields == [])
                    return

                self.individual[side].definedFields = definedFields.filter(field => field.side !== null)
            })
        } catch (error) {
            console.error(error)
        }
    },
    resetIndividualComponentSideFields(side) {
        self.individual[side].type = null
        self.individual[side].definedFields = null
    },
    resetIndividualComponentCommonFields() {
        self.individual.definedFields = null
    },
    geometryRefractionEvaluation(side, diamlist) {
        if (!diamlist || diamlist === undefined || diamlist == []){
            this.resetGeometryRefractionFields(side)
            return
        }

        let ranges = []
        let sphere = Helpers.parseOrNull(self.refraction[side].sphere)
        let cylinder = Helpers.parseOrNull(self.refraction[side].cylinder)
        let addition = Helpers.parseOrNull(self.refraction[side].addition)

        if (cylinder < 0) {
            sphere = sphere + cylinder
            cylinder = cylinder * -1
        }

        // 1. Filter out all diamlist values that don't fall into sphere, cylinder and addition min-max
        let evals = diamlist.map(x => {
            let sphereMin = Helpers.parseDecimal(x.POWERFR)
            let sphereMax = Helpers.parseDecimal(x.POWERTO)
            let powerMinNew = sphereMin
            let powerMaxNew = sphereMax
            let powerMin = Helpers.parseOrNull(x.CYLPOWERFR)
            let powerMax = Helpers.parseOrNull(x.CYLPOWERTO)

            let cylinderMin = Helpers.parseDecimal(x.CYLFR)
            let cylinderMax = Helpers.parseDecimal(x.CYLTO)

            if (powerMin > 0 && sphere <= sphereMin)
                powerMinNew = powerMinNew - cylinder * powerMin / 100

            if (powerMax > 0 && sphere >= (powerMaxNew - cylinderMax * powerMax / 100))
                powerMaxNew = powerMaxNew - cylinder * powerMax / 100

            if (!Helpers.inRange(sphere  , powerMinNew                  , powerMaxNew)) return
            if (!Helpers.inRange(cylinder, Helpers.parseDecimal(x.CYLFR), Helpers.parseDecimal(x.CYLTO))) return
            if (!Helpers.inRange(addition, Helpers.parseDecimal(x.ADDFR), Helpers.parseDecimal(x.ADDTO))) return
            return x
        }).filter(x => x)

        // 2. Push both min and max diameter values into lens diameter ranges array
        // 3. Set geometry type from the last available value in the evaluated array
        // 4. Set decimal steps for sphere, cylinder and addition
        evals.forEach(x => {
            Helpers.pushToRange(x.DIAM1, ranges)
            self.formAndGeometry[side].typeAllowedCode = x.CRIBTYPE
            if(self.formAndGeometry[side].type !== 'O')
                self.formAndGeometry[side].type = this.parseCribType(x.CRIBTYPE)
            self.refraction[side].sphereStep = Helpers.toStep(x.SPHSTEP)
            self.refraction[side].cylinderStep = Helpers.toStep(x.CYLSTEP)
            self.refraction[side].additionStep = Helpers.toStep(x.ADDSTEP)
        })

        // 5. Set lens diameter range array
        self.formAndGeometry[side].lensDiameterValues = ranges
    },
    parseCribType(crib){
        let parsed = crib.toLowerCase()
        if (parsed === 'o') return 'O'
        if (parsed === 'r') return 'R'
        return 'R'
    },
    resetGeometryRefractionFields(side) {
        self.formAndGeometry[side].lensDiameterValues = self.orders.stateCopy.details.formAndGeometry[side].lensDiameterValues
        self.formAndGeometry[side].typeAllowedCode = self.orders.stateCopy.details.formAndGeometry[side].typeAllowedCode
        self.formAndGeometry[side].type = self.orders.stateCopy.details.formAndGeometry[side].type
        self.refraction[side].sphereStep = self.orders.stateCopy.details.refraction[side].sphereStep
        self.refraction[side].cylinderStep = self.orders.stateCopy.details.refraction[side].cylinderStep
        self.refraction[side].additionStep = self.orders.stateCopy.details.refraction[side].additionStep
    },
    isAxisRequiredEvaluation(side) {
        let isRequired = false
        if (self.refraction[side].cylinder) isRequired = true
        self.refraction[side].isAxisRequired = isRequired
    },
    getLensFilter() {
        if (!self.initializator.hasStartDelayFinished) return

        self.orders.isLoading = true
        return store.dispatch('getLensFilter', {
        }).then((response) => {
            self.orders.isLoading = false
            self.erp.getLensFilter = response
            self.initializator.lensFilter = response.LensFilter
            this.lensFilterReload()
        }, e => {
            self.orders.isLoading = false
            self.initializator.lensFilter = []

            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        });
    },
    lensFilterReload(){
        // Match Group value with LGroup from response
        let group = self.productCategory.selectedGroup
        if (group && self.initializator.lensFilter){
            let selection = self.initializator.lensFilter.filter(element => group.Value == element.LGroup)
            self.productCategory.lensFilterSelection = selection
        }
    },
    corridorCheck(){
        // Enable Corridor dropdown only on group 3 (progressive lenses)
        let group = self.productCategory.selectedGroup
        if (group){
            if (group.Value == 3 || self.settings.getIniProperties.LENS_ORDER_DESIGNASBRAND == "YES")
                self.productCategory.isCorridorDisabled = false
            else self.productCategory.isCorridorDisabled = true
        }
    },
    getLensTypesOnly() {
        if (!self.initializator.hasStartDelayFinished) return

        let pc = self.productCategory
        let material = pc.selectedMaterial ? pc.selectedMaterial.Value : '2'
        let index = pc.selectedIndex ? pc.selectedIndex.Value : 'X'
        let special = pc.selectedSpecial ? pc.selectedSpecial.Value : 'X'
        let group = pc.selectedGroup ? pc.selectedGroup.Value : '0'
        let design = pc.selectedCorridor ? pc.selectedCorridor.Value : 'X'
        let lensFilter = pc.selectedLensFilter ? pc.selectedLensFilter.Value : ''

        let searchField = self.product.searchInput
        let searchWithFilter = +self.product.searchWithFilter

        self.orders.isLoading = true
        return store.dispatch('getLensTypesOnly', {
            prodlist: '',
            material: material,
            index: index,
            special: special,
            group: group,
            type: 'X',
            usage: 'X',
            design: design,
            search_field: searchField,
            search_with_filter: searchWithFilter,
            lens_filter: lensFilter,
        }).then((response) => {
            self.orders.isLoading = false
            self.erp.getLensTypesOnly = response
            self.initializator.lensTypeOnly = response.Lens
            self.product.left.lensTypeSelection = this.lensTypeStock(self.product.left.lensTypeSelection, self.product.left.isStock)
            self.product.right.lensTypeSelection = this.lensTypeStock(self.product.right.lensTypeSelection, self.product.right.isStock)
            document.dispatchEvent(new Event('getLensTypesOnlyEventReceived'))
        }, e => {
            self.orders.isLoading = false
            self.initializator.lensTypeOnly = []
            self.product.left.lensTypeSelection = []
            self.product.right.lensTypeSelection = []
            self.product.left.selectedLensType = []
            self.product.right.selectedLensType = []

            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
        });
    },
    lensTypeStock(selection, isStock){
        if (!self.initializator.lensTypeOnly) return []
        if (isStock) selection = self.initializator.lensTypeOnly.filter(x => x.STOCK == 1)
        else selection = self.initializator.lensTypeOnly.filter(x => x.STOCK == 0)
        return selection
    },
    setLensOptimizedDiameter(side) {
        if (!side || !self.formAndGeometry[side].isOptimized || self.formAndGeometry[side].minimumDiameter === null) {
            self.formAndGeometry[side].lensDiameterOptimizedValue = []
            return
        }

        self.formAndGeometry[side].lensDiameterOptimizedValue = [self.formAndGeometry[side].minimumDiameter]
        self.formAndGeometry[side].lensDiameter = self.formAndGeometry[side].minimumDiameter
    },
    lensDiameterEvaluation(side) {
        let minimumDiameter = self.formAndGeometry[side].minimumDiameter
        let lensDiameter = self.formAndGeometry[side].lensDiameter
        let lensDiameterValues = self.formAndGeometry[side].lensDiameterValues
        const isLensDiameterDisabled = self.formAndGeometryProperties[side].lensDiameter.disabled
        const details = store.state.moduleOrders.details

        if ((side === undefined || side === null || side === '') ||
            (minimumDiameter === null || minimumDiameter === undefined) ||
            (lensDiameter === null || lensDiameter === undefined || lensDiameter === 0) ||
            (lensDiameterValues === null || lensDiameterValues === undefined || Object.entries(lensDiameterValues).length === 0) ||
            !details.isComponentEnabled('formAndGeometry') ||
            !self.product[side].hasLens) {
            return
        }
        // if (!side || self.formAndGeometry[side].isOptimized || self.formAndGeometry[side].minimumDiameter === null ||
        //     (self.formAndGeometry[side].lensDiameter === null  || self.formAndGeometry[side].lensDiameter == 0 ) ||
        //     (!self.formAndGeometry[side].lensDiameterValues || self.formAndGeometry[side].lensDiameterValues.length == 0)) {
        //     return
        // }

        lensDiameter = parseFloat(self.formAndGeometry[side].lensDiameter)
        minimumDiameter = parseFloat(self.formAndGeometry[side].minimumDiameter)

        // if (lensDiameter >= minimumDiameter) return

        // If lens diameter selection is ex.: [70,75,80] and minimum diameter is ex.: 72,
        // and lens minimum lens diameter is less than selected diameter value, set lens diameter to the first greatest value.
        // If it is lesser than minimum diameter, snap lens diameter to 75 (next greater value in selection).
        let firstGreaterValue = lensDiameter
        for (let value of self.formAndGeometry[side].lensDiameterValues){
            let parsedValue = parseFloat(value)

            if (parsedValue >= minimumDiameter){
                firstGreaterValue = parsedValue
                break
            }
        }
        if (minimumDiameter >= self.formAndGeometry[side].lensDiameterValues.at(-1)){
            firstGreaterValue = self.formAndGeometry[side].lensDiameterValues.at(-1)
        }

        self.formAndGeometry[side].lensDiameter = firstGreaterValue
    },
    opticalDiameterEvaluation(side, diamlist) {
        // Local helper functions
        const listHasValues = (list) => list !== undefined && list && list.length !== undefined && list.length > 0
        const set = (val) => self.formAndGeometry[side].opticalDiameter = val

        try {
            // Get diamlist
            const hasDiamlist = listHasValues(diamlist)
            if (!hasDiamlist) {
                set(null)
                return
            }

            // Get diam1 and diam2 from first element in diamlist (to calculate offset for optical diameter)
            const diam1 = Helpers.getObjProp(diamlist[0], 'DIAM1', null)
            let diam2 = Helpers.getObjProp(diamlist[0], 'DIAM2', null)
            if ((diam1 === undefined || diam1 === null || diam1 === '')){
                set(null)
                return
            }
            // In case diam2 is not present, use diam1 as diam2
            if ((diam2 === undefined || diam2 === null || diam2 === ''))
                diam2 = diam1

            // Check if component is enabled, has lens, optimized is not selected and has diameter values
            const isComponentEnabled = self.details.isComponentEnabled('formAndGeometry') ? true : false
            const hasLensDiameterValues = listHasValues(self.formAndGeometry[side].lensDiameterValues)
            const isOptimized = self.formAndGeometry[side].isOptimized
            const hasLens = self.details.product[side].hasLens
            if (!isComponentEnabled || !hasLens || !hasLensDiameterValues){
                set(null)
                return
            }

            // Get physical diameter (lensDiameter)
            const physicalDiameter = self.formAndGeometry[side].lensDiameter
            if (physicalDiameter === undefined || physicalDiameter === null){
                set(null)
                return
            }

            // optical (diam2) = physical (diam1) + diamlist[0].diam2 - diamlist[0].diam1
            const opticalDiameter = parseFloat(physicalDiameter) + parseFloat(diam2) - parseFloat(diam1)
            if(isNaN(opticalDiameter)) {
                set(null)
            } else {
                // Assign final result
                set(opticalDiameter)
            }
        } catch (error) {
            console.error(error)
            set(null)
        }
    },
    elipticalDiameterEvaluation(side, diamlist) {
        // Local helper functions
        const listHasValues = (list) => list !== undefined && list && list.length !== undefined && list.length > 0
        const set = (val) => self.formAndGeometry[side].elipticalDiameter = val

        try {
            // Get iniproperties
            const iniProperties = self.settings.getIniProperties
            if (iniProperties === undefined || !iniProperties || Object.entries(iniProperties).length == 0){
                set(null)
                return
            }

            // Get OVALDIFF
            const ovalDiff = Helpers.getObjProp(iniProperties, 'OVALDIFF', null)
            if ((ovalDiff === undefined || ovalDiff === null || ovalDiff === '')){
                set(null)
                return
            }

            // Get diamlist
            const hasDiamlist = listHasValues(diamlist)
            if (!hasDiamlist) {
                set(null)
                return
            }

            // Get diam1
            const diam1 = Helpers.getObjProp(diamlist[0], 'DIAM1', null)
            if ((diam1 === undefined || diam1 === null || diam1 === '')){
                set(null)
                return
            }

            // Check if component is enabled, has lens, optimized is not selected and has diameter values
            const isComponentEnabled = self.details.isComponentEnabled('formAndGeometry') ? true : false
            const hasLensDiameterValues = listHasValues(self.formAndGeometry[side].lensDiameterValues)
            const isOptimized = self.formAndGeometry[side].isOptimized
            const hasLens = self.details.product[side].hasLens
            const isDisplayed = self.formAndGeometryProperties[side].elipticalDiameter.displayed
            if (!isComponentEnabled || !hasLens  || !hasLensDiameterValues || !isDisplayed){
                set(null)
                return
            }

            // Get physical diameter (lensDiameter)
            const physicalDiameter = self.formAndGeometry[side].lensDiameter
            if (physicalDiameter === undefined || physicalDiameter === null){
                set(null)
                return
            }

            // eliptical (diam3) = physical (diam1) - OVALDIFF
            const elipticalDiameter = parseFloat(physicalDiameter) - parseFloat(ovalDiff)

            // Assign final result
            set(elipticalDiameter)
        } catch (error) {
            console.error(error)
            set(null)
        }
    },
    leaveListener(event) {
        event.returnValue = store.getters.translations.notifications.changesMayNotBeSaved
    },
    setOptimized(){
        const iniProperties = self.settings.getIniProperties
        return  Helpers.getObjProp(iniProperties, 'SETOPTIMIZED', 'NO')
    }
}
