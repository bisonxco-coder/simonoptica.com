import store from '@/store'
import axios from 'axios'
import Helpers from '@/utils/helpers.js'
import ApiService from '@/utils/api';


const self = {
    get tracer() { return store.state.moduleTracer},
    get tracerSettings() { return store.state.moduleTracer.tracerSettings},
    get lensSoapTracerSettings() { return store.state.moduleTracer.lensSoapTracerSettings},
    get orders() {return store.state.moduleOrders},
}
export default {
    isTracerNameValid(tracerName) {
        return tracerName && tracerName !== undefined && tracerName !== '-'
    },
    loadTracerConfiguration() {
        if (!self.tracer.tracerConfiguration || self.tracer.tracerConfiguration === undefined) return

        const resource = self.tracer.tracerConfiguration

        // Tracer, VCA / OMA, Mode
        self.tracerSettings.selectedTracer = self.tracerSettings.tracerSelection.filter(x => x).find(x => x.tracerName == Helpers.getObjProp(resource, 'tracer', null))
        self.tracerSettings.isVca = Helpers.getObjProp(resource, 'vca', 0) == 1
        self.tracerSettings.selectedMode = self.tracerSettings.modeSelection.filter(x => x).find(x => x.id == Helpers.getObjProp(resource, 'mode', null))

        // Port
        const port = Helpers.getObjProp(resource, 'port', null)
        self.tracerSettings.selectedPort = port

        // Use port dropdown list or input
        if (self.tracerSettings.portSelection.length <= 0) {
            self.tracerSettings.hasPortSelection = false
        }else {
            if (port && port !== undefined){
                const portInList = self.tracerSettings.portSelection.filter(x => x).find(x => x == port)
                self.tracerSettings.hasPortSelection = portInList && portInList !== undefined
            }
        }

        // Baud, Parity, Data bit, Stop bit
        self.tracerSettings.selectedBaud = self.tracerSettings.baudSelection.filter(x => x).find(x => x == Helpers.getObjProp(resource, 'baud', null))
        self.tracerSettings.selectedParity = self.tracerSettings.paritySelection.filter(x => x).find(x => x.id == Helpers.getObjProp(resource, 'parity', null))
        self.tracerSettings.selectedDataBit = self.tracerSettings.dataBitSelection.filter(x => x).find(x => x == Helpers.getObjProp(resource, 'databit', null))
        self.tracerSettings.selectedStopBit = self.tracerSettings.stopBitSelection.filter(x => x).find(x => x == Helpers.getObjProp(resource, 'stopbit', null))

        // Flow control
        this.setFlowControl(resource, 'com_soft', 'com_hard', 'com_dtr')

        // Tracer configuration, Serial number, Lens soap tracer Ip
        self.tracerSettings.tracerIp = Helpers.getObjProp(resource, 'tracerip')
        self.tracerSettings.serialNumber = Helpers.getObjProp(resource, 'tracerserialno')
        self.lensSoapTracerSettings.ip = Helpers.getObjProp(resource, 'ip')

        const tracerName = Helpers.getObjProp(self.tracerSettings.selectedTracer, 'tracerName')
        const isVca = self.tracerSettings.isVca
        const tracerDriver = Helpers.getObjProp(self.tracerSettings.selectedTracer, 'tracerDriver')
        this.setVcaAndModeDisabled(tracerName, isVca, tracerDriver)
    },
    setDefaultValuesFromTracerDefinition(tracerDefinition) {
        if (!tracerDefinition || tracerDefinition === undefined) return

        // Set mode, baud, parity, data bit, stop bit
        self.tracerSettings.selectedMode = self.tracerSettings.modeSelection.filter(x => x).find(x => x.id == Helpers.getObjProp(tracerDefinition, 'mode', null))
        self.tracerSettings.selectedBaud = self.tracerSettings.baudSelection.filter(x => x).find(x => x == Helpers.getObjProp(tracerDefinition, 'baud', null))
        self.tracerSettings.selectedParity = self.tracerSettings.paritySelection.filter(x => x).find(x => x.id == Helpers.getObjProp(tracerDefinition, 'parity', null))
        self.tracerSettings.selectedDataBit = self.tracerSettings.dataBitSelection.filter(x => x).find(x => x == Helpers.getObjProp(tracerDefinition, 'databit', null))
        self.tracerSettings.selectedStopBit = self.tracerSettings.stopBitSelection.filter(x => x).find(x => x == Helpers.getObjProp(tracerDefinition, 'stopbit', null))

        this.setFlowControl(tracerDefinition, 'softwareHandshake', 'hardwareHandshake', 'dtr')

        const tracerName = Helpers.getObjProp(tracerDefinition, 'tracerName')
        const isVca = Helpers.getObjProp(tracerDefinition, 'isVca')
        const tracerDriver = Helpers.getObjProp(tracerDefinition, 'tracerDriver')
        this.setVcaAndModeDisabled(tracerName, isVca, tracerDriver)

        this.tracerModeChanged()
    },
    setVcaAndModeDisabled(tracerName, isVca, tracerDriver) {
        // Set VCA/ OMA enabled/disabled
        self.tracerSettings.isVcaDisabled = false
        const excludedTracerNamesList = ["Essilor Phi", "Essilor Tess", "Hoya GT series", "Indo Schoene", "Nidek LT series", "Fuji"]
        if (excludedTracerNamesList.every(x => x != tracerName)){

            if (isVca == "0") {
                self.tracerSettings.isVcaDisabled = true
                self.tracerSettings.isVca = false
            } else if(isVca =='1') {
              self.tracerSettings.isVcaDisabled = false
              self.tracerSettings.isVca = true
            } else
             {
                if (tracerDriver.indexOf("VCA") > 0) {
                    self.tracerSettings.isVcaDisabled = true
                    self.tracerSettings.isVca = true
                } else {
                    self.tracerSettings.isVcaDisabled = false
                    self.tracerSettings.isVca = false
                }
            }
        } else self.tracerSettings.isVcaDisabled = false

        // Set Mode enabled/disabled
        const isTracerNamePresentList = ["Essilor Mr. Blue", "Essilor Mr. Orange", "Essilor Tess"]
        self.tracerSettings.isModeDisabled = !isTracerNamePresentList.some(x => x == tracerName)
    },
    setFlowControl(resource, comSoftId, comHardId, dtrId) {
        /*
            if softwareHandshake is true, XON/XOFF (id = 2) is selected
            if hardwareHandshake is true, RTS/CTS (id = 1) is selected
            if dtr is true, DSR/DTR (id = 3) is selected
        */
            self.tracerSettings.selectedFlowControl = self.tracerSettings.flowControlSelection.filter(x => x).find(x => x.label == 'None')
            if (Helpers.getObjProp(resource, comSoftId, null))
                self.tracerSettings.selectedFlowControl = self.tracerSettings.flowControlSelection.filter(x => x).find(x => x.label == 'XON/XOFF')
            else if (Helpers.getObjProp(resource, comHardId, null))
                self.tracerSettings.selectedFlowControl = self.tracerSettings.flowControlSelection.filter(x => x).find(x => x.label == 'RTS/CTS')
            else if (Helpers.getObjProp(resource, dtrId, null))
                self.tracerSettings.selectedFlowControl = self.tracerSettings.flowControlSelection.filter(x => x).find(x => x.label == 'DSR/DTR')
    },
    tracerModeChanged() {
        const modeId = Helpers.getObjProp(self.tracerSettings.selectedMode, 'id', null)
        if (modeId && modeId !== undefined && modeId == 'L') {
            self.tracerSettings.isTracerIpDisabled = false
            self.tracerSettings.isSerialNumberDisabled = false

            self.tracerSettings.isPortDisabled = true
            self.tracerSettings.isBaudDisabled = true
            self.tracerSettings.isParityDisabled = true
            self.tracerSettings.isDataBitDisabled = true
            self.tracerSettings.isStopBitDisabled = true
            self.tracerSettings.isFlowControlDisabled = true
        }else {
            self.tracerSettings.isTracerIpDisabled = true
            self.tracerSettings.isSerialNumberDisabled = true

            self.tracerSettings.isPortDisabled = false
            self.tracerSettings.isBaudDisabled = false
            self.tracerSettings.isParityDisabled = false
            self.tracerSettings.isDataBitDisabled = false
            self.tracerSettings.isStopBitDisabled = false
            self.tracerSettings.isFlowControlDisabled = false

            self.tracerSettings.tracerIp = null
            self.tracerSettings.serialNumber = null
        }
    },
    getTracerConfiguration() {
        self.tracer.tracerConfiguration = null
        self.orders.isLoading = true
        return store.dispatch('getLastTracerConfiguration', {}).then(response => {
            self.orders.isLoading = false
            if (self.tracer.cancelTracingSignal) return null

            if (!response || response === undefined || Object.entries(response).length <= 0) return null

            self.tracer.tracerConfiguration = response

            return response
        },e => {
            self.orders.isLoading = false
            if (self.tracer.cancelTracingSignal) return null

            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return null
        })
    },
    getTracerDefinitions() {
        self.orders.isLoading = true
        return store.dispatch('getTracerDefinitions', {}).then(response => {
            if (!response || response === undefined) return
            self.orders.isLoading = false

            self.tracerSettings.tracerSelection = response
        },e => {
            self.orders.isLoading = false

            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
        })
    },
    getComPorts() {
        let url = 'http://127.0.0.1:33333/GetComPortList?'
        self.orders.isLoading = true
        const config = {headers: {common: []}, timeout: 10000} //10 seconds
        return axios.get(url, config).then(response => {
            self.orders.isLoading = false

            if (!response || response === undefined || !response.data || response.data === undefined){
                self.tracerSettings.hasPortSelection = false
                return
            }

            try {
                const comPortsString = response.data
                const comPortList = comPortsString.split(';')

                if (!comPortList || comPortList === undefined || comPortList.length <= 0){
                    self.tracerSettings.hasPortSelection = false
                    return
                }

                self.tracerSettings.hasPortSelection = true
                self.tracerSettings.portSelection = comPortList
                self.tracerSettings.selectedPort = comPortList.at(-1)
            } catch (error) {
                self.tracerSettings.hasPortSelection = false
                console.error(error)
                return
            }
        }).catch(e => {
            self.orders.isLoading = false

            console.error(e)
            store.dispatch('notify', {
                type: 'warning',
                message: 'LensSOAPTracer is not running. '
            });
            return 'LensSoapTracerMissing'

        })
    },
    getLensSoapTracerUrl(tracerConfigurationId) {
        let params = {}
        if (tracerConfigurationId && tracerConfigurationId !== undefined)
        params.id = tracerConfigurationId

        self.orders.isLoading = true
        return store.dispatch('getUrl', params).then(response => {
            self.orders.isLoading = false
            if (self.tracer.cancelTracingSignal) return null
            if (!response || response === undefined) return null

            return response
        },e => {
            self.orders.isLoading = false
            if (self.tracer.cancelTracingSignal) return null

            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return null
        })
    },
    getLensSoapTracerData(url) {
        const CancelToken = axios.CancelToken;
        self.tracer.lensSoapTracerSourceToken = CancelToken.source();
        const config = {
            headers: {common: []},
            timeout: self.tracer.lensSoapTracerTimeout,
            cancelToken: self.tracer.lensSoapTracerSourceToken.token
        }

        self.orders.isLoading = true
        return axios.get(url, config).then(response => {
            self.orders.isLoading = false
            if (self.tracer.cancelTracingSignal) return null

            // Parse html string
            const html = response.data
            var tracerHtml = document.createElement('html')
            tracerHtml.innerHTML = html

            // Get value of the html node or return ""
            const getValueOfNode = (node) => node !== undefined && node && node.value !== undefined && node.value ? node.value : ""

            // Get concatenated string of all <input> elements, whose ids are "tracerData{number}"
            let idCount = 0
            const getTracerData = (result = "", id = 0) => {
                idCount = id
                const tracerDataNode = tracerHtml.querySelector("#tracerData" + id)
                return tracerDataNode ? getTracerData(result += getValueOfNode(tracerDataNode), ++id) : result
            }
            const tracerData = getTracerData()

            // If the number of tracerData elements is only 1, then there is an error.
            let errorMessage = null
            if (idCount <= 1){
                const errorNodes = tracerHtml.getElementsByTagName('font')
                errorMessage = ""
                for(const errorNode of errorNodes){
                    errorMessage += errorNode.innerText + "\n"
                }
            }

            // If the number of tracerData elements is more than 5, it's probably a successfull tracing
            let successMessage = null
            if (idCount > 5){
                const successNodes = tracerHtml.getElementsByTagName('font')
                successMessage = ""
                for(const successNode of successNodes){
                    successMessage += successNode.innerText + "\n"
                }
            }

            // Return all variables
            return {
                tracerData: tracerData,
                supplier: getValueOfNode(tracerHtml.querySelector("#supplier")),
                ipLSDecrypt: getValueOfNode(tracerHtml.querySelector("#ipLSDecrypt")),
                vcaFileName: getValueOfNode(tracerHtml.querySelector("#vcaFileName")),
                version: getValueOfNode(tracerHtml.querySelector("#version")),
                errorMessage: errorMessage,
                successMessage: successMessage
            }
        }).catch(e => {
            self.orders.isLoading = false
            if (self.tracer.cancelTracingSignal) return null

            console.error(e)
            store.dispatch('notify', {
                message: e,
            })
            return null
        })
    },
    getDecryptTracedata(tracerDataString) {
        if (!tracerDataString || tracerDataString === undefined || tracerDataString == '') return null

        let params = {
            'cryptTrace': tracerDataString
        }
        self.orders.isLoading = true
        return store.dispatch('decryptTracedata', params).then(response => {
            self.orders.isLoading = false
            if (self.tracer.cancelTracingSignal) return null
            if (!response || response === undefined) return null

            return response
        },e => {
            self.orders.isLoading = false
            if (self.tracer.cancelTracingSignal) return null

            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return null
        })
    },

    // Get lens shape form from LensSoapTracer API (Form and geometry Trace Shape button)
    async traceShape(tracerConfiguration){
        try {
            if (!tracerConfiguration || tracerConfiguration === undefined || Object.entries(tracerConfiguration).length <= 0 ||
                !tracerConfiguration.tracer || tracerConfiguration.tracer === undefined) return null

            // 1. Get url for LensSoapTracer API
            const lensSoapTracerUrl = await this.getLensSoapTracerUrl(tracerConfiguration.id)
            if (self.tracer.cancelTracingSignal) return null
            if (!lensSoapTracerUrl || lensSoapTracerUrl === undefined || lensSoapTracerUrl == '') return null

            // 2. Get tracer data from LensSoapTracer API
            const lensSoapTracerData = await this.getLensSoapTracerData(lensSoapTracerUrl)
            if (self.tracer.cancelTracingSignal) return null
            if (!lensSoapTracerData || lensSoapTracerData === undefined || Object.entries(lensSoapTracerData).length <= 0
                || lensSoapTracerData.tracerData === undefined || lensSoapTracerData.tracerData == '') return null
            if (lensSoapTracerData.errorMessage && lensSoapTracerData.errorMessage !== undefined)
                return {errorMessage: lensSoapTracerData.errorMessage}

            // 3. Decrypt LensSoapTracer data
            const decryptedLensSoapTracerData = await this.getDecryptTracedata(lensSoapTracerData.tracerData)
            if (self.tracer.cancelTracingSignal) return null
            if (!decryptedLensSoapTracerData || decryptedLensSoapTracerData === undefined || Object.entries(decryptedLensSoapTracerData).length <= 0) return null

            return {
                vca: decryptedLensSoapTracerData,
                successMessage: lensSoapTracerData.successMessage,
                reference: lensSoapTracerData.vcaFileName
            }
        } catch (error) {
            if (self.tracer.cancelTracingSignal) return null
            console.error(error)

            store.dispatch('notify', {
                message: store.getters.translations.notifications.errorTracing,
                duration: 3
            })
            return null
        }
    },

    // Save the current tracer configuration (button in footer)
    saveTracerConfiguration() {
        let tracer = Helpers.getObjProp(self.tracerSettings.selectedTracer, 'tracerName')
        if (!tracer || tracer == undefined || tracer == '') tracer = '-'

        const data = ApiService.addAuthParameters({});
        const flowId = Helpers.getObjProp(self.tracerSettings.selectedFlowControl, 'id', 0)
        let params = {
            'tracer': tracer,
            'customerNo': data.uid,
            'vca': + self.tracerSettings.isVca,
            'port': self.tracerSettings.selectedPort,
            'baud': self.tracerSettings.selectedBaud,
            'databit': self.tracerSettings.selectedDataBit,
            'stopbit': self.tracerSettings.selectedStopBit,
            'parity': Helpers.getObjProp(self.tracerSettings.selectedParity, 'id'),
            'mode': Helpers.getObjProp(self.tracerSettings.selectedMode, 'id'),
            'flow': flowId,
            'com_soft': flowId == 2 ? 1 : 0,
            'com_hard': flowId == 1 ? 1 : 0,
            'com_dtr': flowId == 3 ? 1 : 0,
            'ip': self.lensSoapTracerSettings.ip,
            'tracerip': self.tracerSettings.tracerIp,
            'tracerserialno': self.tracerSettings.serialNumber,
        }

        // create a new tracer configuration or edit currently loaded one
        let action = 'saveTracerConfiguration'
        if (self.tracer.tracerConfiguration && self.tracer.tracerConfiguration !== undefined){
            params.id = self.tracer.tracerConfiguration.id
            action = 'updateTracerConfiguration'
        }

        self.orders.isLoading = true
        self.tracer.canSave = false
        store.dispatch(action, params).then(response => {
            if (!response || response === undefined) return

            self.orders.isLoading = false
            self.tracer.canSave = true

            self.tracer.tracerConfiguration = response
            store.dispatch('notify', {
                message: "Configuration was saved",
                duration: 3,
                type: 'success'
            })
        },e => {
            self.orders.isLoading = false
            self.tracer.canSave = true

            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
        })
    },

    /**
     * Decode tracer data from hex to ascii string and return all variables.
     * @param {string} tracerHexFormData tracer data in hex.
     * @param {boolean} decodeFromHex convert data from hex to ascii?
     */
    decodeTracerFormData(tracerHexFormData, decodeFromHex = true){
        let data = {

            // Convert hex form into ascii string
            form: decodeFromHex ? Helpers.hexToString(tracerHexFormData) : tracerHexFormData,

            // Contains all properties except TRCFMT, ZFMT, R= and Z= lines
            properties: {},

            left: {
                rHeader: [], // Contains R= header values like: TRCFMT=1;1000;E;R;F
                zHeader: [], // Contains Z= header values like: ZFMT=1;1000;E;L;F
                rValues: [], // Contains all the R= values
                zValues: [], // Contains all the Z= values
            },
            right: {
                rHeader: [],
                zHeader: [],
                rValues: [],
                zValues: [],
            },
        }

        let rSide = null
        let zSide = null
        data.form.split('\n').forEach(x => {
            // Format to get a clean line
            if (x.includes('\r'))
                x = x.split('\r')[0]

            // Find R side and get header
            if (x.includes('TRCFMT')){
                rSide = x.includes(';R;') ? 'right' : 'left'
                data[rSide].rHeader = x.split(';')
            }

            // Find Z side and get header
            if (x.includes('ZFMT')){
                zSide = x.includes(';R;') ? 'right' : 'left'
                data[zSide].zHeader = x.split(';')
            }

            // Push R values for each side
            if (x.includes('R=') && rSide)
                x.split('R=')[1].split(';').forEach(r => data[rSide].rValues.push(r))

            // Push Z values for each side
            if (x.includes('Z=') && zSide)
                x.split('Z=')[1].split(';').forEach(r => data[zSide].zValues.push(r))

            // Push any remaining values to properties
            if (['TRCFMT', 'R=', 'ZFMT', 'Z='].some(val => x.includes(val))) return
            if (!x.includes('=')) return
            let entry = x.split('=')
            data.properties[entry[0]] = entry[1]
        })

        return data
    },

    /**
     * Prepare tracer string for interpolation.
     * @param {string} tracerString tracer string in hex, as received from the erp.
     * @param {boolean} decodeFromHex convert data from hex to ascii?
     * @param {string} tracerType tracer type, as received from the erp.
     * @return {string} filtered string.
     */
    prepareTracerForm(tracerString, tracerType = 'HOYVCA') {
        let baseString = Helpers.hexToString(tracerString)
        let newString = baseString

        if (newString.includes('\r')) {
            newString = newString.replace('\r', '\r\n')
        }
        else if (newString.includes('\n')){
            newString = newString.replace('\n', '\r\n')
        }

        if (tracerType !== 'NDKNAT' && newString.includes('TRCFMT=2;')) {
            newString = newString.replace("\"", "")
        }

        if (newString.includes('DBL=')){
            newString = newString.replace(" ", "")
        }

        return Helpers.stringToHex(newString)
    }
}
