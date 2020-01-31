export default {
  methods: {
    goTo(page) {
      const currentUrl = window.location.href;
      const newUrl = currentUrl.replace(window.location.search, "?page=" + page);
      this.$store.commit("setCurrent", page);

      history.pushState({}, null, newUrl);
    },
    publicPath() {
      return this.$store.state.pictifly.publicPath;
    },
    autoHeight(event) {
      event.target.style.height = event.target.scrollHeight + "px";
    },
  },
  data: function() {
    return {};
  },
};
