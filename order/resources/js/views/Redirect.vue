<template>
    <div>
        <!-- Loading indicator -->
        <div class=" d-flex justify-content-center align-items-center" >
            <span class="spinner-icon large opaque position-absolute start-50"/>
        </div>
    </div>
</template>

<script>
export default {
  beforeCreate() {
    // Redirect will try to push to homesite. If there is no homesite, the app will wait
    let route = null
    if (this.$store.getters.getIniProperties !== null) {
      route = this.$store.getters.getIniProperties['HOMESITE']
    }

    if (route !== null) {
      this.$router.push({name: route})
      return
    }

    const interval = setInterval(() => {
      if (this.$store.getters.getIniProperties !== null) {
        route = this.$store.getters.getIniProperties['HOMESITE']
        clearInterval(interval)
        this.$router.push({name: route})
      }
    }, 10);
  },
}
</script>
