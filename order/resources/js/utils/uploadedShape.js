import store from '@/store'

const self = {
    get orders() {return store.state.moduleOrders},
    get basicData() { return store.state.moduleOrders.basicData},
}
export default {
    get(params = {}, then = response => {}, error = e => {}) {
        self.orders.isLoading = true
        store.dispatch("uploadedShapeGet", params).then(response => {
            self.orders.isLoading = false
            then(response)
        }, e => {
            self.orders.isLoading = false
            
            store.dispatch('notify', {
                message: e.data.message
            })
            console.error(e)

            error(e)
        })
    },
    saveFromTracer(params, then = response => {}, error = e => {}) {
        // Helper method to save shape form using vca string
        if (!params.vca || params.vca === undefined || params.vca == '') return
            
        let formData = new FormData()
        formData.append('vca', params.vca)
        formData.append('type', 'tracer')
        if (params.reference)
            formData.append('reference', params.reference)

        this.save(formData, then, error)
    },
    saveFromDisk(params, then = response => {}, error = e => {}) {
        // Helper method to save shape form using a file
        if (!params.file || params.file === undefined) return
        
        let formData = new FormData()
        formData.append('file', params.file)
        formData.append('type', 'disk')
        if (params.reference)
            formData.append('reference', params.reference)

        this.save(formData, then, error)
    },
    save(formData, then = response => {}, error = e => {}) {
        if (!formData || formData === undefined) return

        // Make post request
        self.orders.isLoading = true
        store.dispatch("uploadedShapeSave", formData).then(response => {
            self.orders.isLoading = false
            then(response)
        }, e => {
            console.error(e)
            self.orders.isLoading = false
            
            store.dispatch('notify', {
                message: e.data.message
            })

            error(e)
        })
    },
    delete(shape, then = response => {}, error = e => {}) {
        if (!shape) return

        const params = {
            id: shape.id
        }
        self.orders.isLoading = true
        return store.dispatch("uploadedShapeDelete", params).then(response => {
            self.orders.isLoading = false
            then(response)
        }, e => {
            self.orders.isLoading = false
            
            console.error(e)
            store.dispatch('notify', {
                message: e.data.message
            })

            error(e)
        })
    },
    getStandardShapesImages() {
        self.orders.isLoading = true
        return store.dispatch("getStandardShapesImages", {}).then(response => {
            self.orders.isLoading = false
            return response
        }, e => {
            self.orders.isLoading = false
            
            store.dispatch('notify', {
                message: e.data.message
            })
            console.error(e)
        })
    },
    getStandardShapeForm(shapeId) {
        if (shapeId === null || shapeId === undefined) return null

        self.orders.isLoading = true
        return store.dispatch("getStandardShapeForm", {shapeId: shapeId}).then(response => {
            self.orders.isLoading = false
            return response
        }, e => {
            self.orders.isLoading = false
            
            store.dispatch('notify', {
                message: e.data.message
            })
            console.error(e)
        })
    },
    generateReference(prefix = 'Trace') {
        let today = new Date();
        let dd = String(today.getDate()).padStart(2, '0');
        let mm = String(today.getMonth() + 1).padStart(2, '0');
        let yyyy = today.getFullYear();

        return `${prefix}_${yyyy}${mm}${dd}_${self.basicData.referenceNumber}`
    }
}