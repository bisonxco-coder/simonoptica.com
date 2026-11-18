<template>
    <div class="sticky-top" style="z-index: 9005 !important;">
        <div v-if="isError" class="card alert-danger border border-danger border-radius-xl shadow mb-0 ps-5 pe-2 py-1">
            <div class="d-flex justify-content-end close-icon-bar">
                <span class="btn close-icon" @click="onCloseAlert"/>
            </div>
            <div class="glyphicon glyphicon-exclamation-sign"></div>
            <strong class="py-2 text-danger">{{$translations.notifications.error}}</strong>
            <span class="text-dark">{{orders.notification.message}}</span>
        </div>
        <div v-if="isSuccess" class="card alert-success border border-success border-radius-xl shadow mb-0 ps-5 pe-2 py-1">
            <div class="d-flex justify-content-end close-icon-bar">
                <span class="btn close-icon" @click="onCloseAlert"/>
            </div>
            <div class="glyphicon glyphicon-exclamation-sign"></div>
            <strong class="py-2 text-success">{{$translations.notifications.success}}</strong>
            <span class="text-dark">{{orders.notification.message}}</span>
        </div>
        <div v-if="isWarning" class="card alert-warning border border-warning border-radius-xl shadow mb-0 ps-5 pe-2 py-1">
            <div class="d-flex justify-content-end close-icon-bar">
                <span class="btn close-icon" @click="onCloseAlert"/>
            </div>
            <div class="glyphicon glyphicon-exclamation-sign"></div>
            <strong class="py-2 text-warning">{{$translations.dialog.warning}}</strong>
            <span class="text-dark">{{orders.notification.message}}</span>
        </div>
    </div>  
</template>

<script>
export default {
    data() {
        return {
            timer: null
        }
    },
    computed: {
        orders () {return this.$store.state.moduleOrders},
        hasMessage () {
            let msg = this.orders.notification.message
            if (!msg || msg == '' || msg == null)
                return false
            return true
        },
        isError (){
            if (!this.hasMessage) return false
            if (this.orders.notification.type == 'error')
                return true
            return false
        },
        isSuccess (){
            if (!this.hasMessage) return false
            if (this.orders.notification.type == 'success')
                return true
            return false
        },
        isWarning (){
            if (!this.hasMessage) return false
            if (this.orders.notification.type == 'warning')
                return true
            return false
        }

    },
    mounted() {
        this.registerNotifyEvent()
    },
    methods: {        
        registerNotifyEvent(){
            document.addEventListener('notifyEvent', () => {
                this.closeTimer()
            })
        },
        onCloseAlert() {
            this.orders.notification.message = ''
        },
        closeTimer() {
            if (this.orders.notification.durationSeconds <= 0) return

            if (this.timer)
                clearTimeout(this.timer)

            this.timer = setTimeout(() => {
                this.onCloseAlert()
            }, this.orders.notification.durationSeconds * 1000)
        }
    }
}
</script>

<style scoped>
.close-icon-bar {
    height: 0;
    z-index: 0;
}
.sticky-top {
    top: 10%;
    z-index: 1021;
    height: 0;
}
.alert .glyphicon{
    display:table-cell;
}

.alert div,
.alert span{
    padding-left: 5px;
    display:table-cell;
}
</style>