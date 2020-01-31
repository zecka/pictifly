module.exports = {
  root: true,
  env: {
    node: true,
  },
  globals: {
    _: true,
    pictifly: true,
    jQuery: true,
  },
  extends: ["plugin:vue/essential", "@vue/prettier"],
  rules: {
    "no-console": process.env.NODE_ENV === "production" ? "error" : "off",
    "no-debugger": process.env.NODE_ENV === "production" ? "error" : "off",
    "no-undef": "off",
    strict: 0,
  },
  parserOptions: {
    parser: "babel-eslint",
  },
};
