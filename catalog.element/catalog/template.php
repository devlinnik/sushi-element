<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use Bitrix\Sale\Fuser;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);
$currencyList = '';
$templateLibrary = [];

$basketItemsMap = [];

if (Loader::includeModule('sale'))
{
    $siteId = \Bitrix\Main\Context::getCurrent()->getSite();
    $fUserId = Fuser::getId();
    $basket = Sale\Basket::loadItemsForFUser($fUserId, $siteId);

    foreach ($basket as $basketItem)
    {
        $productId = (int)$basketItem->getProductId();
        $qty = (float)$basketItem->getQuantity();

        if (!isset($basketItemsMap[$productId]))
        {
            $basketItemsMap[$productId] = 0;
        }

        $basketItemsMap[$productId] += $qty;
    }
}

if (!empty($arResult['CURRENCIES']))
{
    $templateLibrary[] = 'currency';
    $currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}

$haveOffers = !empty($arResult['OFFERS']);

$templateData = [
    'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
    'TEMPLATE_LIBRARY' => $templateLibrary,
    'CURRENCIES' => $currencyList,
    'ITEM' => [
        'ID' => $arResult['ID'],
        'IBLOCK_ID' => $arResult['IBLOCK_ID'],
    ],
];

if ($haveOffers)
{
    $templateData['ITEM']['OFFERS_SELECTED'] = $arResult['OFFERS_SELECTED'];
    $templateData['ITEM']['JS_OFFERS'] = $arResult['JS_OFFERS'];
}
unset($currencyList, $templateLibrary);

$mainId = $this->GetEditAreaId($arResult['ID']);
$itemIds = array(
    'ID' => $mainId,
    'DISCOUNT_PERCENT_ID' => $mainId.'_dsc_pict',
    'STICKER_ID' => $mainId.'_sticker',
    'BIG_SLIDER_ID' => $mainId.'_big_slider',
    'BIG_IMG_CONT_ID' => $mainId.'_bigimg_cont',
    'SLIDER_CONT_ID' => $mainId.'_slider_cont',
    'OLD_PRICE_ID' => $mainId.'_old_price',
    'PRICE_ID' => $mainId.'_price',
    'DESCRIPTION_ID' => $mainId.'_description',
    'DISCOUNT_PRICE_ID' => $mainId.'_price_discount',
    'PRICE_TOTAL' => $mainId.'_price_total',
    'SLIDER_CONT_OF_ID' => $mainId.'_slider_cont_',
    'QUANTITY_ID' => $mainId.'_quantity',
    'QUANTITY_DOWN_ID' => $mainId.'_quant_down',
    'QUANTITY_UP_ID' => $mainId.'_quant_up',
    'QUANTITY_MEASURE' => $mainId.'_quant_measure',
    'QUANTITY_LIMIT' => $mainId.'_quant_limit',
    'BUY_LINK' => $mainId.'_buy_link',
    'ADD_BASKET_LINK' => $mainId.'_add_basket_link',
    'BASKET_ACTIONS_ID' => $mainId.'_basket_actions',
    'NOT_AVAILABLE_MESS' => $mainId.'_not_avail',
    'COMPARE_LINK' => $mainId.'_compare_link',
    'TREE_ID' => $mainId.'_skudiv',
    'DISPLAY_PROP_DIV' => $mainId.'_sku_prop',
    'DISPLAY_MAIN_PROP_DIV' => $mainId.'_main_sku_prop',
    'OFFER_GROUP' => $mainId.'_set_group_',
    'BASKET_PROP_DIV' => $mainId.'_basket_prop',
    'SUBSCRIBE_LINK' => $mainId.'_subscribe',
    'TABS_ID' => $mainId.'_tabs',
    'TAB_CONTAINERS_ID' => $mainId.'_tab_containers',
    'SMALL_CARD_PANEL_ID' => $mainId.'_small_card_panel',
    'TABS_PANEL_ID' => $mainId.'_tabs_panel'
);

$obName = $templateData['JS_OBJ'] = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);

$name = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
    : $arResult['NAME'];

$title = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']
    : $arResult['NAME'];

$alt = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']
    : $arResult['NAME'];

if ($haveOffers)
{
    $actualItem = $arResult['OFFERS'][$arResult['OFFERS_SELECTED']] ?? reset($arResult['OFFERS']);
    $showSliderControls = false;

    foreach ($arResult['OFFERS'] as $offer)
    {
        if ($offer['MORE_PHOTO_COUNT'] > 1)
        {
            $showSliderControls = true;
            break;
        }
    }
}
else
{
    $actualItem = $arResult;
    $showSliderControls = $arResult['MORE_PHOTO_COUNT'] > 1;
}

