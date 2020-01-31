<template>
  <div class="confirmation">
    <div class="confirmation__backdrop" @click="closeConfirmation"></div>
    <div class="confirmation__wrap">
      <div class="confirmation__title">{{ title }}</div>
      <div class="confirmation__description">
        <slot />
      </div>
      <div class="confirmation__actions">
        <ui-button color="white" outline @click="closeConfirmation">Cancel</ui-button>
        <ui-button :color="confirmColor" @click="$emit('confirm')">{{ confirmText }}</ui-button>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    title: { default: "Confirmation" },
    confirmText: { default: "confirm" },
    confirmColor: { default: "red" },
  },
  data() {
    return {};
  },
  methods: {
    closeConfirmation() {
      this.$emit("close");
    },
  },
};
</script>
<style lang="scss" scoped>
.confirmation {
  z-index: 22;
  @include absolute(0, 0, 0, 0);
  display: flex;
  justify-content: center;
  align-items: center;
  &__title {
    border-bottom: 1px solid rgba($white, 0.2);
    font-size: 23px;
    padding: 14px 10px;
  }
  &__description {
    padding: 20px 10px;
    font-size: 17px;
    small {
      display: block;
      margin-top: 10px;
    }
  }
  &__backdrop {
    @include absolute(0, 0, 0, 0);
    background-color: rgba($white, 0.5);
  }
  &__wrap {
    border-radius: 3px;
    box-shadow: $box-shadow;
    position: relative;
    z-index: 3;
    max-width: 500px;
    background: $background;
  }
  &__actions {
    border-top: 1px solid rgba($white, 0.2);
    padding: 10px;
    text-align: right;
    > * + * {
      margin-left: 10px;
    }
  }
}
</style>
