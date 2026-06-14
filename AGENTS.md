# BelajarCerdas-LMS

Laravel 12 LMS for Indonesian schools. Domain roles in code include `Administrator`, `Yayasan`, `Guru`, `Murid`/`Siswa`, and `Orang Tua`; seeders still mix labels.

## Commands

| Action | Command |
|---|---|
| Setup | `composer setup` (`composer install`, copy `.env`, key, migrate, `npm install`, `npm run build`) |
| Dev stack | `composer dev` (runs `php artisan serve`, queue listener, Pail, and Vite via `concurrently`) |
| Tests | `composer test` (`php artisan config:clear --ansi` then `php artisan test`) |
| Focused test | `php artisan test --filter Name` or `vendor/bin/phpunit --filter Name tests/Path/File.php` |
| PHP format | `vendor/bin/pint` |
| Assets | `npm run build`; Vite dev server is `npm run dev` |

## Architecture

- Server-rendered Blade plus vanilla JS; there are no React/Vue resources.
- Vite only builds `resources/css/app.css` and `resources/js/app.js`; much feature JS is committed under `public/assets/js/**` and included directly from Blade.
- Tailwind is v4 through `@tailwindcss/vite`; DaisyUI is loaded in `resources/css/app.css`. There is no `tailwind.config.*`.
- Auth uses the `web` guard with provider model `App\Models\UserAccount`; scaffolded `App\Models\User`, `users` table, and `UserFactory` are not the LMS login model.
- Routes use middleware classes directly (`AuthMiddleware`, `RedirectIfAuthenticated`); no middleware aliases are registered in `bootstrap/app.php`.
- Nearly all HTTP and AJAX/dropdown endpoints live in `routes/web.php`.
- Many LMS features have paired default and school-partner routes; school-partner route names usually end in `.schoolPartner` and include `{schoolName}/{schoolId}` params.
- Be careful adding short `/lms/...` routes near `/lms/{role}/{schoolName}/{schoolId}` in `routes/web.php`; route order can send them to the student LMS view.
- Yayasan is a parent layer over schools: `yayasans`, `yayasan_profiles`, and nullable `yayasan_id` columns on school-scoped tables. Yayasan role routes are under `/lms/yayasan/{yayasanId}/...`; administrator CRUD is `/lms/yayasan-management` via `AdminYayasanController`.
- Uploads are written directly under app-specific `public/` folders such as `assessment/`, `school-logo/`, `yayasan-logo/`, `lms-contents/`, `library/`, `lms-docx-image/`, and `lms-assessment-submission/`.

## Environment And Tests

- `.env.example` defaults to SQLite plus database-backed session/cache/queue and `BROADCAST_CONNECTION=log`; `phpunit.xml` overrides tests to in-memory SQLite, array cache/session, sync queue, and null broadcasting.
- Current tests are only scaffold examples in `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php`; a green suite does not cover LMS behavior.
- No CI workflow, pre-commit config, Makefile, JS lint, or JS typecheck script exists beyond Composer/npm scripts.

## Seeders And Data Gotchas

- `php artisan db:seed` creates `user_accounts` for `kepsek@belajarcerdas.id`, `kurikulum@belajarcerdas.id`, `guru@belajarcerdas.id`, `murid@belajarcerdas.id`, `orangtua@belajarcerdas.id`, and `yayasan@belajarcerdas.id`; default password is `password123`.
- `kurikulum@belajarcerdas.id` is the seeded `Administrator` account for admin pages such as Yayasan management.
- `orangtua@belajarcerdas.id` is seeded with role `Siswa`; `OrangTuaSeeder` uses role `Orang Tua`, password `12345678`, and hard-coded `school_partner_id`/`student_id` values.
- `database/seeders/SampleSeeder.php` declares another `DatabaseSeeder` class, so normal autoloading can collide with `database/seeders/DatabaseSeeder.php`.

## Feature Quirks

- Excel imports use `maatwebsite/excel` for syllabus, school partners, school users, and subject passing grade criteria.
- Student assessment exams increment `StudentAssessmentAttempt::tab_switch_count`; `StudentAssessmentExamController::reportTabSwitch` marks an attempt `cheating` at 3 switches.
- Pusher/Echo dependencies are installed, but `resources/js/bootstrap.js` only initializes Echo when `VITE_PUSHER_APP_KEY` is set.
