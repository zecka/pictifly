<template>
  <div class="compress">
    <ui-button @click="handleClick()" :loading="isRunning">Compress</ui-button>
    <div>
      <progress-bar :options="barOptions" :value="percent" />
    </div>
    <div>{{ this.current_item }} / {{ this.post_count }}</div>
  </div>
</template>
<script>
import ProgressBar from "vuejs-progress-bar";
import Pictifly from "../pictifly";
import Compressor from "compressorjs";
const { $, ajaxUrl, nonce, quality, max_file_uploads } = Pictifly;
export default {
  data() {
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
      barOptions: this.getBarOptions()
    };
  },
  components: {
    ProgressBar
  },
  mounted() {},
  methods: {
    handleClick() {
      if (!this.isRunning) {
        this.isRunning = true;
        this.getAttachments();
      }
    },
    getAttachments() {
      console.log("get", this.current_item);
      const data = {
        action: "pf_get_attachments",
        nonce: nonce,
        current_item: this.current_item
      };
      $.ajax({
        type: "post",
        url: ajaxUrl,
        data: data,
        error: (response, error) => {
          console.error("response", response);
          console.error("error", error);
        },
        success: response => {
          this.post_count = response.data.post_count;
          this.current_item = response.data.current_item;
          this.compressImages(response);
        }
      });
    },
    async compressImages(response) {
      const { attachments } = response.data;
      const promises = [];
      // const blobs = [];
      console.log("receive attachment", response);
      console.log("Receive " + attachments.length + " attachments");
      attachments.forEach((attachment, key) => {
        console.log(key + "attachment have" + attachment.files.length);
        attachment.files.forEach(file => {
          promises.push(this.compressSize(file));
          promises.push(this.compressSize(file, true));
        });
      });

      const files = await Promise.all(promises);
      // PHP have a maximum file number to send in one post method
      // So we need to split our array of file into multiple array
      const filesChunks = [];
      // const chunkMaxSize = this.max_file_uploads;
      const chunkMaxSize = 2;

      while (files.length > 0) {
        filesChunks.push(files.splice(0, chunkMaxSize));
      }
      // https://stackoverflow.com/a/37576787/2838586
      await Promise.all(
        filesChunks.map(async filesChunk => {
          await this.uploadCompressedFiles(filesChunk);
        })
      );
      this.nextPage();
    },
    compressSize(file, webp = false) {
      let url = webp ? file.url + ".webp" : file.url;
      return new Promise(resolve => {
        fetch(url)
          .then(res => res.blob()) // Gets the response and returns it as a blob
          .then(blob => {
            if (blob.type == "text/html") {
              console.error("not image but html", file.url);
              resolve(false);
            } else {
              new Compressor(blob, {
                quality: this.quality / 100,
                success: blob => {
                  resolve({ ...file, blob, webp });
                },
                error(err) {
                  console.error(err);
                  resolve(false);
                }
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
          data.append("files_keys[]", fileData.key);
          data.append("attachments_ids[]", fileData.id);
          data.append("files_path[]", fileData.file);
          data.append("files_iswebp[]", fileData.webp);
        });
        $.ajax({
          type: "post",
          url: this.ajaxUrl,
          data: data,
          processData: false,
          contentType: false,
          error: (response, error) => {
            console.error("response", response);
            console.error("error", error);
          },
          success: () => {
            resolve(true);
          }
        });
      });
    },
    nextPage() {
      this.percent = parseInt((this.current_item / this.post_count) * 100);
      if (this.current_item < this.post_count) {
        this.getAttachments();
      } else {
        this.isRunning = false;
        this.current_item = 0;
      }
    },
    getBarOptions() {
      return {
        text: {
          color: "#FFFFFF",
          shadowEnable: true,
          shadowColor: "#000000",
          fontSize: 20,
          fontFamily: "Helvetica",
          dynamicPosition: true,
          hideText: true
        },
        progress: {
          color: "#2dbd2d",
          backgroundColor: "#333333"
        },
        layout: {
          height: 200,
          width: 200,
          verticalTextAlign: 100,
          horizontalTextAlign: 100,
          zeroOffset: 0,
          strokeWidth: 10,
          progressPadding: 0,
          type: "circle"
        }
      };
    }
  }
};
</script>
<style lang="scss" scoped>
::v-deep .progress-bar {
  position: relative;
  width: auto !important;
  div {
    position: absolute !important;
    left: 50% !important;
    top: 50% !important;
    transform: translate(-50%, -50%);
  }
  svg {
    display: block;
  }
}
</style>
