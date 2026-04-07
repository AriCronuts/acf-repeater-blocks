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
                        accordion.querySelectorAll( '.arb-acc-item.is-open' ).forEach( function ( openItem ) {
                            closeItem( openItem );
                        } );
                    }

                    if ( ! isOpen ) { openItem( item ); }
                    else            { closeItem( item ); }
                } );
            } );
        } );
    }

    function openItem( item ) {
        item.classList.add( 'is-open' );
        item.querySelector( '.arb-acc-header' ).setAttribute( 'aria-expanded', 'true' );
        item.querySelector( '.arb-acc-body' ).removeAttribute( 'hidden' );
    }

    function closeItem( item ) {
        item.classList.remove( 'is-open' );
        item.querySelector( '.arb-acc-header' ).setAttribute( 'aria-expanded', 'false' );
        item.querySelector( '.arb-acc-body' ).setAttribute( 'hidden', '' );
    }

    document.addEventListener( 'DOMContentLoaded', function () {
        initAccordion( document );
    } );

    if ( window.elementorFrontend ) {
        window.elementorFrontend.hooks.addAction(
            'frontend/element_ready/arb-accordion/default',
            function ( $scope ) {
                initAccordion( $scope[0] || $scope );
            }
        );
    }
} )();
