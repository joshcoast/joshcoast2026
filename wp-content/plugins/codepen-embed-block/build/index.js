/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/edit.js":
/*!*********************!*\
  !*** ./src/edit.js ***!
  \*********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _settings__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./settings */ "./src/settings.js");
/* harmony import */ var _utils_getPenHTML__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./utils/getPenHTML */ "./src/utils/getPenHTML.js");
/* harmony import */ var _utils_getPenID__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./utils/getPenID */ "./src/utils/getPenID.js");
/* harmony import */ var _utils_getNewEditorCheck__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./utils/getNewEditorCheck */ "./src/utils/getNewEditorCheck.js");

/**
 * WordPress dependencies
 */




/**
 * Internal dependencies
 */





/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
function Edit({
  attributes,
  setAttributes,
  isSelected
}) {
  const {
    penURL,
    penID
  } = attributes;
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps)();
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...blockProps
  }, !!isSelected && (0,_settings__WEBPACK_IMPORTED_MODULE_4__["default"])(attributes, setAttributes), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
    style: {
      textAlign: "center",
      border: "solid 1px rgba(100,100,100,0.25)"
    },
    onChange: value => setAttributes({
      penURL: value,
      penID: (0,_utils_getPenID__WEBPACK_IMPORTED_MODULE_6__["default"])(value),
      isEditorURL: (0,_utils_getNewEditorCheck__WEBPACK_IMPORTED_MODULE_7__["default"])(value)
    }),
    value: penURL,
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Enter a Pen URL..."),
    label: null
  }), penURL === "" || !penURL.length ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "error"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      textAlign: "center"
    }
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("A Pen URL is required. Please enter one in the field above."))) : !penID || penID === "" || penID === null ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "error"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      textAlign: "center"
    }
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("The pen URL is invalid."))) : (0,_utils_getPenHTML__WEBPACK_IMPORTED_MODULE_5__["default"])(attributes));
}

/***/ }),

/***/ "./src/save.js":
/*!*********************!*\
  !*** ./src/save.js ***!
  \*********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ save)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _utils_getPenHTML__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./utils/getPenHTML */ "./src/utils/getPenHTML.js");

/**
 * WordPress dependencies
 */



/**
 * Internal dependencies
 */


/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
function save({
  attributes
}) {
  const {
    penID,
    penURL
  } = attributes;
  if (!penID || penID === "" || penID === null) {
    return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
      className: "wp-block-cp-codepen-gutenberg-embed-block codepen error",
      style: {
        textAlign: "center"
      }
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)("The pen URL is invalid."));
  }
  return (0,_utils_getPenHTML__WEBPACK_IMPORTED_MODULE_3__["default"])(attributes);
}

/***/ }),

/***/ "./src/settings.js":
/*!*************************!*\
  !*** ./src/settings.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ formFields)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);

/**
 * WordPress dependencies
 */



function formFields(attributes, setAttributes) {
  const {
    penHeight,
    penType,
    clickToLoad,
    penTheme,
    editable
  } = attributes;
  const penTypeOptions = [{
    value: "result",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Result only")
  }, {
    value: "html,result",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("HTML and result")
  }, {
    value: "js,result",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("JavaScript and result")
  }, {
    value: "css,result",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("CSS and result")
  }];
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.InspectorControls, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Pen Height (in pixels)"),
    onChange: value => setAttributes({
      penHeight: Number.parseInt(value, 10)
    }),
    value: penHeight,
    type: "number"
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Pen Theme ID"),
    select: penTheme,
    onChange: value => setAttributes({
      penTheme: value
    }),
    value: penTheme,
    help: "You can use 'light' and 'dark' also"
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Pen View"),
    select: penType,
    options: penTypeOptions,
    onChange: value => setAttributes({
      penType: value
    }),
    value: penType
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Use click-to-load (increases performance)"),
    checked: !!clickToLoad,
    onChange: () => setAttributes({
      clickToLoad: !clickToLoad
    })
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.ToggleControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Editable? (decreases performance, but becomes live editor)"),
    checked: !!editable,
    onChange: () => setAttributes({
      editable: !editable
    })
  }));
}

/***/ }),

/***/ "./src/utils/getNewEditorCheck.js":
/*!****************************************!*\
  !*** ./src/utils/getNewEditorCheck.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ getNewEditorCheck)
/* harmony export */ });
function getNewEditorCheck(penURL) {
  return penURL.includes("/editor/");
}

