import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    current: "pictifly",
    pictifly: { ...window.pictifly },
    $: jQuery,
  },
  mutations: {
    setCurrent(state, value) {
      Vue.set(state, "current", value);
    },
    saveOptions(state, value) {
      Vue.set(state.pictifly, "options", value);
    },
  },
  actions: {},
  modules: {},
});
