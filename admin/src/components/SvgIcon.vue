<template>
  <svg v-if="exist" v-bind="attributes" class="svg-icon" v-html="content" />
  <span v-else>{{ icon }}</span>
</template>
<script>
import IconJson from "../assets/icons.json";

export default {
  name: "svgicon",
  props: {
    icon: { required: true },
    width: { required: false, default: "1em" },
    height: { required: false, default: "1em" },
    color: { required: false, default: "currentColor" }
  },
  computed: {
    exist() {
      if (IconJson[this.icon]) {
        return true;
      } else {
        return false;
      }
    },
    content() {
      if (IconJson[this.icon]) {
        return IconJson[this.icon].content;
      } else {
        return false;
      }
    },
    attributes() {
      const { attributes } = IconJson[this.icon];
      const { stroke, fill } = attributes;
      attributes.width = this.width;
      attributes.height = this.height;
      if (this.color !== "currentColor") {
        if (fill && fill !== "none") {
          attributes.fill = this.color;
        }
        if (stroke && stroke !== "none") {
          attributes.stroke = this.color;
        }
      }
      return attributes;
    }
  }
};
</script>
