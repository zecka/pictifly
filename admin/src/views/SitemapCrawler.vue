<template>
  <div class="crawler">
    <div class="crawler__intro pictifly__info">
      If you have not created image regeneration functions you can use this tool instead. It will go through each page of your
      sitemap or given urls one by one to force
      <strong>Pictifly</strong> to regenerate the images. If you don't have a sitemap you can also enter the urls manually.
    </div>
    <div class="crawler__actions">
      <div class="crawler__action">
        <label for="togglebtn">Manual mode</label>
        <toggle-button
          color="#5bd05b"
          :width="60"
          :height="25"
          :font-size="18"
          v-model="manual"
          :labels="{ checked: 'on', unchecked: 'off' }"
        />
      </div>
      <div class="crawler__action" v-if="!manual">
        <label for="sitemapurl">Sitemap URL</label>
        <input id="sitemapurl" type="text" v-model="sitemap" />
      </div>
      <div class="crawler__action" v-else>
        <label for="textareaurls">Your urls <small>1 url per line</small></label>
        <textarea id="textareaurls" v-model="manualUrls" @input="formatManual"></textarea>
      </div>
      <ui-button @click="handleClick" :loading="isRunning" color="white" outline>Start Crawling</ui-button>
    </div>
    <div class="crawler__scroll">
      <div class="crawler__sitemap">
        <div
          class="crawler__item"
          v-for="(url, idx) in urls"
          :class="'crawler__item--' + getItemClass(url.status)"
          :key="'url' + idx"
        >
          <a :href="url.loc" target="_blank">{{ url.loc }}</a>
          <svgicon v-if="url.status !== null" :icon="getIcon(url.status)"></svgicon>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";
const parseString = require("xml2js").parseString;
import { ToggleButton } from "vue-js-toggle-button";

export default {
  components: {
    ToggleButton,
  },
  data() {
    return {
      sitemap: "",
      isRunning: false,
      urls: [],
      manualUrls: "",
      manual: false,
    };
  },
  created() {
    this.axiosCatcher();
  },
  methods: {
    formatManual(event) {
      this.autoHeight(event);
      let val = event.target.value;
      this.manualUrls = val.replace(" ", "\n").replace("\n\n", "\n");
    },
    axiosCatcher() {
      axios.interceptors.response.use(
        response => response,
        error => {
          if (typeof error.response === "undefined") {
            alert(
              "A network error occurred. " +
                "This could be a CORS issue or a dropped internet connection. " +
                "It is not possible for us to know.",
            );
            this.isRunning = false;
          }
          return Promise.reject(error);
        },
      );
    },
    handleClick() {
      this.isRunning = true;
      if (this.manual) {
        this.buildArrayManual();
      } else {
        this.getSitemapXML();
      }
    },
    buildArrayManual() {
      const urls = this.manualUrls.split("\n");
      urls.forEach(url => {
        if (url) {
          this.urls.push({
            loc: url,
            status: null,
          });
        }
      });
      this.crawlUrls();
    },
    getSitemapXML() {
      const url = this.sitemap;
      axios.get(url).then(response => {
        parseString(response.data, (err, result) => {
          if (err) {
            //Do something
            this.isRunning = false;
            alert("Your sitemap url seems not valid !");
          } else {
            this.buildArrayFromSitemap(result.urlset.url);
          }
        }).catch(() => {
          this.isRunning = false;
          alert("Your sitemap url seems not valid !");
        });
      });
    },
    buildArrayFromSitemap(responseUrls) {
      responseUrls.forEach(url => {
        url.loc.forEach(async loc => {
          this.urls.push({
            loc: loc,
            status: null,
          });
        });
      });
      this.crawlUrls();
    },
    async crawlUrls() {
      if (this.urls.length > 0) {
        this.crawlUrl(0);
      } else {
        this.isRunning = false;
      }
    },
    crawlUrl(key) {
      this.$set(this.urls[key], "status", "loading");
      axios
        .get(this.urls[key])
        .then(response => {
          this.$set(this.urls[key], "status", response.status === 200);
          this.nextUrl(key);
        })
        .catch(() => {
          this.$set(this.urls[key], "status", false);
          this.nextUrl(key);
        });
    },
    nextUrl(key) {
      if (this.urls.length - 1 >= key + 1) {
        this.crawlUrl(key + 1);
      } else {
        this.isRunning = false;
      }
    },
    getIcon(status) {
      if (status === true) {
        return "check-circle";
      } else if (status === false) {
        return "x-circle";
      } else {
        return "spinner";
      }
    },
    getItemClass(status) {
      if (status === true) {
        return "done";
      } else if (status === false) {
        return "false";
      } else if (status === "loading") {
        return "spinner";
      } else {
        return "queue";
      }
    },
  },
};
</script>
<style lang="scss" scoped>
.crawler {
  &__action {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 10px 0;
    label {
      font-size: 18px;
      margin-bottom: 10px;
      display: block;
      small {
        display: block;
        font-size: 12px;
        opacity: 0.6;
      }
    }
    textarea,
    input {
      width: 90%;
      max-width: 500px;
      border: none;
      border-radius: 2px;
      padding: 10px;
    }
  }
  &__sitemap {
    text-align: left;
    max-width: 900px;
    margin: auto;
    padding: 20px;
  }
  &__scroll {
    max-height: 500px;
    overflow-y: scroll;
  }
  &__item {
    padding: 5px 0;
    transition: all 0.3s;
    display: flex;
    justify-content: space-between;
    align-items: center;
    ::v-deep svg {
      width: 1.4em;
      height: 1.4em;
    }
    a {
      color: $white;
      display: inline-block;
      text-decoration: none;
    }
    &--queue {
      opacity: 0.5;
    }
    &--done a {
      color: $green-flash;
    }
    &--false a {
      color: $red;
    }
    & + & {
      border-top: 1px solid rgba($white, 0.3);
    }
  }
}
</style>
