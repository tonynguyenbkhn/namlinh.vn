<?php
get_template_part('templates/blocks/album-feedback', null, ['enable_container' => false]);

if (is_active_sidebar('footer-top')) {
?>
    <div class="footer-top">
        <?php dynamic_sidebar('footer-top'); ?>
    </div><!-- End Footer -->
<?php
}
?>
<?php
if (is_active_sidebar('footer-primary')) {
?>
    <footer id="colophon" class="site-footer">
        <?php dynamic_sidebar('footer-primary'); ?>
    </footer><!-- End Footer -->
<?php
}
?>
<?php
if (is_active_sidebar('footer-absolute')) {
?>
    <div class="footer-absolute">
        <?php dynamic_sidebar('footer-absolute'); ?>
    </div>
<?php
}
?>
<?php
$dataStickyContact['items'] = get_field('sticky_links', 'option') ? get_field('sticky_links', 'option') : [];
get_template_part('templates/blocks/back-to-top', null, []);
get_template_part('templates/blocks/sticky-contact', null, $dataStickyContact);
get_template_part('template-parts/footers/th-mobile-menu', null, []);
get_template_part('template-parts/footers/mini-cart', null, []);
get_template_part('templates/blocks/menu-mobile-footer', null, []);
?>
<?php wp_footer(); ?>

<script>
    jQuery(document).ready(function($) {
        if (window.location.pathname.includes('he-thong-cua-hang')) {
            function updateMapFromList() {
                $('.map-image').empty();
                const $listItems = $('#p-statelist .sl-item');
                if ($listItems.length === 0) return;

                let $targetItem = $listItems.filter('.highlighted').first();

                if ($targetItem.length === 0) {
                    $targetItem = $listItems.first();
                }

                const lat = $targetItem.data('lat');
                const lng = $targetItem.data('lng');

                if (!lat || !lng) return;

                const placeName = $targetItem.find('.sl-addr-list-title').text().trim() || 'Bản đồ vị trí';

                const iframe = `
                <iframe 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    loading="lazy" 
                    allowfullscreen 
                    referrerpolicy="no-referrer-when-downgrade"
                    title="${placeName}"
                    src="https://maps.google.com/maps?q=${lat},${lng}&z=17&output=embed">
                </iframe>
            `;

                $('.map-image').append(iframe).animate({
                    opacity: 1
                }, 400);
            }

            // Theo dõi DOM thay đổi khi AJAX thêm <li>
            const observer = new MutationObserver(function(mutationsList, observer) {
                const $listItems = $('#p-statelist .sl-item');
                if ($listItems.length > 0) {
                    updateMapFromList();
                    observer.disconnect(); // Dừng quan sát sau khi đã xử lý xong
                }
            });

            const targetNode = document.getElementById('p-statelist');
            if (targetNode) {
                observer.observe(targetNode, {
                    childList: true,
                    subtree: false
                });
            }

            // Gọi lại khi người dùng click item
            $(document).on('click', '.sl-item', function() {
                $('.sl-item').removeClass('highlighted');
                $(this).addClass('highlighted');
                updateMapFromList();
            });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.ywcas-popular-searches-item');

        // Hàm gán giá trị vào input và kích hoạt tìm kiếm
        function handleSearch(keyword) {
            const input = document.querySelector('.lapilliUI-Input__field');
            if (!input) return;

            input.focus();

            const nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;

            // Bước 1: Xóa trước giá trị
            nativeInputValueSetter.call(input, '');
            input.dispatchEvent(new Event('input', {
                bubbles: true
            }));

            // Bước 2: Gán lại sau 50ms (hoặc requestAnimationFrame)
            setTimeout(() => {
                nativeInputValueSetter.call(input, keyword);

                input.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
                input.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
                input.dispatchEvent(new KeyboardEvent('keyup', {
                    bubbles: true,
                    key: 'Enter',
                    code: 'Enter',
                    keyCode: 13
                }));
            }, 50);
        }

        // Gắn sự kiện click cho từng nút từ khóa
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const keyword = this.dataset.keyword;
                const input = document.querySelector('.lapilliUI-Input__field');

                if (input) {
                    // Nếu input đã có thì xử lý ngay
                    handleSearch(keyword);
                } else {
                    // Nếu input chưa có, chờ xuất hiện bằng MutationObserver
                    const observer = new MutationObserver((mutations, obs) => {
                        const inputNow = document.querySelector('.lapilliUI-Input__field');
                        if (inputNow) {
                            obs.disconnect(); // Ngưng theo dõi sau khi tìm thấy
                            handleSearch(keyword);
                        }
                    });

                    observer.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Kiểm tra có phải trang sản phẩm hay không (WooCommerce thường dùng class 'single-product')
        if (document.body.classList.contains('single-product')) {
            const titleElement = document.querySelector('h1.product_title.entry-title');
            const inputElement = document.querySelector('input[name="product-name"]');

            if (titleElement && inputElement) {
                inputElement.value = titleElement.textContent.trim();
            }
        }
    });
</script>
<?php if (is_product()): ?>
    <script>
        jQuery(document).ready(function($) {
            const defaultPriceHtml = $(".entry-summary-wrapper .price").html();

            $('form.variations_form').on('show_variation', function(event, variation) {
                const newPriceHtml = variation.price_html;

                $(".entry-summary-wrapper .price").html(newPriceHtml).show();

                $(".woocommerce-variation-price").hide();
            });

            $('form.variations_form').on('reset_data', function() {
                $(".entry-summary-wrapper .price").html(defaultPriceHtml).show();
                $(".woocommerce-variation-price").show();
            });
        });
    </script>
<?php endif; ?>
<script>
    // Hàm định dạng lại giá tiền
    function cleanUpPrices() {
        document.querySelectorAll('.search-result-item__price').forEach((el) => {
            let text = el.textContent.trim();

            // Regex: tìm và xóa ".00" hoặc ",00" ở cuối trước ký tự ₫
            text = text.replace(/([.,]00)(?=\s*₫)/g, '');

            el.textContent = text;
        });
    }

    // Tạo một MutationObserver để theo dõi sự thay đổi trong DOM
    const observer = new MutationObserver(function(mutationsList) {
        for (let mutation of mutationsList) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && node.matches('.ywcas-search-results')) {
                        cleanUpPrices();
                    }
                    // Trường hợp .ywcas-search-results xuất hiện trong node con
                    if (node.nodeType === 1 && node.querySelector('.ywcas-search-results')) {
                        cleanUpPrices();
                    }
                });
            }
        }
    });

    // Bắt đầu quan sát body (hoặc vùng chứa phù hợp)
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
</script>
</body>

</html>