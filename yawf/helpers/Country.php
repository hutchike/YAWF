<?
// Copyright (c) 2010 Guanoo, Inc.
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation; either version 3
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.

/**
 * Return the name of a country in a particular language (default is "en").
 * Feel free to add country names in other languages as you need them for
 * your application.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Country extends YAWF
{
    /**
     * Return the name associated with a country code, in a language
     *
     * @param String $code the country code to lookup
     * @param String $language the language to use (default is "en")
     * @return String the name of the country in the language
     */
    public static function name($code, $language = 'en')
    {
        return array_key(self::$codes[$language], strtoupper($code));
    }

// Code list from http://www.iso.org/iso/english_country_names_and_code_elements

private static $codes = array('en' => array(
'AF' => 'Afghanistan',
'AX' => 'Åland Islands',
'AL' => 'Albania',
'DZ' => 'Algeria',
'AS' => 'American Samoa',
'AD' => 'Andorra',
'AO' => 'Angola',
'AI' => 'Anguilla',
'AQ' => 'Antarctica',
'AG' => 'Antigua And Barbuda',
'AR' => 'Argentina',
'AM' => 'Armenia',
'AW' => 'Aruba',
'AU' => 'Australia',
'AT' => 'Austria',
'AZ' => 'Azerbaijan',
'BS' => 'Bahamas',
'BH' => 'Bahrain',
'BD' => 'Bangladesh',
'BB' => 'Barbados',
'BY' => 'Belarus',
'BE' => 'Belgium',
'BZ' => 'Belize',
'BJ' => 'Benin',
'BM' => 'Bermuda',
'BT' => 'Bhutan',
'BO' => 'Bolivia, Plurinational State Of',
'BA' => 'Bosnia And Herzegovina',
'BW' => 'Botswana',
'BV' => 'Bouvet Island',
'BR' => 'Brazil',
'IO' => 'British Indian Ocean Territory',
'BN' => 'Brunei Darussalam',
'BG' => 'Bulgaria',
'BF' => 'Burkina Faso',
'BI' => 'Burundi',
'KH' => 'Cambodia',
'CM' => 'Cameroon',
'CA' => 'Canada',
'CV' => 'Cape Verde',
'KY' => 'Cayman Islands',
'CF' => 'Central African Republic',
'TD' => 'Chad',
'CL' => 'Chile',
'CN' => 'China',
'CX' => 'Christmas Island',
'CC' => 'Cocos (keeling) Islands',
'CO' => 'Colombia',
'KM' => 'Comoros',
'CG' => 'Congo',
'CD' => 'Congo, The Democratic Republic Of The',
'CK' => 'Cook Islands',
'CR' => 'Costa Rica',
'CI' => 'CÔte D\'ivoire (Ivory Coast)',
'HR' => 'Croatia',
'CU' => 'Cuba',
'CY' => 'Cyprus',
'CZ' => 'Czech Republic',
'DK' => 'Denmark',
'DJ' => 'Djibouti',
'DM' => 'Dominica',
'DO' => 'Dominican Republic',
'EC' => 'Ecuador',
'EG' => 'Egypt',
'SV' => 'El Salvador',
'GQ' => 'Equatorial Guinea',
'ER' => 'Eritrea',
'EE' => 'Estonia',
'ET' => 'Ethiopia',
'FK' => 'Falkland Islands (malvinas)',
'FO' => 'Faroe Islands',
'FJ' => 'Fiji',
'FI' => 'Finland',
'FR' => 'France',
'GF' => 'French Guiana',
'PF' => 'French Polynesia',
'TF' => 'French Southern Territories',
'GA' => 'Gabon',
'GM' => 'Gambia',
'GE' => 'Georgia',
'DE' => 'Germany',
'GH' => 'Ghana',
'GI' => 'Gibraltar',
'GR' => 'Greece',
'GL' => 'Greenland',
'GD' => 'Grenada',
'GP' => 'Guadeloupe',
'GU' => 'Guam',
'GT' => 'Guatemala',
'GG' => 'Guernsey',
'GN' => 'Guinea',
'GW' => 'Guinea-bissau',
'GY' => 'Guyana',
'HT' => 'Haiti',
'HM' => 'Heard Island And Mcdonald Islands',
'VA' => 'Holy See (vatican City State)',
'HN' => 'Honduras',
'HK' => 'Hong Kong',
'HU' => 'Hungary',
'IS' => 'Iceland',
'IN' => 'India',
'ID' => 'Indonesia',
'IR' => 'Iran',
'IQ' => 'Iraq',
'IE' => 'Ireland',
'IM' => 'Isle Of Man',
'IL' => 'Israel',
'IT' => 'Italy',
'JM' => 'Jamaica',
'JP' => 'Japan',
'JE' => 'Jersey',
'JO' => 'Jordan',
'KZ' => 'Kazakhstan',
'KE' => 'Kenya',
'KI' => 'Kiribati',
'KP' => 'North Korea',
'KR' => 'South Korea',
'KW' => 'Kuwait',
'KG' => 'Kyrgyzstan',
'LA' => 'Laos',
'LV' => 'Latvia',
'LB' => 'Lebanon',
'LS' => 'Lesotho',
'LR' => 'Liberia',
'LY' => 'Libya',
'LI' => 'Liechtenstein',
'LT' => 'Lithuania',
'LU' => 'Luxembourg',
'MO' => 'Macao',
'MK' => 'Macedonia',
'MG' => 'Madagascar',
'MW' => 'Malawi',
'MY' => 'Malaysia',
'MV' => 'Maldives',
'ML' => 'Mali',
'MT' => 'Malta',
'MH' => 'Marshall Islands',
'MQ' => 'Martinique',
'MR' => 'Mauritania',
'MU' => 'Mauritius',
'YT' => 'Mayotte',
'MX' => 'Mexico',
'FM' => 'Micronesia',
'MD' => 'Moldova',
'MC' => 'Monaco',
'MN' => 'Mongolia',
'ME' => 'Montenegro',
'MS' => 'Montserrat',
'MA' => 'Morocco',
'MZ' => 'Mozambique',
'MM' => 'Myanmar',
'NA' => 'Namibia',
'NR' => 'Nauru',
'NP' => 'Nepal',
'NL' => 'Netherlands',
'AN' => 'Netherlands Antilles',
'NC' => 'New Caledonia',
'NZ' => 'New Zealand',
'NI' => 'Nicaragua',
'NE' => 'Niger',
'NG' => 'Nigeria',
'NU' => 'Niue',
'NF' => 'Norfolk Island',
'MP' => 'Northern Mariana Islands',
'NO' => 'Norway',
'OM' => 'Oman',
'PK' => 'Pakistan',
'PW' => 'Palau',
'PS' => 'Palestinian Territory',
'PA' => 'Panama',
'PG' => 'Papua New Guinea',
'PY' => 'Paraguay',
'PE' => 'Peru',
'PH' => 'Philippines',
'PN' => 'Pitcairn',
'PL' => 'Poland',
'PT' => 'Portugal',
'PR' => 'Puerto Rico',
'QA' => 'Qatar',
'RE' => 'Réunion',
'RO' => 'Romania',
'RU' => 'Russian Federation',
'RW' => 'Rwanda',
'BL' => 'Saint Barthélemy',
'SH' => 'Saint Helena, Ascension And Tristan Da Cunha',
'KN' => 'Saint Kitts And Nevis',
'LC' => 'Saint Lucia',
'MF' => 'Saint Martin',
'PM' => 'Saint Pierre And Miquelon',
'VC' => 'Saint Vincent And The Grenadines',
'WS' => 'Samoa',
'SM' => 'San Marino',
'ST' => 'Sao Tome And Principe',
'SA' => 'Saudi Arabia',
'SN' => 'Senegal',
'RS' => 'Serbia',
'SC' => 'Seychelles',
'SL' => 'Sierra Leone',
'SG' => 'Singapore',
'SK' => 'Slovakia',
'SI' => 'Slovenia',
'SB' => 'Solomon Islands',
'SO' => 'Somalia',
'ZA' => 'South Africa',
'GS' => 'South Georgia And The South Sandwich Islands',
'ES' => 'Spain',
'LK' => 'Sri Lanka',
'SD' => 'Sudan',
'SR' => 'Suriname',
'SJ' => 'Svalbard And Jan Mayen',
'SZ' => 'Swaziland',
'SE' => 'Sweden',
'CH' => 'Switzerland',
'SY' => 'Syrian Arab Republic',
'TW' => 'Taiwan, Province Of China',
'TJ' => 'Tajikistan',
'TZ' => 'Tanzania, United Republic Of',
'TH' => 'Thailand',
'TL' => 'Timor-leste',
'TG' => 'Togo',
'TK' => 'Tokelau',
'TO' => 'Tonga',
'TT' => 'Trinidad And Tobago',
'TN' => 'Tunisia',
'TR' => 'Turkey',
'TM' => 'Turkmenistan',
'TC' => 'Turks And Caicos Islands',
'TV' => 'Tuvalu',
'UG' => 'Uganda',
'UA' => 'Ukraine',
'AE' => 'United Arab Emirates',
'GB' => 'United Kingdom',
'US' => 'United States',
'UM' => 'United States Minor Outlying Islands',
'UK' => 'United Kingdom', // added by Kevin Hutchinson
'UY' => 'Uruguay',
'UZ' => 'Uzbekistan',
'VU' => 'Vanuatu',
'VE' => 'Venezuela, Bolivarian Republic Of',
'VN' => 'Viet Nam',
'VG' => 'Virgin Islands, British',
'VI' => 'Virgin Islands, U.s.',
'WF' => 'Wallis And Futuna',
'EH' => 'Western Sahara',
'YE' => 'Yemen',
'ZM' => 'Zambia',
'ZW' => 'Zimbabwe',
),
'es' => array(
'AF' => 'Afganistán',
'AL' => 'Albania',
'DZ' => 'Argelia',
'AS' => 'Samoa Americana',
'AD' => 'Andorra',
'AO' => 'Angola',
'AI' => 'Anguilla',
'AQ' => 'Antártida',
'AG' => 'Antigua y Barbuda',
'AR' => 'Argentina',
'AM' => 'Armenia',
'AW' => 'Aruba',
'SH' => 'Isla Ascension',
'AU' => 'Australia',
'AT' => 'Austria',
'AZ' => 'Azerbaiyán',
'BS' => 'Bahamas',
'BH' => 'Bahrein',
'BD' => 'Bangladesh',
'BB' => 'Barbados',
'BY' => 'Bielorrusia',
'BE' => 'Bélgica',
'BZ' => 'Belice',
'BJ' => 'Benín',
'BM' => 'Bermudas',
'BT' => 'Bután',
'BO' => 'Bolivia',
'BA' => 'Bosnia-Herzegovina',
'BW' => 'Botswana',
'BV' => 'Isla Bouvet',
'BR' => 'Brasil',
'IO' => 'Territorio Británico del Océano Indico',
'BN' => 'Brunei Darussalam',
'BG' => 'Bulgaria',
'BF' => 'Burkina Faso',
'BI' => 'Burundi',
'KH' => 'Camboya',
'CM' => 'Camerún',
'CA' => 'Canadá',
'CV' => 'Cabo Verde',
'KY' => 'Islas Caimán',
'CF' => 'República Centroafricana',
'TD' => 'Chad',
'CL' => 'Chile',
'CN' => 'China',
'CX' => 'Isla De Navidad',
'CC' => 'Islas Cocos (Keeling)',
'CO' => 'Colombia',
'KM' => 'Comores',
'CG' => 'República del Congo',
'CD' => 'República Democrática del Congo',
'CK' => 'Islas Cook',
'CR' => 'Costa Rica',
'CI' => 'Costa de Marfil',
'HR' => 'Croacia',
'CU' => 'Cuba',
'CY' => 'Chipre',
'CZ' => 'República Checa',
'DK' => 'Dinamarca',
'DJ' => 'Djibouti ',
'DM' => 'Dominica',
'DO' => 'Dominicana',
'EC' => 'Ecuador',
'EG' => 'Egipto',
'SV' => 'El Salvador',
'GQ' => 'Guinea Ecuatorial',
'ER' => 'Eritrea',
'EE' => 'Estonia',
'ET' => 'Etiopía',
'FK' => 'Islas Malvinas',
'FO' => 'Islas Feroe',
'FJ' => 'Fiyi',
'FI' => 'Finlandia',
'FR' => 'Francia',
'GF' => 'Guayana Francesa',
'FX' => 'France Métropolitaine',
'PF' => 'Polinesia Francesa',
'TF' => 'Terres Australes et Antarctiques Françaises',
'GA' => 'Gabón',
'GM' => 'Gambia',
'GE' => 'Georgia',
'DE' => 'Alemania',
'GH' => 'Ghana',
'GI' => 'Gibraltar',
'GB' => 'Gran Bretaña',
'GR' => 'Grecia',
'GL' => 'Groenlandia',
'GD' => 'Granada',
'GP' => 'Guadalupe',
'GU' => 'Guam',
'GT' => 'Guatemala',
'GG' => 'Guernsey',
'GN' => 'República Guinea',
'GW' => 'Guinea Bissau',
'GY' => 'Guyana',
'HT' => 'Haiti',
'HM' => 'Islas de Heard y McDonald',
'VA' => 'Ciudad del Vaticano',
'HN' => 'Honduras',
'HK' => 'Hong Kong',
'HU' => 'Hungría',
'IS' => 'Islandia',
'IN' => 'India',
'ID' => 'Indonesia',
'IR' => 'Irán',
'IQ' => 'Iraq',
'IE' => 'Irlanda',
'IM' => 'Isla Man',
'IL' => 'Israel',
'IT' => 'Italia',
'JM' => 'Jamaica',
'JP' => 'Japón',
'JE' => 'Jersey',
'JO' => 'Jordania',
'KZ' => 'Kazajstán',
'KE' => 'Kenia',
'KI' => 'Kiribati',
'KP' => 'Corea del Norte',
'KR' => 'Corea del Sur',
'KW' => 'Kuwait',
'KG' => 'Kirguistán',
'LA' => 'Laos',
'LV' => 'Letonia',
'LB' => 'Líbano',
'LS' => 'Lesotho',
'LR' => 'Liberia',
'LY' => 'Libia',
'LI' => 'Liechtenstein',
'LT' => 'Lituania',
'LU' => 'Luxemburgo',
'MO' => 'Macao',
'MK' => 'Macedonia',
'MG' => 'Madagascar',
'MW' => 'Malawi',
'MY' => 'Malasia',
'MV' => 'Maldivas',
'ML' => 'Malí',
'MT' => 'Malta',
'MH' => 'Islas Marshall',
'MQ' => 'Martinica',
'MR' => 'Mauritania',
'MU' => 'Mauricio',
'YT' => 'Mayotte',
'MX' => 'México',
'FM' => 'Micronesia',
'MD' => 'Moldavia',
'MC' => 'Mónaco',
'MN' => 'Mongolia',
'ME' => 'Montenegro',
'MS' => 'Montserrat',
'MA' => 'Marruecos',
'MZ' => 'Mozambique',
'MM' => 'Myanmar',
'NA' => 'Namibia',
'NR' => 'Nauru',
'NP' => 'Nepal',
'NL' => 'Holanda',
'AN' => 'Antillas Holandesas',
'NC' => 'Nueva Caledonia',
'NZ' => 'Nueva Zelanda',
'NI' => 'Nicaragua',
'NE' => 'Niger',
'NG' => 'Nigeria',
'NU' => 'Niue',
'NF' => 'Norfolk Island',
'MP' => 'Marianas del Norte',
'NO' => 'Noruega',
'OM' => 'Omán',
'PK' => 'Pakistán',
'PW' => 'Palau',
'PS' => 'Palestina',
'PA' => 'Panamá',
'PG' => 'Papúa-Nueva Guinea',
'PY' => 'Paraguay',
'PE' => 'Perú',
'PH' => 'Filipinas',
'PN' => 'Isla Pitcairn',
'PL' => 'Polonia',
'PT' => 'Portugal',
'PR' => 'Puerto Rico',
'QA' => 'Qatar',
'RE' => 'Reunión',
'RO' => 'Rumanía',
'RU' => 'Federación Rusa',
'RW' => 'Ruanda',
'KN' => 'San Cristobal y Nevis',
'LC' => 'Santa Lucía',
'MF' => 'San Martino',
'PM' => 'San Pedro y Miquelón',
'VC' => 'San Vincente y Granadinas',
'WS' => 'Samoa',
'SM' => 'San Marino',
'ST' => 'San Tomé y Príncipe',
'SA' => 'Arabia Saudita',
'SN' => 'Senegal',
'RS' => 'Serbia',
'SC' => 'Seychelles',
'SL' => 'Sierra Leona',
'SG' => 'Singapur',
'SK' => 'Eslovaquia',
'SI' => 'Eslovenia',
'SB' => 'Islas Salomón',
'SO' => 'Somalia',
'ZA' => 'Sudáfrica',
'GS' => 'Sudo Georgia y los Islas Sandwich del Sur',
'ES' => 'España',
'LK' => 'Sri Lanka',
'SH' => 'Santa Elena',
'SD' => 'Sudán',
'SR' => 'Surinam',
'SJ' => 'Isla Jan Mayen y Archipiélago de Svalbard',
'SZ' => 'Swazilandia',
'SE' => 'Suecia',
'CH' => 'Suiza',
'SY' => 'Siria',
'TW' => 'Taiwan',
'TJ' => 'Tadjikistan',
'TZ' => 'Tanzania',
'TH' => 'Tailandia',
'TI' => 'Tíbet',
'TL' => 'Timor Oriental',
'TG' => 'Togo',
'TK' => 'Tokelau',
'TO' => 'Tonga',
'TT' => 'Trinidad y Tobago',
'TN' => 'Túnez',
'TR' => 'Turquía',
'TM' => 'Turkmenistan',
'TC' => 'Islas Turcas y Caicos',
'TV' => 'Tuvalu',
'UG' => 'Uganda',
'UA' => 'Ucrania',
'AE' => 'Emiratos Árabes Unidos',
'US' => 'Estados Unidos',
'UM' => 'U.S. Minor Outlying Islands',
'UK' => 'Reino Unido',
'UY' => 'Uruguay',
'UZ' => 'Uzbekistán',
'VU' => 'Vanuatu',
'VE' => 'Venezuela',
'VN' => 'Vietnam',
'VG' => 'Islas Virgenes Británicas ',
'VI' => 'Islas Virgenes Americanas',
'WF' => 'Wallis y Futuna',
'EH' => 'Sáhara Occidental ',
'YE' => 'Yemen',
'ZR' => 'Zaire',
'ZM' => 'Zambia',
'ZW' => 'Zimbabwe',
));
}

// End of Country.php