$skuProps = array();
$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
$measureRatio = $actualItem['ITEM_MEASURE_RATIOS'][$actualItem['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
$showDiscount = $price['PERCENT'] > 0;

if ($arParams['SHOW_SKU_DESCRIPTION'] === 'Y')
{
    $skuDescription = false;
    foreach ($arResult['OFFERS'] as $offer)
    {
        if ($offer['DETAIL_TEXT'] != '' || $offer['PREVIEW_TEXT'] != '')
        {
            $skuDescription = true;
            break;
        }
    }
    $showDescription = $skuDescription || !empty($arResult['PREVIEW_TEXT']) || !empty($arResult['DETAIL_TEXT']);
}
else
{
    $showDescription = !empty($arResult['PREVIEW_TEXT']) || !empty($arResult['DETAIL_TEXT']);
}

$showBuyBtn = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION']);
$buyButtonClassName = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-default' : 'btn-link';
$showAddBtn = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION']);
$showButtonClassName = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-default' : 'btn-link';
$showSubscribe = $arParams['PRODUCT_SUBSCRIPTION'] === 'Y' && ($arResult['PRODUCT']['SUBSCRIBE'] === 'Y' || $haveOffers);

$arParams['MESS_BTN_BUY'] = $arParams['MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCE_CATALOG_BUY');
$arParams['MESS_BTN_ADD_TO_BASKET'] = $arParams['MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCE_CATALOG_ADD');

if ($arResult['MODULES']['catalog'] && $arResult['PRODUCT']['TYPE'] === ProductTable::TYPE_SERVICE)
{
    $arParams['~MESS_NOT_AVAILABLE_SERVICE'] ??= '';
    $arParams['~MESS_NOT_AVAILABLE'] = $arParams['~MESS_NOT_AVAILABLE_SERVICE']
        ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE_SERVICE');

    $arParams['MESS_NOT_AVAILABLE_SERVICE'] ??= '';
    $arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE_SERVICE']
        ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE_SERVICE');
}
else
{
    $arParams['~MESS_NOT_AVAILABLE'] ??= '';
    $arParams['~MESS_NOT_AVAILABLE'] = $arParams['~MESS_NOT_AVAILABLE']
        ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE');

    $arParams['MESS_NOT_AVAILABLE'] ??= '';
    $arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE']
        ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE');
}

$arParams['MESS_BTN_COMPARE'] = $arParams['MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCE_CATALOG_COMPARE');
$arParams['MESS_PRICE_RANGES_TITLE'] = $arParams['MESS_PRICE_RANGES_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_PRICE_RANGES_TITLE');
$arParams['MESS_DESCRIPTION_TAB'] = $arParams['MESS_DESCRIPTION_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_DESCRIPTION_TAB');
$arParams['MESS_PROPERTIES_TAB'] = $arParams['MESS_PROPERTIES_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_PROPERTIES_TAB');
$arParams['MESS_COMMENTS_TAB'] = $arParams['MESS_COMMENTS_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_COMMENTS_TAB');
$arParams['MESS_SHOW_MAX_QUANTITY'] = $arParams['MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCE_CATALOG_SHOW_MAX_QUANTITY');
$arParams['MESS_RELATIVE_QUANTITY_MANY'] = $arParams['MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['MESS_RELATIVE_QUANTITY_FEW'] = $arParams['MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_FEW');

$positionClassMap = array(
    'left' => 'product-item-label-left',
    'center' => 'product-item-label-center',
    'right' => 'product-item-label-right',
    'bottom' => 'product-item-label-bottom',
    'middle' => 'product-item-label-middle',
    'top' => 'product-item-label-top'
);

$discountPositionClass = 'product-item-label-big';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION']))
{
    foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos)
    {
        $discountPositionClass .= isset($positionClassMap[$pos]) ? ' '.$positionClassMap[$pos] : '';
    }
}

$labelPositionClass = 'product-item-label-big';
if (!empty($arParams['LABEL_PROP_POSITION']))
{
    foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos)
    {
        $labelPositionClass .= isset($positionClassMap[$pos]) ? ' '.$positionClassMap[$pos] : '';
    }
}
?>
    <div class="catalog_detail" id="<?=$itemIds['ID']?>" itemscope itemtype="http://schema.org/Product">
        <div class="container row">
            <div class="catalog_detail_l" data-entity="images-container">
                <?$renderImage_prew = CFile::ResizeImageGet($arResult["DETAIL_PICTURE"], Array("width" => 600, "height" => 800), BX_RESIZE_IMAGE_EXACT, true); ?>
                <div class="photo" id="<?=$itemIds['BIG_SLIDER_ID']?>" style="background: url('<?=$renderImage_prew["src"]?>') no-repeat;"></div>

                <?if ($arResult["PROPERTIES"]["VES"]["VALUE"]) : ?>
                    <div class="count"><?=$arResult["PROPERTIES"]["VES"]["VALUE"];?></div>
                <?endif;?>
            </div>

            <div class="catalog_detail_r">
                <div class="mentki row">
                    <?if ($arResult["PROPERTIES"]["NEW"]["VALUE"] == 'да') : ?>
                        <div class="mentka new">новинка</div>
                    <?endif;?>

                    <?if ($arResult["PROPERTIES"]["HIT"]["VALUE"] == 'да') : ?>
                        <div class="mentka hit">sokol хит</div>
                    <?endif;?>

                    <?if ($arResult["PROPERTIES"]["SALE"]["VALUE"] == 'да') : ?>
                        <div class="mentka sale">скидка</div>
                    <?endif;?>

                    <?if ($arResult["PROPERTIES"]["OST"]["VALUE"] == 'да') : ?>
                        <div class="mentka ost">острая</div>
                    <?endif;?>
                </div>

                <div class="product_detail_title row">
                    <h1><?=$name?></h1>
                    <?php
                    foreach ($arParams['PRODUCT_PAY_BLOCK_ORDER'] as $blockName)
                    {
                        switch ($blockName)
                        {
                            case 'price':
                                ?>
                                <div class="price" id="<?=$itemIds['PRICE_ID']?>"><?=$price['PRINT_RATIO_PRICE']?></div>
                                <?php
                                break;
                        }
                    }
                    ?>
                </div>

                <?if ($arResult["PREVIEW_TEXT"]) : ?>
                    <div class="description"><?=$arResult["PREVIEW_TEXT"];?></div>
                <?endif;?>

                <?foreach ($arParams['PRODUCT_INFO_BLOCK_ORDER'] as $blockName)
                {
                    switch ($blockName)
                    {
                        case 'sku':
                            if ($haveOffers && !empty($arResult['OFFERS_PROP']))
                            {
                                ?>
                                <div class="product_detail_select" id="<?=$itemIds['TREE_ID']?>">
                                    <?php
                                    foreach ($arResult['SKU_PROPS'] as $skuProperty)
                                    {
                                        if (!isset($arResult['OFFERS_PROP'][$skuProperty['CODE']]))
                                            continue;

                                        $propertyId = $skuProperty['ID'];
                                        $skuProps[] = array(
                                            'ID' => $propertyId,
                                            'SORT' => $skuProperty['ID'],
                                            'SHOW_MODE' => $skuProperty['SHOW_MODE'],
                                            'VALUES' => $skuProperty['VALUES'],
                                            'VALUES_COUNT' => $skuProperty['VALUES_COUNT']
                                        );
                                        ?>
                                        <div class="title"><?=htmlspecialcharsbx($skuProperty['NAME']);?></div>
                                        <div class="product-item-scu-list row" data-entity="sku-line-block">
                                            <?php
                                            foreach ($skuProperty['VALUES'] as &$value)
                                            {
                                                $value['NAME'] = htmlspecialcharsbx($value['NAME']);
                                                ?>
                                                <li class="product-item-scu" title="<?=$value['NAME']?>" data-treevalue="<?=$propertyId?>_<?=$value['ID']?>" data-onevalue="<?=$value['ID']?>"><?=$value['NAME']?></li>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                            break;
                    }
                }
                ?>

                <?
                if (!empty($arResult['PROPERTIES']['ADD_ORDER']["VALUE"]))
                {
                    ?>
                    <div class="catalog_add_order">
                        <?
                        global $Filter_ADDSOSTAV;
                        $Filter_ADDSOSTAV = array(
                            "SECTION_ID" => $arResult['PROPERTIES']['ADD_ORDER']['VALUE']
                        );

                        $APPLICATION->IncludeComponent("bitrix:news.list", "add_sostav", Array(
                            "ACTIVE_DATE_FORMAT" => "d.m.Y",
                            "ADD_SECTIONS_CHAIN" => "N",
                            "AJAX_MODE" => "N",
                            "AJAX_OPTION_ADDITIONAL" => "",
                            "AJAX_OPTION_HISTORY" => "N",
                            "AJAX_OPTION_JUMP" => "N",
                            "AJAX_OPTION_STYLE" => "Y",
                            "CACHE_FILTER" => "N",
                            "CACHE_GROUPS" => "N",
                            "CACHE_TIME" => "36000000",
                            "CACHE_TYPE" => "A",
                            "CHECK_DATES" => "Y",
                            "DETAIL_URL" => "",
                            "DISPLAY_BOTTOM_PAGER" => "N",
                            "DISPLAY_DATE" => "N",
                            "DISPLAY_NAME" => "N",
                            "DISPLAY_PICTURE" => "N",
                            "DISPLAY_PREVIEW_TEXT" => "N",
                            "DISPLAY_TOP_PAGER" => "N",
                            "FIELD_CODE" => array(
                                0 => "NAME",
                                1 => "PREVIEW_PICTURE",
                                2 => "",
                            ),
                            "FILTER_NAME" => "Filter_ADDSOSTAV",
                            "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                            "IBLOCK_ID" => "6",
                            "IBLOCK_TYPE" => "catalog",
                            "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                            "INCLUDE_SUBSECTIONS" => "Y",
                            "MESSAGE_404" => "",
                            "NEWS_COUNT" => "100",
                            "PAGER_BASE_LINK_ENABLE" => "N",
                            "PAGER_DESC_NUMBERING" => "N",
                            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                            "PAGER_SHOW_ALL" => "N",
                            "PAGER_SHOW_ALWAYS" => "N",
                            "PAGER_TEMPLATE" => ".default",
                            "PAGER_TITLE" => "",
                            "PARENT_SECTION" => "",
                            "PARENT_SECTION_CODE" => "",
                            "PREVIEW_TRUNCATE_LEN" => "",
                            "PROPERTY_CODE" => array(
                                0 => "PRICE",
                                1 => "",
                            ),
                            "SET_BROWSER_TITLE" => "N",
                            "SET_LAST_MODIFIED" => "N",
                            "SET_META_DESCRIPTION" => "N",
                            "SET_META_KEYWORDS" => "N",
                            "SET_STATUS_404" => "N",
                            "SET_TITLE" => "N",
                            "SHOW_404" => "N",
                            "SORT_BY1" => "SORT",
                            "SORT_BY2" => "SORT",
                            "SORT_ORDER1" => "ASC",
                            "SORT_ORDER2" => "ASC",
                            "STRICT_SECTION_CHECK" => "N",
                        ), false);
                        ?>
                    </div>
                    <?
                }
                ?>

                <div class="btns row">
                    <div class="product-item-detail-info-container" data-entity="quantity-block">
                        <div class="basket-card-quantity" data-entity="basket-item-quantity-block">
                            <button
                                    type="button"
                                    class="qty-btn minus"
                                    id="<?=$itemIds['QUANTITY_DOWN_ID']?>"
                                    data-entity="basket-item-quantity-minus"
                            >−</button>

                            <input
                                    type="number"
                                    min="1"
                                    step="<?=$measureRatio ?: 1?>"
                                    name="<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>"
                                    value="<?=($price['MIN_QUANTITY'] > 0 ? $price['MIN_QUANTITY'] : 1)?>"
                                    data-value="<?=($price['MIN_QUANTITY'] > 0 ? $price['MIN_QUANTITY'] : 1)?>"
                                    data-entity="basket-item-quantity-field"
                                    id="<?=$itemIds['QUANTITY_ID']?>"
                                    class="qty-input"
                            >

                            <button
                                    type="button"
                                    class="qty-btn plus"
                                    id="<?=$itemIds['QUANTITY_UP_ID']?>"
                                    data-entity="basket-item-quantity-plus"
                            >+</button>
                        </div>
                    </div>

                    <div data-entity="main-button-container">
                        <div id="<?=$itemIds['BASKET_ACTIONS_ID']?>">
                            <?php if ($showAddBtn): ?>
                                <a
                                        class="btn <?=$showButtonClassName?> product-item-detail-buy-button"
                                        id="<?=$itemIds['ADD_BASKET_LINK']?>"
                                        href="javascript:void(0);"
                                        data-role="add-to-basket-btn"
                                ><span>В КОРЗИНУ</span></a>
                            <?php endif; ?>

                            <?php if ($showBuyBtn): ?>
                                <a
                                        class="btn <?=$buyButtonClassName?>"
                                        id="<?=$itemIds['BUY_LINK']?>"
                                        href="javascript:void(0);"
                                        data-role="buy-btn"
                                ><span>В КОРЗИНУ</span></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if ($haveOffers)
            {
                if ($arResult['OFFER_GROUP'])
                {
                    foreach ($arResult['OFFER_GROUP_VALUES'] as $offerId)
                    {
                        ?>
                        <span id="<?=$itemIds['OFFER_GROUP'].$offerId?>" style="display: none;">
						<?php
                        $APPLICATION->IncludeComponent(
                            'bitrix:catalog.set.constructor',
                            '.default',
                            array(
                                'CUSTOM_SITE_ID' => $arParams['CUSTOM_SITE_ID'] ?? null,
                                'IBLOCK_ID' => $arResult['OFFERS_IBLOCK'],
                                'ELEMENT_ID' => $offerId,
                                'PRICE_CODE' => $arParams['PRICE_CODE'],
                                'BASKET_URL' => $arParams['BASKET_URL'],
                                'OFFERS_CART_PROPERTIES' => $arParams['OFFERS_CART_PROPERTIES'],
                                'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                'CACHE_TIME' => $arParams['CACHE_TIME'],
                                'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                                'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME'],
                                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                'CURRENCY_ID' => $arParams['CURRENCY_ID']
                            ),
                            $component,
                            array('HIDE_ICONS' => 'Y')
                        );
                        ?>
					</span>
                        <?php
                    }
                }
            }
            else
            {
                if ($arResult['MODULES']['catalog'] && $arResult['OFFER_GROUP'])
                {
                    $APPLICATION->IncludeComponent(
                        'bitrix:catalog.set.constructor',
                        '.default',
                        array(
                            'CUSTOM_SITE_ID' => $arParams['CUSTOM_SITE_ID'] ?? null,
                            'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                            'ELEMENT_ID' => $arResult['ID'],
                            'PRICE_CODE' => $arParams['PRICE_CODE'],
                            'BASKET_URL' => $arParams['BASKET_URL'],
                            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                            'CACHE_TIME' => $arParams['CACHE_TIME'],
                            'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                            'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME'],
                            'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                            'CURRENCY_ID' => $arParams['CURRENCY_ID']
                        ),
                        $component,
                        array('HIDE_ICONS' => 'Y')
                    );
                }
            }
            ?>
        </div>

        <meta itemprop="name" content="<?=$name?>" />
        <meta itemprop="category" content="<?=$arResult['CATEGORY_PATH']?>" />

        <?php
        if ($haveOffers)
        {
            foreach ($arResult['JS_OFFERS'] as $offer)
            {
                $currentOffersList = array();

                if (!empty($offer['TREE']) && is_array($offer['TREE']))
                {
                    foreach ($offer['TREE'] as $propName => $skuId)
                    {
                        $propId = (int)mb_substr($propName, 5);

                        foreach ($skuProps as $prop)
                        {
                            if ($prop['ID'] == $propId)
                            {
                                foreach ($prop['VALUES'] as $propId => $propValue)
                                {
                                    if ($propId == $skuId)
                                    {
                                        $currentOffersList[] = $propValue['NAME'];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                $offerPrice = $offer['ITEM_PRICES'][$offer['ITEM_PRICE_SELECTED']];
                ?>
                <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
				<meta itemprop="sku" content="<?=htmlspecialcharsbx(implode('/', $currentOffersList))?>" />
				<meta itemprop="price" content="<?=$offerPrice['RATIO_PRICE']?>" />
				<meta itemprop="priceCurrency" content="<?=$offerPrice['CURRENCY']?>" />
				<link itemprop="availability" href="http://schema.org/<?=($offer['CAN_BUY'] ? 'InStock' : 'OutOfStock')?>" />
			</span>
                <?php
            }

            unset($offerPrice, $currentOffersList);
        }
        else
        {
            ?>
            <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
			<meta itemprop="price" content="<?=$price['RATIO_PRICE']?>" />
			<meta itemprop="priceCurrency" content="<?=$price['CURRENCY']?>" />
			<link itemprop="availability" href="http://schema.org/<?=($actualItem['CAN_BUY'] ? 'InStock' : 'OutOfStock')?>" />
		</span>
            <?php
        }
        ?>
    </div>

<?php
if ($haveOffers)
{
    $offerIds = array();
    $offerCodes = array();

    $useRatio = $arParams['USE_RATIO_IN_RANGES'] === 'Y';

    foreach ($arResult['JS_OFFERS'] as $ind => &$jsOffer)
    {
        $offerIds[] = (int)$jsOffer['ID'];
        $offerCodes[] = $jsOffer['CODE'];

        $fullOffer = $arResult['OFFERS'][$ind];
        $measureName = $fullOffer['ITEM_MEASURE']['TITLE'];

        $strAllProps = '';
        $strMainProps = '';
        $strPriceRangesRatio = '';
        $strPriceRanges = '';

        if ($arResult['SHOW_OFFERS_PROPS'])
        {
            if (!empty($jsOffer['DISPLAY_PROPERTIES']))
            {
                foreach ($jsOffer['DISPLAY_PROPERTIES'] as $property)
                {
                    $current = '<dt>'.$property['NAME'].'</dt><dd>'.(
                        is_array($property['VALUE'])
                            ? implode(' / ', $property['VALUE'])
                            : $property['VALUE']
                        ).'</dd>';
                    $strAllProps .= $current;

                    if (isset($arParams['MAIN_BLOCK_OFFERS_PROPERTY_CODE'][$property['CODE']]))
                    {
                        $strMainProps .= $current;
                    }
                }

                unset($current);
            }
        }

        if ($arParams['USE_PRICE_COUNT'] && count($jsOffer['ITEM_QUANTITY_RANGES']) > 1)
        {
            $strPriceRangesRatio = '('.Loc::getMessage(
                    'CT_BCE_CATALOG_RATIO_PRICE',
                    array(
                        '#RATIO#' => ($useRatio
                                ? $fullOffer['ITEM_MEASURE_RATIOS'][$fullOffer['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']
                                : '1'
                            ).' '.$measureName
                    )
                ).')';

            foreach ($jsOffer['ITEM_QUANTITY_RANGES'] as $range)
            {
                if ($range['HASH'] !== 'ZERO-INF')
                {
                    $itemPrice = false;

                    foreach ($jsOffer['ITEM_PRICES'] as $itemPrice)
                    {
                        if ($itemPrice['QUANTITY_HASH'] === $range['HASH'])
                        {
                            break;
                        }
                    }

                    if ($itemPrice)
                    {
                        $strPriceRanges .= '<dt>'.Loc::getMessage(
                                'CT_BCE_CATALOG_RANGE_FROM',
                                array('#FROM#' => $range['SORT_FROM'].' '.$measureName)
                            ).' ';

                        if (is_infinite($range['SORT_TO']))
                        {
                            $strPriceRanges .= Loc::getMessage('CT_BCE_CATALOG_RANGE_MORE');
                        }
                        else
                        {
                            $strPriceRanges .= Loc::getMessage(
                                'CT_BCE_CATALOG_RANGE_TO',
                                array('#TO#' => $range['SORT_TO'].' '.$measureName)
                            );
                        }

                        $strPriceRanges .= '</dt><dd>'.($useRatio ? $itemPrice['PRINT_RATIO_PRICE'] : $itemPrice['PRINT_PRICE']).'</dd>';
                    }
                }
            }

            unset($range, $itemPrice);
        }

        $jsOffer['DISPLAY_PROPERTIES'] = $strAllProps;
        $jsOffer['DISPLAY_PROPERTIES_MAIN_BLOCK'] = $strMainProps;
        $jsOffer['PRICE_RANGES_RATIO_HTML'] = $strPriceRangesRatio;
        $jsOffer['PRICE_RANGES_HTML'] = $strPriceRanges;
    }

    $templateData['OFFER_IDS'] = $offerIds;
    $templateData['OFFER_CODES'] = $offerCodes;
    unset($jsOffer, $strAllProps, $strMainProps, $strPriceRanges, $strPriceRangesRatio, $useRatio);

    $jsParams = array(
        'CONFIG' => array(
            'USE_CATALOG' => $arResult['CATALOG'],
            'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
            'SHOW_PRICE' => true,
            'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
            'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
            'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
            'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
            'SHOW_SKU_PROPS' => $arResult['SHOW_OFFERS_PROPS'],
            'OFFER_GROUP' => $arResult['OFFER_GROUP'],
            'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
            'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
            'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
            'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
            'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
            'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
            'USE_STICKERS' => true,
            'USE_SUBSCRIBE' => $showSubscribe,
            'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
            'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
            'ALT' => $alt,
            'TITLE' => $title,
            'MAGNIFIER_ZOOM_PERCENT' => 200,
            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
            'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                : null,
            'SHOW_SKU_DESCRIPTION' => $arParams['SHOW_SKU_DESCRIPTION'],
            'DISPLAY_PREVIEW_TEXT_MODE' => $arParams['DISPLAY_PREVIEW_TEXT_MODE']
        ),
        'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
        'VISUAL' => $itemIds,
        'DEFAULT_PICTURE' => array(
            'PREVIEW_PICTURE' => $arResult['DEFAULT_PICTURE'],
            'DETAIL_PICTURE' => $arResult['DEFAULT_PICTURE']
        ),
        'PRODUCT' => array(
            'ID' => $arResult['ID'],
            'ACTIVE' => $arResult['ACTIVE'],
            'NAME' => $arResult['~NAME'],
            'CATEGORY' => $arResult['CATEGORY_PATH'],
            'DETAIL_TEXT' => $arResult['DETAIL_TEXT'],
            'DETAIL_TEXT_TYPE' => $arResult['DETAIL_TEXT_TYPE'],
            'PREVIEW_TEXT' => $arResult['PREVIEW_TEXT'],
            'PREVIEW_TEXT_TYPE' => $arResult['PREVIEW_TEXT_TYPE']
        ),
        'BASKET' => array(
            'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
            'BASKET_URL' => $arParams['BASKET_URL'],
            'SKU_PROPS' => $arResult['OFFERS_PROP_CODES'],
            'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
            'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
        ),
        'OFFERS' => $arResult['JS_OFFERS'],
        'OFFER_SELECTED' => $arResult['OFFERS_SELECTED'],
        'TREE_PROPS' => $skuProps
    );
}
else
{
    $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);

    if ($arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !$emptyProductProperties)
    {
        ?>
        <div id="<?=$itemIds['BASKET_PROP_DIV']?>" style="display: none;">
            <?php
            if (!empty($arResult['PRODUCT_PROPERTIES_FILL']))
            {
                foreach ($arResult['PRODUCT_PROPERTIES_FILL'] as $propId => $propInfo)
                {
                    ?>
                    <input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propId?>]" value="<?=htmlspecialcharsbx($propInfo['ID'])?>">
                    <?php
                    unset($arResult['PRODUCT_PROPERTIES'][$propId]);
                }
            }

            $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);

            if (!$emptyProductProperties)
            {
                ?>
                <table>
                    <?php
                    foreach ($arResult['PRODUCT_PROPERTIES'] as $propId => $propInfo)
                    {
                        ?>
                        <tr>
                            <td><?=$arResult['PROPERTIES'][$propId]['NAME']?></td>
                            <td>
                                <?php
                                if (
                                    $arResult['PROPERTIES'][$propId]['PROPERTY_TYPE'] === 'L'
                                    && $arResult['PROPERTIES'][$propId]['LIST_TYPE'] === 'C'
                                )
                                {
                                    foreach ($propInfo['VALUES'] as $valueId => $value)
                                    {
                                        ?>
                                        <label>
                                            <input
                                                    type="radio"
                                                    name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propId?>]"
                                                    value="<?=$valueId?>"
                                                <?=($valueId == $propInfo['SELECTED'] ? 'checked' : '')?>
                                            >
                                            <?=$value?>
                                        </label>
                                        <br>
                                        <?php
                                    }
                                }
                                else
                                {
                                    ?>
                                    <select name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propId?>]">
                                        <?php
                                        foreach ($propInfo['VALUES'] as $valueId => $value)
                                        {
                                            ?>
                                            <option value="<?=$valueId?>" <?=($valueId == $propInfo['SELECTED'] ? 'selected' : '')?>>
                                                <?=$value?>
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
                <?php
            }
            ?>
        </div>
        <?php
    }

    $jsParams = array(
        'CONFIG' => array(
            'USE_CATALOG' => $arResult['CATALOG'],
            'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
            'SHOW_PRICE' => !empty($arResult['ITEM_PRICES']),
            'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
            'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
            'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
            'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
            'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
            'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
            'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
            'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
            'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
            'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
            'USE_STICKERS' => true,
            'USE_SUBSCRIBE' => $showSubscribe,
            'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
            'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
            'ALT' => $alt,
            'TITLE' => $title,
            'MAGNIFIER_ZOOM_PERCENT' => 200,
            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
            'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                : null
        ),
        'VISUAL' => $itemIds,
        'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
        'PRODUCT' => array(
            'ID' => $arResult['ID'],
            'ACTIVE' => $arResult['ACTIVE'],
            'PICT' => reset($arResult['MORE_PHOTO']),
            'NAME' => $arResult['~NAME'],
            'SUBSCRIPTION' => true,
            'ITEM_PRICE_MODE' => $arResult['ITEM_PRICE_MODE'],
            'ITEM_PRICES' => $arResult['ITEM_PRICES'],
            'ITEM_PRICE_SELECTED' => $arResult['ITEM_PRICE_SELECTED'],
            'ITEM_QUANTITY_RANGES' => $arResult['ITEM_QUANTITY_RANGES'],
            'ITEM_QUANTITY_RANGE_SELECTED' => $arResult['ITEM_QUANTITY_RANGE_SELECTED'],
            'ITEM_MEASURE_RATIOS' => $arResult['ITEM_MEASURE_RATIOS'],
            'ITEM_MEASURE_RATIO_SELECTED' => $arResult['ITEM_MEASURE_RATIO_SELECTED'],
            'SLIDER_COUNT' => $arResult['MORE_PHOTO_COUNT'],
            'SLIDER' => $arResult['MORE_PHOTO'],
            'CAN_BUY' => $arResult['CAN_BUY'],
            'CHECK_QUANTITY' => $arResult['CHECK_QUANTITY'],
            'QUANTITY_FLOAT' => is_float($arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']),
            'MAX_QUANTITY' => $arResult['PRODUCT']['QUANTITY'],
            'STEP_QUANTITY' => $arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'],
            'CATEGORY' => $arResult['CATEGORY_PATH']
        ),
        'BASKET' => array(
            'ADD_PROPS' => $arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y',
            'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
            'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
            'EMPTY_PROPS' => $emptyProductProperties,
            'BASKET_URL' => $arParams['BASKET_URL'],
            'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
            'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
        )
    );

    unset($emptyProductProperties);
}

if ($arParams['DISPLAY_COMPARE'])
{
    $jsParams['COMPARE'] = array(
        'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
        'COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
        'COMPARE_PATH' => $arParams['COMPARE_PATH']
    );
}

$jsParams["IS_FACEBOOK_CONVERSION_CUSTOMIZE_PRODUCT_EVENT_ENABLED"] =
    $arResult["IS_FACEBOOK_CONVERSION_CUSTOMIZE_PRODUCT_EVENT_ENABLED"];
?>

    <style>
        .basket-card-quantity {
            display: inline-flex;
            align-items: center;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            margin-right: 15px;
        }

        .basket-card-quantity .qty-btn {
            width: 25px;
            height: 40px;
            border: none;
            background: #f77504;
            color: #ffffff;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            transition: .2s;
        }

        .basket-card-quantity .qty-btn:hover {
            background: #F77504;
            color: #fff;
        }

        .basket-card-quantity .qty-input {
            width: 55px;
            height: 25px;
            border: none;
            text-align: center;
            font-size: 16px;
            outline: none;
            background: #fff;
        }

        .product-item-detail-buy-button,
        #<?=$itemIds['BUY_LINK']?> {
            background: #F77504 !important;
            border-color: #F77504 !important;
            color: #fff !important;
            transition: .2s ease;
        }

        .product-item-detail-buy-button.in-basket,
        #<?=$itemIds['BUY_LINK']?>.in-basket {
             background: #28a745 !important;
             border-color: #28a745 !important;
             color: #fff !important;
         }

        .catalog-basket-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: #28a745;
            color: #fff;
            padding: 14px 18px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,.15);
            font-size: 14px;
            font-weight: 600;
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
            transition: .25s ease;
        }

        .catalog-basket-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .catalog_detail_r .btns {
            gap: 1rem 1vw;
            display: block;
        }
    </style>

    <script>
        BX.message({
            ECONOMY_INFO_MESSAGE: '<?=GetMessageJS('CT_BCE_CATALOG_ECONOMY_INFO2')?>',
            TITLE_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_ERROR')?>',
            TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_BASKET_PROPS')?>',
            BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_BASKET_UNKNOWN_ERROR')?>',
            BTN_SEND_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_SEND_PROPS')?>',
            BTN_MESSAGE_DETAIL_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
            BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE')?>',
            BTN_MESSAGE_DETAIL_CLOSE_POPUP: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
            TITLE_SUCCESSFUL: '<?=GetMessageJS('CT_BCE_CATALOG_ADD_TO_BASKET_OK')?>',
            COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_OK')?>',
            COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
            COMPARE_TITLE: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_TITLE')?>',
            BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
            PRODUCT_GIFT_LABEL: '<?=GetMessageJS('CT_BCE_CATALOG_PRODUCT_GIFT_LABEL')?>',
            PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_PRICE_TOTAL_PREFIX')?>',
            RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
            RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>',
            SITE_ID: '<?=CUtil::JSEscape($component->getSiteId())?>'
        });

        var <?=$obName?> = new JCCatalogElement(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);

        document.addEventListener('DOMContentLoaded', function () {
            var catalogElement = <?=$obName?>;
            var basketMap = <?=CUtil::PhpToJSObject($basketItemsMap, false, true)?> || {};
            var baseProductId = <?=(int)$arResult['ID']?>;
            var cartUrl = '/cart/';

            var addBtn = document.getElementById('<?=$itemIds['ADD_BASKET_LINK']?>');
            var buyBtn = document.getElementById('<?=$itemIds['BUY_LINK']?>');
            var qtyInput = document.getElementById('<?=$itemIds['QUANTITY_ID']?>');
            var qtyMinusBtn = document.getElementById('<?=$itemIds['QUANTITY_DOWN_ID']?>');
            var qtyPlusBtn = document.getElementById('<?=$itemIds['QUANTITY_UP_ID']?>');
            var treeNode = document.getElementById('<?=$itemIds['TREE_ID']?>');

            var pendingAddProductId = null;
            var pendingAddQty = 1;
            var quantityStep = parseFloat('<?=str_replace(',', '.', (string)($measureRatio ?: 1))?>') || 1;
            var isInternalQtyUpdate = false;

            function debugBasket(eventName, payload) {
                if (typeof console === 'undefined' || typeof console.log !== 'function') {
                    return;
                }

                console.log('[catalog-basket-debug] ' + eventName, payload || {});
            }

            function normalizeQty(val) {
                val = String(val || '').replace(',', '.').replace(/[^\d.]/g, '');
                var num = parseFloat(val);

                if (isNaN(num) || num <= 0) {
                    num = quantityStep > 0 ? quantityStep : 1;
                }

                if (quantityStep > 0) {
                    num = Math.round(num / quantityStep) * quantityStep;
                }

                num = Math.max(quantityStep > 0 ? quantityStep : 1, num);

                return parseFloat(num.toFixed(3));
            }

            function getQty() {
                if (!qtyInput) {
                    return quantityStep > 0 ? quantityStep : 1;
                }

                return normalizeQty(qtyInput.value);
            }

            function syncBitrixQuantity(normalized) {
                if (!catalogElement) {
                    return;
                }

                catalogElement.quantity = normalized;

                if (catalogElement.obQuantity) {
                    catalogElement.obQuantity.value = normalized;
                    catalogElement.obQuantity.setAttribute('value', normalized);
                }

                if (catalogElement.basketData) {
                    catalogElement.basketData.lastQuantity = normalized;
                }

                if (catalogElement.product && typeof catalogElement.product === 'object') {
                    catalogElement.product.quantity = normalized;
                }

                if (catalogElement.currentProduct && typeof catalogElement.currentProduct === 'object') {
                    catalogElement.currentProduct.quantity = normalized;
                }

                if (
                    typeof catalogElement.offerNum !== 'undefined' &&
                    catalogElement.offers &&
                    catalogElement.offers[catalogElement.offerNum]
                ) {
                    catalogElement.offers[catalogElement.offerNum].quantity = normalized;
                }
            }

            function setQty(val) {
                if (!qtyInput) {
                    return;
                }

                var normalized = normalizeQty(val);

                if (isInternalQtyUpdate && normalizeQty(qtyInput.value) === normalized) {
                    return;
                }

                isInternalQtyUpdate = true;

                qtyInput.value = normalized;
                qtyInput.setAttribute('value', normalized);
                qtyInput.setAttribute('data-value', normalized);

                syncBitrixQuantity(normalized);

                isInternalQtyUpdate = false;

                debugBasket('setQty', {
                    normalized: normalized,
                    currentProductId: getCurrentProductId()
                });
            }

            function showToast(message) {
                var toast = document.querySelector('.catalog-basket-toast');

                if (!toast) {
                    toast = document.createElement('div');
                    toast.className = 'catalog-basket-toast';
                    document.body.appendChild(toast);
                }

                toast.textContent = message;
                toast.classList.add('show');

                clearTimeout(toast.hideTimer);
                toast.hideTimer = setTimeout(function () {
                    toast.classList.remove('show');
                }, 2500);
            }

            function getCurrentProductId() {
                if (
                    catalogElement &&
                    typeof catalogElement.offerNum !== 'undefined' &&
                    catalogElement.offers &&
                    catalogElement.offers.length &&
                    catalogElement.offers[catalogElement.offerNum]
                ) {
                    return parseInt(catalogElement.offers[catalogElement.offerNum].ID, 10);
                }

                return baseProductId;
            }

            function isCurrentProductInBasket() {
                var productId = getCurrentProductId();
                return !!basketMap[productId];
            }

            function setButtonsState(isAdded) {
                var buttons = [addBtn, buyBtn].filter(Boolean);

                buttons.forEach(function (btn) {
                    var span = btn.querySelector('span');

                    if (isAdded) {
                        btn.classList.add('in-basket');
                        btn.setAttribute('data-in-basket', 'Y');
                        if (span) {
                            span.textContent = 'ДОБАВЛЕНО В КОРЗИНУ';
                        }
                    } else {
                        btn.classList.remove('in-basket');
                        btn.setAttribute('data-in-basket', 'N');
                        if (span) {
                            span.textContent = 'В КОРЗИНУ';
                        }
                    }
                });
            }

            function updateButtonsByCurrentProduct() {
                var productId = getCurrentProductId();
                var inBasket = !!basketMap[productId];

                setButtonsState(inBasket);

                if (inBasket && basketMap[productId]) {
                    setQty(basketMap[productId]);
                } else {
                    setQty(getQty());
                }

                debugBasket('updateButtonsByCurrentProduct', {
                    productId: productId,
                    inBasket: inBasket,
                    basketQty: basketMap[productId] || 0
                });
            }

            function syncQtyBeforeBasketAction() {
                var qty = getQty();

                pendingAddQty = qty;
                pendingAddProductId = getCurrentProductId();

                setQty(qty);
                syncBitrixQuantity(qty);

                if (catalogElement && typeof catalogElement.setQuantity === 'function') {
                    catalogElement.setQuantity(qty);
                }

                debugBasket('syncQtyBeforeBasketAction', {
                    pendingAddProductId: pendingAddProductId,
                    pendingAddQty: pendingAddQty
                });
            }

            function handleBasketButtonClick(e) {
                var inBasket = isCurrentProductInBasket();

                if (inBasket) {
                    e.preventDefault();
                    window.location.href = cartUrl;
                    return false;
                }

                syncQtyBeforeBasketAction();
                return true;
            }

            if (qtyInput) {
                qtyInput.addEventListener('input', function () {
                    this.value = this.value.replace(/[^\d.,]/g, '');
                });

                qtyInput.addEventListener('blur', function () {
                    setQty(this.value);
                });

                qtyInput.addEventListener('change', function () {
                    if (isInternalQtyUpdate) {
                        return;
                    }

                    setQty(this.value);
                });
            }

            if (qtyMinusBtn) {
                qtyMinusBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    setQty(getQty() - quantityStep);
                }, true);
            }

            if (qtyPlusBtn) {
                qtyPlusBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    setQty(getQty() + quantityStep);
                }, true);
            }

            if (addBtn) {
                addBtn.addEventListener('click', handleBasketButtonClick, true);
            }

            if (buyBtn) {
                buyBtn.addEventListener('click', handleBasketButtonClick, true);
            }

            if (typeof BX !== 'undefined' && BX.addCustomEvent) {
                BX.addCustomEvent('OnBasketChange', function () {
                    if (pendingAddProductId) {
                        basketMap[pendingAddProductId] = (parseFloat(basketMap[pendingAddProductId]) || 0) + pendingAddQty;
                        updateButtonsByCurrentProduct();
                        showToast('Товар добавлен в корзину');
                        debugBasket('OnBasketChange:added', {
                            productId: pendingAddProductId,
                            qty: basketMap[pendingAddProductId]
                        });
                        pendingAddProductId = null;
                    } else {
                        updateButtonsByCurrentProduct();
                        debugBasket('OnBasketChange:refresh', {
                            currentProductId: getCurrentProductId(),
                            basketQty: basketMap[getCurrentProductId()] || 0
                        });
                    }
                });

                BX.addCustomEvent('onCatalogElementChangeOffer', function () {
                    setTimeout(function () {
                        updateButtonsByCurrentProduct();
                    }, 0);
                });
            }

            if (treeNode) {
                treeNode.addEventListener('click', function () {
                    setTimeout(function () {
                        updateButtonsByCurrentProduct();
                    }, 200);

                    setTimeout(function () {
                        updateButtonsByCurrentProduct();
                    }, 500);
                });
            }

            setQty(getQty());

            setTimeout(function () {
                updateButtonsByCurrentProduct();
            }, 100);

            setTimeout(function () {
                updateButtonsByCurrentProduct();
            }, 500);

            const priceBlock = document.querySelector("#<?=$itemIds['PRICE_ID']?>");
            if (priceBlock) {
                const basePriceRaw = priceBlock.textContent.replace(/\s/g, '').replace(/[^\d.,]/g, '').replace(",", ".");
                const basePrice = parseFloat(basePriceRaw) || 0;

                const checkboxes = document.querySelectorAll(".catalog_add_order_item input.check");

                function updatePrice() {
                    let addPrice = 0;

                    checkboxes.forEach(function (checkbox) {
                        if (checkbox.checked) {
                            const item = checkbox.closest(".catalog_add_order_item");
                            if (!item) return;

                            const itemPriceNode = item.querySelector(".price");
                            if (!itemPriceNode) return;

                            const priceText = itemPriceNode.textContent.replace(/\s/g, '').replace(/[^\d.,]/g, '').replace(",", ".");
                            const price = parseFloat(priceText) || 0;
                            addPrice += price;
                        }
                    });

                    const total = basePrice + addPrice;
                    priceBlock.textContent = total.toFixed(2) + " руб.";
                }

                checkboxes.forEach(function (checkbox) {
                    checkbox.addEventListener("change", updatePrice);
                });
            }
        });
    </script>

<?php
unset($actualItem, $itemIds, $jsParams);
?>
