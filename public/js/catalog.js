if(typeof(ANORRL) == 'undefined') {
    ANORRL = {}
}

if (!Object.keys) {
    Object.keys = function(obj) {
        var keys = [];
        for (var i in obj) {
            if (obj.hasOwnProperty(i)) {
                keys.push(i);
            }
        }
        return keys;
    };
}

const regex = /[^A-Za-z0-9 ]/g;

ANORRL.Catalog = {
    CurrentPage: 1,
    CurrentFilter: 1,
    CurrentCategory: 8,
    CurrentQuery: '',
    CurrentlyLoadingCrapBruh: false,
    Submit: function() {
        this.GrabAssets(this.CurrentFilter, this.CurrentCategory, 1, $('#SearchBox[name=query]').val());
    },
    NextPage: function() {
        this.GrabAssets(this.CurrentFilter, this.CurrentCategory, this.CurrentPage + 1);
    },
    PrevPage: function() {
        this.GrabAssets(this.CurrentFilter, this.CurrentCategory, this.CurrentPage - 1);
    },
    GrabAssets: function(filter, category, page, query) {
        if (this.CurrentlyLoadingCrapBruh) {
            return;
        } else {
            this.CurrentlyLoadingCrapBruh = true;
        }

        var loadingMessage = $('#AssetsContainer #StatusText #Loading');
        var emptyMessage = $('#AssetsContainer #StatusText #NoAssets');
        var assetsContainer = $('#AssetsContainer #Assets');
        var pagercontainer = $('#AssetsContainer #Paginator');
        var backPager = pagercontainer.find('#PrevPager');
        var nextPager = pagercontainer.find('#NextPager');

        emptyMessage.hide();
        loadingMessage.show();
        assetsContainer.empty();

        if (category === undefined) { category = this.CurrentCategory; } else { this.CurrentCategory = category; }
        if (page === undefined) { page = 1; }
        if (filter === undefined) { filter = this.CurrentFilter; } else { this.CurrentFilter = filter; }
        if (query === undefined) { query = this.CurrentQuery; } else { this.CurrentQuery = query; }

        $('[data_category], [data_filter]').removeAttr('selected').removeClass('active');
        $('[data_category=' + category + ']').attr('selected', '').addClass('active');
        $('[data_filter=' + filter + ']').attr('selected', '').addClass('active');

        $.get('/api/catalog', { f: filter, c: category, q: query, p: page }, function(data) {
            var assets = data['assets'];
            ANORRL.Catalog.CurrentPage = data['page'];
            var current_page = ANORRL.Catalog.CurrentPage;
            var total_pages = data['total_pages'];

            if (assets.length === 0) {
                pagercontainer.hide();
                loadingMessage.hide();
                emptyMessage.show();
            } else {
                loadingMessage.hide();
                pagercontainer.show();

                for (var key in assets) {
                    var asset = assets[key];
                    var template = $($('.Asset[template]').clone().prop('outerHTML'));
                    template.removeAttr('template');

                    var urlname = asset['name'].replaceAll(regex, '').trim().toLowerCase().replaceAll(' ', '-');
                    if (urlname === '') {
                        urlname = 'unnamed';
                    }

                    var soldLabel = asset['onsale']
                        ? 'Sold: ' + (asset['sales_count'] == 1 ? asset['sales_count'] + ' time' : asset['sales_count'] + ' times')
                        : 'Not on sale';

                    template.find('#Pricing').html($('<span class="catalog-chip"></span>').text(soldLabel));
                    template.find('#NameAndThumbs > .asset-thumb-container > img').attr('src', '/thumbs/?id=' + asset['id'] + '&sxy=220');
                    template.find('#NameAndThumbs').attr('href', '/' + urlname + '-item?id=' + asset['id']);
                    template.find('.catalog-item-title').text(asset['name']);
                    template.find('#Creator > span').text(asset['creator']['name']);
                    template.find('#Creator').attr('href', '/users/' + asset['creator']['id'] + '/profile');
                    template.find('#FavouritesArea > span').text(asset['favourites']);

                    assetsContainer.append(template);
                }

                current_page == 1 ? backPager.hide() : backPager.show();
                current_page == total_pages ? nextPager.hide() : nextPager.show();
                pagercontainer.find('input').val(current_page);
                pagercontainer.find('#Counter').html(total_pages);
            }

            ANORRL.Catalog.CurrentlyLoadingCrapBruh = false;
        }).fail(function() {
            loadingMessage.hide();
            emptyMessage.show();
            ANORRL.Catalog.CurrentlyLoadingCrapBruh = false;
        });
    }
}

$(function() {
    $('[data_category]').on('click', function() {
        ANORRL.Catalog.GrabAssets(ANORRL.Catalog.CurrentFilter, $(this).attr('data_category'), 1, ANORRL.Catalog.CurrentQuery);
    });

    $('[data_filter]').on('click', function() {
        ANORRL.Catalog.GrabAssets($(this).attr('data_filter'), ANORRL.Catalog.CurrentCategory, 1, ANORRL.Catalog.CurrentQuery);
    });

    ANORRL.Catalog.GrabAssets();

    $('#SearchBox').on('keypress', function(e) {
        if (e.keyCode == 13) {
            ANORRL.Catalog.Submit();
        }
    });

    $('#Paginator').find('input').on('change', function() {
        ANORRL.Catalog.GrabAssets(ANORRL.Catalog.CurrentFilter, ANORRL.Catalog.CurrentCategory, Number($(this).val()), ANORRL.Catalog.CurrentQuery);
    });
});
