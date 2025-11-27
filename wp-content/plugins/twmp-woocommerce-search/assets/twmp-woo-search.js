/* global jQuery, TWMPWooSearch */
(function ($) {
    'use strict';

    var debounceTimeout = null;
    var $input, $results, minChars, maxResults;
    var activeIndex = -1;
    var products = [];
    var lastTerm = '';
    var isLoading = false;
    var lastXhr = null;

    function closeResults() {
        $results.empty();
        $results.hide();
        $results.attr('aria-hidden', 'true');
        activeIndex = -1;
        // Keep products and lastTerm in memory so focus can re-show cached results if needed.
    }

    function openResults() {
        $results.show();
        $results.attr('aria-hidden', 'false');
    }

    function renderResults(items) {
        products = items || [];
        if (!products.length) {
            $results.html('<div class="twmp-woo-search-none">Không có kết quả</div>');
            openResults();
            return;
        }
        var html = '<ul class="twmp-woo-search-list">';
        products.forEach(function (p, idx) {
            html += '<li class="twmp-woo-search-item" data-index="' + idx + '">'
                + '<a href="' + p.permalink + '">'
                + '<img src="' + p.thumb + '" class="twmp-woo-search-thumb" alt="' + p.title + '" />'
                + '<span class="twmp-woo-search-title">' + p.title + '</span>'
                + '<span class="twmp-woo-search-price">' + p.price + '</span>'
                + '</a>'
                + '</li>';
        });
        html += '</ul>';
        $results.html(html);
        openResults();
    }

    function renderLoading() {
        var html = '<div class="twmp-woo-search-loading" role="status" aria-live="polite">'
            + '<span class="twmp-woo-spinner" aria-hidden="true"></span>'
            + '<span class="twmp-woo-loading-text">Đang tìm...</span>'
            + '</div>';
        $results.html(html);
        openResults();
    }

    function fetch(term) {
        if (!term || term.length < minChars) {
            closeResults();
            return;
        }
        // Abort previous request if still in progress
        if (lastXhr && lastXhr.readyState && lastXhr.readyState !== 4) {
            try {
                lastXhr.abort();
            } catch (e) {
                // ignore
            }
        }
        // Show loading
        renderLoading();
        isLoading = true;

        var xhr = $.ajax({
            url: TWMPWooSearch.ajax_url,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'twmp_woo_search',
                nonce: TWMPWooSearch.nonce,
                term: term,
                max_results: TWMPWooSearch.max_results,
            }
        });
        lastXhr = xhr;

        xhr.done(function (resp) {
            // Only process a successful response if this request wasn't aborted
            if (!resp || !resp.success) {
                products = [];
                renderResults([]);
                return;
            }
            lastTerm = term;
            renderResults(resp.data.products);
        }).fail(function (jqXHR, textStatus) {
            // If aborted, don't override anything
            if (textStatus === 'abort') {
                return;
            }
            products = [];
            renderResults([]);
        }).always(function () {
            // Clear lastXhr only when the finished one was the lastXhr
            if (lastXhr === xhr) {
                lastXhr = null;
            }
            isLoading = false;
        });
    }

    function init($root) {
        $input = $root.find('.twmp-woo-search-input');
        $results = $root.find('.twmp-woo-search-results');
        minChars = parseInt(TWMPWooSearch.min_chars, 10) || 3;
        maxResults = parseInt(TWMPWooSearch.max_results, 10) || 5;

        $input.on('input', function () {
            var val = $(this).val().trim();
            if (debounceTimeout) clearTimeout(debounceTimeout);
            if (val.length < minChars) {
                closeResults();
                return;
            }
            debounceTimeout = setTimeout(function () {
                fetch(val);
            }, 1000);
        });

        // When focusing the input, if it contains a value >= min_chars, re-run search (or show cached results)
        $input.on('focus', function () {
            var val = $(this).val().trim();
            if (val.length < minChars) {
                return;
            }
            // If we already have results for this term, just re-render them
            if (!isLoading && lastTerm && lastTerm === val && products && products.length) {
                renderResults(products);
                return;
            }
            // If an AJAX call is already in flight for this term, do nothing
            if (isLoading && lastTerm === val) {
                return;
            }
            // Otherwise perform a fresh fetch using the current value
            fetch(val);
        });

        // Click outside closes popup
        $(document).on('click', function (e) {
            if ($(e.target).closest('.twmp-woo-search').length === 0) {
                closeResults();
            }
        });

        // Navigate with arrows
        $input.on('keydown', function (e) {
            if (!$results.is(':visible')) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActive(activeIndex + 1);
                return;
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(activeIndex - 1);
                return;
            }
            if (e.key === 'Enter') {
                if (activeIndex >= 0 && products[activeIndex]) {
                    window.location.href = products[activeIndex].permalink;
                    e.preventDefault();
                }
            }
            if (e.key === 'Escape') {
                closeResults();
            }
        });

        // Click item
        $results.on('click', '.twmp-woo-search-item a', function (e) {
            // let the anchor work naturally
        });
    }

    $(document).ready(function () {
        $('.twmp-woo-search').each(function () {
            init($(this));
        });
        // Click on popular keyword should fill search input and immediately search (abort previous if any)
        $(document).on('click', '.ywcas-popular-searches-item, .ywcas-popular-searches-label', function (e) {
            e.preventDefault();
            var kw = $(this).data('keyword') || $(this).text().trim();
            if (!kw) return;
            // find a search input within the same header/nav area
            var $nav = $(this).closest('.header__nav');
            var $targetInput = $nav.find('.twmp-woo-search-input').first();
            if (!$targetInput.length) {
                $targetInput = $('.twmp-woo-search-input').first();
            }
            if (!$targetInput.length) return;
            // Set globals to target input and its results container
            $input = $targetInput;
            $results = $targetInput.closest('.twmp-woo-search').find('.twmp-woo-search-results').first();
            // set value and focus
            $input.val(kw);
            $input.trigger('focus');
            if (debounceTimeout) clearTimeout(debounceTimeout);
            fetch(kw);
        });
    });
})(jQuery);
