<template>
    <div v-for="(val, index) in listOfValues" :value="val" :key="val" class="d-flex mb-4">
        <panel-select 
            style="margin-bottom: 0px !important"
            :placeholder="placeholder"
            :selection="getDistinctAvailableSelection(val)"
            v-model="listOfValues[index]" 
            :titleParameter="titleParameter"
            :isImmutable="isImmutable"
            :disabled="disabled"
        />
        <div>
            <span class="btn close-icon h-100" @click="onDeleteValue(val, index)"/>
        </div>
    </div>
    <panel-select 
        :placeholder="placeholder"
        :selection="getDistinctAvailableSelection()"
        v-model="listPlaceholder" 
        :titleParameter="titleParameter"
        :isImmutable="isImmutable"
        :disabled="disabled"
    />
</template>

<script>
import PanelSelect from './PanelSelect.vue'

export default {
    props: ['modelValue', 'placeholder', 'selection', 'titleParameter', 'disabled', 'isImmutable'],
    components: {PanelSelect},
    data() {
        return {
            listPlaceholder: null,
            listOfValues: [],
        }
    },
    watch: {
        selection: {
            handler: function(val){
                if (!val || val === undefined){
                    this.listPlaceholder = null
                    this.listOfValues = []
                    return
                }
                this.listOfValues = this.listOfValues.filter(x => {
                    const json = JSON.stringify(x)
                    let found = false

                    let existing = this.listOfValues.find(y => {
                        const jsonToCompare = JSON.stringify(y)
                        return json == jsonToCompare
                    })

                    if (existing && existing !== undefined)
                        found = true

                    return found
                })
            },
            deep: true
        },
        listPlaceholder: function(newValue){
            if (!newValue || newValue === undefined) return

            this.listOfValues.push(newValue)
            this.listPlaceholder = null
        },
        listOfValues: {
            handler: function(val){
                const emptyValues = this.listOfValues.filter(x => !x || x === null)
                emptyValues.forEach(x => this.onDeleteValue(x))

                this.$emit('update:modelValue', val)
            },
            deep: true
        }
    },
    updated () {
        // Make sure that the selection updates on every change
        this.listOfValues = this.modelValue
    },
    mounted() {
        if (this.modelValue && this.modelValue !== undefined && 
            typeof this.modelValue == 'object' && this.modelValue.length !== undefined ){
            this.listOfValues = this.modelValue
        }
    },
    methods: {
        onDeleteValue(val) {
            const i = this.listOfValues.indexOf(val)
            if (i > -1) this.listOfValues.splice(i, 1)
        },
        getDistinctAvailableSelection(withValue = null) {
            if (!this.selection || this.selection === undefined || Object.entries(this.selection).length <= 0) return

            const newSelection = this.selection.filter(x => {
                const json = JSON.stringify(x)
                let found = false

                let existing = this.listOfValues.find(y => {
                    const jsonToCompare = JSON.stringify(y)
                    return json == jsonToCompare
                })
                if (existing && existing !== undefined)
                    found = true

                return !found
            })

            if (withValue && withValue !== undefined) {
                newSelection.splice(0, 0, withValue)
            }

            return newSelection
        }
    }
}
</script>