import store from '@/store'
const self = {
    get orders() {return store.state.moduleOrders},
}
export default {
    saveAnalyseB2B(params, then = response => {}, error = e => {}) {
        // Helper method to save shape form using a file
        if (!params.file || params.file === undefined) return

        let formData = new FormData()
        formData.append('file', params.file)
        if (params.indtype)
            formData.append('INDTYPE', params.indtype)
        if (params.reference)
            formData.append('reference', params.reference)

        this.save(formData, then, error)
    },
    save(formData, then = response => {}, error = e => {}) {
        // Make post request
        self.orders.isLoading = true
        store.dispatch("uploadedAnalyseB2bSave", formData).then(response => {
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
    }
}