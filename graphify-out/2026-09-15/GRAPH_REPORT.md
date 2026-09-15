# Graph Report - ngebadmintonyuk  (2026-09-15)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1881 nodes · 3069 edges · 213 communities (174 shown, 39 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Factories\HasFactory
- scripts
- composer.json
- PlaySessionController.php
- SaveGuestRequest
- package.json
- laravel-best-practices/SKILL.md
- Illuminate\Database\Schema\Blueprint
- require-dev
- Illuminate\Foundation\Http\FormRequest
- command
- psr-4
- CreateTopUpRequestAction.php
- logging.php
- console.php
- app.js
- Illuminate\Database\Seeder
- Modul
- RotationScheduleService
- Modul
- Database
- Modul
- Modul
- Modul
- PlaySession
- Modul
- 09-ui-guideline.md
- Controller
- TopUpRequest
- AGENTS.md
- Livewire Development
- User
- Illuminate\Database\Eloquent\Factories\Factory
- Modul
- Illuminate\Validation\ValidationException
- PushSubscription
- Pest 5 Features
- Illuminate\Database\Eloquent\Relations\BelongsTo
- TopUpRequestController.php
- PushNotificationSender.php
- Tailwind CSS Development
- StorePushSubscriptionRequest
- Status dan konteks proyek NgeBadmintonYuk — arsip
- Modul
- Detection Checklist
- Process
- Architecture Best Practices
- Security Best Practices
- Actions Models Http
- Laravel Boost Guidelines
- TopUpSetting
- SessionRegistration.php
- StorePlaySessionRequest
- Laravel Boost
- Advanced Query Patterns
- Database Performance Best Practices
- Events & Notifications Best Practices
- Queue & Job Best Practices
- Illuminate\Http\Request
- AppServiceProvider.php
- Illuminate\View\View
- Symfony\Component\HttpFoundation\Response
- Caching Best Practices
- Eloquent Best Practices
- Migration Best Practices
- require
- Blade & Views Best Practices
- Error Handling Best Practices
- Task Scheduling Best Practices
- Testing Best Practices
- ShuttlecockItem
- FirebaseCloudMessaging.php
- Collection Best Practices
- HTTP Client Best Practices
- Mail Best Practices
- PRD NgeBadmintonYuk — baseline 15 September 2026
- Routing & Controllers Best Practices
- Conventions & Style
- Validation & Forms Best Practices
- UpdateTopUpSettingRequest
- FilterMemberReportRequest
- config
- SessionRegistration
- Color System
- Typography Hierarchy
- Actions Models Services Http
- 04-dashboard.md
- 08-report.md
- SessionRegistrationController.php
- Actions Http
- Actions Models
- Controllers
- Jsviews
- 05-category.md
- Visual Signature
- Semantic Color
- Public Sessions
- Push Notifications
- autoload-dev
- extra
- keywords
- 10-project-status.md
- Anti AI-Slop Rules
- Button
- NgeKas — UI Guideline
- PushNotification
- Membership
- Aturan operasional yang tidak boleh terlewat
- Konteks final proyek NgeBadmintonYuk
- Http
- UserFactory
- Illuminate\Support\Facades\Schema
- Illuminate\Database\Migrations\Migration
- Actions Services Http
- MarkSessionRegistrationPresentRequest
- 06-income.md
- StoreGuestSessionRegistrationRequest
- 07-expense.md
- Status UI — 7 September 2026
- UpdateProfileRequest
- StoreSessionRegistrationRequest
- StoreStockMovementRequest
- UpdateMemberRequest
- TestCase
- Illuminate\Validation\Rule
- web.php
- PushNotificationManager
- Auth
- StockMovement
- Illuminate\Http\RedirectResponse
- RecordStockMovementAction
- 03-authentication.md
- FirebaseCloudMessaging
- Actions
- Profile
- Services Http
- PushNotificationTest.php

