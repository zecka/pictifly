<template>
  <div class="compress">
    <div class="compress__info pictifly__info">
      To not have to use an external service (which will cost you money) and not to be blocked due to the limitations of your
      hosting provider,
      <strong>Pictifly</strong>
      uses your browser to compress your images. This is why we advise you to perform this operation on a recent browser. Before
      compressing your images, please download and use the
      <a rel="noopener noreferrer" href="https://www.google.com/chrome/" target="_blank">latest version of Google Chrome</a>
      <small
        >Please note that you must perform this operation regularly, as your images are not compressed during upload. If you
        configure
        <a rel="noopener noreferrer" href="https://www.imgix.com/" target="_blank">imgix</a>
        (paid service) in the Pictifly settings you will no longer need to manually compress your images.</small
      >
    </div>
    <ui-button @click="handleClick()" :loading="isRunning">Start Compression</ui-button>
    <div class="compress__progress">
      <progress-bar :options="barOptions" :value="percent" />
      <small>{{ this.current_item }} / {{ this.post_count }}</small>
    </div>
  </div>
</template>
<script>
import ProgressBar from "vuejs-progress-bar";
import Compressor from "compressorjs";
import barOptions from "../components/barOptions.js";
const $ = jQuery;
export default {
  data() {
    const { ajaxUrl, nonce, quality, max_file_uploads } = this.$store.state.pictifly;
    return {
      debug: 0,
      current_item: 0,
      post_count: 0,
      isRunning: false,
      quality: quality,
      compressedFiles: [],
      max_file_uploads: max_file_uploads,
      nonce: nonce,
      ajaxUrl: ajaxUrl,
      percent: 0,
      barOptions: this.getBarOptions(),
    };
  },
  components: {
    ProgressBar,
  },
  mounted() {},
  methods: {
    handleClick() {
      if (!this.isRunning) {
        this.current_item = 0;
        this.isRunning = true;
        this.getAttachments();
      }
    },
    getAttachments() {
      const data = {
        action: "pf_get_attachments",
        nonce: this.nonce,
        current_item: this.current_item,
      };
      $.ajax({
        type: "post",
        url: this.ajaxUrl,
        data: data,
        error: (response, error) => {
          console.error("response", response); // eslint-disable-line no-console
          console.error("error", error); // eslint-disable-line no-console
        },
        success: response => {
          this.post_count = response.data.post_count;
          this.current_item = response.data.current_item;
          this.compressImages(response);
        },
      });
    },
    async compressImages(response) {
      const { attachments } = response.data;
      const promises = [];
      // const blobs = [];
      attachments.forEach(attachment => {
        attachment.files.forEach(file => {
          promises.push(this.compressSize(file));
        });
      });

      const files = await Promise.all(promises);
      // PHP have a maximum file number to send in one post method
      // So we need to split our array of file into multiple array
      const filesChunks = [];
      const chunkMaxSize = this.max_file_uploads;

      while (files.length > 0) {
        filesChunks.push(files.splice(0, chunkMaxSize));
      }
      // https://stackoverflow.com/a/37576787/2838586
      await Promise.all(
        filesChunks.map(async filesChunk => {
          await this.uploadCompressedFiles(filesChunk);
        }),
      );
      this.nextPage();
    },
    compressSize(file) {
      let url = file.url;
      return new Promise(resolve => {
        fetch(url)
          .then(res => res.blob()) // Gets the response and returns it as a blob
          .then(blob => {
            if (blob.type == "text/html") {
              console.error("not image but html", file.url); // eslint-disable-line no-console
              resolve(false);
            } else {
              new Compressor(blob, {
                quality: this.quality / 100,
                success: blob => {
                  resolve({ ...file, blob });
                },
                error(err) {
                  console.error(err); // eslint-disable-line no-console
                  resolve(false);
                },
              });
            }
          });
      });
    },
    uploadCompressedFiles(files) {
      if (files.length < 1) {
        return new Promise(resolve => resolve(false));
      }

      return new Promise(resolve => {
        let data = new FormData();
        data.append("action", "pf_upload_compressed_files");
        data.append("nonce", this.nonce);
        files.forEach((file, idx) => {
          let { blob, ...fileData } = file;
          data.append(idx, blob);
          data.append("attachments_ids[]", fileData.id);
          data.append("files_path[]", fileData.file);
        });
        $.ajax({
          type: "post",
          url: this.ajaxUrl,
          data: data,
          processData: false,
          contentType: false,
          error: (response, error) => {
            console.error("response", response); // eslint-disable-line no-console
            console.error("error", error); // eslint-disable-line no-console
          },
          success: () => {
            resolve(true);
          },
        });
      });
    },
    nextPage() {
      this.percent = parseInt((this.current_item / this.post_count) * 100);
      if (this.current_item < this.post_count) {
        this.getAttachments();
      } else {
        this.isRunning = false;
      }
    },
    getBarOptions() {
      return barOptions;
    },
  },
};
</script>
<style lang="scss" scoped>
.compress {
  &__progress {
    margin: 30px 0;
    position: relative;
    small {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, calc(-50% + 20px));
    }
  }
}
</style>
