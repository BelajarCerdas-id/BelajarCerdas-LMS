<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAssessmentAnticheatViolation extends Model
{
    protected $fillable = [
        'student_assessment_attempt_id',
        'student_id',
        'school_assessment_id',
        'reason',
        'severity',
        'ip',
        'browser',
        'browser_version',
        'device_type',
        'platform',
        'user_agent',
    ];

    /**
     * Parse a raw User-Agent string into browser / version / device / platform.
     * Deliberately dependency-free; enough fidelity for a cheating report.
     */
    public static function parseUserAgent(string $ua): array
    {
        $browsers = [
            'Edg' => 'Edge',
            'OPR' => 'Opera',
            'SamsungBrowser' => 'Samsung Internet',
            'Firefox' => 'Firefox',
            'CriOS' => 'Chrome',
            'FxiOS' => 'Firefox',
            'EdgiOS' => 'Edge',
            'Chrome' => 'Chrome',
            'Safari' => 'Safari',
        ];

        $browser = 'Unknown';
        $version = null;

        foreach ($browsers as $token => $name) {
            if (preg_match('/' . preg_quote($token, '/') . '\/([\d.]+)/', $ua, $m)) {
                $browser = $name;
                $version = $m[1];
                break;
            }
        }

        $deviceType = 'desktop';
        if (preg_match('/iPad|Tablet/i', $ua)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/Mobi|iPhone|Android.*Mobile/i', $ua)) {
            $deviceType = 'mobile';
        }

        // iPadOS masquerades as Macintosh with touch support
        if (preg_match('/Macintosh/i', $ua) && strpos($ua, 'Touch') !== false) {
            $deviceType = 'tablet';
        }

        $platform = 'Unknown';
        if (preg_match('/Windows/i', $ua)) $platform = 'Windows';
        elseif (preg_match('/Android/i', $ua)) $platform = 'Android';
        elseif (preg_match('/iPhone|iPod/i', $ua)) $platform = 'iOS';
        elseif (preg_match('/iPad/i', $ua)) $platform = 'iPadOS';
        elseif (preg_match('/Mac OS X|Macintosh/i', $ua)) $platform = 'macOS';
        elseif (preg_match('/Linux/i', $ua)) $platform = 'Linux';

        return compact('browser', 'version', 'deviceType', 'platform');
    }

    public function attempt()
    {
        return $this->belongsTo(StudentAssessmentAttempt::class);
    }
}
