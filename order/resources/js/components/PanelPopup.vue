<template>
    <div v-if="isOpen" class="position-fixed w-100 h-100 start-0 top-0 d-flex justify-content-center align-items-center" style="z-index:10000">
        <div class="w-100 h-100 position-absolute bg-dark opacity-75" @click="onClosePopupWithBackground()"/>
        <div class="card popup-card" :style="parentStyle">
            <div v-if="canCloseWithX" class="d-flex justify-content-end close-icon-bar" >
                <span class="btn close-icon" @click="onClosePopupWithX()"/>
            </div>
            <div v-else style="height:25px"/>
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        isOpen: {
            type: Boolean,
            default: false
        },
        parentStyle: {
            type: String,
            default: ''
        },
        allowClosingWithBackground: {
            type: Boolean,
            default: true
        },
        allowClosingWithX: {
            type: Boolean,
            default: true
        }
    },
    emits: ['onClose'],
    computed: {
        canCloseWithBackground() { return this.allowClosingWithBackground !== undefined && this.allowClosingWithBackground },
        canCloseWithX() { return this.allowClosingWithX !== undefined && this.allowClosingWithX }
    },
    methods: {
        onClosePopupWithX() {
            if (!this.canCloseWithX) return
            this.closePopup()
        },
        onClosePopupWithBackground() {
            if (!this.canCloseWithBackground) return
            this.closePopup()
        },
        closePopup(){
            this.$emit('onClose')
        }
    }
}
</script>
