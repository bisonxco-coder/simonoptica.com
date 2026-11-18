<template>

    <!-- Loading indicator -->
    <div v-if="isLoading">
        <span class="spinner-icon large opaque position-absolute start-50"/>
    </div>

    <!-- FORM VIEW -->
    <div v-if="formData">
        <div class="d-flex justify-content-end close-icon-bar">
            <slot name="formToolbar" :formData="formData"></slot>
            <button v-if="canClose" class="btn close-icon" @click="onFormClose"/>
        </div>
        <hr>
        <slot name="form" :formData="formData"></slot>

        <div class="d-flex justify-content-end mt-5">
            <button v-if="canClose" class="expand-button dark border-0" @click="onFormClose">{{$translations.common.close}}</button>
            <slot name="formFooter" :formData="formData"></slot>
        </div>
    </div>

    <!-- TABLE VIEW -->
    <div v-if="!formData">

        <!-- Table -->
        <div class="table-responsive" v-if="tableData">
            <table class="table table-hover table-style" >
                <thead class="table-header" v-if="tableData.headers.length > 0">
                    <tr :class="trHeaderStyle + ' justify-content-start'">
                        <span  v-if="areRowsSelectable" style="padding:0"></span>

                        <!-- Type checkbox -->
                        <th v-if="type == 'checkbox' && tableData.rows.length > 0" class="checkbox-th" >
                            <input 
                                class="form-check-input panel-checkbox small" 
                                type="checkbox" 
                                @change="onHeaderCheckboxChange" 
                                :checked="isHeaderChecked()"
                            />
                        </th>
                      <empty v-for="(header, index) in tableData.headers" :key="header" >
                        <th v-if="showId || index > 0 || isFrame" :class="thPointerStyle(header)" scope="col" @click="onHeaderClick(header)">
                            <span :class="thStyle(header)" v-html="headerLabelLogic(header)" />
                            <br>
                            <span :class="sortingArrowLogic(header)" />
                        </th>
                      </empty>
                        <!-- Action icons space -->
                        <th v-if="$slots.form || $slots.actions"></th>
                    </tr>
                </thead>
                <tbody v-if="tableData.rows.length > 0" class="table-body" ref="tableBodyRef">
                    <empty v-for="(row, index) in tableData.rows" :key="row.id">
                        <tr @click="onRowClick(row)" :class="trStyle(row, index) + (areRowsSelectable ? ' pointer': '')" @mouseenter="row.hover = true" @mouseleave="row.hover = false">

                            <!-- Custom row selection element -->
                            <span v-if="areRowsSelectable" class="row-selection-item-position p-2 px-0 pointer-no-events" :style="getStyle('selection')" >
                                <span :class="selectElementStyle(row) + (row.hover ? ' hover' : '' )"/>
                            </span>

                            <!-- Type checkbox -->
                            <td :class="tdStyle(row)" v-if="type == 'checkbox'">
                                <input 
                                    class="form-check-input panel-checkbox small" 
                                    type="checkbox" 
                                    @change="onRowCheckboxChange($event, row)"
                                    :checked="isRowChecked(row)"
                                    :disabled="!isRowSelectable(row)"
                                />
                            </td>

                          <empty v-for="(val, index) in row.values" :key="val" >
                            <td v-if="showId || index > 0 || isFrame" :class="tdStyle(row)" :style="getStyle('td')" :title="getValTooltip(val)" v-html="getVal(val)" />
                          </empty>
                            <!-- Action icons -->
                            <td @click="onActionBoxClick" v-if="$slots.form || $slots.actions" class="align-middle">
                                <div class="d-flex justify-content-end" >
                                    <span v-if="!hideRowEyeAction" :class="'eye-icon small pointer me-md-0 me-3' + (isSelected(row) ? ' inverted-color' : '' )" @click="onFormShow(row)"/>
                                    <slot name="actions" :isSelected="isSelected(row)" :row="row"></slot>
                                </div>
                            </td>
                        </tr>

                        <!-- CONTENT VIEW -->
                        <tr class="border border-1 border-radius-xl height0" :style="contentStyle(row)">
                            <div v-if="rowHasContent(row)" class="d-flex p-0 m-0 position-relative w-0">
                                <div class="position-absolute" :style="contentBodyStyle" id="content">
                                    <slot name="content" :isSelected="isSelected(row)" :row="row" ></slot>
                                </div>
                            </div>
                        </tr>
                    </empty>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="hasPages" class="mt-sm-4 mt-2">
            <nav class="d-flex justify-content-center">
                <ul class="pagination noselect">

                    <!-- Previous page -->
                    <li class="page-item" v-if="showPreviousPage" @click="onPreviousPageClick" >
                        <a class="page-link" href="#/">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <!-- First page -->
                    <li class="page-item" v-if="showFirstPage" @click="onPageClick(1)">
                        <a class="page-link" href="#/">1</a>
                    </li>

                    <!-- Pages -->
                    <div v-for="pageNumber in pageNumbers" :key="pageNumber">
                        <li :class="pageStyle(pageNumber)" @click="onPageClick(pageNumber)">
                            <a class="page-link" href="#/">{{pageNumber}}</a>
                        </li>
                    </div>

                    <!-- Last page -->
                    <li class="page-item" v-if="showLastPage" @click="onPageClick(totalPages)">
                        <a class="page-link" href="#/">{{totalPages}}</a>
                    </li>                    

                    <!-- Next page -->
                    <li class="page-item" v-if="showNextPage" @click="onNextPageClick">
                        <a class="page-link" href="#/">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