## God Nodes (most connected - your core abstractions)
1. `User` - 136 edges
2. `PlaySession` - 96 edges
3. `SessionRegistration` - 61 edges
4. `Membership` - 42 edges
5. `PushSubscription` - 29 edges
6. `Controller` - 28 edges
7. `ShuttlecockItem` - 25 edges
8. `RotationScheduleService` - 23 edges
9. `Income` - 23 edges
10. `Guest` - 22 edges

## Surprising Connections (you probably didn't know these)
- `send()` --references--> `PushSubscription`  [EXTRACTED]
  tests/Feature/PushNotificationTest.php → app/Models/PushSubscription.php
- `createMembership()` --calls--> `Membership`  [EXTRACTED]
  tests/Feature/MembershipManagementTest.php → app/Models/Membership.php
- `createMembership()` --references--> `User`  [EXTRACTED]
  tests/Feature/MembershipManagementTest.php → app/Models/User.php
- `send()` --references--> `PushSubscription`  [EXTRACTED]
  app/Contracts/PushNotificationSender.php → app/Models/PushSubscription.php
- `browser()` --calls--> `installMemberNotifications()`  [EXTRACTED]
  tests/JavaScript/member-notifications.test.js → resources/js/member-notifications.js

## Import Cycles
- None detected.

## Communities (213 total, 39 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.18
Nodes (7): MembershipFactory, PlaySessionFactory, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon

### Community 1 - "scripts"
Cohesion: 0.06
Nodes (37): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+29 more)

### Community 2 - "composer.json"
Cohesion: 0.25
Nodes (7): description, license, minimum-stability, name, prefer-stable, $schema, type

### Community 3 - "PlaySessionController.php"
Cohesion: 0.22
Nodes (3): DeletePlaySessionAction, UpdatePlaySessionAction, PlaySessionController

### Community 5 - "package.json"
Cohesion: 0.06
Nodes (31): aislop, concurrently, firebase, @laravel/multiplex, laravel-vite-plugin, lightningcss-linux-x64-gnu, dependencies, concurrently (+23 more)

### Community 6 - "laravel-best-practices/SKILL.md"
Cohesion: 0.17
Nodes (10): Configuration Best Practices, `env()` Only in Config Files, Use `App::environment()` for Environment Checks, Use Constants and Language Files, Use Encrypted Env or External Secrets, Consistency First, Decision Rules, How to Apply (+2 more)

### Community 8 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 9 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.16
Nodes (5): UpdateProfilePasswordRequest, UpdateSessionRegistrationPaymentRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Illuminate\Validation\Rules\Password

### Community 10 - "command"
Cohesion: 0.20
Nodes (9): command, enabled, type, mcp, laravel-boost, $schema, artisan, boost:mcp (+1 more)

### Community 11 - "psr-4"
Cohesion: 0.29
Nodes (7): autoload, files, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\, app/helpers.php

### Community 12 - "CreateTopUpRequestAction.php"
Cohesion: 0.17
Nodes (9): UpdateMemberProfileAction, GdImage, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Hash, Illuminate\Support\Facades\Storage, Illuminate\Support\Str, Pdo\Mysql, RuntimeException (+1 more)

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 22 - "app.js"
Cohesion: 0.06
Nodes (36): installButton, installGuide, menu, scoreboard, serverLoading, sidebar, installAppVersionUpdates(), readStoredVersion() (+28 more)

### Community 36 - "Illuminate\Database\Seeder"
Cohesion: 0.09
Nodes (11): AttendanceSeeder, DatabaseSeeder, MembershipSeeder, MembershipTransactionSeeder, PlaySessionSeeder, ShuttlecockItemSeeder, StockMovementSeeder, TopUpRequestSeeder (+3 more)

### Community 37 - "Modul"
Cohesion: 0.04
Nodes (47): Amount, Business Rule, Category, Category, Contoh Data, Create Transaction, Currency Input, Database (+39 more)

