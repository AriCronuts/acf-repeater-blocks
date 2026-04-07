/* ACF Repeater for Elementor — Accordion JS */
(function () {
    'use strict';

    function initAccordion( scope ) {
        var root = scope || document;
        root.querySelectorAll( '.arb-accordion' ).forEach( function ( accordion ) {
            if ( accordion.dataset.arbInit ) return;
            accordion.dataset.arbInit = '1';

            accordion.querySelectorAll( '.arb-acc-header' ).forEach( function ( btn ) {
                btn.addEventListener( 'click', function () {
                    var item        = btn.closest( '.arb-acc-item' );
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

    function openItem( item ) {
        var body   = item.querySelector( '.arb-acc-body' );
        var header = item.querySelector( '.arb-acc-header' );

        body.removeAttribute( 'hidden' );
        body.style.maxHeight = '0';
        body.style.opacity   = '0';

        // Fuerza reflow para que la transición arranque desde 0
        void body.offsetHeight;

        item.classList.add( 'is-open' );
        body.style.maxHeight = body.scrollHeight + 'px';
        body.style.opacity   = '1';
        header.setAttribute( 'aria-expanded', 'true' );
    }

    function closeItem( item ) {
        var body   = item.querySelector( '.arb-acc-body' );
        var header = item.querySelector( '.arb-acc-header' );

        // Fija la altura actual antes de animar a 0
        body.style.maxHeight = body.scrollHeight + 'px';
        void body.offsetHeight;

        item.classList.remove( 'is-open' );
        body.style.maxHeight = '0';
        body.style.opacity   = '0';
        header.setAttribute( 'aria-expanded', 'false' );

        body.addEventListener( 'transitionend', function onEnd( e ) {
            if ( e.propertyName !== 'max-height' ) return;
            body.removeEventListener( 'transitionend', onEnd );
            if ( ! item.classList.contains( 'is-open' ) ) {
                body.setAttribute( 'hidden', '' );
                body.style.maxHeight = '';
                body.style.opacity   = '';
            }
        } );
    }

    document.addEventListener( 'DOMContentLoaded', function () {
        initAccordion( document );
    } );

    if ( window.elementorFrontend ) {
        window.elementorFrontend.hooks.addAction(
            'frontend/element_ready/arb-acf-repeater/default',
            function ( $scope ) {
                initAccordion( $scope[0] || $scope );
            }
        );
    }
} )();
