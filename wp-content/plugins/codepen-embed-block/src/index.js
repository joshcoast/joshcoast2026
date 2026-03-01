/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-blocks/#registerblocktype
 */
import { registerBlockType, createBlock } from "@wordpress/blocks";
import getPenID from "./utils/getPenID";
import Edit from "./edit";
import Save from "./save";

try {
  registerBlockType("cp/codepen-gutenberg-embed-block", {
    title: "CodePen Embed",
    category: "embed",
    edit: Edit,
    save: Save,
    attributes: {
      penURL: {
        type: "string",
        default: "",
      },
      penID: {
        type: "string",
        default: "",
      },
      penHeight: {
        type: "number",
        default: 450,
      },
      penTheme: {
        type: "string",
        default: "1",
      },
      penType: {
        type: "string",
        default: "result",
      },
      isEditorURL: {
        type: "boolean",
        default: false,
      },
    },
    transforms: {
      from: [
        {
          type: "raw",
          priority: 8,
          isMatch: (node) =>
            node.nodeName === "P" && node.className === "codepen",
          transform: function (node) {
            let penURL = node.querySelector("a").getAttribute("href");
            return createBlock("cp/codepen-gutenberg-embed-block", {
              penURL: penURL,
              penID: getPenID(penURL),
            });
          },
        },
        {
          type: "raw",
          priority: 8,
          isMatch: (node) =>
            node.nodeName === "P" &&
            node.innerText.startsWith("https://codepen.io/"),
          transform: function (node) {
            return createBlock("cp/codepen-gutenberg-embed-block", {
              penURL: node.innerText,
              penID: getPenID(node.innerText),
            });
          },
        },
      ],
    },
  });

  console.log("Block with all required attributes registration completed!");
} catch (error) {
  console.error("Error registering block:", error);
}