### Community 38 - "RotationScheduleService"
Cohesion: 0.08
Nodes (12): GenerateRotationScheduleAction, PublishRotationScheduleAction, MemberRotationController, RotationScheduleController, GenerateRotationScheduleRequest, PublishRotationScheduleRequest, IncomeDetail, MemberReportService (+4 more)

### Community 39 - "Modul"
Cohesion: 0.05
Nodes (38): Business Rule, Daftar Pemasukan, Daftar Pengeluaran, Database, Default Periode, Empty Result, Empty State, End Date (+30 more)

### Community 40 - "Database"
Cohesion: 0.05
Nodes (37): Business Rule, categories, Category, Category Type, Database, Database Transaction, Date, Delete Rule (+29 more)

### Community 41 - "Modul"
Cohesion: 0.05
Nodes (37): Admin Seeder, Authenticated User, Business Rule, Database, Error State, Flow, Future Improvement, Guest (+29 more)

### Community 42 - "Modul"
Cohesion: 0.06
Nodes (34): Business Rule, Category Type, CategoryForm, CategoryIndex, Database, Default Category, Delete Category, Delete Confirmation (+26 more)

### Community 43 - "Modul"
Cohesion: 0.06
Nodes (32): Business Rule, Dashboard Read Only, Database, Empty State, Flow, Future Improvement, Header, Livewire Component (+24 more)

### Community 44 - "PlaySession"
Cohesion: 0.17
Nodes (6): DeleteSessionRegistrationAction, RegisterForPlaySessionAction, SendSessionRegistrationNotificationAction, SessionRegistrationController, UpdateGuestAttendanceRequest, PlaySession

### Community 45 - "Modul"
Cohesion: 0.07
Nodes (30): Accent, Background, Brand Utama, Business Rule, Button, Card, Color Palette, Currency (+22 more)

### Community 46 - "09-ui-guideline.md"
Cohesion: 0.06
Nodes (30): Border, Border Radius, Brand Personality, Dashboard Layout, Design Direction, Design Test, Destructive Action, Dynamic Detail (+22 more)

### Community 47 - "Controller"
Cohesion: 0.22
Nodes (5): Controller, NotificationSetupController, RotationSchedulePdfController, Illuminate\Contracts\View\View, Illuminate\Support\Facades\Gate

### Community 49 - "AGENTS.md"
Cohesion: 0.15
Nodes (12): APIs & Eloquent Resources, Deployment, Do Things the Laravel Way, Laravel Pint Code Formatter, Livewire, Model Creation, Pest, PHP (+4 more)

### Community 50 - "Livewire Development"
Cohesion: 0.08
Nodes (24): Component-Scoped Interceptors, Intercept Messages, Intercept Requests, Interceptor System (v4), Livewire 4 JavaScript Integration, Magic Properties, Alpine & JavaScript, Basic Usage (+16 more)

### Community 51 - "User"
Cohesion: 0.11
Nodes (4): User, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Foundation\Auth\User, createMembership()

### Community 52 - "Illuminate\Database\Eloquent\Factories\Factory"
Cohesion: 0.13
Nodes (6): GuestFactory, MembershipTransactionFactory, PushNotificationFactory, PushSubscriptionFactory, ShuttlecockItemFactory, Illuminate\Database\Eloquent\Factories\Factory

### Community 53 - "Modul"
Cohesion: 0.10
Nodes (21): Business Rule, Database, Flow, Future Improvement, Livewire Component, Modul, Pemasukan, Pengeluaran (+13 more)

### Community 54 - "Illuminate\Validation\ValidationException"
Cohesion: 0.20
Nodes (3): Illuminate\Database\QueryException, Illuminate\Support\Facades\DB, Illuminate\Validation\ValidationException

### Community 55 - "PushSubscription"
Cohesion: 0.21
Nodes (3): PushNotificationController, SendPushNotificationRequest, PushSubscription

### Community 56 - "Pest 5 Features"
Cohesion: 0.10
Nodes (19): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Browser Test Example, Common Pitfalls, Creating Tests, Datasets (+11 more)

