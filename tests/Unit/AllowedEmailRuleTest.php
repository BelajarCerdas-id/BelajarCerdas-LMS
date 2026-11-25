<?php

namespace Tests\Unit;

use App\Helpers\AllowedEmailRule;
use Tests\TestCase;

/**
 * The centralized e-mail domain lists must encode EXACTLY the sets the
 * controllers used to hardcode inline — including the odd corners
 * (gmail.id is accepted by the office/foundation rule, '+' is only valid
 * in the wali-kelas rule) — so the refactor stays behavior-preserving.
 */
class AllowedEmailRuleTest extends TestCase
{
    public function test_office_management_rule_matches_the_old_inline_regex_exactly(): void
    {
        $rule = AllowedEmailRule::regex('office_management');
        $this->assertStringStartsWith('regex:/^[', $rule);
        $this->assertStringEndsWith('$/', $rule);

        $old = '/^[A-Za-z0-9._-]+@(belajar|belajarcerdas|gmail)\.(id|com)$/';

        $samples = [
            'a@gmail.com' => true,
            'a.b_c-d@belajarcerdas.id' => true,
            // the alternation really did allow these; keep allowing them
            'a@gmail.id' => true,
            'a@belajar.com' => true,
            'a@yahoo.com' => false,
            'a@sub.belajarcerdas.id' => false,
            // '+' was never in the office local-part class
            'a+b@gmail.com' => false,
            'a@gmail.net' => false,
            '@gmail.com' => false,
        ];

        foreach ($samples as $email => $expected) {
            $this->assertSame($expected, preg_match($old, $email) === 1, "old regex disagrees on [{$email}] — test is wrong");
            $this->assertSame(
                $expected,
                preg_match(trim($rule, 'regex:') ?: $rule, $email) === 1,
                "centralized office_management rule changed behavior for [{$email}]"
            );
        }
    }

    public function test_foundation_management_rule_uses_the_same_domain_set(): void
    {
        $office = config('email-domains.contexts.office_management');
        $foundation = config('email-domains.contexts.foundation_management');

        $this->assertSame($office['domains'], $foundation['domains']);
        $this->assertSame($office['local_part'], $foundation['local_part']);
        $this->assertNotSame('', $foundation['local_part']);
    }

    public function test_wali_kelas_rule_stays_scoped_to_belajarcerdas_id(): void
    {
        $rule = AllowedEmailRule::regex('class_wali_kelas');
        $this->assertSame('regex:/^[A-Za-z0-9._%+-]+@(belajarcerdas\.id)$/', $rule);

        $this->assertSame(1, preg_match('/^[A-Za-z0-9._%+-]+@belajarcerdas\.id$/', 'a+b@belajarcerdas.id'));
        $this->assertSame(1, preg_match(trim($rule, 'regex:'), 'a+b@belajarcerdas.id'), "'+' must stay valid for wali kelas");
        $this->assertSame(0, preg_match(trim($rule, 'regex:'), 'a@gmail.com'), 'gmail must stay rejected for wali kelas');
    }

    public function test_every_declared_context_has_a_usable_shape(): void
    {
        foreach (array_keys(config('email-domains.contexts', [])) as $context) {
            $conf = config("email-domains.contexts.{$context}");
            $this->assertNotEmpty($conf['domains'], "[{$context}] must list at least one domain");
            $this->assertNotSame('', $conf['local_part'], "[{$context}] must declare a local-part class");
            $this->assertStringStartsWith('regex:/^[', AllowedEmailRule::regex($context));
        }
    }

    public function test_unknown_context_aborts_instead_of_validating_everything(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        AllowedEmailRule::regex('no_such_context');
    }
}
