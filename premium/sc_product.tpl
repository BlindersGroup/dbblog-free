
{if $type == 'product'}
    <div class="products row"">
        {include file="catalog/_partials/miniatures/product.tpl" product=$product}
    </div>
{/if}

{if $type == 'category'}
    <div class="products">
        <div id="splide_dbblogproducts" class="splide">
            <div class="splide__track">
                <div class="splide__list">
                    {foreach from=$products item=product}
                        <div class="splide__slide">
                            {include file="catalog/_partials/miniatures/product.tpl" product=$product}
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener( 'DOMContentLoaded', function () {
            new Splide( '#splide_dbblogproducts', {
                perPage     : 3,
                pagination: false,
                lazyLoad: 'sequential',
                arrows: true,
                breakpoints: {
                    600: {
                        perPage: 2,
                    },
                    800: {
                        perPage: 2,
                    },
                    1200: {
                        perPage: 3,
                    }
                },
            } ).mount();
        } );
    </script>
{/if}