### Community 57 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.14
Nodes (4): Attendance, ExpenseDetail, AttendanceFactory, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 58 - "TopUpRequestController.php"
Cohesion: 0.15
Nodes (4): ReviewTopUpRequestAction, TopUpRequestController, ReviewTopUpRequest, StoreTopUpRequest

### Community 60 - "Tailwind CSS Development"
Cohesion: 0.14
Nodes (13): Basic Usage, Common Patterns, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Flexbox Layout, Grid Layout (+5 more)

### Community 61 - "StorePushSubscriptionRequest"
Cohesion: 0.17
Nodes (6): AppVersionController, PushSubscriptionController, DeletePushSubscriptionRequest, StorePushSubscriptionRequest, Illuminate\Http\JsonResponse, Illuminate\Http\Response

### Community 62 - "Status dan konteks proyek NgeBadmintonYuk — arsip"
Cohesion: 0.20
Nodes (10): Arsitektur aktual, Cara melanjutkan pekerjaan, Data dan aturan bisnis penting, Fitur yang sudah memiliki implementasi, Hal yang perlu dituntaskan atau diputuskan, Peta dokumentasi, Posisi proyek, Status dan konteks proyek NgeBadmintonYuk — arsip (+2 more)

### Community 63 - "Modul"
Cohesion: 0.04
Nodes (49): Amount, Business Rule, Category, Category, Contoh Data, Create Transaction, Currency Input, Database (+41 more)

### Community 64 - "Detection Checklist"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 65 - "Process"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 66 - "Architecture Best Practices"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Code to Interfaces, Convention Over Configuration, Default Sort by Descending, Single-Purpose Action Classes, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution, Use `Context` for Request-Scoped Data (+3 more)

### Community 67 - "Security Best Practices"
Cohesion: 0.17
Nodes (11): Audit Dependencies, Authorize Every Action, CSRF Protection, Encrypt Sensitive Database Fields, Escape Output to Prevent XSS, Keep Secrets Out of Code, Mass Assignment Protection, Prevent SQL Injection (+3 more)

### Community 68 - "Actions Models Http"
Cohesion: 0.14
Nodes (13): Actions Models Http, Admin-managed guests may join without member accounts, Admin settings supersede fixed top up amount, Member profile and birth date are mandatory before joining, Play-session capacity is enforced atomically, Play sessions include an ordered waiting list and linked income, Session lists are scoped and member-backed, Session registration requires an account (+5 more)

### Community 69 - "Laravel Boost Guidelines"
Cohesion: 0.22
Nodes (9): Application Structure & Architecture, Conventions, Documentation Files, Foundational Context, Frontend Bundling, Laravel Boost Guidelines, Replies, Skills Activation (+1 more)

### Community 70 - "TopUpSetting"
Cohesion: 0.25
Nodes (3): TopUpSetting, TopUpSettingFactory, self

### Community 73 - "Laravel Boost"
Cohesion: 0.29
Nodes (7): Artisan, Laravel Boost, Project Rules, Search Syntax, Searching Documentation (IMPORTANT), Tinker, Tools

### Community 74 - "Advanced Query Patterns"
Cohesion: 0.20
Nodes (9): Advanced Query Patterns, Create Dynamic Relationships via Subquery FK, Prefer `whereIn` + Subquery Over `whereHas`, Sometimes Two Simple Queries Beat One Complex Query, Use `addSelect()` Subqueries for Single Values from Has-Many, Use Compound Indexes Matching `orderBy` Column Order, Use Conditional Aggregates Instead of Multiple Count Queries, Use Correlated Subqueries for Has-Many Ordering (+1 more)

### Community 75 - "Database Performance Best Practices"
Cohesion: 0.20
Nodes (9): Add Database Indexes, Always Eager Load Relationships, Chunk Large Datasets, Database Performance Best Practices, No Queries in Blade Templates, Prevent Lazy Loading in Development, Select Only Needed Columns, Use `cursor()` for Memory-Efficient Iteration (+1 more)

