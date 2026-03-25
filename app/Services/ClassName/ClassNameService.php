<?php

namespace App\Services\ClassName;

class ClassNameService
{
    // HELPER NAMING CLASS
    public function extractClassLevel(string $className): int
    {
        $className = trim(strtoupper($className));

        // 1. Coba angka di depan (7, 10, 12, dst)
        if (preg_match('/^\d+/', $className, $match)) {
            return (int) $match[0];
        }

        // 2. Coba romawi di depan (I, II, III, IV, V, VI, VII, VIII, IX, X, XI, XII)
        if (preg_match('/^(XII|XI|X|IX|VIII|VII|VI|V|IV|III|II|I)\b/', $className, $match)) {
            return $this->romanToInt($match[0]);
        }

        return 0; // fallback aman
    }

    private function romanToInt(string $roman): int
    {
        $map = [
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
            'VI' => 6,
            'VII' => 7,
            'VIII' => 8,
            'IX' => 9,
            'X' => 10,
            'XI' => 11,
            'XII' => 12,
        ];

        return $map[$roman] ?? 0;
    }
} 