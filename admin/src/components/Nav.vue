<template>
  <div class="nav">
    <div class="nav__title">
      Pictifly <small>v{{ pictifly.version }}</small>
    </div>
    <div class="nav__items">
      <ui-button :color="getColor('pictifly')" @click="goTo('pictifly')">Settings</ui-button>
      <ui-button :color="getColor('pictifly-regenerate')" @click="goTo('pictifly-regenerate')">Regenerate</ui-button>
      <ui-button :color="getColor('pictifly-crawler')" @click="goTo('pictifly-crawler')">Crawler</ui-button>
      <ui-button :color="getColor('pictifly-compress')" @click="goTo('pictifly-compress')">Compress</ui-button>
      <ui-button :color="getColor('pictifly-tools')" @click="goTo('pictifly-tools')">Tools</ui-button>
    </div>
  </div>
</template>
<script>
export default {
  name: "Nav",
  data() {
    return {
      pictifly: this.$store.state.pictifly,
    };
  },
  methods: {
    getColor(page) {
      return this.$store.state.current == page ? "primary" : "gray";
    },
  },
};
</script>
<style lang="scss" scoped>
.nav {
  height: 50px;
  background: $black;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  padding: 10px 20px;
  &__title {
    font-size: 25px;
    font-weight: bold;
    margin-right: 20px;
    small {
      font-size: 12px;
      font-weight: lighter;
      opacity: 0.7;
    }
  }
  &__items {
    ::v-deep .btn {
      margin-right: 20px;
    }
  }
}
</style>
