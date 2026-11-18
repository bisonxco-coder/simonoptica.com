import ApiService from '@/utils/api';

const testSelection = [1, 2, "asdasdasd", "AAAA"]
export default {
    state () {
        return {
            canSave: false,
            tracerConfiguration: null,
            lensSoapTracerTimeout: 120000, // 2 minutes
            lensSoapTracerSourceToken: null, // Used when canceling request to LensSoapTracer
            cancelTracingSignal: false,

            tracerSettings: {
                hasPortSelection: false,
                isVca: false,
                tracerIp: null,
                serialNumber: null,
                tracerSelection: [],
                modeSelection: [{id: 'S', label: 'Serial'}, {id: 'L', label: 'LAN'}],
                portSelection: [],
                baudSelection: [9600, 19200, 38400, 57600, 115200],
                paritySelection: [{id: 'N', label: 'None'}, {id: 'E', label: 'Even'}],
                dataBitSelection: [7,8],
                stopBitSelection: [1, 2],
                flowControlSelection: [{id: 0, label: 'None'}, {id: 2, label: 'XON/XOFF'}, {id: 1, label: 'RTS/CTS'}, {id: 3, label: 'DSR/DTR'}],
                selectedTracer: null,
                selectedMode: null,
                selectedPort: null,
                selectedBaud: null,
                selectedParity: null,
                selectedDataBit: null,
                selectedStopBit: null,
                selectedFlowControl: null,
                isVcaDisabled: false,
                isTracerIpDisabled: false,
                isSerialNumberDisabled: false,
                isTracerDisabled: false,
                isModeDisabled: false,
                isPortDisabled: false,
                isBaudDisabled: false,
                isParityDisabled: false,
                isDataBitDisabled: false,
                isStopBitDisabled: false,
                isFlowControlDisabled: false,
            },
            lensSoapTracerSettings: {
                ip: "localhost:33333",
                isIpDisabled: false,
            }
        }
    },
    getters: {},
    mutations: {},
    actions: {
        getTracerConfiguration (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('tracer', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        saveTracerConfiguration (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('tracer/save', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        deleteTracerConfiguration (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('tracer/delete', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        updateTracerConfiguration (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('tracer/update', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getLastTracerConfiguration (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('tracer/get-last-tracer-configuration', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getUrl (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('tracer/get-url', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getTracerDefinitions (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('tracer/tracer-definitions', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
    }
}