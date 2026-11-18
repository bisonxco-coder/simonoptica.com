<template>
    <div v-if="dialog.hasDialog">
        <div class="position-fixed w-100 h-100 d-fixed bg-dark opacity-75" style="z-index:10001" />
        <div class="position-fixed w-100 h-100 d-flex justify-content-center align-items-center" style="z-index:10001">
            <div class="card dialog-card">
                <div v-if="dialog.allowClosing" class="d-flex justify-content-end close-icon-bar" >
                    <span class="btn close-icon" @click="onCloseDialogBox"/>
                </div>
                <h4 :class="titleStyle">{{dialog.title}}</h4>
                <span>{{dialog.description}}</span>

                <div class="d-flex flex-md-nowrap flex-wrap justify-content-start pt-4 my-2">
                    <button class="btn btn-secondary cancel panel-button border-0 w-100 p-md-2 p-2 my-md-0 my-2 me-md-2 me-0" @click="onFalseButtonClick">{{dialog.falseButtonText}}</button>
                    <button 
                        v-for="button in dialog.extraButtons"
                        :key="button"
                        :class="extraButtonStyle(button)"
                        @click="onExtraButtonClick(button)"
                    > {{extraButtonText(button)}}</button>
                    <button class="btn btn-secondary panel-button blue border-0 w-100 p-md-2 p-2 my-md-0 my-2 ms-md-2 ms-0" @click="onTrueButtonClick" >{{dialog.trueButtonText}}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    computed: {
        dialog() {return this.$store.state.moduleOrders.dialog},
        titleStyle() {
            let style = "panel-title pb-2"
            if (!this.dialog.allowClosing)
                style += " pt-3"

            if (this.dialog.type == 'warning')
                style += ' text-danger'

            return style
        }
    },
    methods: {
        onExtraButtonClick(extraButton) {
            document.dispatchEvent(new Event('dialogEventsClear'))
            if (!extraButton || extraButton.click === undefined) {
                return
            }
            extraButton.click()
        },
        onCloseDialogBox() {
            document.dispatchEvent(new Event('dialogEventClose'))
        },
        onFalseButtonClick(){
            document.dispatchEvent(new Event('dialogEventFalse'))
        },
        onTrueButtonClick(){
            document.dispatchEvent(new Event('dialogEventTrue'))
        },
        extraButtonStyle(extraButton) {
            let style = 'btn btn-secondary panel-button border-0 w-100 p-md-2 p-2 my-md-0 my-2 mx-md-2 mx-0 '
            if (!extraButton || extraButton.style === undefined) return style

            style += extraButton.style
            return style
        },
        extraButtonText(extraButton) {
            if (!extraButton || extraButton.text === undefined) return ''
            return extraButton.text
        }
    }
}
</script>

<style scoped lang="scss">
.card {
    max-width: 36%;
    @media only screen and (max-width: 600px) {
        width: 100%;
        max-width: 100%;
    }
}

.close-icon-bar {
    z-index: 1;
}
</style>