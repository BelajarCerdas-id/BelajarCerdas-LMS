<?php

namespace App\Helpers;

/**
 * Builds the e-mail regex validation rule for a context declared in
 * config/email-domains.php. The generated pattern is character-for-character
 * equivalent to the regexes the controllers used to hardcode inline:
 *
 *   login                /^[A-Za-z0-9._-]+@(belajarcerdas|belajar|pilar\.sch|gmail)\.(id|com)$/ (domains: belajarcerdas.id, belajar.id, pilar.sch.id, gmail.com)
 *   class_wali_kelas      /^[A-Za-z0-9._%+-]+@belajarcerdas\.id$/
 *   office_management     /^[A-Za-z0-9._-]+@(belajar|belajarcerdas|gmail)\.(id|com)$/
 *   foundation_management /^[A-Za-z0-9._-]+@(belajar|belajarcerdas|gmail)\.(id|com)$/
 */
class AllowedEmailRule
{
    public static function regex(string $context): string
    {
        $conf = config("email-domains.contexts.{$context}");

        if (!is_array($conf) || empty($conf['domains'])) {
            abort(500, "Konfigurasi domain email untuk konteks [{$context}] tidak ditemukan.");
        }

        $domains = array_map(
            static fn (string $domain): string => str_replace('.', '\.', $domain),
            $conf['domains']
        );

        return 'regex:/^[' . $conf['local_part'] . ']+@(' . implode('|', $domains) . ')$/';
    }
}