/**
 * 'type' parameter has 3 options:
 *      -type == null: readonly data
 *      -radio: for single row selection
 *      -checkbox: adds checkbox to each row for multiple selection
 */
import Empty from './Empty.vue'
export default {
    components: {Empty},
    props: ['modelValue','tableData','type','isLoading', 'totalPages', 'currentPage', 'hideRowEyeAction', 'dense', 'colorEverySecondRow', 'allowClosing', 'showId', 'isFrame'],
    emits: ['update:modelValue','update:currentPage','update:totalPages','onHeaderClick','onContentRowOpen','onFormViewOpen','onFormViewClose'],
    data () {
        return {
            selection: null,
            isActionBoxSelected: false,
            formData: null,
            pageOffset: 1,
            currentPageRange: [],
            sortingBy: null,
            isSorting: false,
            tableWidth: 0,
            clickedRow: null,
            contentNode: null,
        }
    },
    watch: {
        selection (value) {
            this.$emit('update:modelValue', value)
        },
        totalPages () {
            this.$emit('update:currentPage', 1)
        },
        'tableData.rows' () {
            this.getTableWidth()
            this.onFormClose()
        },
    },
    updated () {
        // Make sure that the selection updates on every change
        this.selection = this.modelValue
    },
    computed: {
        canClose () { 
            if (this.allowClosing === undefined) 
                return true
            return this.allowClosing === true
        },
        isDense () { return this.dense === undefined ? false : true },
        shouldColorEverySecondRow () { return this.colorEverySecondRow === undefined ? false : true },
        hasPages() {
            return this.totalPages > 1
        },
        showPreviousPage() {
            return true
        },
        showFirstPage() {
            if (this.currentPageRange.find(x => x == 1))
                return false
            return true
        },
        pageNumbers(){
            let min = this.currentPage - this.pageOffset
            let max = this.currentPage + this.pageOffset
            
            // Keep a consistent amount of displayed page number buttons
            if (min < 1 ) {
                min = 1
                max += this.pageOffset + 1
            }
            else if (min == this.pageOffset)
                max += this.pageOffset

            if (max > this.totalPages){
                max = this.totalPages 
                min -= this.pageOffset + 1
            }
            else if (max == this.totalPages)
                min -= this.pageOffset

            if (min < 1 ) 
                min = 1

            // Create a range array with numbers from min to max
            this.currentPageRange = []
            for (let i = min; i <= max; i++) {
                this.currentPageRange.push(i)
            }
            
            return this.currentPageRange            
        },
        showNextPage() {
            return true
        },
        showLastPage() {
            if (this.currentPageRange.find(x => x == this.totalPages))
                return false
            return true
        },
        trHeaderStyle() {
            let style = ''
            if (this.isLoading) style += ' disabled'
            return style
        },
        areRowsSelectable() {
            if (this.type == 'radio' || this.type == 'checkbox') return true
            return false
        },
        contentBodyStyle() {
            return `width: ${this.tableWidth}px;`
        },
    },
    mounted() {
        this.getTableWidth()

        // Disable all hovering
        this.tableData.rows.map(x => x.hover = false)

        this.selection = this.modelValue

        // Cast selection to array
        if (this.type == 'checkbox'){
            if (Array.isArray(this.selection)) return
            if (this.modelValue) return
            this.selection = []
        }        
    },
    unmounted() {
        window.removeEventListener('resize', this.resizeListener)
    },
    methods: {
        selectRow(row){
            // Deselect if clicked on the same row twice
            if (this.type == 'radio' && this.isRowSelected(row)) {
                this.selection = null
                return
            }
            if (this.type == 'checkbox' && this.isRowInSelection(row)){
                this.deleteRowFromSelection(row)
                return
            }

            if (this.type == 'radio')
                this.selection = row

            if (this.type == 'checkbox')
                this.selection.push(row)
        },
        onPreviousPageClick(){
            let pageNum = this.currentPage - 1
            if (pageNum <= 1) pageNum = 1 
            
            this.$emit('update:currentPage', pageNum)
        },
        onPageClick(pageNum){
            this.$emit('update:currentPage', pageNum)
        },
        onNextPageClick(){
            let pageNum = this.currentPage + 1
            if (pageNum >= this.totalPages) pageNum = this.totalPages 

            this.$emit('update:currentPage', pageNum)
        },
        onFormClose(){
            this.$emit('onFormViewClose', this.formData)

            // Disable all hovering
            this.tableData.rows.map(x => x.hover = false)

            this.getContentNode()
            this.formData = null
        },
        onFormShow(row, triggerEvent = true){
            this.formData = row.rawData
            if (row.custom && row.custom.getFormData){
                this.formData = row.custom.getFormData(row)
            }

            if (triggerEvent)
                this.$emit('onFormViewOpen', row.rawData)
        },
        onActionBoxClick(){
            this.isActionBoxSelected = true
        },
        onHeaderCheckboxChange(event){
            if (this.type != 'checkbox') return
            let val = event.target.checked
            this.tableData.rows.forEach(row => {
                this.deleteDuplicateRows(row)
                if (val) {
                    if (this.isRowSelectable(row)) 
                        this.selection.push(row)
                }
                else this.deleteRowFromSelection(row)
            })
        },
        onRowCheckboxChange(event, row){
            if (this.type != 'checkbox') return
            if (!this.isRowSelectable(row)) return

            this.deleteDuplicateRows(row)
           
            // Check
            let val = event.target.checked
            if (val) {
                this.selection.push(row)
                return
            }

            // Uncheck
            this.deleteRowFromSelection(row)
        },
        onRowClick(row){
            // Prevent selecting row when clicked in action button box
            if (this.isActionBoxSelected) {
                this.isActionBoxSelected = false
                return
            }
            // Keep track of the clicked-on row in order to open the correct content row
            if (this.clickedRow && this.clickedRow == row)
                this.clickedRow = null
            else {
                this.clickedRow = row

                // Fire an event when the content row was opened
                if (this.$slots.content != undefined) 
                    this.$emit('onContentRowOpen', row) 
            }

            // Disable all hovering
            this.tableData.rows.map(x => x.hover = false)

            // Pass row selection functionallity in order to be able to select it from other scripts
            if (row.custom)
                row.custom.selectRow = (row) => this.selectRow(row)

            this.getContentNode()

            if (!this.areRowsSelectable) return
            if (!this.isRowSelectable(row)) return
            this.selectRow(row)
        },
        onHeaderClick(header){
            if (header.enabled != undefined && !header.enabled)
                return

            // For sorting arrow styling
            if (this.sortingBy && header.sortParam == this.sortingBy.sortParam ){
                if (this.isSorting) this.isSorting = false
                else this.isSorting = true
            }
            else this.isSorting = true

            this.sortingBy = header
            this.$emit('onHeaderClick', {header: header, isSorting: this.isSorting})
        },
        thPointerStyle(header) {
            let style = ''
                
            if (header.sortParam) 
                style += ' pointer'
            else style += ' pointer-no-events'

            if (header.enabled != undefined && !header.enabled)
                style = ' pointer-no-events'
            return style
        },
        thStyle(header) {
            let style = 'th-elements align-middle'
            if (header.sortParam) 
                style += ' th-elements pointer'
            return style
        },
        trStyle(row, index) {
            let style = ''
            if (this.isLoading) style += ' disabled'
            if (this.shouldColorEverySecondRow && index % 2 === 0) style += ' tr-color'
            return style
        },
        tdStyle(row) {
            let style = "align-middle"
            if (this.isSelected(row)) 
                style += " select-style"
            if (this.isDense)
                style += " py-2"

            return style
        },
        contentStyle(row) {
            let style = ' '
            if (this.rowHasContent(row)) {
                if (this.contentNode){
                    row.hover = true
                    style = `height: ${this.contentNode.clientHeight}px;`
                }
            }
            return style
        },
        selectElementStyle(row){
            if (!this.areRowsSelectable) return ""

            let style = "row-selection-item h-100 w-100 border-radius-xl"
            if(this.isSelected(row)) style += " selected"

            // Add custom functionallity
            if (row.custom && row.custom.selectElementStyle){
                style += " " + row.custom.selectElementStyle(row)
            }

            return style
        },
        pageStyle(pageNumber) {
            if (this.currentPage == pageNumber) return 'page-item active'
            return 'page-item'
        },
        isRowSelected(row) {
            if (this.type != 'radio') return false
            if (!this.selection) return false
            if (this.selection.id == row.id) return true
            return false
        },
        isRowInSelection(row) {
            if (this.type != 'checkbox') return false
            if (!this.selection) return false
            return this.selection.some(x => x.id  == row.id)
        },
        isRowChecked(row) {
            if (this.isRowInSelection(row)) return 'checked'
            return false
        },
        isSelected(row) {
            if (this.isRowSelected(row) || this.isRowInSelection(row)) return true
            return false
        },
        rowHasContent(row){
            if (this.$slots.content != undefined && 
                this.clickedRow && 
                this.clickedRow == row
            ) return true
            return false
        },
        isHeaderChecked(){
            if (this.type != 'checkbox') return false
            if (!this.selection) return false
            
            // If every row is selected in the current table data row data, return true
            let checked = this.tableData.rows.map(x => {
                if (this.isRowSelectable(x))
                    return this.selection.findIndex(y => y.id == x.id) > -1
            }).filter(x => x != undefined)

            // In case every row is not selectable, return false
            if (checked.length == 0) return false
            checked = checked.every(x => x)

            if (checked) return 'checked'
            return false
        },
        isRowSelectable(row){
            if (!this.areRowsSelectable) return

            // Add custom functionallity
            if (row.custom && row.custom.isRowSelectable) 
                return row.custom.isRowSelectable(row)
            return true
        },
        deleteRowFromSelection(row){
            if (this.isRowInSelection(row)){
                let index = this.selection.findIndex(x => x.id == row.id)
                this.selection.splice(index, 1)
            }
        },
        deleteDuplicateRows(row){
            if (this.type != 'checkbox') return
            if (this.selection){
                this.selection = this.selection.filter(x => x.id != row.id)
            }
        },
        getVal(value){
            if (value && value.value != undefined) return value.value
            return value
        },
        getValTooltip(value){
            if (value && value.value != undefined) return value.tooltip
            return null
        },
        getStyle(property){
            if (this.tableData.style)
                if (this.tableData.style[property])
                    return this.tableData.style[property]
            return ""
        },
        getContentNode(){
            // Get parent content element to get the total height of elements
            setTimeout(() => {
                this.contentNode = document.getElementById('content')
            }, 20);

            setTimeout(() => {
                this.$forceUpdate();
            }, 150)
        },
        getTableWidth(){
            setTimeout(() => {
                if (this.tableData.rows.length > 0){
                    this.tableWidth = this.$refs.tableBodyRef.clientWidth
                    window.addEventListener('resize', this.resizeListener)
                }
            }, 10);
        },
        resizeListener(){
            if (this && this.$refs && this.$refs.tableBodyRef)
                this.tableWidth = this.$refs.tableBodyRef.clientWidth
        },
        headerLabelLogic(header){
            if (header.label) return header.label 
            return header
        },
        sortingArrowLogic(header){
            let style = 'arrow-position no-transition'
            if (header.sortParam) {
                if (this.sortingBy && header.sortParam == this.sortingBy.sortParam){
                    style += ' arrow-icon opaque small'
                    if (this.isSorting) style += ' rotated'
                }
            }
            return style
        }
    }
}
</script>

