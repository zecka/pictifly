<template>
  <component
    :is="theMarkup"
    :to="toData"
    :href="href"
    :class="classes.join(' ') + ' ' + getLoadingClass()"
    @click="$emit('click')"
  >
    <span class="btn__icon" v-if="icon">
      <svgicon :icon="theIcon"></svgicon>
    </span>
    <span v-if="$slots.default" class="btn__content" :class="{ loading: loading }">
      <span class="btn__loading" v-if="loading">
        <svgicon icon="spinner" />
      </span>
      <span class="btn__text"><slot /></span>
    </span>
  </component>
</template>
<script>
export default {
  name: "ui-button",
  props: {
    icon: { required: false, default: null, type: String },
    text: { required: false, default: false },
    outline: { required: false, default: false },
    alt: { required: false, default: false },
    round: { required: false, default: false },
    size: { required: false, default: "m" },
    color: { required: false, default: "black" },
    loading: { required: false, default: false },
    href: { required: false, default: false },
    markup: { required: false, default: "button" },
    to: { required: false },
  },
  data() {
    return {
      theIcon: this.icon,
      theMarkup: "button",
      classes: [],
    };
  },
  computed: {
    toData: function() {
      if (this.to) {
        return this.to;
      }
      if (this.markup === "router-link" || this.markup === "a") {
        return "";
      }
      return false;
    },
  },
  mounted() {
    if (this.loading) {
      this.theIcon = "spinner";
    }
  },
  created() {
    if (this.href !== false) {
      this.theMarkup = "a";
    }
    this.setClasses();
  },
  methods: {
    getLoadingClass() {
      return this.loading ? " loading" : "";
    },
    setClasses() {
      const classes = [];
      classes.push("btn");
      if (this.text !== false) {
        classes.push("btn--text");
      } else {
        if (this.alt === false) {
          if (this.icon) {
            classes.push("btn--icon");
          }
          if (this.outline !== false) {
            classes.push("outline");
          } else {
            classes.push("plain");
          }
        }
      }

      if (this.alt !== false) {
        classes.push("btn--alt");
      }
      classes.push("btn--" + this.size.toLowerCase());
      classes.push("btn--" + this.color.toLowerCase());
      if (!this.$slots.default) {
        classes.push("btn--noslot");
      }
      if (this.round !== false) {
        classes.push("btn--round");
      }
      this.classes = classes;
    },
    removeClass(toRemove) {
      this.classes = this.classes.filter(className => className != toRemove);
    },
  },
  watch: {
    loading: function(loading) {
      if (!this.$slots.default) {
        this.theIcon = loading ? "spinner" : this.icon;
      }
    },
    color: function() {
      this.setClasses();
    },
  },
};
</script>
<style lang="scss">
.btn {
  padding: 0;
  cursor: pointer;
  text-decoration: none;
  color: black;
  border: 1px solid black;
  height: 2.5em;
  font-size: 1rem;
  display: inline-flex;
  align-items: center;
  position: relative;
  border-radius: $radius;
  transition: all 0.3s;
  &:hover {
    opacity: 0.6;
  }
  &.outline {
    background-color: transparent;
  }
  &.loading {
    cursor: not-allowed;
    pointer-events: none;
  }
  &.plain {
    color: $white;
    background-color: $black;
    &:hover {
      opacity: 0.8;
    }
  }
  &__content {
    padding: 0 1.5em;
    position: relative;
    .btn--alt &,
    .btn--text & {
      padding: 0;
    }
    .btn--icon & {
      padding: 0 1em;
    }
    .btn__text {
      transition: opacity 0.3s;
      opacity: 1;
    }
    &.loading .btn__text {
      opacity: 0.4;
    }
  }
  &__loading {
    @include absolute(0, 0, 0, 0);
  }

  // SIZES
  &--xs {
    font-size: 0.7rem;
  }
  &--s {
    font-size: 0.85rem;
  }
  &--l {
    font-size: 1.3rem;
  }
  &--xl {
    font-size: 1.5rem;
  }
  // COLOR
  &--primary {
    color: $primary;
    &.plain {
      background-color: $primary;
      border: none;
      color: $white;
    }
    &.outline,
    &.outline .btn__icon::before {
      border-color: $primary;
      color: $primary;
    }
  }
  &--black {
    color: $black;
    &.plain {
      background-color: $black;
      border: none;
      .btn__icon::before {
        background: rgba($white, 0.2);
      }
    }
    &.outline,
    &.outline .btn__icon::before {
      border-color: $black;
      color: $black;
    }
  }
  &--white {
    &.plain {
      color: $black;
      background-color: $white;
      border: none;
      .btn__icon::before {
        background: rgba($white, 0.2);
      }
    }
    &.outline,
    &.outline .btn__icon::before {
      border-color: $white;
      color: $white;
    }
  }
  &--red {
    color: $red;
    &.plain {
      background-color: $red;
      border: none;
    }
    &.outline,
    &.outline .btn__icon::before {
      border-color: $red;
      color: $red;
    }
  }
  &--green {
    color: $green;
    &.plain {
      background-color: $green;
      border: none;
    }
    &.outline,
    &.outline .btn__icon::before {
      border-color: $green;
      color: $green;
    }
  }
  &--gray {
    color: $gray;
    &.plain {
      background-color: $gray;
      border: none;
    }
    &.outline,
    &.outline .btn__icon::before {
      border-color: $gray;
      color: $gray;
    }
  }
  &--blue {
    color: $blue;
    &.plain {
      background-color: $blue;
      border: none;
    }
    &.outline,
    &.outline .btn__icon::before {
      border-color: $blue;
      color: $blue;
    }
  }
  &--google {
    color: $google-color;
    &.plain {
      background-color: $google-color;
      border: none;
    }
    &.outline,
    &.outline .btn__icon::before {
      border-color: $google-color;
      color: $google-color;
    }
  }
  &--facebook {
    color: $facebook-color;
    &.plain {
      background-color: $facebook-color;
      border: none;
    }
    &.outline,
    &.outline .btn__icon::before {
      border-color: $facebook-color;
      color: $facebook-color;
    }
  }

  &--alt {
    position: relative;
    display: inline-flex;
    align-items: center;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-decoration: none;
    padding-bottom: 5px;
    color: $black;
    border: none;
    padding: 0 0 5px 0;
    height: unset;
    background-color: transparent;
    .btn__text {
      color: $black;
    }
    &button {
      background: transparent;
    }
    svg {
      width: 1.2em;
      height: 1.2em;
      fill: $black;
    }
    &::after {
      content: $pseudo-content;
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 100%;
      background: $primary;
      height: 2px;
      transition: width 0.3s;
    }
    &:hover::after {
      width: calc(100% + 10px);
    }
  }
  &--text {
    display: inline-flex;
    align-items: center;
    height: unset;
    padding: 0;
    border: 0;
    background: transparent;
    svg {
      margin-right: 0.2em;
      width: 0.8em;
      height: 0.8em;
    }
    .btn__icon {
      display: inline-flex;
    }
  }
  &--icon {
    position: relative;
    padding-left: 0;
    align-items: center;
    .btn__icon {
      text-align: center;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2.7em;
      &::before {
        color: white;
        width: 2.7em;
        content: $pseudo-content;
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        background: rgba($black, 0.2);
      }
      svg {
        position: relative;
        display: inline-block;
        margin: 0;
      }
    }
    &.outline .btn__icon::before {
      background: transparent;
      border-right-style: solid;
      border-right-width: 1px;
    }
  }
  &--round {
    border-radius: 200px;
    width: 2.5em;
  }
  &--noslot {
    .btn__icon::before {
      display: none;
    }
  }
}
</style>
