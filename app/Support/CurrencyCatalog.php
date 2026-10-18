<?php

namespace App\Support;

class CurrencyCatalog
{
    /**
     * @return array<string, array{name: string, symbol: string}>
     */
    public static function all(): array
    {
        return [
            'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ'],
            'ARS' => ['name' => 'Argentine Peso', 'symbol' => '$'],
            'AUD' => ['name' => 'Australian Dollar', 'symbol' => '$'],
            'BDT' => ['name' => 'Bangladeshi Taka', 'symbol' => '৳'],
            'BHD' => ['name' => 'Bahraini Dinar', 'symbol' => '.د.ب'],
            'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$'],
            'CAD' => ['name' => 'Canadian Dollar', 'symbol' => '$'],
            'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF'],
            'CLP' => ['name' => 'Chilean Peso', 'symbol' => '$'],
            'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥'],
            'COP' => ['name' => 'Colombian Peso', 'symbol' => '$'],
            'CZK' => ['name' => 'Czech Koruna', 'symbol' => 'Kč'],
            'DKK' => ['name' => 'Danish Krone', 'symbol' => 'kr'],
            'EGP' => ['name' => 'Egyptian Pound', 'symbol' => 'E£'],
            'EUR' => ['name' => 'Euro', 'symbol' => '€'],
            'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
            'HKD' => ['name' => 'Hong Kong Dollar', 'symbol' => '$'],
            'HUF' => ['name' => 'Hungarian Forint', 'symbol' => 'Ft'],
            'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp'],
            'ILS' => ['name' => 'Israeli New Shekel', 'symbol' => '₪'],
            'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
            'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥'],
            'KES' => ['name' => 'Kenyan Shilling', 'symbol' => 'KSh'],
            'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩'],
            'KWD' => ['name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك'],
            'LKR' => ['name' => 'Sri Lankan Rupee', 'symbol' => 'Rs'],
            'MAD' => ['name' => 'Moroccan Dirham', 'symbol' => 'د.م.'],
            'MXN' => ['name' => 'Mexican Peso', 'symbol' => '$'],
            'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
            'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦'],
            'NOK' => ['name' => 'Norwegian Krone', 'symbol' => 'kr'],
            'NPR' => ['name' => 'Nepalese Rupee', 'symbol' => 'रू'],
            'NZD' => ['name' => 'New Zealand Dollar', 'symbol' => '$'],
            'OMR' => ['name' => 'Omani Rial', 'symbol' => 'ر.ع.'],
            'PHP' => ['name' => 'Philippine Peso', 'symbol' => '₱'],
            'PKR' => ['name' => 'Pakistani Rupee', 'symbol' => 'Rs'],
            'PLN' => ['name' => 'Polish Złoty', 'symbol' => 'zł'],
            'QAR' => ['name' => 'Qatari Riyal', 'symbol' => 'ر.ق'],
            'RUB' => ['name' => 'Russian Ruble', 'symbol' => '₽'],
            'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'ر.س'],
            'SEK' => ['name' => 'Swedish Krona', 'symbol' => 'kr'],
            'SGD' => ['name' => 'Singapore Dollar', 'symbol' => '$'],
            'THB' => ['name' => 'Thai Baht', 'symbol' => '฿'],
            'TRY' => ['name' => 'Turkish Lira', 'symbol' => '₺'],
            'TWD' => ['name' => 'New Taiwan Dollar', 'symbol' => 'NT$'],
            'TZS' => ['name' => 'Tanzanian Shilling', 'symbol' => 'TSh'],
            'UAH' => ['name' => 'Ukrainian Hryvnia', 'symbol' => '₴'],
            'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
            'VND' => ['name' => 'Vietnamese Đồng', 'symbol' => '₫'],
            'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R'],
        ];
    }
}
