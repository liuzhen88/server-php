<?php
/**
 * 检查手机访问方法集

 */
function check_wap()
{
    if (isset($_SERVER['HTTP_VIA'])) return true;
    if (isset($_SERVER['HTTP_X_NOKIA_CONNECTION_MODE'])) return true;
    if (isset($_SERVER['HTTP_X_UP_CALLING_LINE_ID'])) return true;
    if (strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML") > 0) {
        // Check whether the browser/gateway says it accepts WML.
        $br = "WML";
    } else {
        $browser = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
        if (empty($browser)) return true;
        $clientkeywords = array(
            'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-'
        , 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu',
            'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini',
            'operamobi', 'opera mobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'
        );
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", $browser) && strpos($browser, 'ipad') === false) {
            $br = "WML";
        } else {
            $br = "HTML";
        }
    }
    if ($br == "WML") {
        return TRUE;
    } else {
        return FALSE;
    }
}

if (check_wap()) {
    if ($_GET['act'] == 'index') {
        Header("Location:" . WAP_SITE_URL);
    } elseif ($_GET['act'] == 'goods') {
		//适配商品页
        $wapUrl = WAP_SITE_URL . '/tmpl/product_detail.html?goods_id=' . $_GET['goods_id'];
        Header("Location:" . $wapUrl);
    } else {
		//在没有适配更多控制器的时候只能统一跳转到wap的首页
		Header("Location:" . WAP_SITE_URL);
	}
    exit();
}