<template>
  <div class="regenerate">
    <div class="pictifly__info" style="margin-top: 20px ">
      <small
        >You can also use the
        <a @click="goTo('pictifly-crawler')" href="javascript:void(0)">Crawler</a>
        Tool</small
      >
    </div>
    <div v-if="!isAllowed">
      <div class="pictifly__info">
        You need to define your regenerates functions first Example of pictifly regeneration function
      </div>
      <ui-button href="https://gist.github.com/zecka/93d11cd384b6d7d6ca1f846e7c06c9da">On github</ui-button>
    </div>

    <div v-if="isAllowed">
      <div class="pictifly__info">
        Regenerate your images with to the function created in the "pf_post_images_regenerate" hook.
      </div>
      <div class="spacer"></div>
      <ui-button @click="handleClick" :loading="isRunning">Regenerate images</ui-button>
      <div class="spacer"></div>

      <div>
        {{ nbpost }} posts regenerate<br />
        {{ nbimage }} image sizes regenerate<br />
        <div class="spacer"></div>

        <progress-bar :options="barOptions" :value="percent" />
      </div>
      <div class="regenerate__log">
        <ul v-if="logs">
          <li class="regenerate_logitem" v-for="(logitem, key) in logs" :key="'log' + key">
            {{ logitem.title }}<br />
            <em v-for="(size, keysize) in logitem.sizes" :key="'size' + keysize + '-' + key"> {{ size }} | </em>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import barOptions from "../components/barOptions.js";
const $ = jQuery;
export default {
  data() {
    const { ajaxUrl, nonce } = this.$store.state.pictifly;
    return {
      barOptions: barOptions,
      nonce: nonce,
      ajaxUrl: ajaxUrl,
      totalpost: 0,
      stopRegeneration: false,
      nbpost: 0,
      nbimage: 0,
      percent: 0,
      logs: ["test"],
      isRunning: false,
    };
  },
  components: {},
  computed: {
    isAllowed: function() {
      const { post_types_regenerate } = this.$store.state.pictifly;
      return post_types_regenerate && post_types_regenerate.length;
    },
  },
  methods: {
    handleClick() {
      if (!this.isRunning) {
        this.nbpost = 0;
        this.nbimage = 0;
        this.percent = 0;
        this.logs = [];
        this.isRunning = true;
        this.getItems();
      }
    },
    getItems() {
      const data = {
        action: "pf_ajax_regenerate_get_items",
        nonce: this.nonce,
      };
      $.ajax({
        type: "post",
        url: this.ajaxUrl,
        data: data,
        success: response => {
          this.totalpost = response.data.length;
          this.regenerateItems(response.data);
        },
      });
    },
    regenerateItems(items) {
      let p = $.when();

      items.map(item => {
        const data = {
          action: "pf_ajax_item_image_regenerate",
          id: item.id,
          post_type: item.post_type,
          nonce: this.nonce,
        };
        p = p.then(() => {
          if (!this.stopRegeneration) {
            return this.regenerateItem(data);
          }
        });
      });
    },
    regenerateItem(data) {
      return $.ajax({
        type: "post",
        url: myAjax.ajaxurl,
        data: data,
        success: response => {
          this.nbpost = this.nbpost + 1;
          this.progressBar();
          const logItem = {
            title: response.data.post,
            sizes: [],
          };
          response.data.sizes.map(size => {
            logItem.sizes.push(size);
          });
          this.logs.push(logItem);
          this.nbimage += response.data.images.length;
        },
      });
    },
    progressBar() {
      let percent = (this.nbpost / this.totalpost) * 100;
      // Round percent 2 decimal
      this.percent = Math.round(percent * 100) / 100;
      if (this.percent == 100) {
        this.isRunning = false;
      }
    },
  },
};
</script>
<style lang="scss" scoped>
.regenerate {
  &__gist {
    text-align: left;
  }
  &__log {
    max-height: 300px;
    overflow-y: scroll;
  }
}
</style>
