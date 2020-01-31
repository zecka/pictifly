import Vue from "vue";
import App from "./App.vue";
import store from "./store";
import Mixins from "@/mixins/";
Vue.prototype.$pictifly = window.pictifly;
Vue.config.productionTip = false;
Vue.mixin(Mixins);
import SvgIcon from "@/components/SvgIcon";
Vue.component("svgicon", SvgIcon);
import UiButton from "@/components/ui/UiButton";
Vue.component("ui-button", UiButton);
new Vue({
  store,
  render: h => h(App),
}).$mount("#app");
