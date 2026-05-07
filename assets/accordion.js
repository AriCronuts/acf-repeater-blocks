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

    // Returns the first transition-duration value in seconds (0 when none).
    // Used to detect CSS overrides (e.g. theme !important) that suppress
    // transitions outside of the prefers-reduced-motion media query, which
    // would prevent transitionend from ever firing.
    function getTransitionDuration( el ) {
        return parseFloat( window.getComputedStyle( el ).transitionDuration ) || 0;
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

        body.removeAttribute( 'hidden' );

        if ( prefersReducedMotion() ) {
            // Skip animation: show content and update state synchronously.
            item.classList.add( 'is-open' );
            body.style.maxHeight = body.scrollHeight + 'px';
            body.style.opacity   = '1';
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

        // If transitions are suppressed by a CSS override other than the
        // prefers-reduced-motion media query (e.g. theme !important rule),
        // transitionend will never fire — lift max-height synchronously so
        // lazy-loaded content that grows the panel is never clipped.
        if ( ! getTransitionDuration( body ) ) {
            body.style.maxHeight = 'none';
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

        // If transitions are suppressed by a CSS override other than the
        // prefers-reduced-motion media query, transitionend will never fire.
        // Restore hidden synchronously so the panel body is removed from the
        // accessibility tree and screen readers cannot read closed content.
        if ( ! getTransitionDuration( body ) ) {
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
