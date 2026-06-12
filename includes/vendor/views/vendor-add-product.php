<?php if (!defined('ABSPATH')) exit; ?>

<div class="neo-dashboard-content">
    <h2>افزودن محصول جدید به لیست شما</h2>

    <div class="neo-products-toolbar">
        <div class="neo-filters-container">
            <input type="text" id="neo-search-keyword" placeholder="جستجوی نام یا SKU...">

            <select id="neo-filter-category" style="flex: 1; padding: 10px;">
                <option value="">همه دسته‌بندی‌ها</option>
                <?php
                $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
                foreach ($categories as $cat) {
                    echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="neo-toolbar-right">
            <!-- Pagination اینجا منتقل شود -->
            <div id="neo-pagination-container"></div>
        </div>
    </div>

</div>



<div class="neo-results-table-wrapper">
    <table class="neo-table">
        <thead>
            <tr>
                <th>تصویر</th>
                <th>نام محصول</th>
                <th>SKU</th>
                <th>دسته‌بندی</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody id="neo-search-results-body">
        </tbody>
    </table>
</div>
<!-- بخش صفحه‌بندی -->
</div>