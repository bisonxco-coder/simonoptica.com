<template>
    <div :class="'card pt-3 pb-4 px-5 mb-3 ' + (isComponentEnabled ? '' : 'component-disabled')" id="individual">
        <h4 class="panel-title pt-4 pb-4">{{$translations.newOrder.details.individual.title}}</h4>

        <!-- RIGHT AND LEFT LENS -->
        <div class="row">
            <div 
                class="col-md-6 col-12"
                v-for="side in ['right', 'left']" 
                :key="side" 
                :value="side"
            >
                <h4 class="fs-6 d-flex justify-content-center mb-3">
                    {{side == 'right' ? $translations.common.rightLens : $translations.common.leftLens}}
                </h4>

                <panel-input
                    v-for="field in getFieldsOfComponentType(individual[side].definedFields, 'input')"
                    :key="field"
                    :value="field"
                    :placeholder="field.label" 
                    v-model="field.value"
                    :type="field.type"
                    :min="field.min"
                    :max="field.max"
                    :step="field.step"
                    :decimals="field.decimals"
                    :disabled="!isComponentEnabled"
                    v-model:errorMessage="field.errorMessage"
                />
                <panel-select
                    v-for="field in getFieldsOfComponentType(individual[side].definedFields, 'select')"
                    :key="field"
                    :value="field"
                    :placeholder="field.label" 
                    v-model="field.value"
                    :selection="field.list"
                    :disabled="!isComponentEnabled"
                    v-model:errorMessage="field.errorMessage"
                />
            </div>
        </div>

        <hr>

        <panel-input
            v-for="field in getFieldsOfComponentType(individual.definedFields, 'input')"
            :key="field"
            :value="field"
            :placeholder="field.label" 
            v-model="field.value"
            :type="field.type"
            :min="field.min"
            :max="field.max"
            :step="field.step"
            :decimals="field.decimals"
            :disabled="!isComponentEnabled"
            v-model:errorMessage="field.errorMessage"
            @input="onInputChange"
        />
        <panel-select
            v-for="field in getFieldsOfComponentType(individual.definedFields, 'select')"
            :key="field"
            :value="field"
            :placeholder="field.label" 
            v-model="field.value"
            :selection="field.list"
            :disabled="!isComponentEnabled"
            v-model:errorMessage="field.errorMessage"
        />
    </div>
</template>

<script>
import PanelInput from '@/components/PanelInput.vue'
import PanelSelect from '@/components/PanelSelect.vue'

export default {
    components: {
        PanelInput, PanelSelect
    },
    data() {
        return{}
    },
    computed: {
        isComponentEnabled() {return this.$store.state.moduleOrders.details.isComponentEnabled('individual')},
        individual() {return this.$store.state.moduleOrders.details.individual},
        evaluator() {return this.$store.state.moduleEvaluator},
    },
    methods: {
        onInputChange() {
            this.evaluator.methods.initialEngravingEvaluation()
        },
        getFieldsOfComponentType(fields, componentTypeName) {
            try {
                if (!fields || fields === undefined || fields == []) return []
                return fields.filter(x => x.component == componentTypeName)
            } catch (error) {
                console.error(error)
            }
            return []
        }
    }
}
</script>