/* ACF Repeater for Elementor — Accordion JS */
(function () {
    'use strict';

    // Tracks the pending transitionend listener (open OR close) for each body
    // element. Any new animation cancels the previous listener before registering
    // its own, preventing stale listeners when the user toggles rapidly.
    var pendingTransitionListeners = new WeakMap();

    function cancelPendingTransition( body ) {
        var prev = pendingTransitionListeners.get( body );
        if ( prev ) {
            body.removeEventListener( 'transitionend', prev );
            pendingTransitionListeners.delete( body );
        }
    }

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

        // Cancel any in-flight close transition before starting the open.
        cancelPendingTransition( body );

        body.removeAttribute( 'hidden' );

        if ( prefersReducedMotion() ) {
            // No animation: add the class and let the CSS open-state rule
            // (.arb-acc-item.is-open .arb-acc-body { max-height: none }) handle
            // height. Clearing inline styles ensures the CSS rule is not masked.
            item.classList.add( 'is-open' );
            body.style.maxHeight = '';
            body.style.opacity   = '';
            header.setAttribute( 'aria-expanded', 'true' );
            return;
        }

        body.style.maxHeight = '0';
        body.style.opacity   = '0';

        // Fuerza reflow para que la transición arranque desde 0
        void body.offsetHeight;

        item.classList.add( 'is-open' );
        body.style.maxHeight = body.scrollHeight + 'px';
        body.style.opacity   = '1';
        header.setAttribute( 'aria-expanded', 'true' );

        // After the open transition completes, clear the fixed inline max-height
        // so the CSS open-state rule (max-height: none) takes over. Without this,
        // lazy-loaded images or dynamic content taller than scrollHeight at open
        // time would be clipped by the frozen inline value.
        function onOpenEnd( e ) {
            if ( e.propertyName !== 'max-height' ) return;
            body.removeEventListener( 'transitionend', onOpenEnd );
            pendingTransitionListeners.delete( body );
            if ( item.classList.contains( 'is-open' ) ) {
                body.style.maxHeight = '';
                body.style.opacity   = '';
            }
        }
        pendingTransitionListeners.set( body, onOpenEnd );
        body.addEventListener( 'transitionend', onOpenEnd );
    }

    function closeItem( item ) {
        var body   = item.querySelector( '.arb-acc-body' );
        var header = item.querySelector( '.arb-acc-header' );
        if ( ! body || ! header ) return;

        // Cancel any in-flight open transition before starting the close.
        cancelPendingTransition( body );

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

        // Pin the current natural height (max-height: none after open transition
        // was cleared) so the collapse animation has a defined numeric start point.
        body.style.maxHeight = body.scrollHeight + 'px';
        void body.offsetHeight;

        item.classList.remove( 'is-open' );
        body.style.maxHeight = '0';
        body.style.opacity   = '0';
        header.setAttribute( 'aria-expanded', 'false' );

        function onCloseEnd( e ) {
            if ( e.propertyName !== 'max-height' ) return;
            body.removeEventListener( 'transitionend', onCloseEnd );
            pendingTransitionListeners.delete( body );
            if ( ! item.classList.contains( 'is-open' ) ) {
                body.setAttribute( 'hidden', '' );
                body.style.maxHeight = '';
                body.style.opacity   = '';
            }
        }
        pendingTransitionListeners.set( body, onCloseEnd );
        body.addEventListener( 'transitionend', onCloseEnd );
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
