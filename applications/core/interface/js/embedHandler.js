/*
 * IPS embed handling
 * http://www.invisionpower.com
 */
( function () {
	"use strict";

	var _origin;
	var _div;
	var _posting = false;
	var _timer;
	var _timeout = 200; //ms
	var _embedId = '';

	/**
	 * Init method, called when the document is ready
	 *
	 * @returns {void}
	 */
	function init() {
		// Check for postMessage and JSON support
		if( !window.postMessage || !window.JSON.parse ){
			return;
		}

		// Work out our URL
		_div = utils.get('ipsEmbed');
		var url = utils.parseURL( window.location );

		if( url.protocol == '' || url.protocol == ':' ){
			utils.log( url );
			url.protocol = window.location.protocol;
		}

		// Set our origin
		_origin = url.protocol + '//' + url.host;
		utils.log( "Origin in loader is " + _origin );	

		// Hide the content
		_div.style.opacity = '0.00001';
	};	

	/**
	 * Starts our main loop, which posts messages to the parent frame as needed
	 *
	 * @returns {void}
	 */
	function startTimeout() {

		var currentSize = 0;
		var repeats = 0;

		utils.log("Starting timeout...");

		// Main loop
		_timer = setInterval( function () {			
			// What we want to do here is make the loading process more pleasant and less jumpy.
			// To do that, we'll make the embed invisible until we've fetched the same height 6 times
			// in a row (approx 1 second). If that happens we'll assume it has finished loading, and then
			// we'll fade it in and start sending the recorded height to the parent for resizing.

			var height = utils.getObjHeight( _div );

			// If we HAVEN'T started posting our size
			if( !_posting ){
				if( height == currentSize ){
					repeats++;
				} else {
					// The height has changed, so reset our repeat counter
					repeats = 0;
				}

				if( repeats == 6 ){
					_posting = true;
					utils.fadeIn( _div );
				}
			}

			currentSize = height;

			if( _posting ){
				_div.parentNode.className = '';

				_postMessage('height', {
					height: height
				});
			}
		}, _timeout);
	};

	/**
	 * Posts a message to the iframe
	 *
	 * @param	{number} 	[pageNo]	Page number to load
	 * @returns {void}
	 */
	var _postMessage = function (method, obj) {
		// Send to parent window
		window.top.postMessage( JSON.stringify( utils.extendObj( obj || {}, { 
			method: method,
			embedId: _embedId
		} ) ), _origin );
	};	

	/**
	 * Events sent to the iframe
	 */
	var messageEvents = {
		/**
		 * The parent is ready for messages
		 *
		 * @param 	{object} 	data 	Data from the iframe
		 * @returns {void}
		 */
		ready: function (data) {
			_embedId = data.embedId;
			_postMessage('ok');

			startTimeout();
		},

		stop: function (data) {
			clearInterval( _timer );
			eventHandler.off( window, 'message', windowMessage );
		}
	};

	/*******************************************************************************************/
	/* Boring stuff below */


	/**
	 * Event handling
	 */
	// http://www.anujgakhar.com/2013/05/22/cross-browser-event-handling-in-javascript/
	var eventHandler = {
		on: function (el, ev, fn) {
			if( window.addEventListener ){
				el.addEventListener( ev, fn, false );
			} else if( window.attachEvent ){
				el.attachEvent( 'on' + ev, fn );
			} else {
				el[ 'on' + ev ] = fn;
			}
		},

		off: function (el, ev, fn) {
			if( window.removeEventListener ){
				el.removeEventListener( ev, fn, false );
			} else if( window.detachEvent ) {
				el.detachEvent( 'on' + ev, fn );
			} else {
				elem[ 'on' + ev ] = null;
			}
		},

		stop: function (ev) {
			var e = ev || window.event;
			e.cancelBubble = true;
			if( e.stopPropagation ){
				e.stopPropagation();
			}
		}
	};

	// Main message handler
	eventHandler.on( window, 'message', windowMessage );

	function windowMessage (e) {
		if( e.origin !== _origin ){
			utils.log( e.origin + 'does not equal' + _origin );
			return;
		}

		try {
			var pmData = JSON.parse( e.data );
			var method = pmData.method;	
		} catch (err) {
			utils.error("iframe: invalid data.");
			return;
		}			

		if( method && typeof messageEvents[ method ] != 'undefined' ){
			utils.log("Called " + method );
			messageEvents[ method ].call( this, pmData );
		} else {
			utils.log("Method " + method + " doesn't exist");
		}
	};

	/**
	 * Utilities
	 */
	var utils = {

		/**
		 * Log to console if supported
		 *
		 * @param	{string} 	message 	Message to log
		 * @returns {void}
		 */
		log: function (message) {
			if( window.console ){
				console.log( "(EMBED): " + message );
			}
		},

		/**
		 * Error to console if supported
		 *
		 * @param	{string} 	message 	Message to log
		 * @returns {void}
		 */
		error: function (message) {
			if( window.console ){
				console.error( message );
			}
		},

		/**
		 * Gets an element based on ID
		 *
		 * @param	{string} 	id 	Element ID
		 * @returns {element}
		 */
		get: function (id) {
			return document.getElementById( id );
		},

		/**
		 * Extend an object with another object
		 *
		 * @param 	{object} 	originalObj 	Original object
		 * @param	{object} 	newObj 			New object
		 * @returns {void}
		 */
		extendObj: function (originalObj, newObj) {
			for( var i in newObj ){
				if( newObj.hasOwnProperty(i) ){
					originalObj[i] = newObj[i];
				}
			}

			return originalObj;
		},

		/**
		 * Returns parsed information about a URL
		 *
		 * @param 	{string} 	url 	URL to parse
		 * @returns {object}
		 */
		parseURL: function (url) {
			var elem = document.createElement('a');
			utils.insertBefore( elem, _div );
			elem.href = url;

			var data = {
				protocol: elem.protocol,
				hostname: elem.hostname,
				port: elem.port,
				pathname: elem.pathname,
				search: elem.search,
				hash: elem.hash,
				host: elem.host
			};

			elem.parentNode.removeChild( elem );
			return data;
		},

		/**
		 * Returns the document scroll offset
		 *
		 * @returns {object}
		 */
		getScrollOffset: function () {
			var doc = document.documentElement;

			return {
				left: (window.pageXOffset || doc.scrollLeft) - (doc.clientLeft || 0),
				top: (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0)
			};
		},

		/**
		 * Returns the offset relative to the document for an element
		 *
		 * @param	{element} 	element 	Element to get the offset for
		 * @returns {object}
		 */
		getOffset: function (element) {
			var p = {
				left: element.offsetLeft || 0,
				top: element.offsetTop || 0
			};

			while (element = element.offsetParent) {
				p.left += element.offsetLeft;
				p.top += element.offsetTop;
			}

			return p;
		},

		/**
		 * Returns the outer height of the element
		 *
		 * @returns {number}
		 */
		getObjHeight: function (elem) {
			return elem.offsetHeight || 0;
		},

		/**
		 * Fade the element in
		 *
		 * @returns void
		 */
		fadeIn: function (elem) {
			elem.style.opacity = 0;

			var last = +new Date();
			var tick = function() {
				elem.style.opacity = +elem.style.opacity + (new Date() - last) / 400;
				last = +new Date();

				if( +elem.style.opacity < 1 ){
					( window.requestAnimationFrame && requestAnimationFrame(tick) ) || setTimeout(tick, 16);
				}
			};

			tick();
		},

		/**
		 * Returns the document height
		 *
		 * @returns {number}
		 */
		getDocHeight: function () {
			var D = document;

			return Math.max(
				D.body.scrollHeight, D.documentElement.scrollHeight,
				D.body.offsetHeight, D.documentElement.offsetHeight,
				D.body.clientHeight, D.documentElement.clientHeight
			);
		},

		/**
		 * Returns the viewport height
		 *
		 * @returns {number}
		 */
		getViewportHeight: function () {
			return Math.max( document.documentElement.clientHeight, window.innerHeight || 0 );
		},

		/**
		 * Returns attributes for an element
		 *
		 * @param	{element} 	element 	DOM element
		 * @returns {object}
		 */
		getAttributes: function (element) {
			if( typeof element == 'undefined' ){
				return;
			}

			var attributes = {};

			if( element.hasAttributes() ){
				var _attr = element.attributes;

				for( var i = 0; i < _attr.length; i++ ){
					attributes[ _attr[ i ].name.toLowerCase() ] = _attr[ i ].value;
				}
			}

			return attributes;
		},

		/**
		 * A simple method to insert an element after another element
		 *
		 * @param	{element} 	elem 		The new element
		 * @param 	{element} 	existing	The existing element, after which the new will be inserted
		 * @returns {void}
		 */
		insertAfter: function (elem, existing) {
			existing.parentNode.insertBefore( elem, existing.nextSibling );
		},

		/**
		 * A simple method to insert an element before another element
		 *
		 * @param	{element} 	elem 		The new element
		 * @param 	{element} 	existing	The existing element, before which the new will be inserted
		 * @returns {void}
		 */
		insertBefore: function (elem, existing) {
			existing.parentNode.insertBefore( elem, existing );
		},

		/**
		 * Returns a style property value for the given element and style
		 *
		 * @param	{element} 	elem 		Element whose style is to be fetched
		 * @param 	{string} 	style		Property to fetch
		 * @returns {mixed}
		 */
		getStyle: function (elem, style) {
			return window.getComputedStyle( elem, null ).getPropertyValue( style );
		},

		/*
		 * Cross-browser 'dom ready' event handler to init the page
		 */
		/*! contentloaded.min.js - https://github.com/dperini/ContentLoaded - Author: Diego Perini - License: MIT */
		contentLoaded: function (b,i) {
			var j=false,k=true,a=b.document,l=a.documentElement,f=a.addEventListener,h=f?'addEventListener':'attachEvent',n=f?'removeEventListener':'detachEvent',g=f?'':'on',c=function(d){if(d.type=='readystatechange'&&a.readyState!='complete')return;(d.type=='load'?b:a)[n](g+d.type,c,false);if(!j&&(j=true))i.call(b,d.type||d)},m=function(){try{l.doScroll('left')}catch(e){setTimeout(m,50);return}c('poll')};if(a.readyState=='complete')i.call(b,'lazy');else{if(!f&&l.doScroll){try{k=!b.frameElement}catch(e){}if(k)m()}a[h](g+'DOMContentLoaded',c,false);a[h](g+'readystatechange',c,false);b[h](g+'load',c,false)}
		}
	};
	utils.contentLoaded( window, function () {
		init();
	});
})();