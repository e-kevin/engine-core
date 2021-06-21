<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\enums;

/**
 * 多语言枚举
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class I18nEnum extends Enums
{
    
    /**
     * {@inheritdoc}
     */
    public static function list($showUnlimited = false)
    {
        return [
            'af-ZA' => 'Afrikaans',
            'ar-AR' => '‏العربية‏',
            'az-AZ' => 'Azərbaycan dili',
            'be-BY' => 'Беларуская',
            'bg-BG' => 'Български',
            'bn-IN' => 'বাংলা',
            'bs-BA' => 'Bosanski',
            'ca-ES' => 'Català',
            'cs-CZ' => 'Čeština',
            'cy-GB' => 'Cymraeg',
            'da-DK' => 'Dansk',
            'de-DE' => 'Deutsch',
            'el-GR' => 'Ελληνικά',
            'en-GB' => 'English (UK)',
            'en-PI' => 'English (Pirate)',
            'en-UD' => 'English (Upside Down)',
            'en-US' => 'English (US)',
            'eo-EO' => 'Esperanto',
            'es-ES' => 'Español (España)',
            'es-LA' => 'Español',
            'et-EE' => 'Eesti',
            'eu-ES' => 'Euskara',
            'fa-IR' => '‏فارسی‏',
            'fb-LT' => 'Leet Speak',
            'fi-FI' => 'Suomi',
            'fo-FO' => 'Føroyskt',
            'fr-CA' => 'Français (Canada)',
            'fr-FR' => 'Français (France)',
            'fy-NL' => 'Frysk',
            'ga-IE' => 'Gaeilge',
            'gl-ES' => 'Galego',
            'he-IL' => '‏עברית‏',
            'hi-IN' => 'हिन्दी',
            'hr-HR' => 'Hrvatski',
            'hu-HU' => 'Magyar',
            'hy-AM' => 'Հայերեն',
            'id-ID' => 'Bahasa Indonesia',
            'is-IS' => 'Íslenska',
            'it-IT' => 'Italiano',
            'ja-JP' => '日本語',
            'ka-GE' => 'ქართული',
            'km-KH' => 'ភាសាខ្មែរ',
            'ko-KR' => '한국어',
            'ku-TR' => 'Kurdî',
            'la-VA' => 'lingua latina',
            'lt-LT' => 'Lietuvių',
            'lv-LV' => 'Latviešu',
            'mk-MK' => 'Македонски',
            'ml-IN' => 'മലയാളം',
            'ms-MY' => 'Bahasa Melayu',
            'nb-NO' => 'Norsk (bokmål)',
            'ne-NP' => 'नेपाली',
            'nl-NL' => 'Nederlands',
            'nn-NO' => 'Norsk (nynorsk)',
            'pa-IN' => 'ਪੰਜਾਬੀ',
            'pl-PL' => 'Polski',
            'ps-AF' => '‏پښتو‏',
            'pt-BR' => 'Português (Brasil)',
            'pt-PT' => 'Português (Portugal)',
            'ro-RO' => 'Română',
            'ru-RU' => 'Русский',
            'sk-SK' => 'Slovenčina',
            'sl-SI' => 'Slovenščina',
            'sq-AL' => 'Shqip',
            'sr-RS' => 'Српски',
            'sv-SE' => 'Svenska',
            'sw-KE' => 'Kiswahili',
            'ta-IN' => 'தமிழ்',
            'te-IN' => 'తెలుగు',
            'th-TH' => 'ภาษาไทย',
            'tl-PH' => 'Filipino',
            'tr-TR' => 'Türkçe',
            'uk-UA' => 'Українська',
            'vi-VN' => 'Tiếng Việt',
            'xx-XX' => 'Fejlesztő',
            'zh-CN' => '中文(简体)',
            'zh-HK' => '中文(香港)',
            'zh-TW' => '中文(台灣)'
        ];
    }
    
}