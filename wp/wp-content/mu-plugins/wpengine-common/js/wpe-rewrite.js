var regexp = new RegExp( "(?:https?:)?\/\/" + WPERewriteData.domain + "\/" );

var rewriteHref = function( e, attributeName ) {
	if ( e.hasAttribute( attributeName ) ) {
		var href = e.getAttribute( attributeName );
		if ( href.match( regexp ) ) {
			var newHref = href.replace( regexp, WPERewriteData.replacement );
			e.setAttribute( attributeName, newHref );
		}
	}
};

var rewriteElementlinks = function( tagName, attributeName ) {
	var elements = document.getElementsByTagName( tagName );
	for ( var i = 0, len = elements.length; i < len; i++ ) {
		rewriteHref( elements[i], attributeName );
	}
};

rewriteElementlinks( "a", "href" );
rewriteElementlinks( "img", "src" );
rewriteElementlinks( "link", "href" );
rewriteElementlinks( "script", "src" );
