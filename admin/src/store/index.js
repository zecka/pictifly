import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    current: "compress"
  },
  mutations: {
    setCurrent(state, value) {
      Vue.set(state, "current", value);
    }
  },
  actions: {},
  modules: {}
});