<style scoped lang="scss">
td.select-style {
    filter: contrast(1);
    color: white;
    --bs-table-hover-color: white;
}
.arrow-position {
    display: block;
    height: 20px;
    /* width: auto; */
}
.th-elements {
    opacity: 0.32;
}
.table-header{
    border-color: transparent;
    font-weight: 700;
    font-size: 12px;
}
.table-body{
    font-size: 12px;
    font-weight: 500;
}
td{
    max-width: 10rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
th{
    max-width: 15rem;
    min-width: 3.5rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
th.checkbox-th{
    max-width: 0.1rem;
    min-width: 0.1rem;
    width: 0.1rem;
    vertical-align: middle;
}

/* Remove the thick header bottom line */
.table > :not(:first-child) {
    border-top: 0;
}

/* Add extra padding */
.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}

/* Table selected */
.table tbody tr.selected td {    
    /* background-color: #00569C; */
    /* --bs-table-hover-bg: #00569C; */
    // color:white;
}

/* Table hover */
.table-style {
    // --bs-table-hover-bg: #8BB2DD;
    --bs-table-hover-bg: transparent;
    --bs-table-hover-color: black;
    // color:white;
}
tr.disabled {
    pointer-events: none;
    opacity: 0.5;
    transition: 0.2s;
}

tr {
    transition: 0.1s;
    &.height0{
        height: 0;
    }
}

.close-icon-bar {
    // height: 0;
    z-index: 0;
}
</style>