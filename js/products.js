var $ = jQuery;

$(document).ready(function () {
    function moProductListingOnload() {
        function isEmpty(str) {
            return (!str || 0 === str.length || $.trim(str) === "");
        }

        function isBlank(str) {
            return (!str || /^\s*$/.test(str));
        }

        function numberWithCommas(x,fixed = 0) {
            if(isNaN(x)) return 0;
            x = x.toFixed(fixed);
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function parsePriceShopee(price){
            let priceInt = parseInt(price);
            return numberWithCommas(priceInt);
        }

        String.prototype.isEmpty = function () {
            return (this.length === 0 || !this.trim());
        };

        $(".mo-product-listing-wrapper").each(function(index, value) {
            let wrapperElm = $(this);
            let api = wrapperElm.attr('api-shortcode');
            let html = "";
            wrapperElm.html('');

            $.get(api, function (response){
                if(response.status !== 'true'){
                    return ;
                }
                let dataLinks = response.data.data_link;

                $.each(dataLinks, function (index,data) {
                    let elmDiscountPrice = '';
                    if(data.price !== '0' && data.price !== data.sale_price){
                        elmDiscountPrice = `<div class="text-muted"><strike>${parsePriceShopee(data.price)}<small>₫</small></strike></div>`;
                    }
                    let urlObj = new URL(response.data.aff_url);
                    urlObj.searchParams.append('url', (data.url));

                    if(typeof data.aff_sub1 !== 'undefined' && data.aff_sub1 !== null && data.aff_sub1.length > 0){
                        urlObj.searchParams.append('aff_sub1', data.aff_sub1);
                    }
                    if(typeof data.aff_sub2 !== 'undefined' && data.aff_sub2 !== null && data.aff_sub2.length > 0){
                        urlObj.searchParams.append('aff_sub2', data.aff_sub2);
                    }
                    if(typeof data.aff_sub3 !== 'undefined' && data.aff_sub3 !== null && data.aff_sub3.length > 0){
                        urlObj.searchParams.append('aff_sub3', data.aff_sub3);
                    }
                    if(typeof data.aff_sub4 !== 'undefined' && data.aff_sub4 !== null && data.aff_sub4.length > 0){
                        urlObj.searchParams.append('aff_sub4', data.aff_sub4);
                    }
                    urlObj.searchParams.append('mo_source', response.data.mo_source);

                    let affUrl = urlObj.href;

                    let shopName = '';
                    if(response.data.show_shop_name == 1){
                        shopName = data.offer.charAt(0).toUpperCase() + data.offer.slice(1);
                    }

                    let shopLogo = '';
                    if(response.data.show_shop_logo == 1){
                        shopLogo = `<img class="mo-logo-offer" src="${response.data.logo_offer[data.offer]}">`;
                    }

                    if(data.type == 0 || data.type == null){
                        html +=
                            `
                                <div class="row-products">
                                    <div class="col-md-2 col-sm-2 col-xs-12 mo-image-cell">
                                        <a rel="nofollow" target="_blank" href="${affUrl}" >
                                            <img src="${data.image}"
                                                    alt="${data.item_name}">
                                        </a>
                                    </div>
                                    <div class="col-md-5 col-sm-5 col-xs-12">
                                        <div class="mo-no-top-margin mo-list-logo-title">
                                            <b><a rel="nofollow" target="_blank" href="${affUrl}" > ${data.item_name} </a> </b></div>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 text-center">
                            `;
                        if (response.data.show_price == 1) {
                            html +=
                                `
                                        <div class="mo-price-row">
                                            <div class="mo-price">${parsePriceShopee(data.sale_price)}₫</div>
                                            ${elmDiscountPrice}
                                        </div>
                                `;
                        }

                        html +=
                            `
                                    </div>
                                    <div class="col-md-2 col-sm-2 col-xs-12 mo-btn-cell">
                                        <div class="mo-mb5">
                                            <a rel="nofollow" target="_blank" href="${affUrl}" class="btn btn-success">${response.data.button_title}</a>
                                        </div>
                                        <div class="mo-mb5">
                                            ${shopLogo} <small class="text-muted title-case"> ${shopName}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                    }
                    if(data.type == 1){
                        html +=
                            `<div class="products">
                                <div class="row">
                                    <div class="col-md-6 text-center mo-image-container cegg-mb20">
                                        <a rel="nofollow" target="_blank"
                                           href="${affUrl}">
                                            <img class="mo-thumbnail-carousel" src="${data.image}"
                                                 alt="${data.item_name}">
                                        </a>
                                    </div>
                                    <div class="col-md-6 mo-product-slide-info-1">
                                        <p class="cegg-no-top-margin cegg-mb15 mo-slide-1-title">${data.item_name}</p>
                            `;
                        if (response.data.show_price == 1) {
                            html +=
                                `
                                        <div class="cegg-price-row cegg-mb10">
                                            ${elmDiscountPrice}
                                            <span class="mo-price">${parsePriceShopee(data.sale_price)}<span class="cegg-currency">₫</span></span>
                                        </div>
                                `;
                        }

                        html +=
                            `
                                        <div class="cegg-btn-row cegg-mb20">
                                            <a rel="nofollow" target="_blank"
                                               href="${affUrl}"
                                               class="btn btn-success cegg-btn-big cegg-mb5">${response.data.button_title}</a>
                                            <br>
                                            ${shopLogo} <small> ${shopName}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                    }
                    if(data.type == 2){
                        if(index >3){
                            return false;
                        }
                        html +=
                            `<div class="row mo-no-margin-b">
                                <div class="flex-col">
                                    <a rel="nofollow" class="mo-product-a-shop" target="_blank" href="${affUrl}">
                                        ${shopLogo} ${shopName}
                                    </a>
                                </div>
                                <div class="flex-col">
                            `;
                        if (response.data.show_price == 1) {
                            html +=
                                `
                                    <span class="mo-price">${parsePriceShopee(data.sale_price)}<span class="cegg-currency">₫</span></span>
                                `;
                        }
                        html +=
                            `
                                </div>
                                <div class="flex-col mo-align-center">
                                    <a rel="nofollow" target="_blank" href="${affUrl}" class="btn btn-success">${response.data.button_title}</a>
                                </div>
                            </div>`;
                    }
                });
                if(dataLinks[0].type == 0 || dataLinks[0].type == null){
                    let parentHtml =
                        `<div class="mo-product-container mo-list-withlogos">
                            <div class="mo-listcontainer">
                                ${html}
                            </div>
                        </div>`;
                    wrapperElm.append(parentHtml);
                }
                if(dataLinks[0].type == 1){
                    let parrentSlideHtml =
                        `<div class="mo-product-container egg-item">
                            <div class="owl-carousel owl-theme">
                                ${html}
                            </div>
                        </div>`;
                    wrapperElm.append(parrentSlideHtml);
                    $(".owl-carousel").owlCarousel({
                        nav: true,
                        items:1,
                        responsive:{
                            0:{
                                nav:false
                            },
                            1000:{
                                nav:true,
                            }
                        }
                    });
                }
                if(dataLinks[0].type == 2){
                    let parrentSlideHtml =
                        `<div class="mo-product-container">
                            <div class="container-fluid mo-product-container-2">
                                <div class="col-md-5">
                                    <img class="img-responsive" src="${dataLinks[0].image}" alt="${dataLinks[0].item_name}">
                                </div>
                                <div class="col-md-7">
                                    <p class="cegg-no-top-margin mo-product-title-hidden">${dataLinks[0].item_name}</p>
                                    ${html}
                                </div>
                            </div>
                        </div>`;
                    wrapperElm.append(parrentSlideHtml);
                }
            });
        });
    }
    moProductListingOnload();
});
