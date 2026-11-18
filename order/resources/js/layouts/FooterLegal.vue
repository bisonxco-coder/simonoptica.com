<template>
  <div class="mb-5 pt-5">
    <div class="footer-legal fixed-bottom">
      <div class="footer-legal-buttons noselect">
        <div @click="routeTo('imprint')" class="floating"><span class="footer-legal-button">Imprint</span></div>
        <div class="floating"><span class="footer-legal-button-sep">|</span></div>
        <div @click="routeTo('privacy')" class="floating"><span class="footer-legal-button">Privacy</span></div>
      </div>

    </div>
  </div>
</template>

<script>
  import axios from "axios";

  export default {
    methods:{
      async routeTo(val){
        if(this.$route.meta.layout === 'layout-main'){
          this.$router.push(val)
          return
        }
        await axios.get("./"+ val + "-data").then(response => {
          this.emitter.emit('legal-popup', response.data);
        })
      }
    }
  }
</script>

<style scoped lang="scss">
.footer-legal {
  z-index: 9000 !important;
  height: 25px;
}
.footer-legal-buttons{
  border-top: 1px solid silver;
  background: #F0F0F0;
  height: 100%;
  text-align: center;
  .footer-legal-button{
    padding: 0 10px;
    font-size: 12px;
    cursor: pointer;
    &:hover{
      text-decoration: underline;
    }
  }
  .floating{
    float: right;
    /*display: inline-block;*/
  }
  .floating:first-child{
    padding-right: 20px;
  }
}
.noselect {
  -webkit-touch-callout: none;
  /* iOS Safari */
  -webkit-user-select: none;
  /* Safari */
  -khtml-user-select: none;
  /* Konqueror HTML */
  -moz-user-select: none;
  /* Firefox */
  -ms-user-select: none;
  /* Internet Explorer/Edge */
  user-select: none;
  /* Non-prefixed version, currently
  supported by Chrome and Opera */ }
</style>

