/* ACF Repeater for Elementor — Accordion JS */
(function () {
    'use strict';

    // Tracks the pending transitionend listener for each body element so that
    // a rapid second closeItem() call (e.g. via closeOthers) removes the first
    // listener before adding a new one, preventing unbounded listener stacking.
    var closeTransitionListeners = new WeakMap();
    // Same guard for the open-animation listener so closeItem() (or a second
    // openItem()) can remove an in-flight onOpenEnd before adding a new one.
    var openTransitionListeners  = new WeakMap();

    function initAccordion( scope ) {
        var root = scope || document;
        root.querySelectorAll( '.arb-accordion' ).forEach( function ( accordion ) {
            if ( accordion.dataset.arbInit ) return;
            accordion.dataset.arbInit = '1';

            // Delegated listener on the accordion container instead of per-header
            // listeners so that items added dynamically after initialization also
            // respond to clicks without requiring a second initAccordion() call.
            accordion.addEventListener( 'click', function ( e ) {
                var btn = e.target.closest( '.arb-acc-header' );
                // Ignore clicks not on a header, or from a nested accordion.
                if ( ! btn || btn.closest( '.arb-accordion' ) !== accordion ) return;
                var item = btn.closest( '.arb-acc-item' );
                if ( ! item ) return;
                var isOpen      = item.classList.contains( 'is-open' );
                var closeOthers = accordion.dataset.closeOthers === '1';

                if ( closeOthers ) {
                    accordion.querySelectorAll( '.arb-acc-item.is-open' ).forEach( function ( openEl ) {
                        if ( openEl !== item ) closeItem( openEl );
                    } );
                }

                if ( ! isOpen ) { openItem( item ); }
                else            { closeItem( item ); }
            } );

            // WAI-ARIA APG accordion keyboard pattern: arrow keys move focus between
            // headers so keyboard users can navigate without tabbing through all page content.
            accordion.addEventListener( 'keydown', function ( e ) {
                if ( e.key !== 'ArrowDown' && e.key !== 'ArrowUp' &&
                     e.key !== 'Home'      && e.key !== 'End' ) return;
                var btn = e.target.closest( '.arb-acc-header' );
                if ( ! btn || btn.closest( '.arb-accordion' ) !== accordion ) return;

                // Collect only headers that belong to this accordion (not nested ones).
                var headers = [];
                accordion.querySelectorAll( '.arb-acc-header' ).forEach( function ( h ) {
                    if ( h.closest( '.arb-accordion' ) === accordion ) headers.push( h );
                } );
                if ( headers.length < 2 ) return;

                var idx  = headers.indexOf( btn );
                var next;
                if ( e.key === 'ArrowDown' ) {
                    next = headers[ ( idx + 1 ) % headers.length ];
                } else if ( e.key === 'ArrowUp' ) {
                    next = headers[ ( idx - 1 + headers.length ) % headers.length ];
                } else if ( e.key === 'Home' ) {
                    next = headers[ 0 ];
                } else {
                    next = headers[ headers.length - 1 ];
                }

                e.preventDefault();
                next.focus();
            } );
        } );
    }

    function prefersReducedMotion() {
        return window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
    }

    // Returns the transition-duration for max-height specifically (0 when absent/none).
    // Reading the first transitionDuration value alone is unreliable: it maps to whichever
    // property appears first in transitionProperty, not necessarily max-height.  If a theme
    // replaces the transition with e.g. `transition: opacity .3s !important`, the first
    // duration is non-zero but max-height has no transition — transitionend for max-height
    // never fires, onEnd/onOpenEnd never run, `hidden` is never restored on close, and
    // closed panels remain in the accessibility tree (screen readers can read closed content).
    function getMaxHeightTransitionDuration( el ) {
        var style     = window.getComputedStyle( el );
        var props     = ( style.transitionProperty || '' ).split( ',' );
        var durations = ( style.transitionDuration  || '' ).split( ',' );
        for ( var i = 0; i < props.length; i++ ) {
            var prop = props[ i ].trim();
            if ( prop === 'max-height' || prop === 'all' ) {
                return parseFloat( durations[ i ] ) || 0;
            }
        }
        return 0;
    }

    function openItem( item ) {
        var body   = item.querySelector( '.arb-acc-body' );
        var header = item.querySelector( '.arb-acc-header' );
        if ( ! body || ! header ) return;

        // Cancel any pending close-transition cleanup so it does not hide the
        // body or clear inline styles at the end of the upcoming open animation.
        var prevClose = closeTransitionListeners.get( body );
        if ( prevClose ) {
            body.removeEventListener( 'transitionend', prevClose );
            closeTransitionListeners.delete( body );
        }

        // Cancel any in-flight open listener (e.g. openItem called again before
        // the previous open animation completed and its transitionend never fired).
        var prevOpen = openTransitionListeners.get( body );
        if ( prevOpen ) {
            body.removeEventListener( 'transitionend', prevOpen );
            openTransitionListeners.delete( body );
        }

        // aria-expanded must be set before removing hidden so the button state
        // and the region's presence in the accessibility tree are consistent
        // from the moment AT can observe either change.
        header.setAttribute( 'aria-expanded', 'true' );
        body.removeAttribute( 'hidden' );

        if ( prefersReducedMotion() ) {
            // Skip animation: show content and update state synchronously.
            // Use 'none' (not scrollHeight px) so dynamically-added content
            // (e.g. lazy-loaded images) is never clipped — mirrors the
            // onOpenEnd cleanup done at the end of the animated path.
            item.classList.add( 'is-open' );
            body.style.maxHeight = 'none';
            body.style.overflow  = 'visible';
            body.style.opacity   = '1';
            return;
        }

        body.style.maxHeight = '0';
        body.style.opacity   = '0';

        // Fuerza reflow para que la transición arranque desde 0
        void body.offsetHeight;

        item.classList.add( 'is-open' );
        body.style.maxHeight = body.scrollHeight + 'px';
        body.style.opacity   = '1';

        // If transitions are suppressed by a CSS override other than the
        // prefers-reduced-motion media query (e.g. theme !important rule),
        // transitionend will never fire — lift max-height synchronously so
        // lazy-loaded content that grows the panel is never clipped.
        if ( ! getMaxHeightTransitionDuration( body ) ) {
            body.style.maxHeight = 'none';
            body.style.overflow  = 'visible';
            return;
        }

        // Lift the fixed max-height once the open animation ends so that
        // content added after open (e.g. loading="lazy" images) is never clipped.
        function onOpenEnd( e ) {
            if ( e.propertyName !== 'max-height' ) return;
            body.removeEventListener( 'transitionend', onOpenEnd );
            openTransitionListeners.delete( body );
            if ( item.classList.contains( 'is-open' ) ) {
                body.style.maxHeight = 'none';
                body.style.overflow  = 'visible';
            }
        }
        openTransitionListeners.set( body, onOpenEnd );
        body.addEventListener( 'transitionend', onOpenEnd );
    }

    function closeItem( item ) {
        var body   = item.querySelector( '.arb-acc-body' );
        var header = item.querySelector( '.arb-acc-header' );
        if ( ! body || ! header ) return;

        // Remove any previous transitionend listener before adding a new one.
        // Without this, rapid closeItem() calls (e.g. from closeOthers) stack
        // listeners on the same element — the orphaned listeners are never
        // removed when the element is already closed and no transition fires.
        var prevListener = closeTransitionListeners.get( body );
        if ( prevListener ) {
            body.removeEventListener( 'transitionend', prevListener );
            closeTransitionListeners.delete( body );
        }

        // Cancel any in-flight open listener so it cannot race with the close
        // animation's transitionend and set maxHeight = 'none' unexpectedly.
        var prevOpen = openTransitionListeners.get( body );
        if ( prevOpen ) {
            body.removeEventListener( 'transitionend', prevOpen );
            openTransitionListeners.delete( body );
        }

        // Restore overflow:hidden (CSS value) so the close animation clips
        // correctly. This reverses the 'visible' set at end of openItem.
        body.style.overflow = '';

        // aria-expanded must reflect the new state before any visual change
        // begins — mirrors the pattern in openItem() where aria-expanded is
        // set before the open animation so AT state and visuals are in sync.
        header.setAttribute( 'aria-expanded', 'false' );

        if ( prefersReducedMotion() ) {
            // Skip animation: hide content and update state synchronously.
            // Critical: if we relied on transitionend here and transitions are
            // suppressed, the hidden attribute would never be restored, leaving
            // the panel content readable by assistive technologies.
            item.classList.remove( 'is-open' );
            body.setAttribute( 'hidden', '' );
            body.style.maxHeight = '';
            body.style.opacity   = '';
            return;
        }

        // Fija la altura actual antes de animar a 0
        body.style.maxHeight = body.scrollHeight + 'px';
        void body.offsetHeight;

        item.classList.remove( 'is-open' );
        body.style.maxHeight = '0';
        body.style.opacity   = '0';

        // If transitions are suppressed by a CSS override other than the
        // prefers-reduced-motion media query, transitionend will never fire.
        // Restore hidden synchronously so the panel body is removed from the
        // accessibility tree and screen readers cannot read closed content.
        if ( ! getMaxHeightTransitionDuration( body ) ) {
            body.setAttribute( 'hidden', '' );
            body.style.maxHeight = '';
            body.style.opacity   = '';
            return;
        }

        function onEnd( e ) {
            if ( e.propertyName !== 'max-height' ) return;
            body.removeEventListener( 'transitionend', onEnd );
            closeTransitionListeners.delete( body );
            if ( ! item.classList.contains( 'is-open' ) ) {
                body.setAttribute( 'hidden', '' );
                body.style.maxHeight = '';
                body.style.opacity   = '';
            }
        }
        closeTransitionListeners.set( body, onEnd );
        body.addEventListener( 'transitionend', onEnd );
    }

    // Guard against deferred/async script loading (e.g. WP Rocket) where
    // DOMContentLoaded may have already fired before this script executes.
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', function () {
            initAccordion( document );
        } );
    } else {
        initAccordion( document );
    }

    function registerElementorHook() {
        if ( ! window.elementorFrontend || ! window.elementorFrontend.hooks ) return;
        window.elementorFrontend.hooks.addAction(
            'frontend/element_ready/arb-accordion/default',
            function ( $scope ) {
                initAccordion( $scope[0] || document );
            }
        );
    }

    // elementorFrontend may not exist yet at parse time; fall back to the
    // jQuery-based init event that Elementor fires when its frontend is ready.
    if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
        registerElementorHook();
    } else if ( window.jQuery ) {
        window.jQuery( window ).on( 'elementor/frontend/init', registerElementorHook );
    }
} )();
