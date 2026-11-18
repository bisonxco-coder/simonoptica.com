<template>
    <div class="card p-5">
      <div v-html="data"/>
    </div>
</template>

<script>
import axios from "axios";

export default {
  data(){
    return {
      data: null
    }
  },
  mounted() {
    this.$store.commit('setDashboardTitle', {
      title: () => this.$translations.contact.title,
      subTitle: () => ""
    })
    axios.get("./contact-data").then(response => {
      this.data = response.data
    })
  }
}
</script>
