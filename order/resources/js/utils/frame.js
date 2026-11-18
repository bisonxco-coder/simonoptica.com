import store from '@/store'
import Helpers from '@/utils/helpers.js'
import Tracer from '@/utils/tracer.js'

const self = {
    get orders() {return store.state.moduleOrders},
    get frame() { return store.state.moduleOrders.details.frame},
}
export default {
    onOpenImage(row) {
        self.frame.showImage = true
        let params = {
            frameNo: Helpers.getObjProp(row.rawData,'FRAMECODE'),
            frameNoX: '',
        }
        this.getFramePic(params)
    },
    onCloseImage() {
        self.frame.showImage = false
        self.frame.imageUrl = null
    },
    onFrameSelect(row) {
        if (!self.frame.hasFrameBeenSelected || !self.frame.selectedFrame){
            self.frame.hasFrameBeenSelected = true
            row.custom.selectRow(row)
            return
        }

        // Make sure to warn the user if a different frame is selected or de-selected
        let dialogParams = {
            title: store.getters.translations.dialog.warning,
            description: store.getters.translations.dialog.messages.changeFrame,
            falseButtonText: store.getters.translations.dialog.cancel,
            trueButtonText: store.getters.translations.dialog.continue,
            allowClosing: true,
            type: 'warning'
        }
        store.dispatch('dialog', dialogParams).then(hasConfirmed => {
            if (hasConfirmed) {
                if (row.custom && row.custom.selectRow)
                    row.custom.selectRow(row)
                else self.frame.selectedFrame = false
            }
            return
        })
    },
    onSearch (then = (response) => {}, error = (error) => {}) {
        let params = {
            supplier: Helpers.getObjProp(self.frame.search.selectedBrand, 'FSUPPCODE'),
            model: self.frame.search.model,
            eyeSize: self.frame.search.eyeSize,
            dbl: self.frame.search.dbl,
            frameType: Helpers.getObjProp(self.frame.search.selectedFrameType, 'control'),
            color: self.frame.search.color,
            frameNumb: self.frame.search.frameCode,
        }
        return this.getSalesFramesList(params, then, error)
    },
    async getFramePreviewData(row) {
        // If preview form already exists, return it in order to save api calls.
        if (row.framePreviewData && row.framePreviewData != null && row.framePreviewData != undefined)
            return row.framePreviewData

        self.orders.isLoading = true
        return store.dispatch('getFrameByID', {
            frameNo: row.rawData.ID.trim()
        }).then((response) => {
            if (!response.Frame || response.Frame.length == 0) return null
            let frame = response.Frame[0]

            let vca = frame.FORM
            let type = frame.TRCTYPE
            if (type !== 'TBLNAT'){
                vca = Tracer.prepareTracerForm(vca, type)
            }

            return store.dispatch('interpolate', {
                type: frame.TRCTYPE,
                vca: vca
            }).then(response => {
                self.orders.isLoading = false

                return Tracer.decodeTracerFormData(response, false)
            }, e => {
                self.orders.isLoading = false
                store.dispatch('notify', {message: e.data.message})
                console.error(e)
                return null
            })
        }, e => {
            self.orders.isLoading = false
            store.dispatch('notify', {message: e.data.message})
            console.error(e)
            return null
        })
    },
    getFramePic(params, then = (response) => {}, error = (error) => {}) {
        self.orders.isLoading = true
        store.dispatch('getFramePic', params)
        .then((response) => {
            self.orders.isLoading = false
            response.Frame.forEach(x => {
                self.frame.imageUrl = `data:image/jpg;base64,${x.PICTURE}`
            })
            then(response)
        }, e => {
            self.orders.isLoading = false
            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
            error(e)
        });
    },
    getSalesFramesList(params, then = (response) => {}, error = (error) => {}) {
        let t = this
        self.orders.isLoading = true
        return store.dispatch('getSalesFramesList', params)
        .then((response) => {
            self.orders.isLoading = false

            if (!response.Frame) {
                self.frame.frameTableData.rows = []
                return
            }

            self.frame.frameTableData.rows = response.Frame.map(row => {
                // Concatenate FTYP;ETYP to get correct frame type
                let ftyp = Helpers.getObjProp(row, 'FTYP')
                let etyp = Helpers.getObjProp(row, 'ETYP')
                let frameType = store.getters.getFrameTypeSelection
                .filter(x => x !== undefined && x)
                .find(x => x.control == `${ftyp};${etyp}`)

                let brand = Helpers.getObjProp(row, 'FSUPPLIER', null)
                let model = Helpers.getObjProp(row, 'MODEL', null)
                let frameCode = Helpers.getObjProp(row, 'FRAMECODE', null)
                let eyeSize = Helpers.getObjProp(row, 'EYESIZE', null)
                let dbl = Helpers.getObjProp(row, 'DBL', null)
                let frameTypeName = Helpers.getObjProp(frameType, 'description', null)
                let color = Helpers.getObjProp(row, 'COLOR', null)

                // Check if any attributes are missing and create a warnign notification message
                /*let missingAttributes = []
                if (!brand) missingAttributes.push('Brand')
                if (!model) missingAttributes.push('Model')
                if (!frameCode) missingAttributes.push('Frame Code')
                if (!eyeSize) missingAttributes.push('Eyesize')
                if (!dbl) missingAttributes.push('DBL')
                if (!frameTypeName) missingAttributes.push('Frame type')
                if (!color) missingAttributes.push('Color')*/

                /*if (missingAttributes.length > 0) {
                    let message = store.getters.translations.notifications.frameMissingInfo + missingAttributes.join(', ')
                    console.warn(message)
                    store.dispatch('notify', {
                        message: message,
                        duration: 5,
                        type: 'warning'
                    })
                }*/

                let missingDataString = '?'
                return {
                    id: Helpers.getObjProp(row, 'ID', 0),
                    rawData: row,
                    values: [
                        brand || missingDataString,
                        model || missingDataString,
                        frameCode || missingDataString,
                        eyeSize || missingDataString,
                        dbl || missingDataString,
                        frameTypeName || ftyp + ";" + etyp,
                        color || missingDataString,
                    ],
                    custom: {
                        isRowSelectable: function(row) {
                            return false
                        },
                        getFormData(row) {
                            t.onOpenImage(row)
                            return null
                        }
                    }
                }
            })
            then(response)
        }, e => {
            self.orders.isLoading = false
            store.dispatch('notify', {
                message: e.data.message,
            })
            console.error(e)
            error(e)
        });
    }
}