### Community 76 - "Events & Notifications Best Practices"
Cohesion: 0.20
Nodes (9): Always Queue Notifications, Events & Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Run `event:cache` in Production Deploy, Use `afterCommit()` on Notifications in Transactions, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 77 - "Queue & Job Best Practices"
Cohesion: 0.18
Nodes (10): Always Implement `failed()`, Batch Related Jobs, Implement `ShouldBeUnique`, Queue & Job Best Practices, Rate Limit External API Calls in Jobs, `retryUntil()` Needs `$tries = 0`, Set `retry_after` Greater Than `timeout`, Use Exponential Backoff (+2 more)

### Community 78 - "Illuminate\Http\Request"
Cohesion: 0.05
Nodes (18): CategoryController, DashboardController, ReportController, TransactionController, Category, Expense, Income, ReportRepository (+10 more)

### Community 79 - "AppServiceProvider.php"
Cohesion: 0.24
Nodes (5): AppServiceProvider, Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\Facades\View, Illuminate\Support\ServiceProvider

### Community 80 - "Illuminate\View\View"
Cohesion: 0.17
Nodes (5): DeleteMemberAction, MemberController, PublicPlaySessionController, FilterPlaySessionsRequest, Illuminate\View\View

### Community 81 - "Symfony\Component\HttpFoundation\Response"
Cohesion: 0.23
Nodes (6): ResetLegacyPushSubscriptionsAction, RequireCompleteMemberProfile, RequireCurrentPushSetup, RequireMemberNotifications, Closure, Symfony\Component\HttpFoundation\Response

### Community 82 - "Caching Best Practices"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::memo()` to Avoid Redundant Hits Within a Request, Use `Cache::remember()` Instead of Manual Get/Put, Use Cache Tags to Invalidate Related Groups, Use `once()` for Per-Request Memoization

### Community 83 - "Eloquent Best Practices"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Avoid Hardcoded Table Names in Queries, Cast Date Columns Properly, Define Attribute Casts, Eloquent Best Practices, Use Correct Relationship Types, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 84 - "Migration Best Practices"
Cohesion: 0.22
Nodes (8): Add Indexes in the Migration, Generate Migrations with Artisan, Keep Migrations Focused, Migration Best Practices, Mirror Defaults in Model `$attributes`, Never Modify Deployed Migrations, Use `constrained()` for Foreign Keys, Write Reversible `down()` Methods by Default

### Community 85 - "require"
Cohesion: 0.22
Nodes (9): require, barryvdh/laravel-dompdf, google/auth, laravel/framework, laravel/tinker, livewire/blaze, livewire/livewire, minishlink/web-push (+1 more)

### Community 86 - "Blade & Views Best Practices"
Cohesion: 0.25
Nodes (7): Blade & Views Best Practices, Prefer Blade Components Over `@include`, Use `$attributes->merge()` in Component Templates, Use `@aware` for Deeply Nested Component Props, Use Blade Fragments for Partial Re-Renders (htmx/Turbo), Use `@pushOnce` for Per-Component Scripts, Use View Composers for Shared View Data

### Community 87 - "Error Handling Best Practices"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Enable `dontReportDuplicates()`, Error Handling Best Practices, Exception Reporting and Rendering, Force JSON Error Rendering for API Routes, Throttle High-Volume Exceptions, Use `ShouldntReport` for Exceptions That Should Never Log

### Community 88 - "Task Scheduling Best Practices"
Cohesion: 0.25
Nodes (7): Task Scheduling Best Practices, Use `environments()` to Restrict Tasks, Use `onOneServer()` on Multi-Server Deployments, Use `runInBackground()` for Concurrent Long Tasks, Use Schedule Groups for Shared Configuration, Use `takeUntilTimeout()` for Time-Bounded Processing, Use `withoutOverlapping()` on Variable-Duration Tasks

