<template>
  <div id="app">
    <Nav />
    <Home v-if="this.$store.state.current === 'home'" />
    <Compress v-if="this.$store.state.current === 'compress'" />
  </div>
</template>

<script>
import Home from "./views/Home.vue";
import Compress from "./views/Compress.vue";
import Nav from "./components/Nav.vue";
export default {
  name: "app",
  mounted() {},
  components: {
    Nav,
    Compress,
    Home
  }
};
</script>

<style lang="scss">
#app {
  position: relative;
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
