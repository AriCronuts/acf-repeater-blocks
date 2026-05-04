/* ACF Repeater for Elementor — Accordion JS */
(function () {
    'use strict';

    // Tracks the pending transitionend listener for each body element so that
    // a rapid second closeItem() call (e.g. via closeOthers) removes the first
    // listener before adding a new one, preventing unbounded listener stacking.
    var closeTransitionListeners = new WeakMap();

    function initAccordion( scope ) {
        var root = scope || document;
        root.querySelectorAll( '.arb-accordion' ).forEach( function ( accordion ) {
            if ( accordion.dataset.arbInit ) return;
            accordion.dataset.arbInit = '1';

            accordion.querySelectorAll( '.arb-acc-header' ).forEach( function ( btn ) {
                btn.addEventListener( 'click', function () {
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
            } );
        } );
    }

    function prefersReducedMotion() {
        return window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
    }

    function openItem( item ) {
        var body   = item.querySelector( '.arb-acc-body' );
        var header = item.querySelector( '.arb-acc-header' );
        if ( ! body || ! header ) return;

        // Clear any stale close-transition listener so it does not fire during
        // the open animation (rapid close→open sequence).
        var prevListener = closeTransitionListeners.get( body );
        if ( prevListener ) {
            body.removeEventListener( 'transitionend', prevListener );
            closeTransitionListeners.delete( body );
        }

        // Update ARIA and remove hidden together: AT must never see the panel
        // as accessible (hidden removed) while aria-expanded still says false.
        body.removeAttribute( 'hidden' );
        header.setAttribute( 'aria-expanded', 'true' );

        if ( prefersReducedMotion() ) {
            // Skip animation: show content and update state synchronously.
            item.classList.add( 'is-open' );
            body.style.maxHeight = body.scrollHeight + 'px';
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

        if ( prefersReducedMotion() ) {
            // Skip animation: hide content and update state synchronously.
            // Critical: if we relied on transitionend here and transitions are
            // suppressed, the hidden attribute would never be restored, leaving
            // the panel content readable by assistive technologies.
            item.classList.remove( 'is-open' );
            header.setAttribute( 'aria-expanded', 'false' );
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
        header.setAttribute( 'aria-expanded', 'false' );

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

    document.addEventListener( 'DOMContentLoaded', function () {
        initAccordion( document );
    } );

    function registerElementorHook() {
        window.elementorFrontend.hooks.addAction(
            'frontend/element_ready/arb-accordion/default',
            function ( $scope ) {
                initAccordion( $scope[0] || $scope );
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