### Community 89 - "Testing Best Practices"
Cohesion: 0.25
Nodes (7): Call `Event::fake()` After Factory Setup, Testing Best Practices, Use `Exceptions::fake()` to Assert Exception Reporting, Use Factory States and Sequences, Use `LazilyRefreshDatabase` Over `RefreshDatabase`, Use Model Assertions Over Raw Database Assertions, Use `recycle()` to Share Relationship Instances Across Factories

### Community 90 - "ShuttlecockItem"
Cohesion: 0.13
Nodes (5): DeleteShuttlecockItemAction, ShuttlecockInventoryController, StoreShuttlecockItemRequest, UpdateShuttlecockItemRequest, ShuttlecockItem

### Community 91 - "FirebaseCloudMessaging.php"
Cohesion: 0.20
Nodes (9): Google\Auth\Credentials\ServiceAccountCredentials, Illuminate\Http\Client\ConnectionException, Illuminate\Http\Client\PendingRequest, Illuminate\Http\Client\RequestException, Illuminate\Support\Arr, Illuminate\Support\Facades\Log, JsonException, Minishlink\WebPush\Subscription (+1 more)

### Community 92 - "Collection Best Practices"
Cohesion: 0.29
Nodes (6): Choose `cursor()` vs. `lazy()` Correctly, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 93 - "HTTP Client Best Practices"
Cohesion: 0.29
Nodes (6): Always Set Explicit Timeouts, Fake HTTP Calls in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Use Request Pooling for Concurrent Requests, Use Retry with Backoff for External APIs

### Community 94 - "Mail Best Practices"
Cohesion: 0.29
Nodes (6): Implement `ShouldQueue` on the Mailable Class, Mail Best Practices, Separate Content Tests from Sending Tests, Use `afterCommit()` on Mailables Inside Transactions, Use `assertQueued()` Not `assertSent()` for Queued Mailables, Use Markdown Mailables for Transactional Emails

### Community 95 - "PRD NgeBadmintonYuk — baseline 15 September 2026"
Cohesion: 0.18
Nodes (11): 10. Sumber teknis dan hasil audit, 1. Ringkasan produk dan posisi proyek, 2. Masalah dan tujuan, 3. Pengguna dan akses, 4. Cakupan dan kriteria penerimaan, 5. Aturan bisnis yang berlaku, 6. Alur utama, 7. Kebutuhan nonfungsional dan batas verifikasi (+3 more)

### Community 96 - "Routing & Controllers Best Practices"
Cohesion: 0.29
Nodes (6): Keep Controllers Thin, Routing & Controllers Best Practices, Type-Hint Form Requests, Use Implicit Route Model Binding, Use Resource Controllers, Use Scoped Bindings for Nested Resources

### Community 97 - "Conventions & Style"
Cohesion: 0.29
Nodes (6): Conventions & Style, Follow Laravel Naming Conventions, No Inline JS/CSS in Blade, No Unnecessary Comments, Prefer Shorter Readable Syntax, Use Laravel String & Array Helpers

### Community 98 - "Validation & Forms Best Practices"
Cohesion: 0.29
Nodes (6): Always Use `validated()`, Array vs. String Notation for Rules, Use Form Request Classes, Use `Rule::when()` for Conditional Validation, Use the `after()` Method for Custom Validation, Validation & Forms Best Practices

### Community 100 - "FilterMemberReportRequest"
Cohesion: 0.24
Nodes (3): MemberReportController, FilterMemberReportRequest, Illuminate\Pagination\LengthAwarePaginator

### Community 101 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 102 - "SessionRegistration"
Cohesion: 0.13
Nodes (6): CreateGuestSessionRegistrationAction, CreateSessionRegistrationByAdminAction, UpdateSessionRegistrationRequest, Guest, SessionRegistration, Illuminate\Support\Facades\File

### Community 103 - "Color System"
Cohesion: 0.33
Nodes (6): Accent, Background, Color System, Primary, Surface, Text

