<template>
  <div class="nav">
    <!--
    <ui-button :color="getColor('home')" @click="goTo('home')">Home</ui-button>
    <ui-button :color="getColor('compress')" @click="goTo('compress')">Compress</ui-button>
    -->
  </div>
</template>
<script>
export default {
  name: "Nav",
  data() {
    return {
      current: this.$store.state.current
    };
  },
  methods: {
    goTo(page) {
      this.$store.commit("setCurrent", page);
      this.current = page;
    },
    getColor(page) {
      console.log("page", page);
      return this.current == page ? "primary" : "gray";
    }
  }
};
</script>
<style lang="scss" scoped>
.nav {
  height: 50px;
  background: $white;
}
</style>
