<?php
namespace App\Helpers;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

/**
 * Fixed Salary Grade -> maximum assignable asset value.
 *
 * Source: NIA business rule table (Salary Grade / Threshold).
 *
 */
class SalaryGradeHelper
{
    /** @var array<int, array{min:int,max:int,threshold:float,label:string}> */
    private static $brackets = [
        ['min' => 1,  'max' => 7,  'threshold' => 70000.00,     'label' => 'Up to ₱70,000.00'],
        ['min' => 8,  'max' => 10, 'threshold' => 500000.00,    'label' => 'Up to ₱500,000.00'],
        ['min' => 11, 'max' => 17, 'threshold' => 1000000.00,   'label' => 'Up to ₱1,000,000.00'],
        ['min' => 18, 'max' => 21, 'threshold' => 10000000.00,  'label' => 'Up to ₱10,000,000.00'],
        ['min' => 22, 'max' => 30, 'threshold' => PHP_INT_MAX,  'label' => 'Above ₱10,000,000.00'],
    ];

    public static function minGrade(): int
    {
        return 1;
    }

    public static function maxGrade(): int
    {
        return 30;
    }

    /**
     * Maximum peso value of an asset that can be assigned to this Salary Grade.
     */
    public static function getThreshold(int $salaryGrade): float
    {
        foreach (self::$brackets as $bracket) {
            if ($salaryGrade >= $bracket['min'] && $salaryGrade <= $bracket['max']) {
                return (float)$bracket['threshold'];
            }
        }
        // Unrecognized/invalid SG -> no assignment allowed.
        return 0.0;
    }

    /**
     * Human-readable threshold label for display in forms/tooltips.
     */
    public static function getThresholdLabel(int $salaryGrade): string
    {
        foreach (self::$brackets as $bracket) {
            if ($salaryGrade >= $bracket['min'] && $salaryGrade <= $bracket['max']) {
                return $bracket['label'];
            }
        }
        return 'No threshold defined';
    }

    /**
     * Per-asset check: can an employee at this Salary Grade be assigned
     * an asset of this acquisition cost? (Not cumulative — only the
     * value of the asset being assigned right now is checked.)
     */
    public static function canAssign(int $salaryGrade, float $assetCost): bool
    {
        return $assetCost <= self::getThreshold($salaryGrade);
    }

    /**
     * Full bracket table, e.g. for rendering a reference legend in the
     * Employee form.
     * @return array<int, array{min:int,max:int,threshold:float,label:string}>
     */
    public static function getBrackets(): array
    {
        return self::$brackets;
    }
}