### Community 104 - "Typography Hierarchy"
Cohesion: 0.33
Nodes (6): Body, Hero Number, Label, Page Title, Section Title, Typography Hierarchy

### Community 105 - "Actions Models Services Http"
Cohesion: 0.29
Nodes (6): Actions Models Services Http, No refund workflow, Push notifications are manual and synchronous, Session activity uses standard Web Push, Session activity uses standard Web Push, Top up cash and session quota settle without duplicate income

### Community 108 - "SessionRegistrationController.php"
Cohesion: 0.13
Nodes (8): MarkSessionRegistrationPresentAction, RecordAttendanceAction, RecordSessionRegistrationPaymentAction, UpdateGuestAttendanceAction, UpdateSessionRegistrationAction, AttendanceController, CancelSessionRegistrationRequest, StoreSessionRegistrationByAdminRequest

### Community 109 - "Actions Http"
Cohesion: 0.50
Nodes (3): Actions Http, Legacy FCM requires one-time reinstall, Players may cancel eligible registrations

### Community 110 - "Actions Models"
Cohesion: 0.50
Nodes (3): Actions Models, Membership credits use a ledger, Shuttlecock stock uses movements

### Community 111 - "Controllers"
Cohesion: 0.50
Nodes (3): Controllers, Member dashboard shows joined sessions only, Session indexes require month selection

### Community 112 - "Jsviews"
Cohesion: 0.50
Nodes (3): Jsviews, Keep mobile navigation actions reachable, Scoreboard remains device-local

### Community 114 - "Visual Signature"
Cohesion: 0.50
Nodes (4): 1. Cobalt Hero, 2. Yellow Motion Accent, 3. Editorial Transaction List, Visual Signature

### Community 115 - "Semantic Color"
Cohesion: 0.50
Nodes (4): Expense, Income, Semantic Color, Warning

### Community 118 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 119 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 120 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 121 - "10-project-status.md"
Cohesion: 0.15
Nodes (9): Project Rules Index, Acuan proyek saat ini — 7 September 2026, Arsip rancangan MVP awal, Referensi desain awal, Status branding — 7 September 2026, Arsip schema MVP awal, Database saat ini — 7 September 2026, Arsip pemeriksaan 6 September 2026 (+1 more)

### Community 148 - "Membership"
Cohesion: 0.07
Nodes (10): AdjustMembershipCreditAction, CreateTopUpRequestAction, DeleteMembershipAction, GrantMembershipAction, MembershipController, AdjustMembershipCreditRequest, StoreMembershipRequest, UpdateMembershipRequest (+2 more)

### Community 150 - "Aturan operasional yang tidak boleh terlewat"
Cohesion: 0.22
Nodes (9): Aturan operasional yang tidak boleh terlewat, Backlog yang tetap terbuka, Inventaris fitur, Inventori, push dan akses, Matriks fitur dan keselarasan proyek, Membership dan top-up, Perbedaan dengan dokumen lama yang diselesaikan, Sesi dan keuangan (+1 more)

### Community 151 - "Konteks final proyek NgeBadmintonYuk"
Cohesion: 0.18
Nodes (11): Alur dan batas integrasi, Identitas dan scope, Konteks final proyek NgeBadmintonYuk, Konteks siap dipakai untuk pekerjaan berikutnya, Pembaruan antarmuka: indikator proses server, Pembaruan terbaru: notifikasi wajib bagi member, Peta sumber, Riwayat verifikasi audit awal, sebelum tahap kas–kuota (+3 more)

### Community 156 - "Actions Services Http"
Cohesion: 0.33
Nodes (5): Actions Services Http, PRD timing formula supersedes target-game rotation rounds, Published session rotation follows the confirmed list, Rotation chooses set format instead of manual rounds, Rotation generation is a draft requiring explicit admin approval

### Community 168 - "TestCase"
Cohesion: 0.18
Nodes (4): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, static, TestCase