/***/ }),

/***/ "./src/utils/getPenHTML.js":
/*!*********************************!*\
  !*** ./src/utils/getPenHTML.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ getPenHTML)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _getNewEditorCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./getNewEditorCheck */ "./src/utils/getNewEditorCheck.js");

/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */

function getPenHTML(attributes) {
  const {
    penID,
    penTheme,
    penHeight,
    penURL,
    penType,
    clickToLoad,
    editable,
    isEditorURL
  } = attributes;
  if (penID === null && penURL === "") {
    return null;
  }
  if (null === penID) {
    return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
      className: "codepen error",
      style: {
        textAlign: "center"
      }
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("The pen URL is invalid."));
  }
  let embedUrlPrefix = "//codepen.io/anon/embed";
  if (isEditorURL || (0,_getNewEditorCheck__WEBPACK_IMPORTED_MODULE_2__["default"])(penURL)) {
    embedUrlPrefix = "//codepen.io/editor/anon/embed";
  }
  if (clickToLoad) {
    embedUrlPrefix += "/preview";
  }
  let src = `${embedUrlPrefix}/${penID}?height=${penHeight}&theme-id=${penTheme}&slug-hash=${penID}&default-tab=${penType}`;
  if (editable) {
    src += "&editable=true";
  }
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "cp_embed_wrapper"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("iframe", {
    id: `cp_embed_${penID}`,
    src: src,
    height: penHeight,
    scrolling: "no",
    frameborder: "0",
    allowTransparency: true,
    allowFullScreen: true,
    allowPaymentRequest: "true",
    name: `CodePen Embed ${penID}`,
    title: `CodePen Embed ${penID}`,
    className: "cp_embed_iframe",
    style: {
      width: "100%",
      overflow: "hidden"
    }
  }, "CodePen Embed Fallback"));
}

/***/ }),

/***/ "./src/utils/getPenID.js":
/*!*******************************!*\
  !*** ./src/utils/getPenID.js ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ getPenID)
/* harmony export */ });
function getPenID(penURL) {
  let matches_array = penURL.match(/http[s]?:\/\/codepen\.io\/(?:editor\/)?(?:team\/)?[\w-]+\/(?:pen|details|full|pres|debug|live|embed)\/([A-Z-a-z0-9\/]{32,}|[A-Za-z]{5,8})\/?/);
  return Array.isArray(matches_array) ? matches_array[1] : null;
}

// https://regex101.com/r/d8MV8q/1

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils_getPenID__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./utils/getPenID */ "./src/utils/getPenID.js");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./save */ "./src/save.js");
/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-blocks/#registerblocktype
 */




try {
  (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)("cp/codepen-gutenberg-embed-block", {
    title: "CodePen Embed",
    category: "embed",
    edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"],
    save: _save__WEBPACK_IMPORTED_MODULE_3__["default"],
    attributes: {
      penURL: {
        type: "string",
        default: ""
      },
      penID: {
        type: "string",
        default: ""
      },
      penHeight: {
        type: "number",
        default: 450
      },
      penTheme: {
        type: "string",
        default: "1"
      },
      penType: {
        type: "string",
        default: "result"
      },
      isEditorURL: {
        type: "boolean",
        default: false
      }
    },
    transforms: {
      from: [{
        type: "raw",
        priority: 8,
        isMatch: node => node.nodeName === "P" && node.className === "codepen",
        transform: function (node) {
          let penURL = node.querySelector("a").getAttribute("href");
          return (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.createBlock)("cp/codepen-gutenberg-embed-block", {
            penURL: penURL,
            penID: (0,_utils_getPenID__WEBPACK_IMPORTED_MODULE_1__["default"])(penURL)
          });
        }
      }, {
        type: "raw",
        priority: 8,
        isMatch: node => node.nodeName === "P" && node.innerText.startsWith("https://codepen.io/"),
        transform: function (node) {
          return (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.createBlock)("cp/codepen-gutenberg-embed-block", {
            penURL: node.innerText,
            penID: (0,_utils_getPenID__WEBPACK_IMPORTED_MODULE_1__["default"])(node.innerText)
          });
        }
      }]
    }
  });
  console.log("Block with all required attributes registration completed!");
} catch (error) {
  console.error("Error registering block:", error);
}
})();

/******/ })()
;
//# sourceMappingURL=index.js.map