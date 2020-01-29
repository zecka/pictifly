var fs = require("fs");
const svgson = require("svgson");
var parser = require("xml2json");
const { stringify, parseSync } = require("svgson");

const icons = {};
const filesName = fs
  .readdirSync("./src/assets/svg/", { withFileTypes: true })
  .filter(item => !item.isDirectory())
  .map(item => item.name);

filesName.forEach(fileName => {
  let fileContent = fs.readFileSync("./src/assets/svg/" + fileName, "utf8");

  const key = fileName.replace(".svg", "");

  /* icons[key] = {
    data: svgson.parseSync(fileContent),
    content: fileContent
  }; */
  const json = parseSync(fileContent);
  const { width, height, fill, stroke, color } = json.attributes;

  // define size at 1em
  if (width && height) {
    if (width === height) {
      json.attributes.width = "1em";
      json.attributes.height = "1em";
    }
  } else {
    json.attributes["width"] = "1em";
    json.attributes["height"] = "1em";
  }
  if (fill && fill !== "none") {
    json.attributes.fill = "currentColor";
  }
  if (stroke && stroke !== "none") {
    json.attributes.stroke = "currentColor";
  }
  if (color && color !== "none") {
    json.attributes.color = "currentColor";
  }
  console.log(json);
  if (json.attributes["stroke-width"]) {
    json.attributes["stroke-width"] = "2";
  }
  icons[key] = {
    attributes: json.attributes,
    content: stringify(json.children)
  };
});

fs.writeFileSync("./src/assets/icons.json", JSON.stringify(icons), "utf8");
