/**
 * WordPress dependencies
 */
import { useBlockProps } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

/**
 * Internal dependencies
 */
import getPenHTML from "./utils/getPenHTML";

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
export default function save({ attributes }) {
  const { penID, penURL } = attributes;

  if (!penID || penID === "" || penID === null) {
    return (
      <p
        className="wp-block-cp-codepen-gutenberg-embed-block codepen error"
        style={{ textAlign: "center" }}
      >
        {__("The pen URL is invalid.")}
      </p>
    );
  }

  return getPenHTML(attributes);
}
