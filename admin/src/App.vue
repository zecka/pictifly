<template>
  <div id="app">
    <div v-if="ready">
      <Nav />
      <Settings v-if="this.$store.state.current === 'pictifly'" />
      <Compress v-if="this.$store.state.current === 'pictifly-compress'" />
      <Regenerate v-if="this.$store.state.current === 'pictifly-regenerate'" />
      <SitemapCrawler v-if="this.$store.state.current === 'pictifly-crawler'" />
      <Tools v-if="this.$store.state.current === 'pictifly-tools'" />
    </div>
  </div>
</template>

<script>
import SitemapCrawler from "./views/SitemapCrawler.vue";
import Settings from "./views/Settings.vue";
import Compress from "./views/Compress.vue";
import Regenerate from "./views/Regenerate.vue";
import Tools from "./views/Tools.vue";
import Nav from "./components/Nav.vue";
export default {
  name: "app",
  components: {
    Nav,
    Compress,
    Regenerate,
    Settings,
    SitemapCrawler,
    Tools,
  },
  data() {
    return {
      ready: false,
    };
  },
  mounted() {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const page = urlParams.get("page");
    this.$store.commit("setCurrent", page);
    this.ready = true;
  },
};
</script>

<style lang="scss">
#app {
  border-radius: 5px;
  overflow: hidden;
  box-shadow: $box-shadow;
  margin-right: 15px;
  padding-bottom: 20px;
  position: relative;
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #fff;
  margin-top: 60px;
  background-color: $background;
  .progress-bar {
    position: relative;
    width: auto !important;
    div {
      position: absolute !important;
      left: 50% !important;
      top: 50% !important;
      transform: translate(-50%, -50%);
    }
    svg {
      display: block;
    }
  }
  .pictifly {
    &__info {
      max-width: 500px;
      margin: auto;
      font-size: 16px;
      line-height: 1.4em;
      margin-bottom: 10px;
      a {
        color: $primary;
      }
      small {
        display: block;
        margin-top: 15px;
      }
    }
  }
  .spacer {
    height: 20px;
  }
}
</style>
