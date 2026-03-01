/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { TextControl } from "@wordpress/components";
import { useBlockProps } from "@wordpress/block-editor";

/**
 * Internal dependencies
 */
import formFields from "./settings";
import getPenHTML from "./utils/getPenHTML";
import getPenID from "./utils/getPenID";
import getNewEditorCheck from "./utils/getNewEditorCheck";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes, isSelected }) {
  const { penURL, penID } = attributes;
  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      {!!isSelected && formFields(attributes, setAttributes)}
      <TextControl
        style={{
          textAlign: "center",
          border: "solid 1px rgba(100,100,100,0.25)",
        }}
        onChange={(value) =>
          setAttributes({
            penURL: value,
            penID: getPenID(value),
            isEditorURL: getNewEditorCheck(value),
          })
        }
        value={penURL}
        placeholder={__("Enter a Pen URL...")}
        label={null}
      />
      {penURL === "" || !penURL.length ? (
        <div className="error">
          <p style={{ textAlign: "center" }}>
            {__("A Pen URL is required. Please enter one in the field above.")}
          </p>
        </div>
      ) : !penID || penID === "" || penID === null ? (
        <div className="error">
          <p style={{ textAlign: "center" }}>{__("The pen URL is invalid.")}</p>
        </div>
      ) : (
        getPenHTML(attributes)
      )}
    </div>
  );
}
