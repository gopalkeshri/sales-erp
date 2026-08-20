<?php

namespace App\Services;

class GstService
{
    /**
     * Complete list of Indian States and Union Territories with official GST State Codes.
     */
    public static function getIndianStates(): array
    {
        return [
            ['code' => '01', 'name' => 'Jammu and Kashmir', 'type' => 'UT'],
            ['code' => '02', 'name' => 'Himachal Pradesh', 'type' => 'State'],
            ['code' => '03', 'name' => 'Punjab', 'type' => 'State'],
            ['code' => '04', 'name' => 'Chandigarh', 'type' => 'UT'],
            ['code' => '05', 'name' => 'Uttarakhand', 'type' => 'State'],
            ['code' => '06', 'name' => 'Haryana', 'type' => 'State'],
            ['code' => '07', 'name' => 'Delhi', 'type' => 'UT'],
            ['code' => '08', 'name' => 'Rajasthan', 'type' => 'State'],
            ['code' => '09', 'name' => 'Uttar Pradesh', 'type' => 'State'],
            ['code' => '10', 'name' => 'Bihar', 'type' => 'State'],
            ['code' => '11', 'name' => 'Sikkim', 'type' => 'State'],
            ['code' => '12', 'name' => 'Arunachal Pradesh', 'type' => 'State'],
            ['code' => '13', 'name' => 'Nagaland', 'type' => 'State'],
            ['code' => '14', 'name' => 'Manipur', 'type' => 'State'],
            ['code' => '15', 'name' => 'Mizoram', 'type' => 'State'],
            ['code' => '16', 'name' => 'Tripura', 'type' => 'State'],
            ['code' => '17', 'name' => 'Meghalaya', 'type' => 'State'],
            ['code' => '18', 'name' => 'Assam', 'type' => 'State'],
            ['code' => '19', 'name' => 'West Bengal', 'type' => 'State'],
            ['code' => '20', 'name' => 'Jharkhand', 'type' => 'State'],
            ['code' => '21', 'name' => 'Odisha', 'type' => 'State'],
            ['code' => '22', 'name' => 'Chhattisgarh', 'type' => 'State'],
            ['code' => '23', 'name' => 'Madhya Pradesh', 'type' => 'State'],
            ['code' => '24', 'name' => 'Gujarat', 'type' => 'State'],
            ['code' => '26', 'name' => 'Dadra and Nagar Haveli and Daman and Diu', 'type' => 'UT'],
            ['code' => '27', 'name' => 'Maharashtra', 'type' => 'State'],
            ['code' => '29', 'name' => 'Karnataka', 'type' => 'State'],
            ['code' => '30', 'name' => 'Goa', 'type' => 'State'],
            ['code' => '31', 'name' => 'Lakshadweep', 'type' => 'UT'],
            ['code' => '32', 'name' => 'Kerala', 'type' => 'State'],
            ['code' => '33', 'name' => 'Tamil Nadu', 'type' => 'State'],
            ['code' => '34', 'name' => 'Puducherry', 'type' => 'UT'],
            ['code' => '35', 'name' => 'Andaman and Nicobar Islands', 'type' => 'UT'],
            ['code' => '36', 'name' => 'Telangana', 'type' => 'State'],
            ['code' => '37', 'name' => 'Andhra Pradesh', 'type' => 'State'],
            ['code' => '38', 'name' => 'Ladakh', 'type' => 'UT'],
            ['code' => '97', 'name' => 'Other Territory', 'type' => 'UT'],
        ];
    }

    /**
     * Map state code to state name.
     */
    public static function getStateByCode(string $code): ?string
    {
        $code = str_pad(trim($code), 2, '0', STR_PAD_LEFT);
        foreach (self::getIndianStates() as $state) {
            if ($state['code'] === $code) {
                return $state['name'];
            }
        }
        return null;
    }

    /**
     * Map state name to state code.
     */
    public static function getCodeByState(string $name): ?string
    {
        $clean = strtolower(trim($name));
        foreach (self::getIndianStates() as $state) {
            if (strtolower($state['name']) === $clean) {
                return $state['code'];
            }
        }
        return null;
    }

    /**
     * Validate Indian GSTIN format and extract State Code, PAN, Entity No, Checksum.
     * Pattern: 2 digits (State) + 10 chars (PAN) + 1 entity char + 'Z' + 1 checksum char.
     */
    public static function validateGstin(string $gstin): array
    {
        $gstin = strtoupper(trim($gstin));
        $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';

        if (!preg_match($pattern, $gstin)) {
            return [
                'valid' => false,
                'gstin' => $gstin,
                'message' => 'Invalid GSTIN format. Expected 15-character alphanumeric format (e.g. 27AACCD9999F1Z1).',
            ];
        }

        $stateCode = substr($gstin, 0, 2);
        $pan = substr($gstin, 2, 10);
        $stateName = self::getStateByCode($stateCode) ?? 'Unknown State';

        return [
            'valid' => true,
            'gstin' => $gstin,
            'state_code' => $stateCode,
            'state_name' => $stateName,
            'pan' => $pan,
            'entity_number' => substr($gstin, 12, 1),
            'checksum' => substr($gstin, 14, 1),
        ];
    }

