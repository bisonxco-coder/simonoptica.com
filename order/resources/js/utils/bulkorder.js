import store from '@/store'
import Helpers from '@/utils/helpers.js'

const self = {
    get evaluator () {return store.state.moduleEvaluator},
    get orders() {return store.state.moduleOrders},
    get erp() {return store.state.moduleOrders.erp},
    get bulkorder() {return store.state.moduleOrders.details.bulkorder},
    get productCategory() {return store.state.moduleOrders.details.productCategory},
}
export default {
    lensCodeKey: "LENSCODE",
    lensDescriptionKey: "LENSDESCRIPTION",
    lensDiameterKey: "DIA",

    parseListCell(fieldListCell){
        if (fieldListCell === undefined || fieldListCell === null) return null

        return {
            original: fieldListCell,
            sphere: parseFloat(fieldListCell['SPH']),
            cylinder: parseFloat(fieldListCell['CYL']),
            addition: parseFloat(fieldListCell['ADD']),
            quantity: parseFloat(fieldListCell['QUANT']),
            quantityWar: parseFloat(fieldListCell['QUANT_WAR']),
            itemNumber: fieldListCell['ITEMNUMB'],
            locationNumber: fieldListCell['LOCATNUMB'],
            value: null,
            errorMessage: "",
        }
    },
    resetProduct () {
        self.erp.getStockLensesByProdList = self.orders.stateCopy.erp.getStockLensesByProdList
        self.bulkorder.product.lensTypeSelection = self.orders.stateCopy.details.bulkorder.product.lensTypeSelection
    },
    resetGrid () {
        self.erp.getStockItems = self.orders.stateCopy.erp.getStockItems
        self.bulkorder.grid.xValues = self.orders.stateCopy.details.bulkorder.grid.xValues
        self.bulkorder.grid.yValues = self.orders.stateCopy.details.bulkorder.grid.yValues
        self.bulkorder.grid.rows = self.orders.stateCopy.details.bulkorder.grid.rows
    },
    clearGridValues() {
        self.bulkorder.grid.rows.map(x => x.filter(y => y).map(y => y.value = null))
    },
    sortFieldList(list) {
        // Sort X cylinder ascending
        // Sort Y sphere descending
        return list
        .sort((a, b) => this.parseListCell(a).cylinder - this.parseListCell(b).cylinder)
        .sort((a, b) => this.parseListCell(b).sphere - this.parseListCell(a).sphere)
    },
    sortRows(list, xValues, yValues) {
        // Sort rows and columns for easier looping
        return yValues.map(sphere => {
            return xValues.map(cylinder => list.find(item => this.parseListCell(item).sphere == sphere && this.parseListCell(item).cylinder == cylinder))
        })
    },
    createGridData(list) {
        // Get unique X and Y values
        const xValues = Array.from(new Set(list.map(x => this.parseListCell(x).cylinder)))
        const yValues = Array.from(new Set(list.map(x => this.parseListCell(x).sphere)))
    
        return {
            xValues: xValues,
            yValues: yValues,
        }
    },
    resetAllErrorMessages() {
        const rows = self.bulkorder.grid.rows
        if (Helpers.isListEmpty(rows)) return

        rows.forEach(row => {
            row.filter(x => x).forEach(cell => {
                cell.errorMessage = ""
            })
        })
    },
    evaluateCell(cell) {
        let errorMessage = ""
        if (!this.isCellValuePositive(cell))
            errorMessage = " "
            
        cell.errorMessage = errorMessage
    },
    isLensTypeSelected() {
        const selected = self.bulkorder.product.selectedLensType
        return !(
            (selected === undefined || selected === null || selected === '') || 
            (selected[this.lensCodeKey] === undefined || selected[this.lensCodeKey] === null || selected[this.lensCodeKey] === '')
        )
    },
    isLensDiameterSelected() {
        const selected = self.bulkorder.product.selectedLensDiameter
        return !(
            (selected === undefined || selected === null || selected === '') || 
            (selected[this.lensDiameterKey] === undefined || selected[this.lensDiameterKey] === null || selected[this.lensDiameterKey] === '')
        )
    },
    doesGridHaveData() {
        const rows = self.bulkorder.grid.rows
        return !Helpers.isListEmpty(rows)
    },
    doesCellHaveValue(cell) {
        return cell.value !== undefined && cell.value !== null && cell.value !== '' && cell.value !== 0
    },
    isCellValuePositive(cell) {
        return cell.value >= 0
    },
    doesGridHaveValues() {
        const rows = self.bulkorder.grid.rows
        if (Helpers.isListEmpty(rows)) return false

        return rows.some(row => 
            row
            .filter(x => x)
            .map(cell => this.doesCellHaveValue(cell))
            .some(x => x)   
        )
    },
    areAllCellValuesPositive() {
        const rows = self.bulkorder.grid.rows
        if (Helpers.isListEmpty(rows)) return false

        let result = true
        rows.forEach(row => {
            row.filter(x => x).forEach(cell => {
                if (this.doesCellHaveValue(cell)) {
                    if (!this.isCellValuePositive(cell)){
                        cell.errorMessage = " "
                        result = false
                    }
                }
            })
        })
        return result
    },
    isLensDiameterValueInSelection(value) {
        const selection = self.bulkorder.product.lensDiameterSelection
        if (selection === undefined || selection === null || selection.length === undefined || selection.length === 0) 
            return false

        const found = selection.find(x => value == x[this.lensDiameterKey])
        return found !== undefined && found !== null && found !== ''
    },
    getParsedCellValuesForSaving() {
        const result = {}
        const allCells = this.getAllCells()
        const parseDot = val => parseFloat(val).toFixed(2).toString().replace('.','_')

        allCells.forEach(cell => {
            if (cell.value !== undefined && cell.value !== null && cell.value !== '' && !isNaN(cell.value)){
                const key = `b_${parseDot(cell.sphere)}__${parseDot(cell.cylinder)}__${cell.itemNumber}__${cell.locationNumber}`
                result[key] = cell.value
            }
        })
        return result
    },
    async getLenses() {
        self.orders.isLoading = true
        return store.dispatch('getStockLensesByProdList', {}).then(response => {
            self.orders.isLoading = false

            const lensList = Helpers.getObjProp(response, 'Lens', null)
            if (Helpers.isListEmpty(lensList)) {
                this.resetProduct()
                return 
            }
            self.erp.getStockLensesByProdList = lensList
            self.bulkorder.product.lensTypeSelection = this.getLensCodes(lensList)
        }, e => {
            self.orders.isLoading = false
            this.resetProduct()

            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
        })
    },
    async getGrid() {
        if (!this.isLensTypeSelected() || !this.isLensDiameterSelected()) 
            return

        const params = {
            lensCode: self.bulkorder.product.selectedLensType[this.lensCodeKey],
            diameter: self.bulkorder.product.selectedLensDiameter[this.lensDiameterKey],
            side: self.bulkorder.product.side
        }
        self.orders.isLoading = true
        self.bulkorder.grid.isLoading = true
        return store.dispatch('getStockItems', params).then(response => {
            self.orders.isLoading = false
            self.bulkorder.grid.isLoading = false

            const fieldList = Helpers.getObjProp(response, 'FieldList', [])
            if (Helpers.isListEmpty(fieldList)) {
                this.resetGrid()
                return 
            }
            
            const sortedFieldList = this.sortFieldList(fieldList)
            const gridData = this.createGridData(sortedFieldList)
            const sortedRows = this.sortRows(sortedFieldList, gridData.xValues, gridData.yValues)

            self.erp.getStockItems = response
            self.bulkorder.grid.xValues = gridData.xValues
            self.bulkorder.grid.yValues = gridData.yValues
            self.bulkorder.grid.rows = sortedRows.map(x => x.map(y => this.parseListCell(y)))
        }, e => {
            self.orders.isLoading = false
            self.bulkorder.grid.isLoading = false
            this.resetGrid()

            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
        })
    },
    getAllCells() {
        const allCells = []
        self.bulkorder.grid.rows.forEach(row => row.filter(x => x).forEach(cell => allCells.push(cell)))
        return allCells
    },
    getGroupSelection() {
        const allowed = ['Param1']
        return self.productCategory.groupSelection.filter(x => allowed.includes(x.Key))
    },
    getLensType() {
        if (!this.isLensTypeSelected()) return ''

        const selected = self.bulkorder.product.selectedLensType
        return selected[this.lensDescriptionKey]
    },
    getLensTypeFromCode(code) {
        if (code === undefined || code === null) return null
        return self.bulkorder.product.lensTypeSelection.find(x => x[this.lensCodeKey] == code)
    },
    getLensCode() {
        if (!this.isLensTypeSelected()) return ''

        const selected = self.bulkorder.product.selectedLensType
        return selected[this.lensCodeKey]
    },
    getLensCodes(lensList = []){
        if (Helpers.isListEmpty(lensList)) 
            return []
        
        return lensList.reduce((result, lens) => {
            const val = lens[this.lensCodeKey]
    
            // Validate
            if (val === undefined || val === null || val === '')
                return null
    
            // Make new array
            if (result.length === undefined || result.length === null || result.length === 0){
                result = [lens]
            }
    
            // Take unique values
            if (!result.find(x => x[this.lensCodeKey] === val)){
                result.push(lens)
            }
            
            return result
        })
    },
    getLensDiameterValues(lensList, lensCode = '') {
        if (Helpers.isListEmpty(lensList)) return []
    
        return lensList.filter(lens => lens[this.lensCodeKey] === lensCode)
    },
    getLensDiameter() {
        if (!this.isLensDiameterSelected()) return null

        const selected = self.bulkorder.product.selectedLensDiameter
        return selected[this.lensDiameterKey]
    },
    getLensDiameterByValue(value) {
        const selection = self.bulkorder.product.lensDiameterSelection
        if (selection === undefined || selection === null || selection.length === undefined || selection.length === 0) 
            return false

        const found = selection.find(x => value == x[this.lensDiameterKey])
        return found
    },
    setLensDiameterValues() {
        const list = self.erp.getStockLensesByProdList 
        const selected = self.bulkorder.product.selectedLensType
        if (Helpers.isListEmpty(list) || !this.isLensTypeSelected()) {
            self.bulkorder.product.lensDiameterSelection = self.orders.stateCopy.details.bulkorder.product.lensDiameterSelection
            return
        }

        self.bulkorder.product.lensDiameterSelection = this.getLensDiameterValues(list, selected[this.lensCodeKey])
    },
}