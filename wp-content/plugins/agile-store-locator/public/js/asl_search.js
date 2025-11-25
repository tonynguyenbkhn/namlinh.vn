(function($) {

  window['destination']  = null;


  if (!Array.isArray) {
    Array.isArray = function(arg) {
      return Object.prototype.toString.call(arg) === '[object Array]';
    };
  }

  //  make sure it is not intialized
  //http://getbootstrap.com/customize/?id=23dc7cc41297275c7297bb237a95bbd7
  if(!jQuery.fn.adropdown) {
    /*!
    * Bootstrap v4.6.1 (https://getbootstrap.com/)
    * Copyright 2011-2023 The Bootstrap Authors (https://github.com/twbs/bootstrap/graphs/contributors)
    * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
    */
    !function(e,t){"object"==typeof exports&&"undefined"!=typeof module?t(exports,require("jquery")):"function"==typeof define&&define.amd?define(["exports","jquery"],t):t((e="undefined"!=typeof globalThis?globalThis:e||self).bootstrap={},e.jQuery)}(this,(function(e,t){"use strict";function n(e){return e&&"object"==typeof e&&"default"in e?e:{default:e}}var r=n(t);function o(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function i(e,t,n){return t&&o(e.prototype,t),n&&o(e,n),e}function a(){return a=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(e[r]=n[r])}return e},a.apply(this,arguments)}var s="bs4_transitionend";var l={TRANSITION_END:s,getUID:function(e){do{e+=~~(1e6*Math.random())}while(document.getElementById(e));return e},getSelectorFromElement:function(e){var t=e.getAttribute("data-target");if(!t||"#"===t){var n=e.getAttribute("href");t=n&&"#"!==n?n.trim():""}try{return document.querySelector(t)?t:null}catch(e){return null}},getTransitionDurationFromElement:function(e){if(!e)return 0;var t=r.default(e).css("transition-duration"),n=r.default(e).css("transition-delay"),o=parseFloat(t),i=parseFloat(n);return o||i?(t=t.split(",")[0],n=n.split(",")[0],1e3*(parseFloat(t)+parseFloat(n))):0},reflow:function(e){return e.offsetHeight},triggerTransitionEnd:function(e){r.default(e).trigger(s)},supportsTransitionEnd:function(){return Boolean(s)},isElement:function(e){return(e[0]||e).nodeType},typeCheckConfig:function(e,t,n){for(var r in n)if(Object.prototype.hasOwnProperty.call(n,r)){var o=n[r],i=t[r],a=i&&l.isElement(i)?"element":null===(s=i)||"undefined"==typeof s?""+s:{}.toString.call(s).match(/\s([a-z]+)/i)[1].toLowerCase();if(!new RegExp(o).test(a))throw new Error(e.toUpperCase()+': Option "'+r+'" provided type "'+a+'" but expected type "'+o+'".')}var s},findShadowRoot:function(e){if(!document.documentElement.attachShadow)return null;if("function"==typeof e.getRootNode){var t=e.getRootNode();return t instanceof ShadowRoot?t:null}return e instanceof ShadowRoot?e:e.parentNode?l.findShadowRoot(e.parentNode):null},jQueryDetection:function(){"undefined"==typeof r.default&&console.log("Bootstrap's JavaScript requires jQuery. jQuery must be included before Bootstrap's JavaScript.");var e=r.default.fn.jquery.split(" ")[0].split(".");(e[0]<2&&e[1]<9||1===e[0]&&9===e[1]&&e[2]<1||e[0]>=4)&&console.log("Bootstrap's JavaScript requires at least jQuery v1.9.1 but less than v4.0.0")}};l.jQueryDetection(),r.default.fn.emulateTransitionEnd=function(e){var t=this,n=!1;return r.default(this).one(s,(function(){n=!0})),setTimeout((function(){n||l.triggerTransitionEnd(t)}),e),this},r.default.event.special.bs4_transitionend={bindType:s,delegateType:s,handle:function(e){if(r.default(e.target).is(this))return e.handleObj.handler.apply(this,arguments)}};var f="colision",u="bs.colision",d=r.default.fn[f],p="show",c="colision",h="collapsing",m="colisiond",g="width",v='[data-toggle="colision"]',y={toggle:!0,parent:""},b={toggle:"boolean",parent:"(string|element)"},w=function(){function e(e,t){this._isTransitioning=!1,this._element=e,this._config=this._getConfig(t),this._triggerArray=[].slice.call(document.querySelectorAll('[data-toggle="colision"][href="#'+e.id+'"],[data-toggle="colision"][data-target="#'+e.id+'"]'));for(var n=[].slice.call(document.querySelectorAll(v)),r=0,o=n.length;r<o;r++){var i=n[r],a=l.getSelectorFromElement(i),s=[].slice.call(document.querySelectorAll(a)).filter((function(t){return t===e}));null!==a&&s.length>0&&(this._selector=a,this._triggerArray.push(i))}this._parent=this._config.parent?this._getParent():null,this._config.parent||this._addAriaAndaCollapsedClass(this._element,this._triggerArray),this._config.toggle&&this.toggle()}var t=e.prototype;return t.toggle=function(){r.default(this._element).hasClass(p)?this.hide():this.show()},t.show=function(){var t,n,o=this;if(!(this._isTransitioning||r.default(this._element).hasClass(p)||(this._parent&&0===(t=[].slice.call(this._parent.querySelectorAll(".show, .collapsing")).filter((function(e){return"string"==typeof o._config.parent?e.getAttribute("data-parent")===o._config.parent:e.classList.contains(c)}))).length&&(t=null),t&&(n=r.default(t).not(this._selector).data(u))&&n._isTransitioning))){var i=r.default.Event("show.bs.colision");if(r.default(this._element).trigger(i),!i.isDefaultPrevented()){t&&(e._jQueryInterface.call(r.default(t).not(this._selector),"hide"),n||r.default(t).data(u,null));var a=this._getDimension();r.default(this._element).removeClass(c).addClass(h),this._element.style[a]=0,this._triggerArray.length&&r.default(this._triggerArray).removeClass(m).attr("aria-expanded",!0),this.setTransitioning(!0);var s="scroll"+(a[0].toUpperCase()+a.slice(1)),f=l.getTransitionDurationFromElement(this._element);r.default(this._element).one(l.TRANSITION_END,(function(){r.default(o._element).removeClass(h).addClass("colision show"),o._element.style[a]="",o.setTransitioning(!1),r.default(o._element).trigger("shown.bs.colision")})).emulateTransitionEnd(f),this._element.style[a]=this._element[s]+"px"}}},t.hide=function(){var e=this;if(!this._isTransitioning&&r.default(this._element).hasClass(p)){var t=r.default.Event("hide.bs.colision");if(r.default(this._element).trigger(t),!t.isDefaultPrevented()){var n=this._getDimension();this._element.style[n]=this._element.getBoundingClientRect()[n]+"px",l.reflow(this._element),r.default(this._element).addClass(h).removeClass("colision show");var o=this._triggerArray.length;if(o>0)for(var i=0;i<o;i++){var a=this._triggerArray[i],s=l.getSelectorFromElement(a);null!==s&&(r.default([].slice.call(document.querySelectorAll(s))).hasClass(p)||r.default(a).addClass(m).attr("aria-expanded",!1))}this.setTransitioning(!0),this._element.style[n]="";var f=l.getTransitionDurationFromElement(this._element);r.default(this._element).one(l.TRANSITION_END,(function(){e.setTransitioning(!1),r.default(e._element).removeClass(h).addClass(c).trigger("hidden.bs.colision")})).emulateTransitionEnd(f)}}},t.setTransitioning=function(e){this._isTransitioning=e},t.dispose=function(){r.default.removeData(this._element,u),this._config=null,this._parent=null,this._element=null,this._triggerArray=null,this._isTransitioning=null},t._getConfig=function(e){return(e=a({},y,e)).toggle=Boolean(e.toggle),l.typeCheckConfig(f,e,b),e},t._getDimension=function(){return r.default(this._element).hasClass(g)?g:"height"},t._getParent=function(){var t,n=this;l.isElement(this._config.parent)?(t=this._config.parent,"undefined"!=typeof this._config.parent.jquery&&(t=this._config.parent[0])):t=document.querySelector(this._config.parent);var o='[data-toggle="colision"][data-parent="'+this._config.parent+'"]',i=[].slice.call(t.querySelectorAll(o));return r.default(i).each((function(t,r){n._addAriaAndaCollapsedClass(e._getTargetFromElement(r),[r])})),t},t._addAriaAndaCollapsedClass=function(e,t){var n=r.default(e).hasClass(p);t.length&&r.default(t).toggleClass(m,!n).attr("aria-expanded",n)},e._getTargetFromElement=function(e){var t=l.getSelectorFromElement(e);return t?document.querySelector(t):null},e._jQueryInterface=function(t){return this.each((function(){var n=r.default(this),o=n.data(u),i=a({},y,n.data(),"object"==typeof t&&t?t:{});if(!o&&i.toggle&&"string"==typeof t&&/show|hide/.test(t)&&(i.toggle=!1),o||(o=new e(this,i),n.data(u,o)),"string"==typeof t){if("undefined"==typeof o[t])throw new TypeError('No method named "'+t+'"');o[t]()}}))},i(e,null,[{key:"VERSION",get:function(){return"4.6.1"}},{key:"Default",get:function(){return y}}]),e}();r.default(document).on("click.bs.colision.data-api",v,(function(e){"A"===e.currentTarget.tagName&&e.preventDefault();var t=r.default(this),n=l.getSelectorFromElement(this),o=[].slice.call(document.querySelectorAll(n));r.default(o).each((function(){var e=r.default(this),n=e.data(u)?"toggle":t.data();w._jQueryInterface.call(e,n)}))})),r.default.fn[f]=w._jQueryInterface,r.default.fn[f].Constructor=w,r.default.fn[f].noConflict=function(){return r.default.fn[f]=d,w._jQueryInterface};var _="undefined"!=typeof window&&"undefined"!=typeof document&&"undefined"!=typeof navigator,E=function(){for(var e=["Edge","Trident","Firefox"],t=0;t<e.length;t+=1)if(_&&navigator.userAgent.indexOf(e[t])>=0)return 1;return 0}(),C=_&&window.Promise?function(e){var t=!1;return function(){t||(t=!0,window.Promise.resolve().then((function(){t=!1,e()})))}}:function(e){var t=!1;return function(){t||(t=!0,setTimeout((function(){t=!1,e()}),E))}};function x(e){return e&&"[object Function]"==={}.toString.call(e)}function T(e,t){if(1!==e.nodeType)return[];var n=e.ownerDocument.defaultView.getComputedStyle(e,null);return t?n[t]:n}function O(e){return"HTML"===e.nodeName?e:e.parentNode||e.host}function D(e){if(!e)return document.body;switch(e.nodeName){case"HTML":case"BODY":return e.ownerDocument.body;case"#document":return e.body}var t=T(e),n=t.overflow,r=t.overflowX,o=t.overflowY;return/(auto|scroll|overlay)/.test(n+o+r)?e:D(O(e))}function N(e){return e&&e.referenceNode?e.referenceNode:e}var S=_&&!(!window.MSInputMethodContext||!document.documentMode),A=_&&/MSIE 10/.test(navigator.userAgent);function F(e){return 11===e?S:10===e?A:S||A}function P(e){if(!e)return document.documentElement;for(var t=F(10)?document.body:null,n=e.offsetParent||null;n===t&&e.nextElementSibling;)n=(e=e.nextElementSibling).offsetParent;var r=n&&n.nodeName;return r&&"BODY"!==r&&"HTML"!==r?-1!==["TH","TD","TABLE"].indexOf(n.nodeName)&&"static"===T(n,"position")?P(n):n:e?e.ownerDocument.documentElement:document.documentElement}function j(e){return null!==e.parentNode?j(e.parentNode):e}function k(e,t){if(!(e&&e.nodeType&&t&&t.nodeType))return document.documentElement;var n=e.compareDocumentPosition(t)&Node.DOCUMENT_POSITION_FOLLOWING,r=n?e:t,o=n?t:e,i=document.createRange();i.setStart(r,0),i.setEnd(o,0);var a,s,l=i.commonAncestorContainer;if(e!==l&&t!==l||r.contains(o))return"BODY"===(s=(a=l).nodeName)||"HTML"!==s&&P(a.firstElementChild)!==a?P(l):l;var f=j(e);return f.host?k(f.host,t):k(e,j(t).host)}function L(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"top",n="top"===t?"scrollTop":"scrollLeft",r=e.nodeName;if("BODY"===r||"HTML"===r){var o=e.ownerDocument.documentElement,i=e.ownerDocument.scrollingElement||o;return i[n]}return e[n]}function M(e,t){var n=arguments.length>2&&void 0!==arguments[2]&&arguments[2],r=L(t,"top"),o=L(t,"left"),i=n?-1:1;return e.top+=r*i,e.bottom+=r*i,e.left+=o*i,e.right+=o*i,e}function I(e,t){var n="x"===t?"Left":"Top",r="Left"===n?"Right":"Bottom";return parseFloat(e["border"+n+"Width"])+parseFloat(e["border"+r+"Width"])}function B(e,t,n,r){return Math.max(t["offset"+e],t["scroll"+e],n["client"+e],n["offset"+e],n["scroll"+e],F(10)?parseInt(n["offset"+e])+parseInt(r["margin"+("Height"===e?"Top":"Left")])+parseInt(r["margin"+("Height"===e?"Bottom":"Right")]):0)}function q(e){var t=e.body,n=e.documentElement,r=F(10)&&getComputedStyle(n);return{height:B("Height",t,n,r),width:B("Width",t,n,r)}}var H=function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")},R=function(){function e(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(t,n,r){return n&&e(t.prototype,n),r&&e(t,r),t}}(),W=function(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e},Q=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(e[r]=n[r])}return e};function U(e){return Q({},e,{right:e.left+e.width,bottom:e.top+e.height})}function V(e){var t={};try{if(F(10)){t=e.getBoundingClientRect();var n=L(e,"top"),r=L(e,"left");t.top+=n,t.left+=r,t.bottom+=n,t.right+=r}else t=e.getBoundingClientRect()}catch(e){}var o={left:t.left,top:t.top,width:t.right-t.left,height:t.bottom-t.top},i="HTML"===e.nodeName?q(e.ownerDocument):{},a=i.width||e.clientWidth||o.width,s=i.height||e.clientHeight||o.height,l=e.offsetWidth-a,f=e.offsetHeight-s;if(l||f){var u=T(e);l-=I(u,"x"),f-=I(u,"y"),o.width-=l,o.height-=f}return U(o)}function Y(e,t){var n=arguments.length>2&&void 0!==arguments[2]&&arguments[2],r=F(10),o="HTML"===t.nodeName,i=V(e),a=V(t),s=D(e),l=T(t),f=parseFloat(l.borderTopWidth),u=parseFloat(l.borderLeftWidth);n&&o&&(a.top=Math.max(a.top,0),a.left=Math.max(a.left,0));var d=U({top:i.top-a.top-f,left:i.left-a.left-u,width:i.width,height:i.height});if(d.marginTop=0,d.marginLeft=0,!r&&o){var p=parseFloat(l.marginTop),c=parseFloat(l.marginLeft);d.top-=f-p,d.bottom-=f-p,d.left-=u-c,d.right-=u-c,d.marginTop=p,d.marginLeft=c}return(r&&!n?t.contains(s):t===s&&"BODY"!==s.nodeName)&&(d=M(d,t)),d}function z(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1],n=e.ownerDocument.documentElement,r=Y(e,n),o=Math.max(n.clientWidth,window.innerWidth||0),i=Math.max(n.clientHeight,window.innerHeight||0),a=t?0:L(n),s=t?0:L(n,"left"),l={top:a-r.top+r.marginTop,left:s-r.left+r.marginLeft,width:o,height:i};return U(l)}function G(e){var t=e.nodeName;if("BODY"===t||"HTML"===t)return!1;if("fixed"===T(e,"position"))return!0;var n=O(e);return!!n&&G(n)}function J(e){if(!e||!e.parentElement||F())return document.documentElement;for(var t=e.parentElement;t&&"none"===T(t,"transform");)t=t.parentElement;return t||document.documentElement}function K(e,t,n,r){var o=arguments.length>4&&void 0!==arguments[4]&&arguments[4],i={top:0,left:0},a=o?J(e):k(e,N(t));if("viewport"===r)i=z(a,o);else{var s=void 0;"scrollParent"===r?"BODY"===(s=D(O(t))).nodeName&&(s=e.ownerDocument.documentElement):s="window"===r?e.ownerDocument.documentElement:r;var l=Y(s,a,o);if("HTML"!==s.nodeName||G(a))i=l;else{var f=q(e.ownerDocument),u=f.height,d=f.width;i.top+=l.top-l.marginTop,i.bottom=u+l.top,i.left+=l.left-l.marginLeft,i.right=d+l.left}}var p="number"==typeof(n=n||0);return i.left+=p?n:n.left||0,i.top+=p?n:n.top||0,i.right-=p?n:n.right||0,i.bottom-=p?n:n.bottom||0,i}function X(e){return e.width*e.height}function Z(e,t,n,r,o){var i=arguments.length>5&&void 0!==arguments[5]?arguments[5]:0;if(-1===e.indexOf("auto"))return e;var a=K(n,r,i,o),s={top:{width:a.width,height:t.top-a.top},right:{width:a.right-t.right,height:a.height},bottom:{width:a.width,height:a.bottom-t.bottom},left:{width:t.left-a.left,height:a.height}},l=Object.keys(s).map((function(e){return Q({key:e},s[e],{area:X(s[e])})})).sort((function(e,t){return t.area-e.area})),f=l.filter((function(e){var t=e.width,r=e.height;return t>=n.clientWidth&&r>=n.clientHeight})),u=f.length>0?f[0].key:l[0].key,d=e.split("-")[1];return u+(d?"-"+d:"")}function $(e,t,n){var r=arguments.length>3&&void 0!==arguments[3]?arguments[3]:null,o=r?J(t):k(t,N(n));return Y(n,o,r)}function ee(e){var t=e.ownerDocument.defaultView.getComputedStyle(e),n=parseFloat(t.marginTop||0)+parseFloat(t.marginBottom||0),r=parseFloat(t.marginLeft||0)+parseFloat(t.marginRight||0);return{width:e.offsetWidth+r,height:e.offsetHeight+n}}function te(e){var t={left:"right",right:"left",bottom:"top",top:"bottom"};return e.replace(/left|right|bottom|top/g,(function(e){return t[e]}))}function ne(e,t,n){n=n.split("-")[0];var r=ee(e),o={width:r.width,height:r.height},i=-1!==["right","left"].indexOf(n),a=i?"top":"left",s=i?"left":"top",l=i?"height":"width",f=i?"width":"height";return o[a]=t[a]+t[l]/2-r[l]/2,o[s]=n===s?t[s]-r[f]:t[te(s)],o}function re(e,t){return Array.prototype.find?e.find(t):e.filter(t)[0]}function oe(e,t,n){return(void 0===n?e:e.slice(0,function(e,t,n){if(Array.prototype.findIndex)return e.findIndex((function(e){return e.name===n}));var r=re(e,(function(e){return e.name===n}));return e.indexOf(r)}(e,0,n))).forEach((function(e){e.function&&console.warn("`modifier.function` is deprecated, use `modifier.fn`!");var n=e.function||e.fn;e.enabled&&x(n)&&(t.offsets.popper=U(t.offsets.popper),t.offsets.reference=U(t.offsets.reference),t=n(t,e))})),t}function ie(){if(!this.state.isDestroyed){var e={instance:this,styles:{},arrowStyles:{},attributes:{},flipped:!1,offsets:{}};e.offsets.reference=$(this.state,this.popper,this.reference,this.options.positionFixed),e.placement=Z(this.options.placement,e.offsets.reference,this.popper,this.reference,this.options.modifiers.flip.boundariesElement,this.options.modifiers.flip.padding),e.originalPlacement=e.placement,e.positionFixed=this.options.positionFixed,e.offsets.popper=ne(this.popper,e.offsets.reference,e.placement),e.offsets.popper.position=this.options.positionFixed?"fixed":"absolute",e=oe(this.modifiers,e),this.state.isCreated?this.options.onUpdate(e):(this.state.isCreated=!0,this.options.onCreate(e))}}function ae(e,t){return e.some((function(e){var n=e.name;return e.enabled&&n===t}))}function se(e){for(var t=[!1,"ms","Webkit","Moz","O"],n=e.charAt(0).toUpperCase()+e.slice(1),r=0;r<t.length;r++){var o=t[r],i=o?""+o+n:e;if("undefined"!=typeof document.body.style[i])return i}return null}function le(){return this.state.isDestroyed=!0,ae(this.modifiers,"applyStyle")&&(this.popper.removeAttribute("x-placement"),this.popper.style.position="",this.popper.style.top="",this.popper.style.left="",this.popper.style.right="",this.popper.style.bottom="",this.popper.style.willChange="",this.popper.style[se("transform")]=""),this.disableEventListeners(),this.options.removeOnDestroy&&this.popper.parentNode.removeChild(this.popper),this}function fe(e){var t=e.ownerDocument;return t?t.defaultView:window}function ue(e,t,n,r){var o="BODY"===e.nodeName,i=o?e.ownerDocument.defaultView:e;i.addEventListener(t,n,{passive:!0}),o||ue(D(i.parentNode),t,n,r),r.push(i)}function de(e,t,n,r){n.updateBound=r,fe(e).addEventListener("resize",n.updateBound,{passive:!0});var o=D(e);return ue(o,"scroll",n.updateBound,n.scrollParents),n.scrollElement=o,n.eventsEnabled=!0,n}function pe(){this.state.eventsEnabled||(this.state=de(this.reference,this.options,this.state,this.scheduleUpdate))}function ce(){var e,t;this.state.eventsEnabled&&(cancelAnimationFrame(this.scheduleUpdate),this.state=(e=this.reference,t=this.state,fe(e).removeEventListener("resize",t.updateBound),t.scrollParents.forEach((function(e){e.removeEventListener("scroll",t.updateBound)})),t.updateBound=null,t.scrollParents=[],t.scrollElement=null,t.eventsEnabled=!1,t))}function he(e){return""!==e&&!isNaN(parseFloat(e))&&isFinite(e)}function me(e,t){Object.keys(t).forEach((function(n){var r="";-1!==["width","height","top","right","bottom","left"].indexOf(n)&&he(t[n])&&(r="px"),e.style[n]=t[n]+r}))}var ge=_&&/Firefox/i.test(navigator.userAgent);function ve(e,t,n){var r=re(e,(function(e){return e.name===t})),o=!!r&&e.some((function(e){return e.name===n&&e.enabled&&e.order<r.order}));if(!o){var i="`"+t+"`",a="`"+n+"`";console.warn(a+" modifier is required by "+i+" modifier in order to work, be sure to include it before "+i+"!")}return o}var ye=["auto-start","auto","auto-end","top-start","top","top-end","right-start","right","right-end","bottom-end","bottom","bottom-start","left-end","left","left-start"],be=ye.slice(3);function we(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1],n=be.indexOf(e),r=be.slice(n+1).concat(be.slice(0,n));return t?r.reverse():r}var _e={placement:"bottom",positionFixed:!1,eventsEnabled:!0,removeOnDestroy:!1,onCreate:function(){},onUpdate:function(){},modifiers:{shift:{order:100,enabled:!0,fn:function(e){var t=e.placement,n=t.split("-")[0],r=t.split("-")[1];if(r){var o=e.offsets,i=o.reference,a=o.popper,s=-1!==["bottom","top"].indexOf(n),l=s?"left":"top",f=s?"width":"height",u={start:W({},l,i[l]),end:W({},l,i[l]+i[f]-a[f])};e.offsets.popper=Q({},a,u[r])}return e}},offset:{order:200,enabled:!0,fn:function(e,t){var n,r=t.offset,o=e.placement,i=e.offsets,a=i.popper,s=i.reference,l=o.split("-")[0];return n=he(+r)?[+r,0]:function(e,t,n,r){var o=[0,0],i=-1!==["right","left"].indexOf(r),a=e.split(/(\+|\-)/).map((function(e){return e.trim()})),s=a.indexOf(re(a,(function(e){return-1!==e.search(/,|\s/)})));a[s]&&-1===a[s].indexOf(",")&&console.warn("Offsets separated by white space(s) are deprecated, use a comma (,) instead.");var l=/\s*,\s*|\s+/,f=-1!==s?[a.slice(0,s).concat([a[s].split(l)[0]]),[a[s].split(l)[1]].concat(a.slice(s+1))]:[a];return f=f.map((function(e,r){var o=(1===r?!i:i)?"height":"width",a=!1;return e.reduce((function(e,t){return""===e[e.length-1]&&-1!==["+","-"].indexOf(t)?(e[e.length-1]=t,a=!0,e):a?(e[e.length-1]+=t,a=!1,e):e.concat(t)}),[]).map((function(e){return function(e,t,n,r){var o=e.match(/((?:\-|\+)?\d*\.?\d*)(.*)/),i=+o[1],a=o[2];return i?0===a.indexOf("%")?U("%p"===a?n:r)[t]/100*i:"vh"===a||"vw"===a?("vh"===a?Math.max(document.documentElement.clientHeight,window.innerHeight||0):Math.max(document.documentElement.clientWidth,window.innerWidth||0))/100*i:i:e}(e,o,t,n)}))})),f.forEach((function(e,t){e.forEach((function(n,r){he(n)&&(o[t]+=n*("-"===e[r-1]?-1:1))}))})),o}(r,a,s,l),"left"===l?(a.top+=n[0],a.left-=n[1]):"right"===l?(a.top+=n[0],a.left+=n[1]):"top"===l?(a.left+=n[0],a.top-=n[1]):"bottom"===l&&(a.left+=n[0],a.top+=n[1]),e.popper=a,e},offset:0},preventOverflow:{order:300,enabled:!0,fn:function(e,t){var n=t.boundariesElement||P(e.instance.popper);e.instance.reference===n&&(n=P(n));var r=se("transform"),o=e.instance.popper.style,i=o.top,a=o.left,s=o[r];o.top="",o.left="",o[r]="";var l=K(e.instance.popper,e.instance.reference,t.padding,n,e.positionFixed);o.top=i,o.left=a,o[r]=s,t.boundaries=l;var f=t.priority,u=e.offsets.popper,d={primary:function(e){var n=u[e];return u[e]<l[e]&&!t.escapeWithReference&&(n=Math.max(u[e],l[e])),W({},e,n)},secondary:function(e){var n="right"===e?"left":"top",r=u[n];return u[e]>l[e]&&!t.escapeWithReference&&(r=Math.min(u[n],l[e]-("right"===e?u.width:u.height))),W({},n,r)}};return f.forEach((function(e){var t=-1!==["left","top"].indexOf(e)?"primary":"secondary";u=Q({},u,d[t](e))})),e.offsets.popper=u,e},priority:["left","right","top","bottom"],padding:5,boundariesElement:"scrollParent"},keepTogether:{order:400,enabled:!0,fn:function(e){var t=e.offsets,n=t.popper,r=t.reference,o=e.placement.split("-")[0],i=Math.floor,a=-1!==["top","bottom"].indexOf(o),s=a?"right":"bottom",l=a?"left":"top",f=a?"width":"height";return n[s]<i(r[l])&&(e.offsets.popper[l]=i(r[l])-n[f]),n[l]>i(r[s])&&(e.offsets.popper[l]=i(r[s])),e}},arrow:{order:500,enabled:!0,fn:function(e,t){var n;if(!ve(e.instance.modifiers,"arrow","keepTogether"))return e;var r=t.element;if("string"==typeof r){if(!(r=e.instance.popper.querySelector(r)))return e}else if(!e.instance.popper.contains(r))return console.warn("WARNING: `arrow.element` must be child of its popper element!"),e;var o=e.placement.split("-")[0],i=e.offsets,a=i.popper,s=i.reference,l=-1!==["left","right"].indexOf(o),f=l?"height":"width",u=l?"Top":"Left",d=u.toLowerCase(),p=l?"left":"top",c=l?"bottom":"right",h=ee(r)[f];s[c]-h<a[d]&&(e.offsets.popper[d]-=a[d]-(s[c]-h)),s[d]+h>a[c]&&(e.offsets.popper[d]+=s[d]+h-a[c]),e.offsets.popper=U(e.offsets.popper);var m=s[d]+s[f]/2-h/2,g=T(e.instance.popper),v=parseFloat(g["margin"+u]),y=parseFloat(g["border"+u+"Width"]),b=m-e.offsets.popper[d]-v-y;return b=Math.max(Math.min(a[f]-h,b),0),e.arrowElement=r,e.offsets.arrow=(W(n={},d,Math.round(b)),W(n,p,""),n),e},element:"[x-arrow]"},flip:{order:600,enabled:!0,fn:function(e,t){if(ae(e.instance.modifiers,"inner"))return e;if(e.flipped&&e.placement===e.originalPlacement)return e;var n=K(e.instance.popper,e.instance.reference,t.padding,t.boundariesElement,e.positionFixed),r=e.placement.split("-")[0],o=te(r),i=e.placement.split("-")[1]||"",a=[];switch(t.behavior){case"flip":a=[r,o];break;case"clockwise":a=we(r);break;case"counterclockwise":a=we(r,!0);break;default:a=t.behavior}return a.forEach((function(s,l){if(r!==s||a.length===l+1)return e;r=e.placement.split("-")[0],o=te(r);var f=e.offsets.popper,u=e.offsets.reference,d=Math.floor,p="left"===r&&d(f.right)>d(u.left)||"right"===r&&d(f.left)<d(u.right)||"top"===r&&d(f.bottom)>d(u.top)||"bottom"===r&&d(f.top)<d(u.bottom),c=d(f.left)<d(n.left),h=d(f.right)>d(n.right),m=d(f.top)<d(n.top),g=d(f.bottom)>d(n.bottom),v="left"===r&&c||"right"===r&&h||"top"===r&&m||"bottom"===r&&g,y=-1!==["top","bottom"].indexOf(r),b=!!t.flipVariations&&(y&&"start"===i&&c||y&&"end"===i&&h||!y&&"start"===i&&m||!y&&"end"===i&&g),w=!!t.flipVariationsByContent&&(y&&"start"===i&&h||y&&"end"===i&&c||!y&&"start"===i&&g||!y&&"end"===i&&m),_=b||w;(p||v||_)&&(e.flipped=!0,(p||v)&&(r=a[l+1]),_&&(i=function(e){return"end"===e?"start":"start"===e?"end":e}(i)),e.placement=r+(i?"-"+i:""),e.offsets.popper=Q({},e.offsets.popper,ne(e.instance.popper,e.offsets.reference,e.placement)),e=oe(e.instance.modifiers,e,"flip"))})),e},behavior:"flip",padding:5,boundariesElement:"viewport",flipVariations:!1,flipVariationsByContent:!1},inner:{order:700,enabled:!1,fn:function(e){var t=e.placement,n=t.split("-")[0],r=e.offsets,o=r.popper,i=r.reference,a=-1!==["left","right"].indexOf(n),s=-1===["top","left"].indexOf(n);return o[a?"left":"top"]=i[n]-(s?o[a?"width":"height"]:0),e.placement=te(t),e.offsets.popper=U(o),e}},hide:{order:800,enabled:!0,fn:function(e){if(!ve(e.instance.modifiers,"hide","preventOverflow"))return e;var t=e.offsets.reference,n=re(e.instance.modifiers,(function(e){return"preventOverflow"===e.name})).boundaries;if(t.bottom<n.top||t.left>n.right||t.top>n.bottom||t.right<n.left){if(!0===e.hide)return e;e.hide=!0,e.attributes["x-out-of-boundaries"]=""}else{if(!1===e.hide)return e;e.hide=!1,e.attributes["x-out-of-boundaries"]=!1}return e}},computeStyle:{order:850,enabled:!0,fn:function(e,t){var n=t.x,r=t.y,o=e.offsets.popper,i=re(e.instance.modifiers,(function(e){return"applyStyle"===e.name})).gpuAcceleration;void 0!==i&&console.warn("WARNING: `gpuAcceleration` option moved to `computeStyle` modifier and will not be supported in future versions of Popper.js!");var a,s,l=void 0!==i?i:t.gpuAcceleration,f=P(e.instance.popper),u=V(f),d={position:o.position},p=function(e,t){var n=e.offsets,r=n.popper,o=n.reference,i=Math.round,a=Math.floor,s=function(e){return e},l=i(o.width),f=i(r.width),u=-1!==["left","right"].indexOf(e.placement),d=-1!==e.placement.indexOf("-"),p=t?u||d||l%2==f%2?i:a:s,c=t?i:s;return{left:p(l%2==1&&f%2==1&&!d&&t?r.left-1:r.left),top:c(r.top),bottom:c(r.bottom),right:p(r.right)}}(e,window.devicePixelRatio<2||!ge),c="bottom"===n?"top":"bottom",h="right"===r?"left":"right",m=se("transform");if(s="bottom"===c?"HTML"===f.nodeName?-f.clientHeight+p.bottom:-u.height+p.bottom:p.top,a="right"===h?"HTML"===f.nodeName?-f.clientWidth+p.right:-u.width+p.right:p.left,l&&m)d[m]="translate3d("+a+"px, "+s+"px, 0)",d[c]=0,d[h]=0,d.willChange="transform";else{var g="bottom"===c?-1:1,v="right"===h?-1:1;d[c]=s*g,d[h]=a*v,d.willChange=c+", "+h}var y={"x-placement":e.placement};return e.attributes=Q({},y,e.attributes),e.styles=Q({},d,e.styles),e.arrowStyles=Q({},e.offsets.arrow,e.arrowStyles),e},gpuAcceleration:!0,x:"bottom",y:"right"},applyStyle:{order:900,enabled:!0,fn:function(e){var t,n;return me(e.instance.popper,e.styles),t=e.instance.popper,n=e.attributes,Object.keys(n).forEach((function(e){!1!==n[e]?t.setAttribute(e,n[e]):t.removeAttribute(e)})),e.arrowElement&&Object.keys(e.arrowStyles).length&&me(e.arrowElement,e.arrowStyles),e},onLoad:function(e,t,n,r,o){var i=$(o,t,e,n.positionFixed),a=Z(n.placement,i,t,e,n.modifiers.flip.boundariesElement,n.modifiers.flip.padding);return t.setAttribute("x-placement",a),me(t,{position:n.positionFixed?"fixed":"absolute"}),n},gpuAcceleration:void 0}}},Ee=function(){function e(t,n){var r=this,o=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{};H(this,e),this.scheduleUpdate=function(){return requestAnimationFrame(r.update)},this.update=C(this.update.bind(this)),this.options=Q({},e.Defaults,o),this.state={isDestroyed:!1,isCreated:!1,scrollParents:[]},this.reference=t&&t.jquery?t[0]:t,this.popper=n&&n.jquery?n[0]:n,this.options.modifiers={},Object.keys(Q({},e.Defaults.modifiers,o.modifiers)).forEach((function(t){r.options.modifiers[t]=Q({},e.Defaults.modifiers[t]||{},o.modifiers?o.modifiers[t]:{})})),this.modifiers=Object.keys(this.options.modifiers).map((function(e){return Q({name:e},r.options.modifiers[e])})).sort((function(e,t){return e.order-t.order})),this.modifiers.forEach((function(e){e.enabled&&x(e.onLoad)&&e.onLoad(r.reference,r.popper,r.options,e,r.state)})),this.update();var i=this.options.eventsEnabled;i&&this.enableEventListeners(),this.state.eventsEnabled=i}return R(e,[{key:"update",value:function(){return ie.call(this)}},{key:"destroy",value:function(){return le.call(this)}},{key:"enableEventListeners",value:function(){return pe.call(this)}},{key:"disableEventListeners",value:function(){return ce.call(this)}}]),e}();Ee.Utils=("undefined"!=typeof window?window:global).PopperUtils,Ee.placements=ye,Ee.Defaults=_e;var Ce=Ee,xe="adropdown",Te="bs.adropdown",Oe="."+Te,De=r.default.fn[xe],Ne=new RegExp("38|40|27"),Se="disabled",Ae="show",Fe="adropdown-menu-right",Pe="hide"+Oe,je="hidden"+Oe,ke="click.bs.adropdown.data-api",Le="keydown.bs.adropdown.data-api",Me='[data-toggle="adropdown"]',Ie=".adropdown-menu",Be={offset:0,flip:!0,boundary:"scrollParent",reference:"toggle",display:"dynamic",popperConfig:null},qe={offset:"(number|string|function)",flip:"boolean",boundary:"(string|element)",reference:"(string|element)",display:"string",popperConfig:"(null|object)"},He=function(){function e(e,t){this._element=e,this._popper=null,this._config=this._getConfig(t),this._menu=this._getMenuElement(),this._inNavbar=this._detectNavbar(),this._addEventListeners()}var t=e.prototype;return t.toggle=function(){if(!this._element.disabled&&!r.default(this._element).hasClass(Se)){var t=r.default(this._menu).hasClass(Ae);e._clearMenus(),t||this.show(!0)}},t.show=function(t){if(void 0===t&&(t=!1),!(this._element.disabled||r.default(this._element).hasClass(Se)||r.default(this._menu).hasClass(Ae))){var n={relatedTarget:this._element},o=r.default.Event("show.bs.adropdown",n),i=e._getParentFromElement(this._element);if(r.default(i).trigger(o),!o.isDefaultPrevented()){if(!this._inNavbar&&t){if("undefined"==typeof Ce)throw new TypeError("Bootstrap's adropdowns require Popper (https://popper.js.org)");var a=this._element;"parent"===this._config.reference?a=i:l.isElement(this._config.reference)&&(a=this._config.reference,"undefined"!=typeof this._config.reference.jquery&&(a=this._config.reference[0])),"scrollParent"!==this._config.boundary&&r.default(i).addClass("position-static"),this._popper=new Ce(a,this._menu,this._getPopperConfig())}"ontouchstart"in document.documentElement&&0===r.default(i).closest(".navbar-nav").length&&r.default(document.body).children().on("mouseover",null,r.default.noop),this._element.focus(),this._element.setAttribute("aria-expanded",!0),r.default(this._menu).toggleClass(Ae),r.default(i).toggleClass(Ae).trigger(r.default.Event("shown.bs.adropdown",n))}}},t.hide=function(){if(!this._element.disabled&&!r.default(this._element).hasClass(Se)&&r.default(this._menu).hasClass(Ae)){var t={relatedTarget:this._element},n=r.default.Event(Pe,t),o=e._getParentFromElement(this._element);r.default(o).trigger(n),n.isDefaultPrevented()||(this._popper&&this._popper.destroy(),r.default(this._menu).toggleClass(Ae),r.default(o).toggleClass(Ae).trigger(r.default.Event(je,t)))}},t.dispose=function(){r.default.removeData(this._element,Te),r.default(this._element).off(Oe),this._element=null,this._menu=null,null!==this._popper&&(this._popper.destroy(),this._popper=null)},t.update=function(){this._inNavbar=this._detectNavbar(),null!==this._popper&&this._popper.scheduleUpdate()},t._addEventListeners=function(){var e=this;r.default(this._element).on("click.bs.adropdown",(function(t){t.preventDefault(),t.stopPropagation(),e.toggle()}))},t._getConfig=function(e){return e=a({},this.constructor.Default,r.default(this._element).data(),e),l.typeCheckConfig(xe,e,this.constructor.DefaultType),e},t._getMenuElement=function(){if(!this._menu){var t=e._getParentFromElement(this._element);t&&(this._menu=t.querySelector(Ie))}return this._menu},t._getPlacement=function(){var e=r.default(this._element.parentNode),t="bottom-start";return e.hasClass("dropup")?t=r.default(this._menu).hasClass(Fe)?"top-end":"top-start":e.hasClass("dropright")?t="right-start":e.hasClass("dropleft")?t="left-start":r.default(this._menu).hasClass(Fe)&&(t="bottom-end"),t},t._detectNavbar=function(){return r.default(this._element).closest(".navbar").length>0},t._getOffset=function(){var e=this,t={};return"function"==typeof this._config.offset?t.fn=function(t){return t.offsets=a({},t.offsets,e._config.offset(t.offsets,e._element)),t}:t.offset=this._config.offset,t},t._getPopperConfig=function(){var e={placement:this._getPlacement(),modifiers:{offset:this._getOffset(),flip:{enabled:this._config.flip},preventOverflow:{boundariesElement:this._config.boundary}}};return"static"===this._config.display&&(e.modifiers.applyStyle={enabled:!1}),a({},e,this._config.popperConfig)},e._jQueryInterface=function(t){return this.each((function(){var n=r.default(this).data(Te);if(n||(n=new e(this,"object"==typeof t?t:null),r.default(this).data(Te,n)),"string"==typeof t){if("undefined"==typeof n[t])throw new TypeError('No method named "'+t+'"');n[t]()}}))},e._clearMenus=function(t){if(!t||3!==t.which&&("keyup"!==t.type||9===t.which))for(var n=[].slice.call(document.querySelectorAll(Me)),o=0,i=n.length;o<i;o++){var a=e._getParentFromElement(n[o]),s=r.default(n[o]).data(Te),l={relatedTarget:n[o]};if(t&&"click"===t.type&&(l.clickEvent=t),s){var f=s._menu;if(r.default(a).hasClass(Ae)&&!(t&&("click"===t.type&&/input|textarea/i.test(t.target.tagName)||"keyup"===t.type&&9===t.which)&&r.default.contains(a,t.target))){var u=r.default.Event(Pe,l);r.default(a).trigger(u),u.isDefaultPrevented()||("ontouchstart"in document.documentElement&&r.default(document.body).children().off("mouseover",null,r.default.noop),n[o].setAttribute("aria-expanded","false"),s._popper&&s._popper.destroy(),r.default(f).removeClass(Ae),r.default(a).removeClass(Ae).trigger(r.default.Event(je,l)))}}}},e._getParentFromElement=function(e){var t,n=l.getSelectorFromElement(e);return n&&(t=document.querySelector(n)),t||e.parentNode},e._dataApiKeydownHandler=function(t){if(!(/input|textarea/i.test(t.target.tagName)?32===t.which||27!==t.which&&(40!==t.which&&38!==t.which||r.default(t.target).closest(Ie).length):!Ne.test(t.which))&&!this.disabled&&!r.default(this).hasClass(Se)){var n=e._getParentFromElement(this),o=r.default(n).hasClass(Ae);if(o||27!==t.which){if(t.preventDefault(),t.stopPropagation(),!o||27===t.which||32===t.which)return 27===t.which&&r.default(n.querySelector(Me)).trigger("focus"),void r.default(this).trigger("click");var i=[].slice.call(n.querySelectorAll(".adropdown-menu .adropdown-item:not(.disabled):not(:disabled)")).filter((function(e){return r.default(e).is(":visible")}));if(0!==i.length){var a=i.indexOf(t.target);38===t.which&&a>0&&a--,40===t.which&&a<i.length-1&&a++,a<0&&(a=0),i[a].focus()}}}},i(e,null,[{key:"VERSION",get:function(){return"4.6.1"}},{key:"Default",get:function(){return Be}},{key:"DefaultType",get:function(){return qe}}]),e}();r.default(document).on(Le,Me,He._dataApiKeydownHandler).on(Le,Ie,He._dataApiKeydownHandler).on(ke+" keyup.bs.adropdown.data-api",He._clearMenus).on(ke,Me,(function(e){e.preventDefault(),e.stopPropagation(),He._jQueryInterface.call(r.default(this),"toggle")})).on(ke,".adropdown form",(function(e){e.stopPropagation()})),r.default.fn[xe]=He._jQueryInterface,r.default.fn[xe].Constructor=He,r.default.fn[xe].noConflict=function(){return r.default.fn[xe]=De,He._jQueryInterface},e.Util=l,e.aCollapse=w,e.aDropdown=He,Object.defineProperty(e,"__esModule",{value:!0})}));
  }

  /**
   * [sortBy description]
   * @param  {[type]} _items  [description]
   * @param  {[type]} _type   [description]
   * @param  {[type]} _string [description]
   * @return {[type]}         [description]
   */
  var sortBy = function(_items, _type, _string) {

    var sort_method = null;

    //  For Strings
    if(_string) {

      if(asl_search_configuration.sort_order == 'desc') {

        //  For string to with localecompare
        sort_method = function(a, b) {
          
          return (a[_type] && b[_type])? -(a[_type].localeCompare(b[_type])): 0;
        };
      }
      else {

        //  For string to with localecompare
        sort_method = function(a, b) {
          
          return (a[_type] && b[_type])? a[_type].localeCompare(b[_type]): 0;
        };
      }
    }
    //  Integers
    else {

      if(asl_search_configuration.sort_order == 'desc') {

        sort_method = function(a, b) {
          return parseInt(b[_type]) - parseInt(a[_type]);
        };
      }
      else
        sort_method = function(a, b) {
          return parseInt(a[_type]) - parseInt(b[_type]);
        };  
    }

    return _items.sort(sort_method);
  };

  /**
   * [asl_search_widget Search Widget of the Store Locator]
   * @return {[type]} [description]
   */
  $.fn.asl_search_widget = function() {

    return this.each(function() {
        
      var container          = $(this),
          settings           = asl_search_configuration,
          widget_config      = container.data('configuration');


      if(widget_config && typeof widget_config == 'object') {

          settings = Object.assign(settings, widget_config);
      }

      var search_input       = container.find('#sl-search-widget-text');

      //  Make sure the search field exist
      if(!search_input[0]) {
        return;
      }

      var clear              = search_input[0].parentNode.querySelector('.asl-clear-btn');

      /**
       * [geoLocatePosition GeoLocate the User Location]
       * @param  {[type]} _callback [description]
       * @param  {[type]} _error    [description]
       * @return {[type]}           [description]
       */
      function geoLocatePosition(_callback, _error) {

        var that = this;

        if (window.navigator && navigator.geolocation) {

          navigator.geolocation.getCurrentPosition(function(pos) {
            
              _callback(new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude));
            },
            function(error) {

              var error_text = '';

              switch (error.code) {

                case error.PERMISSION_DENIED:
                  error_text = error.message || "User denied the request for Geolocation.";
                  break;
                case error.POSITION_UNAVAILABLE:
                  error_text = "Location information is unavailable.";
                  break;
                case error.TIMEOUT:
                  error_text = "The request to get user location timed out.";
                  break;
                case error.UNKNOWN_ERROR:
                  error_text = "An unknown error occurred.";
                  break;
                default:
                  error_text = error.message;
              }

              //  Error Callback
              _error(error_text);

            }, ({
              maximumAge: 60 * 1000,
              timeout: 10 * 1000
            })
          ) ;
        }
      };
      /**
       * [geoCoder Geocoder]
       * @param  {[type]} _input    [description]
       * @param  {[type]} _callback [description]
       * @return {[type]}           [description]
       */
      var geoCoder = function(_input, _callback) {

        var that      = this;
        var geocoder  = new google.maps.Geocoder(),
          _callback   = _callback || function(results, status) {
        
        if (status == 'OK') {

            destination = results[0].geometry;
            search_input.removeClass('on-error');
            clear.style.display = 'block';
          }
            else {
            console.log('Geocode was not successful for the following reason: ' + status);
          }
        };

        
        // Enter Key
        $(_input).bind('keyup', function(e) {

          if (e.keyCode == 13) {
            
            var addr_value = $.trim(this.value);

            if(addr_value) {

              var search_param = { 'address': addr_value };
              
              search_param['componentRestrictions'] = {};
      
              //  Restrict the search
              if (settings.country_restrict) {
              
                var country_restricted = settings.country_restrict.toLowerCase();

                country_restricted = country_restricted.split(',');

                //  Add the Country restrict
                search_param['componentRestrictions']['country'] = country_restricted[0];
              }

              geocoder.geocode(search_param, _callback);
            }
          }
        });
      };

      /**
      * Adds autocomplete to the input box.
      * @private
      */
      var initAutocomplete_ = function() {
        
        var that  = this;

        var options = {};

        if(settings.search_type != '3') {

          if(settings.google_search_type) {
            
            options['types'] = (settings.google_search_type == 'cities' || settings.google_search_type == 'regions')?['('+settings.google_search_type+')']:[settings.google_search_type];
          }

          //options['types'] = ['postal_code'];

          this.autoComplete_ = new google.maps.places.Autocomplete(search_input[0], options);

          //  Restrict the country
          if (settings.country_restrict) {
        
            var country_restricted = settings.country_restrict.toLowerCase();

            country_restricted = country_restricted.split(',');

            this.autoComplete_.setComponentRestrictions({country: country_restricted });
          }

          //  Restrict the data
          var fields = ['geometry'];
          this.autoComplete_.setFields(fields);

          google.maps.event.addListener(this.autoComplete_, 'place_changed',
            function() {
              var p = this.getPlace();

              
              if(p.geometry) {

                clear.style.display = 'block';

                //p.geometry.location
                destination = p.geometry;
                search_input.removeClass('on-error');

              }
          });

        }
        geoCoder(search_input[0]);
      };

      initAutocomplete_();


      //  Clear the button
      clear.addEventListener('click', function(e) {
        search_input.val('');
        clear.style.display = 'none';
        destination = null;
      });

      
      ///////////////////////////
      ///////Category Dropdown //
      ///////////////////////////
      //Multiple or Single 
      var _multiple_cat  = (settings.single_cat_select == '1')?'':'multiple="multiple"';

      //$category_cont.append('<select class="form-control border-0" id="asl-categories" '+_multiple_cat+'></select>');
      var $category_ddl = container.find('#asl-categories');

      //  Add the multiple tag
      if(settings.single_cat_select != '1') {

        $category_ddl.attr('multiple','multiple');
      }

      if($category_ddl[0]) {
            
        //  For NONE
        if(settings.single_cat_select == '1') {
          var $temp = $('<option value="0">'+settings.words['category']+'</option>');
          $category_ddl.append($temp);
        }

        asl_search_categories  =  Object.values(asl_search_categories);
        asl_search_categories  =  (!settings.cat_sort)? sortBy(asl_search_categories, 'name', true): sortBy(asl_search_categories, 'ordr');



        //  Loop over the categories  
        for (var _c in asl_search_categories) {

          var $temp = $('<option  value="'+asl_search_categories[_c].id+'">'+asl_search_categories[_c].name+'</option>');
          $category_ddl.append($temp);
        }

        $category_ddl[0].style.display = 'block';


        //  Default Category Selection
        if (settings.select_category) {
          
          settings.select_category = settings.select_category.split(',');

          var _cat_default = (settings.select_category.length == 1) ? settings.select_category[0] : settings.select_category;
          $category_ddl.val(_cat_default);
        }

        
        $category_ddl.multiselect({
          enableFiltering: false,
          disableIfEmpty: true,
          enableCaseInsensitiveFiltering: false,
          nonSelectedText: settings.words.select_option,
          filterPlaceholder: settings.words.search || "Search",
                nonSelectedText: settings.words['category'] || "Select",
                nSelectedText: settings.words.selected || "selected",
                allSelectedText: (settings.words.all_selected || "All selected"),
          includeSelectAllOption: false,
          numberDisplayed: 1,
          maxHeight: 400,
          onChange : function(option, checked) {
            console.log('===> asl_search.js ===> 285');
          }
        });
      }


      //  Geo-location Bind
      container.find('.asl-geo').bind('click', function(e) {

          geoLocatePosition(function(_coordinate) {

            search_input.val(settings.words.geo);
            clear.style.display = 'block';
            destination = {location: _coordinate};
          }, 
          function(_text) {

            var $err_msg = $('<div class="alert alert-danger asl-geo-err"></div>');
            $err_msg.html(_text || 'Geo-Location is blocked, please check your preferences.');
            $err_msg.appendTo('.asl-cont.asl-search');
              window.setTimeout(function() {
                $err_msg.remove();
              }, 5000);
          });
      });


      /////////////////////////////
      ////// Attribute DDL/////////
      /////////////////////////////

      var attr_keys = Object.keys(asl_attributes);

      for(var attr_key in attr_keys) {

        if (!attr_keys.hasOwnProperty(attr_key)) continue;

        var $attribute_ddl = container.find('#asl-' + attr_keys[attr_key]);

        //  Add the multiple tag
        if(settings.single_cat_select != '1') {

          $attribute_ddl.attr('multiple','multiple');
        }

        if($attribute_ddl[0]) {

          var attr_label = settings.words[attr_keys[attr_key]] ||  "Select";
          

          //  For NONE
          if(settings.single_cat_select == '1') {
            var $temp = $('<option value="0">'+attr_label+'</option>');
            $attribute_ddl.append($temp);
          }

          var attr_list_values = asl_attributes[attr_keys[attr_key]];

          for (var _c in attr_list_values) {
              
              var $temp = $('<option  value="'+attr_list_values[_c].id+'">'+attr_list_values[_c].name+'</option>');
              $attribute_ddl.append($temp);
          }

          $attribute_ddl[0].style.display = 'block';


          $attribute_ddl.multiselect({
            enableFiltering: false,
            disableIfEmpty: true,
            enableCaseInsensitiveFiltering: false,
            nonSelectedText: settings.words.select_option,
            filterPlaceholder: settings.words.search || "Search",
                  nonSelectedText: attr_label,
                  nSelectedText: settings.words.selected || "selected",
                  allSelectedText: (settings.words.all_selected || "All selected"),
            includeSelectAllOption: false,
            numberDisplayed: 1,
            maxHeight: 400,
            onChange : function(option, checked) {
              
            }
          }); 
        }
      }


      //////////////////////////
      //// FIND BUTTON/////// //
      //////////////////////////
        

      /**
       * [search_button_event Event fired to perform the search]
       * @param  {[type]} e [description]
       * @return {[type]}   [description]
       */
      function search_button_event(e) {
        
        ///var addr_value = $.trim(_input.value);
        var categories = ($category_ddl && $category_ddl.val())?$category_ddl.val(): null;

        var params = {};

        if(settings.redirect_website == '1') {
          params['sl-web-redirect'] = 1;
        }

        if(settings.search_radius) {
          params['sl-radius'] = settings.search_radius;
        }

        if(categories && categories != '0') {
          params['sl-category'] = Array.isArray(categories)? categories.join(','): categories;
        }


        var search_text = $.trim(search_input.val());
        var has_value   = false;

        //  Add the Attribute Values
        for(var attr_key in attr_keys) {

          if (!attr_keys.hasOwnProperty(attr_key)) continue;

          var $attribute_ddl = container.find('#asl-' + attr_keys[attr_key]);

          if($attribute_ddl[0]) {

            var attr_values = ($attribute_ddl.val())?$attribute_ddl.val(): null;

            if(attr_values && attr_values != '0') {
              params['sl-' + attr_keys[attr_key]] = Array.isArray(attr_values)? attr_values.join(','): attr_values;

              if(params['sl-' + attr_keys[attr_key]]) {
                has_value = true;
              }
            }
          }
        }

        //  Add the additional query parameters
        if(settings['q-params']) {

          //  explode it on & as multiple parameters can be passed
          var q_params = settings['q-params'];

          q_params  = q_params.split('&');
            
          //  loop over the params
          for(var q = 0;q < q_params.length;q++) {

            var q_param = q_params[q];

            q_param = q_param.split('=');

            if(q_param.length == 2) {

              q_param[0] = q_param[0].replace('amp;', '');

              //  add it in the main parameters list
              params[q_param[0]] = q_param[1];
            }

          }
        }

        //  when condition is positive to redirect
        if(destination || search_text || params['sl-category'] || has_value) {

          //?sl-category=2&sl-addr=Denver%2C+CO%2C+USA&lat=39.7392358&lng=-104.990251

          if(search_input[0].required && !search_text) {
            search_input.addClass('on-error');
            return;
          }

          //  Address
          if(search_text)
            params['sl-addr']    = search_text;

          //  Coordinates
          if(destination && typeof destination == 'object' && destination.location) {

            params['lat'] = destination.location.lat();
            params['lng'] = destination.location.lng();
          }


          window.location.href = settings.redirect + '?' + $.param(params);
        }
        else
          search_input.addClass('on-error'); 
      };

      
      //  Make it Search Button
      container.find('#asl-btn-search').bind('click', search_button_event);

      //  Make it searching on enter
      if(settings.enter_search) {

        search_input.bind('keyup', function(_e) {

          if (_e.keyCode == 13) {
            
            search_button_event(_e);
          }
        });
      }
    });
  };


  //  ASL GDPR Borlabs Callback
  window.asl_gdpr = function() {

    //  Run the script
    $('.asl-cont.asl-search').asl_search_widget();
  };

  /* 
  // Run the widget script
  aslInitializeWhenGAPIReady(function(){
    //  Run the script
    $('.asl-cont.asl-search').asl_search_widget();
  });
  */
  

  //  Run the script
  $('.asl-cont.asl-search').asl_search_widget();

})(jQuery);