    /**
     * Validate Indian PAN format.
     * Pattern: 5 letters + 4 digits + 1 letter (e.g. AACCD9999F).
     */
    public static function validatePan(string $pan): bool
    {
        $pan = strtoupper(trim($pan));
        return (bool) preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan);
    }

    /**
     * Calculate Indian GST (CGST + SGST vs IGST).
     */
    public static function calculateGst(
        float $taxableAmount,
        float $taxRate,
        ?string $supplierStateCode = null,
        ?string $buyerStateCode = null
    ): array {
        $supplierCode = str_pad(trim((string)$supplierStateCode), 2, '0', STR_PAD_LEFT);
        $buyerCode = str_pad(trim((string)$buyerStateCode), 2, '0', STR_PAD_LEFT);

        $isIntraState = empty($buyerCode) || ($supplierCode === $buyerCode);

        $taxAmount = round($taxableAmount * ($taxRate / 100), 2);

        if ($isIntraState) {
            $cgstRate = round($taxRate / 2, 2);
            $sgstRate = round($taxRate / 2, 2);
            $cgstAmount = round($taxAmount / 2, 2);
            $sgstAmount = $taxAmount - $cgstAmount; // avoid 1-paise rounding discrepancies
            $igstRate = 0.00;
            $igstAmount = 0.00;
            $gstType = 'intra_state';
        } else {
            $cgstRate = 0.00;
            $cgstAmount = 0.00;
            $sgstRate = 0.00;
            $sgstAmount = 0.00;
            $igstRate = $taxRate;
            $igstAmount = $taxAmount;
            $gstType = 'inter_state';
        }

        return [
            'taxable_amount' => round($taxableAmount, 2),
            'tax_rate' => round($taxRate, 2),
            'gst_type' => $gstType,
            'is_intra_state' => $isIntraState,
            'cgst_rate' => $cgstRate,
            'cgst_amount' => $cgstAmount,
            'sgst_rate' => $sgstRate,
            'sgst_amount' => $sgstAmount,
            'igst_rate' => $igstRate,
            'igst_amount' => $igstAmount,
            'total_tax' => $taxAmount,
            'total_amount' => round($taxableAmount + $taxAmount, 2),
        ];
    }

    /**
     * Convert numerical currency to Indian English Words (Lakhs, Crores, Rupees and Paise).
     * Example: 125450.50 -> "One Lakh Twenty Five Thousand Four Hundred Fifty Rupees and Fifty Paise Only"
     */
    public static function numberToIndianWords(float $amount): string
    {
        $amount = round($amount, 2);
        $parts = explode('.', number_format($amount, 2, '.', ''));
        $rupees = (int) $parts[0];
        $paise = isset($parts[1]) ? (int) $parts[1] : 0;

        if ($rupees === 0 && $paise === 0) {
            return 'Zero Rupees Only';
        }

        $words = self::convertRupeesToWords($rupees) . ' Rupees';

        if ($paise > 0) {
            $words .= ' and ' . self::convertTwoDigits($paise) . ' Paise';
        }

        return $words . ' Only';
    }

    private static function convertRupeesToWords(int $num): string
    {
        if ($num === 0) {
            return 'Zero';
        }

        $crores = (int) ($num / 10000000);
        $num %= 10000000;

        $lakhs = (int) ($num / 100000);
        $num %= 100000;

        $thousands = (int) ($num / 1000);
        $num %= 1000;

        $hundreds = (int) ($num / 100);
        $remainder = $num % 100;

        $result = '';

        if ($crores > 0) {
            $result .= self::convertTwoDigits($crores) . ' Crore ';
        }
        if ($lakhs > 0) {
            $result .= self::convertTwoDigits($lakhs) . ' Lakh ';
        }
        if ($thousands > 0) {
            $result .= self::convertTwoDigits($thousands) . ' Thousand ';
        }
        if ($hundreds > 0) {
            $result .= self::convertTwoDigits($hundreds) . ' Hundred ';
        }
        if ($remainder > 0) {
            $result .= self::convertTwoDigits($remainder) . ' ';
        }

        return trim($result);
    }

    private static function convertTwoDigits(int $num): string
    {
        $units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        if ($num < 20) {
            return $units[$num];
        }

        $ten = (int) ($num / 10);
        $unit = $num % 10;

        return trim($tens[$ten] . ' ' . $units[$unit]);
    }
}
