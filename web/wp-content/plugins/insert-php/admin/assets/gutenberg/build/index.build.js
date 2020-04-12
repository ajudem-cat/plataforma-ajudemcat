/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("var _wp$blocks = wp.blocks,\n    registerBlockType = _wp$blocks.registerBlockType,\n    createBlock = _wp$blocks.createBlock;\nvar Fragment = wp.element.Fragment;\nvar _wp$components = wp.components,\n    TextControl = _wp$components.TextControl,\n    SelectControl = _wp$components.SelectControl;\nvar _wp$editor = wp.editor,\n    InspectorControls = _wp$editor.InspectorControls,\n    PlainText = _wp$editor.PlainText,\n    RichText = _wp$editor.RichText,\n    InnerBlocks = _wp$editor.InnerBlocks;\nvar snippets = window.winp_snippets.data,\n    firstSnippet = snippets[Object.keys(snippets)[0]] || '',\n    __ = wp.i18n.__;\nregisterBlockType('wp-plugin-insert-php/winp-snippet', {\n  title: __('Woody snippets'),\n  description: __('Executes PHP code, uses conditional logic to insert ads, text, media content and external service’s code. Ensures no content duplication.'),\n  //icon: 'format-aside',\n  icon: React.createElement(\"svg\", {\n    width: \"25\",\n    height: \"25\",\n    viewBox: \"0 0 254 254\",\n    shapeRendering: \"geometricPrecision\",\n    textRendering: \"geometricPrecision\",\n    imageRendering: \"optimizeQuality\",\n    fillRule: \"evenodd\",\n    clipRule: \"evenodd\"\n  }, React.createElement(\"path\", {\n    d: \"M76 187l44 48 45-49c1-1-12 5-22 5-10-1-23-15-23-15s-3 9-19 13c-11 4-25-2-25-2z\"\n  }), React.createElement(\"ellipse\", {\n    cx: 99,\n    cy: 134,\n    rx: 11,\n    ry: 12\n  }), React.createElement(\"ellipse\", {\n    cx: 143,\n    cy: 135,\n    rx: 11,\n    ry: 12\n  }), React.createElement(\"path\", {\n    d: \"M103 103s-10-12-4-35c6-22 16-29 23-33 8-5-5 16-4 20 2 3 14-25 39-27 25-1 41 21 41 21l-13-1s13 5 18 11 11 15 10 18-5 1-5 1 19 13 20 24c1 10-11-10-39-14-29-4-50 14-45 8s17-16 29-17 27 3 27 3-21-12-19-16c2-5 16-2 16-2s-9-8-20-9c-10-2-19 3-18-1 1-5 10-13 15-12 6 1-12-6-24-2-11 5-19 11-26 25s-9 22-13 20c-3-1-4-17-4-17l-4 35zm-60 35l16-21s3-8-1-12c-5-3-9-1-9-1l-22 28s-3 3-2 6c0 2 2 5 2 5l21 28s7 3 10-2c4-5 2-9 2-9l-17-22z\"\n  }), React.createElement(\"path\", {\n    d: \"M199 138l-17-21s-3-8 2-12c4-3 9-1 9-1l23 28s3 3 3 6c0 2-3 5-3 5l-22 28s-7 3-11-2-2-9-2-9l18-22z\"\n  })),\n  category: 'formatting',\n  attributes: {\n    id: {\n      type: 'int',\n      default: ''\n    },\n    attrs: {\n      type: 'array',\n      default: firstSnippet.tags || []\n    },\n    attrValues: {\n      type: 'array',\n      default: []\n    }\n  },\n  edit: function edit(props) {\n    var _props$attributes = props.attributes,\n        id = _props$attributes.id,\n        attrValues = _props$attributes.attrValues,\n        attrs = _props$attributes.attrs;\n    var defaultProps = {};\n\n    if (id === '') {\n      defaultProps['id'] = firstSnippet.id || '';\n    }\n\n    if (defaultProps !== {}) {\n      props.setAttributes(defaultProps);\n    }\n\n    var options = [],\n        snippedIds = Object.keys(snippets),\n        s = 0;\n\n    for (; s < snippedIds.length; s++) {\n      var currentSnippedId = snippedIds[s];\n      options.push({\n        label: snippets[currentSnippedId].title,\n        value: currentSnippedId\n      });\n    }\n\n    function onSnippetChange(id) {\n      props.setAttributes({\n        id: id,\n        attrs: snippets[id].tags\n      });\n    }\n\n    function onAttributeChange(name, value) {\n      var outcome = [];\n\n      for (var i = 0; i < attrs.length; i++) {\n        if (attrs[i] === name) {\n          outcome[i] = value;\n        } else {\n          if (attrValues.hasOwnProperty(i)) {\n            outcome[i] = attrValues[i];\n          } else {\n            outcome[i] = '';\n          }\n        }\n      }\n\n      props.setAttributes({\n        attrValues: outcome\n      });\n    }\n\n    function prepareTags() {\n      var tags = [];\n\n      if (attrs) {\n        var _loop = function _loop(i) {\n          var name = attrs[i];\n          tags.push(React.createElement(\"div\", {\n            className: \"winp-snippet-gb-content-tag\"\n          }, React.createElement(TextControl, {\n            label: __('Attribute \"') + attrs[i] + '\":',\n            className: \"winp-snippet-gb-tag\",\n            value: attrValues[i] || '',\n            name: name,\n            placeholder: __('Attribute value (variable $' + attrs[i] + ')'),\n            onChange: function onChange(value) {\n              return onAttributeChange(name, value);\n            }\n          })));\n        };\n\n        for (var i = 0; i < attrs.length; i++) {\n          _loop(i);\n        }\n      }\n\n      return tags;\n    }\n\n    return [React.createElement(\"div\", {\n      className: \"winp-snippet-gb-container\"\n    }, React.createElement(\"div\", {\n      className: \"winp-snippet-gb-dropdown\"\n    }, React.createElement(SelectControl, {\n      label: __('Select snippet shortcode:'),\n      value: id,\n      options: options,\n      onChange: onSnippetChange\n    })), React.createElement(\"div\", {\n      className: \"winp-snippet-gb-content\"\n    }, React.createElement(InnerBlocks, null)), React.createElement(InspectorControls, null, React.createElement(\"div\", {\n      id: \"winp-snippet-gb-content-tags\"\n    }, prepareTags())))];\n  },\n  save: function save(props) {\n    return React.createElement(\"div\", null, React.createElement(InnerBlocks.Content, null));\n  }\n});\n\n//# sourceURL=webpack:///./src/index.js?");

/***/ })

/******/ });