### Community 169 - "Illuminate\Validation\Rule"
Cohesion: 0.18
Nodes (4): UpdateAttendanceRequest, UpdatePlaySessionRequest, Illuminate\Validation\Rule, Illuminate\Validation\Validator

### Community 170 - "web.php"
Cohesion: 0.21
Nodes (4): CommunityProfileController, ProfileController, Illuminate\Support\Facades\Route, Symfony\Component\HttpFoundation\StreamedResponse

### Community 174 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.15
Nodes (6): AuthController, RegistrationController, TopUpSettingController, RegisterMemberRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Auth

### Community 212 - "PushNotificationTest.php"
Cohesion: 0.50
Nodes (3): Illuminate\Http\Client\Request, Illuminate\Support\Facades\Http, send()

## Knowledge Gaps
- **726 isolated node(s):** `composer install`, `Illuminate\\Foundation\\ComposerScripts::postAutoloadDump`, `Illuminate\\Foundation\\ComposerScripts::prePackageUninstall`, `@lint:check`, `npm install` (+721 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **39 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `PlaySessionController.php`, `CreateTopUpRequestAction.php`, `PushNotification`, `Membership`, `Illuminate\Database\Seeder`, `RotationScheduleService`, `UpdateMemberRequest`, `TestCase`, `Illuminate\Validation\Rule`, `web.php`, `PlaySession`, `StockMovement`, `Illuminate\Http\RedirectResponse`, `RecordStockMovementAction`, `TopUpRequest`, `Illuminate\Database\Eloquent\Factories\Factory`, `Illuminate\Validation\ValidationException`, `PushSubscription`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `TopUpRequestController.php`, `PushNotificationSender.php`, `TopUpSetting`, `SessionRegistration.php`, `Illuminate\Http\Request`, `AppServiceProvider.php`, `Illuminate\View\View`, `Symfony\Component\HttpFoundation\Response`, `PushNotificationTest.php`, `ShuttlecockItem`, `SessionRegistration`, `SessionRegistrationController.php`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **Why does `PlaySession` connect `PlaySession` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `PlaySessionController.php`, `Illuminate\Foundation\Http\FormRequest`, `CreateTopUpRequestAction.php`, `PushNotification`, `MarkSessionRegistrationPresentRequest`, `Illuminate\Database\Seeder`, `RotationScheduleService`, `Illuminate\Validation\Rule`, `Controller`, `User`, `Illuminate\Validation\ValidationException`, `PushSubscription`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `PushNotificationSender.php`, `SessionRegistration.php`, `StorePlaySessionRequest`, `Illuminate\Http\Request`, `Illuminate\View\View`, `PushNotificationTest.php`, `ShuttlecockItem`, `SessionRegistration`, `SessionRegistrationController.php`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Why does `SessionRegistration` connect `SessionRegistration` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `PlaySessionController.php`, `SaveGuestRequest`, `StoreSessionRegistrationRequest`, `RotationScheduleService`, `SessionRegistration.php`, `Illuminate\Foundation\Http\FormRequest`, `SessionRegistrationController.php`, `PlaySession`, `Illuminate\Http\Request`, `CreateTopUpRequestAction.php`, `PushNotification`, `PushNotificationTest.php`, `Illuminate\Validation\ValidationException`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `MarkSessionRegistrationPresentRequest`?**
  _High betweenness centrality (0.015) - this node is a cross-community bridge._
- **What connects `composer install`, `Illuminate\\Foundation\\ComposerScripts::postAutoloadDump`, `Illuminate\\Foundation\\ComposerScripts::prePackageUninstall` to the rest of the system?**
  _726 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `scripts` be split into smaller, more focused modules?**
  _Cohesion score 0.057057057057057055 - nodes in this community are weakly interconnected._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.0625 - nodes in this community are weakly interconnected._
- **Should `app.js` be split into smaller, more focused modules?**
  _Cohesion score 0.055299539170506916 - nodes in this community are weakly interconnected._